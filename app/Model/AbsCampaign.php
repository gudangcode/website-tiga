<?php

/**
 * Abstract Campaign class.
 *
 * Model class for campaigns related functionalities.
 * This is the center of the application
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Carbon\Carbon;
use League\Csv\Writer;
use Acelle\Library\ExtendedSwiftMessage;
use Acelle\Library\Log as MailLog;
use Acelle\Library\StringHelper;
use Acelle\Library\Tool;
use Acelle\Library\Rss;
use Acelle\Model\Setting;
use Validator;
use DB;
use File;
use ZipArchive;

abstract class AbsCampaign extends Model
{
    // Campaign status
    const STATUS_NEW = 'new';
    const STATUS_READY = 'ready'; // equiv. to 'queue'
    const STATUS_SENDING = 'sending';
    const STATUS_ERROR = 'error';
    const STATUS_DONE = 'done';
    const STATUS_PAUSED = 'paused';

    // Campaign types
    const TYPE_REGULAR = 'regular';
    const TYPE_PLAIN_TEXT = 'plain-text';

    // Campaign settings
    const WORKER_DELAY = 1;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'run_at'];

    // parse content
    protected $parsedContent = null;

    /**
     * Get campaign's default mail list.
     */
    public function defaultMailList()
    {
        return $this->belongsTo('Acelle\Model\MailList', 'default_mail_list_id');
    }

    /**
     * Get campaign's associated mail list.
     */
    public function mailLists()
    {
        return $this->belongsToMany('Acelle\Model\MailList', 'campaigns_lists_segments');
    }

    /**
     * Get campaign's associated tracking domain.
     */
    public function trackingDomain()
    {
        return $this->belongsTo('Acelle\Model\TrackingDomain', 'tracking_domain_id');
    }

    /**
     * Get campaign validation rules.
     */
    public function rules($request=null)
    {
        $rules = array(
            'name' => 'required',
            'subject' => 'required',
            'from_email' => 'required|email',
            'from_name' => 'required',
            'reply_to' => 'required|email',
        );

        if ($this->use_default_sending_server_from_email) {
            $rules['from_email'] = 'email';
        } else {
            $rules['from_email'] = 'required|email';
        }

        // tracking domain
        if (isset($request) && $request->custom_tracking_domain) {
            $rules['tracking_domain_uid'] = 'required';
        }

        return $rules;
    }

    /**
     * Get the links for campaign.
     */
    public function links()
    {
        return $this->belongsToMany('Acelle\Model\Link', 'campaign_links');
    }

    /**
     * Get the customer.
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Get campaign tracking logs.
     *
     * @return mixed
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog');
    }

    /**
     * Get campaign bounce logs.
     *
     * @return mixed
     */
    public function bounceLogs()
    {
        return BounceLog::select('bounce_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'bounce_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign open logs.
     *
     * @return mixed
     */
    public function openLogs()
    {
        return OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign click logs.
     *
     * @return mixed
     */
    public function clickLogs()
    {
        return ClickLog::select('click_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign feedback loop logs.
     *
     * @return mixed
     */
    public function feedbackLogs()
    {
        return FeedbackLog::select('feedback_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'feedback_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign unsubscribe logs.
     *
     * @return mixed
     */
    public function unsubscribeLogs()
    {
        return UnsubscribeLog::select('unsubscribe_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'unsubscribe_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign list segment.
     *
     * @return mixed
     */
    public function listsSegments()
    {
        return $this->hasMany('Acelle\Model\CampaignsListsSegment');
    }

    /**
     * Get campaign lists segments.
     *
     * @return mixed
     */
    public function getListsSegments()
    {
        $lists_segments = $this->listsSegments;

        if ($lists_segments->isEmpty()) {
            $lists_segment = new CampaignsListsSegment();
            $lists_segment->campaign_id = $this->id;
            $lists_segment->is_default = true;

            $lists_segments->push($lists_segment);
        }

        return $lists_segments;
    }

    /**
     * Get campaign lists segments group by list.
     *
     * @return mixed
     */
    public function getListsSegmentsGroups()
    {
        $lists_segments = $this->getListsSegments();
        $groups = [];

        foreach ($lists_segments as $lists_segment) {
            if (!isset($groups[$lists_segment->mail_list_id])) {
                $groups[$lists_segment->mail_list_id] = [];
                $groups[$lists_segment->mail_list_id]['list'] = $lists_segment->mailList;
                if ($this->default_mail_list_id == $lists_segment->mail_list_id) {
                    $groups[$lists_segment->mail_list_id]['is_default'] = true;
                } else {
                    $groups[$lists_segment->mail_list_id]['is_default'] = false;
                }
                $groups[$lists_segment->mail_list_id]['segment_uids'] = [];
            }
            if (is_object($lists_segment->segment) && !in_array($lists_segment->segment->uid, $groups[$lists_segment->mail_list_id]['segment_uids'])) {
                $groups[$lists_segment->mail_list_id]['segment_uids'][] = $lists_segment->segment->uid;
            }
        }

        return $groups;
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @return MIME text message
     */
    public function prepareEmail($subscriber, $server = null)
    {
        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Acelle-Message-Id'];

        $message = new ExtendedSwiftMessage();
        $message->setId($msgId);

        if ($this->type == self::TYPE_REGULAR) {
            $message->setContentType('text/html; charset=utf-8');
        } else {
            $message->setContentType('text/plain; charset=utf-8');
        }

        foreach ($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        if (!is_null($server) && $server->allowCustomReturnPath()) {
            $returnPath = $server->getVerp($subscriber->email);
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }
        }
        $message->setSubject($this->getSubject($subscriber, $msgId));

        if ($this->useSendingServerFromEmailAddress()) {
            $message->setFrom($server->default_from_email);
        } else {
            $message->setFrom(array($this->from_email => $this->from_name));
        }

        $message->setTo($subscriber->email);
        $message->setReplyTo($this->reply_to);
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($this->getPlainContent($subscriber, $msgId, $server), 'text/plain');
        if ($this->type == self::TYPE_REGULAR) {
            $message->addPart($this->getHtmlContent($subscriber, $msgId, $server), 'text/html');
        }

        if ($this->sign_dkim) {
            $message = $this->sign($message);
        }

        //@todo attach function used for any attachment of Campaign
        $path_campaign = $this->getAttachmentPath();
        if (is_dir($path_campaign)) {
            $files = File::allFiles($path_campaign);
            foreach ($files as $file) {
                $attachment = \Swift_Attachment::fromPath((string) $file);
                $message->attach($attachment);
                // This is used by certain delivery services like ElasticEmail
                $message->extAttachments[] = [ 'path' => (string) $file, 'type' => $attachment->getContentType()];
            }
        }

        return array($message, $msgId);
    }

    /**
     * Check if the campaign setting is "use sending server's FROM email address".
     *
     * @return mixed
     */
    private function useSendingServerFromEmailAddress()
    {
        return $this->use_default_sending_server_from_email == true;
    }

    /**
     * Reset max_execution_time so that command can run for a long time without being terminated.
     *
     * @return mixed
     */
    public static function resetMaxExecutionTime()
    {
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
        } catch (\Exception $e) {
            MailLog::warning('Cannot reset max_execution_time: '.$e->getMessage());
        }
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function done()
    {
        $this->status = self::STATUS_DONE;
        $this->save();
    }

    /**
     * Mark the campaign as 'sending'.
     */
    public function sending()
    {
        $this->status = self::STATUS_SENDING;
        $this->running_pid = getmypid();
        $this->delivery_at = \Carbon\Carbon::now();
        $this->save();
    }

    /**
     * Check if the campaign is in the "SENDING" status;.
     */
    public function isSending()
    {
        return $this->status == self::STATUS_SENDING;
    }

    /**
     * Check if the campaign is in the "DONE" status;.
     */
    public function isDone()
    {
        return $this->status == self::STATUS_DONE;
    }

    /**
     * Check if the campaign is ready to start.
     */
    public function isReady()
    {
        return $this->status == self::STATUS_READY;
    }

    /**
     * Mark the campaign as 'ready' (which is equiv. to 'queued').
     */
    public function ready()
    {
        $this->status = self::STATUS_READY;
        $this->save();
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function error($error = null)
    {
        $this->status = self::STATUS_ERROR;
        $this->last_error = $error;
        $this->save();
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function refreshStatus()
    {
        $me = self::find($this->id);
        $this->status = $me->status;
        $this->save();

        return $this;
    }

    /**
     * Log delivery message, used for later tracking.
     */
    public function trackMessage($response, $subscriber, $server, $msgId)
    {
        // @todo: customerneedcheck
        $params = array_merge(array(
                'campaign_id' => $this->id,
                'message_id' => $msgId,
                'subscriber_id' => $subscriber->id,
                'sending_server_id' => $server->id,
                'customer_id' => $this->customer->id,
            ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }

        // create tracking log for message
        TrackingLog::create($params);

        // increment customer quota usage
        $this->customer->countUsage();
        $server->countUsage();
    }

    /**
     * Get tagged Subject.
     *
     * @return string
     */
    public function getSubject($subscriber, $msgId)
    {
        return $this->tagMessage($this->subject, $subscriber, $msgId, null);
    }

    /**
     * Append footer.
     *
     * @return string.
     */
    public function appendFooter($body, $footer)
    {
        return $body.$footer;
    }

    /**
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);
        $url = $this->buildTrackingUrl($path);

        return $body.'<img src="'.$url.'" width="0" height="0" alt="" style="visibility:hidden" />';
    }

    /**
     * Build Email Custom Headers.
     *
     * @return Hash list of custom headers
     */
    public function getCustomHeaders($subscriber, $server)
    {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from_email));

        return array(
            'X-Acelle-Campaign-Id' => $this->uid,
            'X-Acelle-Subscriber-Id' => $subscriber->uid,
            'X-Acelle-Customer-Id' => $this->customer->uid,
            'X-Acelle-Message-Id' => $msgId,
            'X-Acelle-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<'.$this->generateUnsubscribeUrl($msgId, $subscriber).'>',
            'Precedence' => 'bulk',
        );
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getHtmlContent($subscriber = null, $msgId = null, $server = null)
    {
        // @note: IMPORTANT: the order must be as follows
        // * addTrackingURL
        // * appendOpenTrackingUrl
        // * tagMessage
        if (is_null($this->parsedContent)) {
            // STEP 01. Get RAW content
            $body = $this->html;

            // STEP 02. Append footer
            if ($this->footerEnabled()) {
                $body = $this->appendFooter($body, $this->getHtmlFooter());
            }
            
            // STEP 03. Parse RSS
            if (Setting::isYes('rss.enabled')) {
                $body = Rss::parse($body);
            }

            // STEP 04. Replace Bare linefeed
            // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
            $body = StringHelper::replaceBareLineFeed($body);

            // "Cache" it, do not repeat this task for every subscriber in the loop
            $this->parsedContent = $body;
        } else {
            // Retrieve content from cache
            $body = $this->parsedContent;
        }

        // STEP 05. Transform URLs
        $body = $this->transformWithoutUpdate($body, true);

        // STEP 05.1. Remove title tag from html
        $body = strip_tags_only($body, 'title');

        if (is_null($msgId)) {
            $msgId = 'SAMPLE'; // for preview mode
        }

        // STEP 06. Add Click Tracking
        //
        // @note: addTrackingUrl() must go before appendOpenTrackingUrl()
        // Enable click tracking
        if ($this->track_click) {
            $body = $this->addTrackingUrl($body, $msgId);
        }

        // STEP 07. Add Open Tracking
        if ($this->track_open) {
            $body = $this->appendOpenTrackingUrl($body, $msgId);
        }

        // STEP 08. Transform Tags
        if (!is_null($subscriber)) {
            // Transform tags
            $body = $this->tagMessage($body, $subscriber, $msgId, $server);
        }

        // STEP 09. Make CSS inline
        //
        // Transform CSS/HTML content to inline CSS
        // Be carefule, it will make
        //       <a href="{BUNSUBSCRIBE_URL}" 
        // become
        //       <a href="%7BUNSUBSCRIBE_URL%7D" 
        $body = $this->inlineHtml($body);

        

        return $body;
    }

    /**
     * Check if email footer enabled.
     *
     * @return string
     */
    public function footerEnabled()
    {
        return ($this->customer->getCurrentSubscription()->plan->getOption('email_footer_enabled') == 'yes') ? true : false;
    }

    /**
     * Get HTML footer.
     *
     * @return string
     */
    public function getHtmlFooter()
    {
        return $this->customer->getCurrentSubscription()->plan->getOption('html_footer');
    }

    /**
     * Get PLAIN TEXT footer.
     *
     * @return string
     */
    public function getPlainTextFooter()
    {
        return $this->customer->getCurrentSubscription()->plan->getOption('plain_text_footer');
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getPlainContent($subscriber, $msgId, $server = null)
    {
        $plain = $this->tagMessage($this->plain, $subscriber, $msgId, $server);

        // Append footer
        if ($this->footerEnabled()) {
            $plain = $this->appendFooter($plain, $this->getPlainTextFooter());
        }

        // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
        $plain = StringHelper::replaceBareLineFeed($plain);

        return $plain;
    }

    /**
     * Find sending domain from email.
     *
     * @return mixed
     */
    public function findSendingDomain($email)
    {
        $domainName = substr(strrchr($email, '@'), 1);

        if ($domainName == false) {
            return;
        }

        $domain = $this->customer->activeDkimSendingDomains()->where('name', $domainName)->first();
        if (is_null($domain)) {
            $domain = SendingDomain::getAllAdminActive()->where('name', $domainName)->first();
        }

        return $domain;
    }

    /**
     * Sign the message with DKIM.
     *
     * @return mixed
     */
    public function sign($message)
    {
        $sendingDomain = $this->findSendingDomain($this->from_email);

        if (empty($sendingDomain)) {
            return $message;
        }

        $privateKey = $sendingDomain->dkim_private;
        $domainName = $sendingDomain->name;
        $selector = $sendingDomain->getDkimSelectorParts()[0];
        $signer = new \Swift_Signers_DKIMSigner($privateKey, $domainName, $selector);
        $signer->ignoreHeader('Return-Path');
        $message->attachSigner($signer);

        return $message;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject', 'from_name', 'from_email',
        'reply_to', 'track_open',
        'track_click', 'sign_dkim', 'track_fbl',
        'html', 'plain', 'template_source',
        'tracking_domain_id', 'use_default_sending_server_from_email',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'mail_list_uid' => 'required',
    );

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('campaigns.*');
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions($customer = null, $status = null)
    {
        $query = self::getAll();
        if (is_object($customer)) {
            $query = $query->where('customer_id', '=', $customer->id);
        }
        if (isset($status)) {
            $query = $query->where('status', '=', $status);
        }
        $options = $query->orderBy('created_at', 'DESC')->get()->map(function ($item) {
            return ['value' => $item->uid, 'text' => $item->name];
        });

        return $options;
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (Campaign::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Update custom order
            Campaign::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });

        // Created
        static::created(function ($item) {
            // Update links
            $item->updateLinks();
        });

        static::updating(function ($item) {
            // Update links
            $item->updateLinks();
        });

        static::deleted(function ($item) {
            $uploaded_dir = $item->getStoragePath();
            \Acelle\Library\Tool::xdelete($uploaded_dir);
        });
    }

    /**
     * Get current links of campaign.
     */
    public function getLinks()
    {
        return $this->links()->whereIn('url', $this->getUrls())->get();
    }

    /**
     * Get urls from campaign html.
     */
    public function getUrls()
    {
        // Find all links in campaign content
        preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $this->html, $matches);
        $hrefs = array_unique($matches['url']);

        $urls = [];
        foreach ($hrefs as $href) {
            if (preg_match('/^http/i', $href) && strpos($href, '{UNSUBSCRIBE_URL}') === false) {
                $urls[] = strtolower(trim($href));
            }
        }

        return $urls;
    }

    /**
     * Update campaign links.
     */
    public function updateLinks()
    {
        foreach ($this->getUrls() as $url) {
            $link = Link::where('url', '=', $url)->first();
            if (!is_object($link)) {
                $link = new Link();
                $link->url = $url;
                $link->save();
            }

            // Campaign link
            if ($this->links()->where('url', '=', $url)->count() == 0) {
                $cl = new CampaignLink();
                $cl->campaign_id = $this->id;
                $cl->link_id = $link->id;
                $cl->save();
            }
        }
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * CHeck UNSUBSCRIBE_URL.
     *
     * @return object
     */
    public function unsubscribe_url_valid()
    {
        if ($this->type != 'plain-text' &&
           \Auth::user()->customer->getOption('unsubscribe_url_required') == 'yes' &&
            strpos($this->html, '{UNSUBSCRIBE_URL}') == false
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get max step.
     *
     * @return object
     */
    public function step()
    {
        $step = 0;

        // Step 1
        if (is_object($this->defaultMailList)) {
            $step = 1;
        } else {
            return $step;
        }

        // Step 2
        if (!empty($this->name) && !empty($this->subject) && !empty($this->from_name)
                && !empty($this->from_email) && !empty($this->reply_to)) {
            $step = 2;
        } else {
            return $step;
        }

        // Step 3
        // if ((!empty($this->html) || $this->type == 'plain-text') && !empty($this->plain) && $this->unsubscribe_url_valid()) {
        if ((!empty($this->html) || $this->type == 'plain-text') && !empty($this->plain)) {
            $step = 3;
        } else {
            return $step;
        }

        // Step 4
        if (isset($this->run_at) && $this->run_at != '0000-00-00 00:00:00') {
            $step = 4;
        } else {
            return $step;
        }

        // Step 5
        // @todo: consider removing this check!
        if (is_object($this->subscribers([], ['email_verifications'])->limit(1)->first())) {
            $step = 5;
        } else {
            return $step;
        }

        return $step;
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $customer = $request->user()->customer;
        $query = self::where('customer_id', '=', $customer->id);

        // Get campaign from ... (all|normal|automated)
        if ($request->source == 'template') {
            $query = $query->where('html', '!=', null);
        } else {
            $query = $query->where('is_auto', '=', false);
        }

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        // Status
        if (!empty(trim($request->statuses))) {
            $query = $query->whereIn('status', explode(',', $request->statuses));
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * Create customer action log.
     *
     * @param string   $cat
     * @param Customer $customer
     * @param array    $add_datas
     */
    public function log($name, $customer, $add_datas = [])
    {
        $data = [
                'id' => $this->id,
                'name' => $this->name,
        ];

        if (is_object($this->defaultMailList)) {
            $data['list_id'] = $this->default_mail_list_id;
            $data['list_name'] = $this->defaultMailList->name;
        }

        if (is_object($this->segment)) {
            $data['segment_id'] = $this->segment_id;
            $data['segment_name'] = $this->segment->name;
        }

        $data = array_merge($data, $add_datas);

        \Acelle\Model\Log::create([
                                'customer_id' => $customer->id,
                                'type' => 'campaign',
                                'name' => $name,
                                'data' => json_encode($data),
                            ]);
    }

    /**
     * Count delivery processed.
     *
     * @return number
     */
    public function trackingCount()
    {
        return $this->trackingLogs()->count();
    }

    /**
     * Count delivery processed.
     *
     * @return number
     */
    public function deliveredCount()
    {
        // including bounced, feedbcak...
        return $this->trackingLogs()->where('status', '!=', TrackingLog::STATUS_FAILED)->count();
    }

    /**
     * Count failed processed.
     *
     * @return number
     */
    public function failedCount()
    {
        return $this->trackingLogs()->where('status', '=', TrackingLog::STATUS_FAILED)->count();
    }

    /**
     * Count failed processed.
     *
     * @return number
     */
    public function notDeliveredCount()
    {
        $subscribersCountUniq = distinctCount($this->subscribers([], ['email_verifications']), 'subscribers.email');
        return $subscribersCountUniq - $this->deliveredCount();
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function deliveredRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->deliveredCount() / $total;
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function failedRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->failedCount() / $total;
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function notDeliveredRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->notDeliveredCount() / $total;
    }

    /**
     * Count click.
     *
     * @return number
     */
    public function clickCount($start = null, $end = null)
    {
        $query = $this->clickLogs();

        if (isset($start)) {
            $query = $query->where('click_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('click_logs.created_at', '<=', $end);
        }

        return $query->count();
    }

    /**
     * Url count.
     *
     * @return number
     */
    public function urlCount()
    {
        return $this->links()->count();
    }

    /**
     * Click rate.
     *
     * @return number
     */
    public function clickedLinkCount()
    {
        return $this->clickLogs()->distinct('url')->count('url');
    }

    /**
     * Click rate.
     *
     * @return number
     */
    public function clickRate()
    {
        $url_count = $this->urlCount();

        if ($url_count == 0) {
            return 0;
        }

        return round(($this->clickedLinkCount() / $url_count), 2);
    }

    /**
     * Count unique clicked opened emails.
     *
     * @return number
     */
    public function clickedEmailsCount()
    {
        $query = $this->clickLogs();

        return $query->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Click a link rate.
     *
     * @return number
     */
    public function clickALinkRate()
    {
        $open_count = $this->openCount();

        if ($open_count == 0) {
            return 0;
        }

        return $this->clickCount() / $open_count;
    }

    /**
     * Clicked emails count.
     *
     * @return number
     */
    public function clickedEmailsRate()
    {
        $open_count = $this->openUniqCount();

        if ($open_count == 0) {
            return 0;
        }

        return $this->clickedEmailsCount() / $open_count;
    }

    /**
     * Count click.
     *
     * @return number
     */
    public function clickPerUniqOpen()
    {
        $open_count = $this->openCount();

        if ($open_count == 0) {
            return 0;
        }

        return $this->clickCount() / $open_count;
    }

    /**
     * Count abuse feedback.
     *
     * @return number
     */
    public function abuseFeedbackCount()
    {
        return $this->feedbackLogs()->where('feedback_type', '=', 'abuse')->count();
    }

    /**
     * Count open.
     *
     * @return number
     */
    public function openCount()
    {
        return $this->openLogs()->count();
    }

    /**
     * Not open count.
     *
     * @return number
     */
    public function notOpenCount($cache = false)
    {
        return $this->subscribersCount($cache) - $this->openUniqCount();
    }

    /**
     * Count unique open.
     *
     * @return number
     */
    public function openUniqCount($start = null, $end = null)
    {
        $query = $this->openLogs();
        if (isset($start)) {
            $query = $query->where('open_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('open_logs.created_at', '<=', $end);
        }

        return $query->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Open rate.
     *
     * @return number
     */
    public function openRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->openCount() / $delivered_count;
    }

    /**
     * Not open rate.
     *
     * @return number
     */
    public function notOpenRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->notOpenCount($cache) / $total;
    }

    /**
     * Count unique open rate.
     *
     * @return number
     */
    public function openUniqRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->openUniqCount() / $delivered_count;
    }

    /**
     * Count bounce back.
     *
     * @return number
     */
    public function feedbackCount()
    {
        return $this->feedbackLogs()->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count feedback rate.
     *
     * @return number
     */
    public function feedbackRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->feedbackCount() / $delivered_count;
    }

    /**
     * Count bounce back.
     *
     * @return number
     */
    public function bounceCount()
    {
        return $this->bounceLogs()->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count bounce rate.
     *
     * @return number
     */
    public function bounceRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->bounceCount() / $delivered_count;
    }

    /**
     * Count hard bounce.
     *
     * @return number
     */
    public function hardBounceCount()
    {
        return $this->campaign_bounce_logs()->where('bounce_type', '=', 'hard')->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count hard bounce rate.
     *
     * @return number
     */
    public function hardBounceRate()
    {
        $delivered_processed_count = $this->deliveryProcessedCount();

        if ($delivered_processed_count == 0) {
            return 0;
        }

        return $this->hardBounceCount() / $delivered_processed_count;
    }

    /**
     * Count soft bounce.
     *
     * @return number
     */
    public function softBounceCount()
    {
        return $this->campaign_bounce_logs()->where('bounce_type', '=', 'soft')->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count soft bounce rate.
     *
     * @return number
     */
    public function softBounceRate()
    {
        $tracking_count = $this->trackingCount();

        if ($tracking_count == 0) {
            return 0;
        }

        return $this->softBounceCount() / $tracking_count;
    }

    /**
     * Count unsubscibe.
     *
     * @return number
     */
    public function unsubscribeCount()
    {
        return $this->unsubscribeLogs()->distinct('unsubscribe_logs.subscriber_id')->count();
    }

    /**
     * Count unsubscibe rate.
     *
     * @return number
     */
    public function unsubscribeRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->unsubscribeCount() / $delivered_count;
    }

    /**
     * Get last click.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastClick()
    {
        return $this->clickLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get last open.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastOpen()
    {
        return $this->openLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get last open list.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastOpens($number)
    {
        return $this->openLogs()->orderBy('created_at', 'desc')->limit($number);
    }

    /**
     * Get most open subscribers.
     *
     * @param number $number
     *
     * @return collect
     */
    public function mostOpenSubscribers($number)
    {
        return \Acelle\Web\Subscriber::selectRaw(DB::getTablePrefix().'list_subscriber.*, COUNT('.DB::getTablePrefix().'campaign_track_unsubscribe.id) AS openCount')
                            ->leftJoin('campaign_track_unsubscribe', 'campaign_track_unsubscribe.subscriber_id', '=', 'list_subscriber.subscriber_id')
                            ->where('campaign_track_unsubscribe.campaign_id', '=', $this->campaign_id)
                            ->groupBy('list_subscriber.subscriber_id')
                            ->orderBy('openCount', 'desc')
                            ->limit($number);
    }

    /**
     * Get last opened time.
     *
     * @return datetime
     */
    public function getLastOpen()
    {
        $last = $this->campaign_track_opens()->orderBy('created_at', 'desc')->first();

        return is_object($last) ? $last->created_at : null;
    }

    /**
     * Campaign top 5 opens.
     *
     * @return datetime
     */
    public static function topOpens($number = 5, $customer = null)
    {
        $records = self::select(DB::raw(DB::getTablePrefix().'campaigns.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaigns.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('is_auto', false);

        if (isset($customer)) {
            $records = $records->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('campaigns.id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public static function topClicks($number = 5, $customer = null)
    {
        $records = self::select(DB::raw(DB::getTablePrefix().'campaigns.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaigns.id')
            ->join('click_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('is_auto', false);

        if (isset($customer)) {
            $records = $records->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('campaigns.id')
                    ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public static function topLinks($number = 5, $customer = null)
    {
        $records = Link::select(DB::raw(DB::getTablePrefix().'links.*, count(*) as `aggregate`'))
            ->join('campaign_links', 'campaign_links.link_id', '=', 'links.id')
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaign_links.campaign_id')
            ->join('click_logs', function ($join) {
                $join->on('click_logs.message_id', '=', 'tracking_logs.message_id')
                ->on('click_logs.url', '=', 'links.url');
            });

        if (isset($customer)) {
            $records = $records->join('campaigns', 'campaign_links.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('links.id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public function getTopLinks($number = 5)
    {
        $records = ClickLog::select(DB::raw(DB::getTablePrefix().'click_logs.*, count(*) as `aggregate`'))
            ->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        $records = $records->groupBy('click_logs.url')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public function getTopOpenSubscribers($number = 5)
    {
        $records = Subscriber::select(DB::raw(DB::getTablePrefix().'subscribers.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('campaign_id', '=', $this->id);

        $records = $records->groupBy('tracking_logs.message_id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Recent subscriber opens.
     *
     * @return datetime
     */
    public function getRecentOpenSubscribers($number = 5)
    {
        $records = Subscriber::select(DB::raw(DB::getTablePrefix().'subscribers.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('campaign_id', '=', $this->id);

        $records = $records->groupBy('tracking_logs.message_id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 open location.
     *
     * @return datetime
     */
    public function topLocations($number = 5, $customer = null)
    {
        $records = IpLocation::select(DB::raw(DB::getTablePrefix().'ip_locations.*, count(*) as `aggregate`'))
            ->join('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($customer)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('open_logs.ip_address')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 open countries.
     *
     * @return datetime
     */
    public function topCountries($number = 5, $customer = null)
    {
        $records = IpLocation::select(DB::raw(DB::getTablePrefix().'ip_locations.*, count(*) as `aggregate`'))
            ->join('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($customer)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('ip_locations.country_name')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 click countries.
     *
     * @return datetime
     */
    public function topClickCountries($number = 5, $customer = null)
    {
        $records = IpLocation::select(DB::raw(DB::getTablePrefix().'ip_locations.*, count(*) as `aggregate`'))
            ->join('click_logs', 'click_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($customer)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('ip_locations.country_name')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    public function generateUnsubscribeUrl($msgId, $subscriber)
    {
        // in case of a fake object, for sending test email
        if (is_a($subscriber, 'stdClass')) {
            $path = route('unsubscribeUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

            return $path;
        }

        // OPTION 1: immediately opt out
        $path = route('unsubscribeUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        // OPTION 2: unsubscribe form, @IMPORTANT: it does not produce tracking log!!
        //$path = route('unsubscribeForm', ['list_uid' => $subscriber->mailList->uid, 'code' => $subscriber->getSecurityToken('unsubscribe'), 'uid' => $subscriber->uid], false);

        return $this->buildPublicUrl($path);
    }

    public function generateWebViewerUrl($msgId)
    {
        $path = route('webViewerUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        return $this->buildPublicUrl($path);
    }

    public function generateUpdateProfileUrl($subscriber)
    {
        $path = route('updateProfileUrl', ['list_uid' => $this->defaultMailList->uid, 'uid' => $subscriber->uid, 'code' => $subscriber->getSecurityToken('update-profile')], false);

        return $this->buildPublicUrl($path);
    }

    public function buildTrackingUrl($path)
    {
        $host = $this->getTrackingHost();

        return join_url($host, $path);
    }

    public function buildPublicUrl($path)
    {
        return join_url($this->getTrackingHost(), $path);
    }

    public function getTrackingHost()
    {
        if ($this->trackingDomain()->exists()) {
            return $this->trackingDomain->getUrl();
        } else {
            return config('app.url');
        }
    }

    /**
     * Campaign locations.
     *
     * @return datetime
     */
    public function locations()
    {
        $records = IpLocation::select('ip_locations.*', 'open_logs.created_at as open_at', 'subscribers.email as email')
            ->leftJoin('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->leftJoin('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->leftJoin('subscribers', 'subscribers.id', '=', 'tracking_logs.subscriber_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        return $records;
    }

    /**
     * Replace link in text by click tracking url.
     *
     * @return text
     * @note addTrackingUrl() must go before appendOpenTrackingUrl()
     */
    public function addTrackingUrl($email_html_content, $msgId)
    {
        if (preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $email_html_content, $matches)) {
            foreach ($matches[0] as $key => $href) {
                $url = $matches['url'][$key];

                $newUrl = route('clickTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId), 'url' => StringHelper::base64UrlEncode($url)], false);
                $newUrl = $this->buildTrackingUrl($newUrl);
                $newHref = str_replace($url, $newUrl, $href);

                // if the link contains UNSUBSCRIBE URL tag
                if (strpos($href, '{UNSUBSCRIBE_URL}') !== false) {
                    // just do nothing
                } elseif (preg_match('/{[A-Z0-9_]+}/', $href)) {
                    // just skip if the url contains a tag. For example: {UPDATE_PROFILE_URL}
                    // @todo: do we track these clicks?
                } else {
                    $email_html_content = str_replace($href, $newHref, $email_html_content);
                }
            }
        }

        return $email_html_content;
    }

    /**
     * Type of campaigns.
     *
     * @return object
     */
    public static function types()
    {
        return [
            'regular' => [
                'icon' => 'icon-magazine',
            ],
            'plain-text' => [
                'icon' => 'icon-file-text2',
            ],
        ];
    }

    /**
     * Copy new campaign.
     */
    public function copy($name)
    {
        $copy = $this->replicate(['cache', 'last_error', 'run_at']);
        $copy->name = $name;
        $copy->created_at = \Carbon\Carbon::now();
        $copy->updated_at = \Carbon\Carbon::now();
        $copy->status = self::STATUS_NEW;
        $copy->custom_order = 0;
        $copy->save();

        // Lists segments
        foreach ($this->listsSegments as $lists_segment) {
            $new_lists_segment = $lists_segment->replicate();
            $new_lists_segment->campaign_id = $copy->id;
            $new_lists_segment->save();
        }

        // refresh to update cache (otherwise, list-segment information will not be available yet)
        $newCampaign = Campaign::find($copy->id);
        $newCampaign->updateCache();

        // copy template
        \Acelle\Library\Tool::xcopy($this->getStoragePath(), $newCampaign->getStoragePath());
        $newCampaign->html = $this->html;
        $newCampaign->save();

        return $newCampaign;
    }

    /**
     * Check if template is layout.
     *
     * @return string.
     */
    public function isLayout($html = null)
    {
        return $html;

        // TODO: review the stuffs below
        if (!$html) {
            $html = $this->render();
        }

        if (strpos($html, 'AcelleSystemLayouts') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Convert html to inline.
     *
     * @todo not very OOP here, consider moving this to a Helper instead
     */
    public function inlineHtml($html)
    {
        // Convert to inline css if template source is builder
        if ($this->isLayout($html)) {
            $cssToInlineStyles = new CssToInlineStyles();
            $css = file_get_contents(public_path('assets2/css/email/main.css'));
            // output
            $html = $cssToInlineStyles->convert(
                $html,
                $css
            );
        }

        return $html;
    }

    /**
     * Send a test email for testing campaign.
     */
    public function sendTestEmail($email)
    {
        try {
            MailLog::info('Sending test email for campaign `'.$this->name.'`');
            MailLog::info('Sending test email to `'.$email.'`');

            // @todo: only send a test message when campaign sufficient information is available

            // build a temporary subscriber oject used to pass through the sending methods
            $subscriber = $this->createStdClassSubscriber(['email' => $email]);

            // Pick up an available sending server
            // Throw exception in case no server available
            $server = $this->pickSendingServer();

            // build the message from campaign information
            list($message, $msgId) = $this->prepareEmail($subscriber, $server);
            //print_r($message);
            //die;
            // actually send
            // @todo consider using queue here
            $result = $server->send($message);

            // examine the result from sending server
            if (array_has($result, 'error')) {
                throw new \Exception($result['error']);
            }

            return [
                'status' => 'success',
                'message' => trans('messages.campaign.test_sent'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the delay time before sending.
     */
    public function getDelayInSeconds()
    {
        $now = Carbon::now();

        if ($now->gte($this->run_at)) {
            return 0;
        } else {
            return $this->run_at->diffInSeconds($now);
        }
    }

    /**
     * Re-queue the campaign for sending.
     */
    public function requeue()
    {
        // clear all campaign's sending jobs which are being queued
        $this->clearAllJobs();

        // and queue again
        $this->queue();
    }

    /**
     * Re-send the campaign for sending.
     */
    public function resend($filter = 'not_receive') // not_receive | not_open | not_click
    {
        // clean up failed log so that they will be included in resend
        switch ($filter) {
            case 'not_receive':
                $this->cleanupFailedLog();
                break;
            case 'not_open':
                $this->cleanupFailedLog();
                $this->cleanupNotOpenLog();
                break;
            case 'not_click':
                $this->cleanupFailedLog();
                $this->cleanupNotClickLog();
                break;
            default:
                throw new \Exception("Unknown campaign RESEND type: ".$filter);
                break;
        }

        // and queue again
        $this->requeue();
    }

    /**
     * Re-send the campaign for sending.
     */
    public function cleanupFailedLog()
    {
        // clean up failed log so that they will be included in resend
        $recipients = $this->trackingLogs()->where('status', SendingServer::DELIVERY_STATUS_FAILED);
        MailLog::warning('Resend to those who failed to deliver: '.$recipients->count());
        $recipients->delete();
    }

    public function cleanupNotOpenLog()
    {
        // clean up failed log so that they will be included in resend
        $recipients = Campaign::first()->trackingLogs()
                         ->leftJoin('open_logs', 'tracking_logs.message_id', 'open_logs.message_id')
                         ->whereNull('open_logs.id');
        MailLog::warning('Resend to those who did not open: '.$recipients->count());
        $recipients->delete();
    }

    public function cleanupNotClickLog()
    {
        // clean up failed log so that they will be included in resend
        $recipients = Campaign::first()->trackingLogs()
                         ->leftJoin('click_logs', 'tracking_logs.message_id', 'click_logs.message_id')
                         ->whereNull('click_logs.id');
        MailLog::warning('Resend to those who did not click: '.$recipients->count());
        $recipients->delete();
    }

    /**
     * Overwrite the delete() method to also clear the pending jobs.
     */
    public function delete()
    {
        $this->clearAllJobs();
        parent::delete();
    }

    /**
     * Create a stdClass subscriber (for sending a campaign test email)
     * The campaign sending functions take a subscriber object as input
     * However, a test email address is not yet a subscriber object, so we have to build a fake stdClass object
     * which can be used as a real subscriber.
     *
     * @param array $subscriber
     */
    public function createStdClassSubscriber($subscriber)
    {
        // default attributes that are required
        $jsonObj = [
            'uid' => uniqid(),
        ];

        // append the customer specified attributes and build a stdClass object
        $stdObj = json_decode(json_encode(array_merge($jsonObj, $subscriber)));

        return $stdObj;
    }

    /**
     * Check if the given variable is a subscriber object (for actually sending a campaign)
     * Or a stdClass subscriber (for sending test email).
     *
     * @param object $object
     */
    public function isStdClassSubscriber($object)
    {
        return get_class($object) == 'stdClass';
    }

    /**
     * Get information from mail list.
     *
     * @param void
     */
    public function getInfoFromMailList($list)
    {
        $this->from_name = !empty($this->from_name) ? $this->from_name : $list->from_name;
        $this->from_email = !empty($this->from_email) ? $this->from_email : $list->from_email;
        $this->subject = !empty($this->subject) ? $this->subject : $list->default_subject;
    }

    /**
     * Check if auto campaign designed.
     *
     * @return object
     */
    public function autoCampaignDesigned()
    {
        $cond = (!empty($this->name) && !empty($this->subject) && !empty($this->from_name)
            && !empty($this->from_email) && !empty($this->reply_to));

        $cond = $cond && ((!empty($this->html) || $this->type == 'plain-text') && !empty($this->plain) && $this->unsubscribe_url_valid());

        return $cond;
    }

    /**
     * Get type select options.
     *
     * @return array
     */
    public static function getTypeSelectOptions()
    {
        return [
            ['text' => trans('messages.'.self::TYPE_REGULAR), 'value' => self::TYPE_REGULAR],
            ['text' => trans('messages.'.self::TYPE_PLAIN_TEXT), 'value' => self::TYPE_PLAIN_TEXT],
        ];
    }

    /**
     * Check if campaign is automated.
     *
     * @return bool
     */
    public function isAuto()
    {
        return $this->is_auto;
    }

    /**
     * The validation rules for automation trigger.
     *
     * @var array
     */
    public function recipientsRules($params = [])
    {
        $rules = [
            'lists_segments' => 'required',
        ];

        if (isset($params['lists_segments'])) {
            foreach ($params['lists_segments'] as $key => $param) {
                $rules['lists_segments.'.$key.'.mail_list_uid'] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Fill recipients by params.
     *
     * @var void
     */
    public function fillRecipients($params = [])
    {
        if (isset($params['lists_segments'])) {
            foreach ($params['lists_segments'] as $key => $param) {
                $mail_list = null;

                if (!empty($param['mail_list_uid'])) {
                    $mail_list = MailList::findByUid($param['mail_list_uid']);

                    // default mail list id
                    if (isset($param['is_default']) && $param['is_default'] == 'true') {
                        $this->default_mail_list_id = $mail_list->id;
                    }
                }

                if (!empty($param['segment_uids'])) {
                    foreach ($param['segment_uids'] as $segment_uid) {
                        $segment = Segment::findByUid($segment_uid);

                        $lists_segment = new CampaignsListsSegment();
                        $lists_segment->campaign_id = $this->id;
                        if (is_object($mail_list)) {
                            $lists_segment->mail_list_id = $mail_list->id;
                        }
                        $lists_segment->segment_id = $segment->id;
                        $this->listsSegments->push($lists_segment);
                    }
                } else {
                    $lists_segment = new CampaignsListsSegment();
                    $lists_segment->campaign_id = $this->id;
                    if (is_object($mail_list)) {
                        $lists_segment->mail_list_id = $mail_list->id;
                    }
                    $this->listsSegments->push($lists_segment);
                }
            }
        }
    }

    /**
     * Save Recipients.
     *
     * @var void
     */
    public function saveRecipients($params = [])
    {
        // Empty current data
        $this->listsSegments = collect([]);
        // Fill params
        $this->fillRecipients($params);

        $lists_segments_groups = $this->getListsSegmentsGroups();

        $data = [];
        foreach ($lists_segments_groups as $lists_segments_group) {
            if (!empty($lists_segments_group['segment_uids'])) {
                foreach ($lists_segments_group['segment_uids'] as $segment_uid) {
                    $segment = Segment::findByUid($segment_uid);
                    $data[] = [
                        'campaign_id' => $this->id,
                        'mail_list_id' => $lists_segments_group['list']->id,
                        'segment_id' => $segment->id,
                    ];
                }
            } else {
                $data[] = [
                    'campaign_id' => $this->id,
                    'mail_list_id' => $lists_segments_group['list']->id,
                    'segment_id' => null,
                ];
            }
        }

        // Empty old data
        $this->listsSegments()->delete();

        // Insert Data
        CampaignsListsSegment::insert($data);

        // Save campaign with default list id
        $campaign = Campaign::find($this->id);
        $campaign->default_mail_list_id = $this->default_mail_list_id;
        $campaign->save();
    }

    /**
     * Display Recipients.
     *
     * @var array
     */
    public function displayRecipients()
    {
        if (!is_object($this->defaultMailList)) {
            return '';
        }

        $lines = [];
        foreach ($this->getListsSegmentsGroups() as $lists_segments_group) {
            if (is_object($lists_segments_group['list'])) {
                $list_name = $lists_segments_group['list']->name;

                $segment_names = [];
                if (!empty($lists_segments_group['segment_uids'])) {
                    foreach ($lists_segments_group['segment_uids'] as $segment_uid) {
                        $segment = Segment::findByUid($segment_uid);
                        $segment_names[] = $segment->name;
                    }
                }

                if (empty($segment_names)) {
                    $lines[] = $list_name;
                } else {
                    $lines[] = implode(': ', [$list_name, implode(', ', $segment_names)]);
                }
            }
        }

        return implode(' | ', $lines);
    }

    /**
     * Check if campaign is paused.
     *
     * @return bool
     */
    public function isPaused()
    {
        return $this->status == self::STATUS_PAUSED;
    }

    /**
     * Update Campaign cached data.
     */
    public function updateCache($key = null)
    {
        // cache indexes
        $index = [
            // @note: SubscriberCount must come first as its value shall be used by the others
            'ActiveSubscriberCount' => function (&$campaign) {
                return $campaign->activeSubscribersCount(); // spepcial key that requires true update
            },
            'SubscriberCount' => function (&$campaign) {
                return $campaign->subscribersCount(false); // spepcial key that requires true update
            },
            'DeliveredRate' => function (&$campaign) {
                return $campaign->deliveredRate(true);
            },
            'DeliveredCount' => function (&$campaign) {
                return $campaign->deliveredCount();
            },
            'FailedDeliveredRate' => function (&$campaign) {
                return $campaign->failedRate(true);
            },
            'FailedDeliveredCount' => function (&$campaign) {
                return $campaign->failedCount();
            },
            'NotDeliveredRate' => function (&$campaign) {
                return $campaign->notDeliveredRate(true);
            },
            'NotDeliveredCount' => function (&$campaign) {
                return $campaign->notDeliveredCount();
            },
            'ClickedRate' => function (&$campaign) {
                return $campaign->clickedEmailsRate();
            },
            'UniqOpenRate' => function (&$campaign) {
                return $campaign->openUniqRate();
            },
            'UniqOpenCount' => function (&$campaign) {
                return $campaign->openUniqCount();
            },
            'NotOpenRate' => function (&$campaign) {
                return $campaign->notOpenRate(true);
            },
            'NotOpenCount' => function (&$campaign) {
                return $campaign->notOpenCount(true);
            },
        ];

        // retrieve cached data
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            $cache = [];
        }

        if (is_null($key)) {
            // update all cache
            foreach ($index as $key => $callback) {
                $cache[$key] = $callback($this);
                if ($key == 'SubscriberCount') {
                    // SubscriberCount cache must always be updated as its value will be used for the others
                    $this->cache = json_encode($cache);
                    $this->save();
                }
            }
        } else {
            // @deprecated, requires updating the SubscriberCount cache before updating any other one
            // update specific key
            $callback = $index[$key];
            $cache[$key] = $callback($this);
        }

        // write back to the DB
        $this->cache = json_encode($cache);
        $this->save();
    }

    /**
     * Retrieve Campaign cached data.
     *
     * @return mixed
     */
    public function readCache($key, $default = null)
    {
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            return $default;
        }
        if (array_key_exists($key, $cache)) {
            if (is_null($cache[$key])) {
                return $default;
            } else {
                return $cache[$key];
            }
        } else {
            return $default;
        }
    }

    /**
     * Count subscribers.
     *
     * @return int
     */
    public function subscribersCount($cache = false)
    {
        if ($cache) {
            return $this->readCache('SubscriberCount', 0);
        }
        // email_verifications join is required in case of segment condition (for example: 'deliverable' or 'risky')
        // return distinctCount($this->subscribers([], []), 'subscribers.email');
        // return distinctCount($this->subscribers([], ['email_verifications']), 'subscribers.email');
        return $this->subscribers([], ['email_verifications'])->count();
    }

    /**
     * Count subscribers.
     *
     * @return int
     */
    public function activeSubscribersCount()
    {
        // return distinctCount($this->subscribers([], ['email_verifications'])->where('subscribers.status', Subscriber::STATUS_SUBSCRIBED), 'subscribers.email');
        return $this->subscribers([], ['email_verifications'])->where('subscribers.status', Subscriber::STATUS_SUBSCRIBED)->count();
    }

    /**
     * Count unique open by hour.
     *
     * @return number
     */
    public function openUniqHours($start = null, $end = null)
    {
        $query = $this->openLogs()->select('open_logs.created_at');
        if (isset($start)) {
            $query = $query->where('open_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('open_logs.created_at', '<=', $end);
        }

        return $query->orderBy('open_logs.created_at', 'asc')->get()->groupBy(function ($date) {
            return \Acelle\Library\Tool::dateTime($date->created_at)->format('H'); // grouping by hours
        });
    }

    /**
     * Count click group by hour.
     *
     * @return number
     */
    public function clickHours($start = null, $end = null)
    {
        $query = $this->clickLogs()->select('click_logs.created_at', 'tracking_logs.subscriber_id');

        if (isset($start)) {
            $query = $query->where('click_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('click_logs.created_at', '<=', $end);
        }

        return $query->orderBy('click_logs.created_at', 'asc')->get()->groupBy(function ($date) {
            return \Acelle\Library\Tool::dateTime($date->created_at)->format('H'); // grouping by hours
        });
    }
    public function fileInfo($filePath)
    {
        $name = $filePath['filename'];
        $extension = $filePath['extension'];

        return $name.'.'.$extension;
    }

    public function fillAttributes($params)
    {
        $this->fill($params);

        // Tacking domain
        if (isset($params['custom_tracking_domain']) && $params['custom_tracking_domain'] && isset($params['tracking_domain_uid'])) {
            $tracking_domain = \Acelle\Model\TrackingDomain::findByUid($params['tracking_domain_uid']);
            if (is_object($tracking_domain)) {
                $this->tracking_domain_id = $tracking_domain->id;
            } else {
                $this->tracking_domain_id = null;
            }
        } else {
            $this->tracking_domain_id = null;
        }
    }

    /**
     * Generate SpamScore.
     */
    public function score()
    {
        // raw output
        $test = $this->execSpamc();

        // Get scores / thresholds
        preg_match('/\s*(?<score>[0-9\.\/]+)\s*/', $test, $score);

        if (!array_key_exists('score', $score)) {
            throw new \Exception('Cannot get SpamScore: '.$test);
        }

        $score = $score['score'];
        list($current, $threshold) = preg_split('/\//', $score);
        $passed = ($current <= $threshold) ? true : false;

        // get the details
        $json = [];

        $firstMatch = false;
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $test) as $line) {
            preg_match('/^\s*(?<score>[\-0-9\.]+)\s+(?<rule>[\w]+)\s+(?<desc>.*)/', $line, $result);

            if (array_key_exists('score', $result) && array_key_exists('rule', $result) && array_key_exists('desc', $result)) {
                $firstMatch = true;
                $json[] = [
                    'score' => $result['score'],
                    'rule' => $result['rule'],
                    'desc' => $result['desc'],
                    'status' => ($result['score'] > 0.0) ? 'failed' : (($result['score'] == 0.0) ? 'neutral' : 'passed'),
                ];
            } elseif ($firstMatch) {
                $lastRecord = end($json);
                $lastRecord['desc'] .= ' '.trim($line);
                // replace last record
                $json[sizeof($json) - 1] = $lastRecord;
            }
        }

        return [
            'result' => $passed,
            'score' => $score,
            'details' => $json,
        ];
    }

    /**
     * Generate SpamScore.
     */
    private function execSpamc()
    {
        $message = $this->getSampleMessage()->toString();

        // Execute SPAMC
        $desc = [
            0 => array('pipe', 'r'), // 0 is STDIN for process
            1 => array('pipe', 'w'), // 1 is STDOUT for process
            2 => array('pipe', 'w'),  // 2 is STDERR for process
        ];

        // command to invoke markup engine
        $cmd = Setting::get('spamassassin.command');

        if (is_null($cmd)) {
            $cmd = 'spamc -R'; // default value
        }

        // spawn the process
        $p = proc_open($cmd, $desc, $pipes);

        // send the wiki content as input to the markup engine
        // and then close the input pipe so the engine knows
        // not to expect more input and can start processing
        fwrite($pipes[0], $message);
        fclose($pipes[0]);

        // read the output from the engine
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        // all done! Clean up
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($p);

        if (!empty($err) || empty($stdout)) {
            throw new \Exception('Error: cannot get SpamScore: '.$stderr);
        }

        return $stdout;
    }

    /**
     * Get sample message.
     */
    public function getSampleMessage()
    {
        // Build a valid message with a fake contact
        // build a temporary subscriber oject used to pass through the sending methods
        $subscriber = $this->createStdClassSubscriber(['email' => 'admin@outlook.com']);

        // Throw exception in case no server available
        $server = $this->pickSendingServer();

        // build the message from campaign information
        list($message, $msgId) = $this->prepareEmail($subscriber, $server);

        return $message;
    }

    /**
     * Get public campaign upload uri.
     */
    public function getUploadUri()
    {
        return 'app/campaigns/template_'.$this->uid;
    }

    /**
     * Get public campaign upload dir.
     */
    public function getUploadDir()
    {
        return storage_path('app/campaigns/template_'.$this->uid);
    }

    /**
     * Update campaign plain text.
     */
    public function updatePlainFromHtml()
    {
        if (!$this->plain) {
            $this->plain = preg_replace('/\s+/', ' ', preg_replace('/\r\n/', ' ', strip_tags($this->html)));
            $this->save();
        }
    }

    /**
     * Create template from layout.
     *
     * All availabel template tags
     */
    public function addTemplateFromLayout($layout)
    {
        $sDir = database_path('layouts/'.$layout);

        // delete old template
        Tool::xdelete($this->getStoragePath());

        // load
        $this->loadFromDirectory($sDir);
    }

    /**
     * Load from directory.
     */
    public function loadFromDirectory($tmp_path)
    {
        // try to find the main file, index.html | index.html | file_name.html | ...
        $main_file = null;
        $sub_path = '';
        $possible_main_file_names = array('index.html', 'index.htm');

        $possible_main_file_names = array('index.html', 'index.htm');
        foreach ($possible_main_file_names as $name) {
            if (is_file($file = $tmp_path.'/'.$name)) {
                $main_file = $file;
                break;
            }
            $dirs = array_filter(glob($tmp_path.'/'.'*'), 'is_dir');
            foreach ($dirs as $sub) {
                if (is_file($file = $sub.'/'.$name)) {
                    $main_file = $file;
                    $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1].'/';
                    break;
                }
            }
        }
        // try to find first htm|html file
        if ($main_file === null) {
            $objects = scandir($tmp_path);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (!is_dir($tmp_path.'/'.$object)) {
                        if (preg_match('/\.html?$/i', $object)) {
                            $main_file = $tmp_path.'/'.$object;
                            break;
                        }
                    }
                }
            }
            $dirs = array_filter(glob($tmp_path.'/'.'*'), 'is_dir');
            foreach ($dirs as $sub) {
                $objects = scandir($sub);
                foreach ($objects as $object) {
                    if ($object != '.' && $object != '..') {
                        if (!is_dir($sub.'/'.$object)) {
                            if (preg_match('/\.html?$/i', $object)) {
                                $main_file = $sub.'/'.$object;
                                $sub_path = explode('/', $sub)[count(explode('/', $sub)) - 1].'/';
                                break;
                            }
                        }
                    }
                }
            }
        }

        // // main file not found
        // if ($main_file === null) {
        //     $validator->errors()->add('file', 'Cannot find index file (index.html) in the template folder');
        //     throw new ValidationException($validator);
        // }

        // read main file content
        $html_content = trim(file_get_contents($main_file));
        $this->html = $html_content;
        $this->save();

        // upload path
        $upload_path = $this->getStoragePath();

        // copy all folder to public path
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        // exec("cp -r {$tmp_path}/* {$public_upload_path}/");
        Tool::xcopy($tmp_path, $upload_path);
    }

    /**
     * Get public campaign upload dir.
     */
    public function getStoragePath($path = '')
    {
        return storage_path('app/users/'.$this->customer->uid.'/campaigns/'.$this->uid.'/'.$path);
    }

    /**
     * Get thumb.
     */
    public function getThumbName()
    {
        // find index
        $names = array('thumbnail.png', 'thumbnail.jpg', 'thumbnail.png', 'thumb.jpg', 'thumb.png');
        foreach ($names as $name) {
            if (is_file($file = $this->getStoragePath().$name)) {
                return $name;
            }
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumb()
    {
        // find index
        if ($this->getThumbName()) {
            return $this->getStoragePath().$this->getThumbName();
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumbUrl()
    {
        if ($this->getThumbName()) {
            return route('campaign_assets', ['uid' => $this->uid, 'path' => $this->getThumbName()]);
        } else {
            return url('assets/images/placeholder.jpg');
        }
    }

    /**
     * transform URL.
     */
    public function transform($useTrackingHost = false)
    {
        $this->html = $this->transformWithoutUpdate($this->html, $useTrackingHost);
        return $this->html;
    }

    /**
     * transform URL.
     */
    public function transformWithoutUpdate($html, $useTrackingHost = false)
    {
        // Notice that there are 2 types of assets that need to untransform
        // #1 Campaign relative URL
        // #2 User assets (uploaded by file manager) absolute URL
        // 

        // Replace #2
        $host = config('app.url'); // Default host
        
        if ($useTrackingHost) {
            // Replace #2
            $html = str_replace($host, $this->getTrackingHost(), $html);
            $host = $this->getTrackingHost();
        }

        // Replace #1
        $path = join_url($host, route('campaign_assets', ['uid' => $this->uid, 'path' => ''], false));
        $html = \Acelle\Library\Tool::replaceTemplateUrl($html, $path);    

        // By the way, fix <html>
        $html = tinyDocTypeTransform($html);

        return $html;
    }

    /**
     * transform URL.
     */
    public function untransform()
    {
        // Notice that there are 2 types of assets that need to untransform
        // #1 Campaign relative URL
        // #2 User assets (uploaded by file manager) absolute URL
        // 
        // and only #1 will be untransformed while #2 is kept as is

        // Replace relative URL with campaign URL
        $this->html = str_replace(
            route('campaign_assets', ['uid' => $this->uid, 'path' => '']).'/',
            '',
            tinyDocTypeUntransform($this->html)
        );

        // remove title tag
        $this->html = strip_tags_only($this->html, 'title');
    }

    /**
     * transform URL.
     */
    public function render()
    {
        $body = $this->transformWithoutUpdate($this->html, $useTrackingHost = false);
        return $body;
    }

    /**
     * Upload asset.
     */
    public function uploadAsset($file)
    {
        return $this->customer->user->uploadAsset($file);
    }

    /**
     * Upload a template.
     */
    public function uploadTemplate($request)
    {
        $rules = array(
            'file' => 'required|mimetypes:application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
        );

        $validator = Validator::make($request->all(), $rules, [
            'file.mimetypes' => 'Input must be a valid .zip file',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // move file to temp place
        $tmp_path = storage_path('tmp/uploaded_template_'.$this->uid.'_'.time());
        $file_name = $request->file('file')->getClientOriginalName();
        $request->file('file')->move($tmp_path, $file_name);
        $tmp_zip = join_paths($tmp_path, $file_name);

        // read zip file check if zip archive invalid
        $zip = new ZipArchive();
        if ($zip->open($tmp_zip, ZipArchive::CREATE) !== true) {
            // @todo hack
            // $validator = Validator::make([], []); // Empty data and rules fields
            $validator->errors()->add('file', 'Cannot open .zip file');
            throw new ValidationException($validator);
        }

        // unzip template archive and remove zip file
        $zip->extractTo($tmp_path);
        $zip->close();
        unlink($tmp_zip);

        // delete old template
        Tool::xdelete($this->getStoragePath());

        // load template
        $this->loadFromDirectory($tmp_path);

        // remove tmp folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($tmp_path);

        // update plain
        $this->updatePlainFromHtml();
    }

    /**
     * Copy new template.
     */
    public function copyFromTemplate($template)
    {
        // delete old template
        Tool::xdelete($this->getStoragePath());

        \Acelle\Library\Tool::xcopy($template->getStoragePath(), $this->getStoragePath());

        // replace image url
        $this->html = $template->content;
        $this->save();
    }

    /**
     * Copy new template.
     */
    public function generateTrackingLogCsv($logtype, $progressCallback = null)
    {
        $tmpTableName = 'log_'.$this->uid.'_'.md5(rand());
        $filePath = $this->customer->user->getStoragePath($tmpTableName.'.csv');

        DB::statement('DROP TABLE IF EXISTS '.table($tmpTableName));
        
        if ($logtype == 'open_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name as campaign_name,
                s.email as subscriber_email,
                l.status as delivery_status,
                l.created_at as sent_at,
                o.created_at AS open_at,
                o.user_agent AS device,
                ip.ip_address,
                ip.country_code,
                ip.country_name,
                ip.region_code,
                ip.region_name,
                ip.city,
                ip.zipcode,
                ip.latitude,
                ip.longitude,
                ip.metro_code
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('open_logs').' o ON o.message_id = l.message_id
              LEFT JOIN '.table('ip_locations').' ip ON ip.ip_address = o.ip_address
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'open_at',
                'device',
                'ip_address',
                'country_code',
                'country_name',
                'region_code',
                'region_name',
                'city',
                'zipcode',
                'latitude',
                'longitude',
                'metro_code',
            ];
        } elseif ($logtype == 'click_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name as campaign_name,
                s.email as subscriber_email,
                l.status as delivery_status,
                l.created_at as sent_at,
                ck.created_at AS click_at,
                ck.url,
                ck.user_agent AS device,
                ip.ip_address,
                ip.country_code,
                ip.country_name,
                ip.region_code,
                ip.region_name,
                ip.city,
                ip.zipcode,
                ip.latitude,
                ip.longitude,
                ip.metro_code
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('click_logs').' ck ON ck.message_id = l.message_id
              LEFT JOIN '.table('ip_locations').' ip ON ip.ip_address = ck.ip_address
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'click_at',
                'url',
                'device',
                'ip_address',
                'country_code',
                'country_name',
                'region_code',
                'region_name',
                'city',
                'zipcode',
                'latitude',
                'longitude',
                'metro_code',
            ];
        } elseif ($logtype == 'unsubscribe_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name as campaign_name,
                s.email as subscriber_email,
                l.status as delivery_status,
                l.created_at as sent_at,
                u.created_at AS unsubscribe_at,
                u.user_agent AS device,
                ip.ip_address,
                ip.country_code,
                ip.country_name,
                ip.region_code,
                ip.region_name,
                ip.city,
                ip.zipcode,
                ip.latitude,
                ip.longitude,
                ip.metro_code
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('unsubscribe_logs').' u ON u.message_id = l.message_id
              LEFT JOIN '.table('ip_locations').' ip ON ip.ip_address = u.ip_address
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'unsubscribe_at',
                'device',
                'ip_address',
                'country_code',
                'country_name',
                'region_code',
                'region_name',
                'city',
                'zipcode',
                'latitude',
                'longitude',
                'metro_code',
            ];
        } elseif ($logtype == 'feedback_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name AS campaign_name,
                s.email AS subscriber_email,
                l.status AS delivery_status,
                l.created_at AS sent_at,
                f.created_at AS feedback_at,
                f.feedback_type AS feedback_type,
                f.raw_feedback_content AS feedback_content
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('feedback_logs').' f ON f.message_id = l.message_id
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'feedback_at',
                'feedback_type',
                'feedback_content',
            ];
        } elseif ($logtype == 'bounce_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name AS campaign_name,
                s.email AS subscriber_email,
                l.status AS delivery_status,
                l.created_at AS sent_at,
                b.created_at AS bounce_at,
                b.bounce_type AS bounce_type,
                b.raw AS bounce_content
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('bounce_logs').' b ON b.message_id = l.message_id
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'bounce_at',
                'bounce_type',
                'bounce_content',
            ];
        } elseif ($logtype == 'tracking_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name AS campaign_name,
                s.email AS subscriber_email,
                l.status AS delivery_status,
                l.created_at AS sent_at
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
            ];
        } else {
            throw new \Exception('Unknown export type: '.$logtype);
        }

        $total = DB::table($tmpTableName)->count();
        $limit = 1000;
        $pages = ceil($total / $limit);

        // insert header
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($headers);

        for ($i = 0; $i < $pages; $i += 1) {
            $items = DB::table($tmpTableName)->select('*')
                                             ->limit($limit)
                                             ->offset($i)
                                             ->get()
                                             ->map(function ($r) {
                                                 return (array) $r;
                                             })
                                             ->toArray();
            $csv->insertAll($items);

            // callback progress
            if (!is_null($progressCallback)) {
                $percentage = ($i + 1) / $pages;
                $progressCallback(['progress' => $percentage, 'path' => $filePath]);
            }
        }

        // callback progress
        if (!is_null($progressCallback)) {
            $progressCallback(['progress' => 1, 'path' => $filePath]);
        }

        return $filePath;
    }

    /**
     * Get attachment path.
     */
    public function getAttachmentPath($path='')
    {
        return $this->getStoragePath('attachment/' . $path);
    }

    /**
     * Upload attachment.
     */
    public function uploadAttachment($file)
    {
        $file_name = $file->getClientOriginalName();

        $new_name = $file_name;

        $path = $file->move(
            $this->getAttachmentPath(),
            $new_name
        );

        return $this->getAttachmentPath().$new_name;
    }

    /**
     * Upload attachment.
     */
    public function getAttachments()
    {
        $atts = [];
        $path_campaign = $this->getAttachmentPath();

        if (!is_dir($path_campaign)) {
            return $atts;
        }

        $ffs = scandir($path_campaign);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1) {
            return $atts;
        }

        foreach ($ffs as $k => $ff) {
            $atts[] = $ff;
        }

        return $atts;
    }

    public function arrayRandFixed($array, $count)
    {
        $result = array_rand($array, $count);
        if (is_array($result)) {
            return $result;
        } else {
            return [$result];
        }
    }

    public function makeSampleData($params = [])
    {
        $this->cleanSampleData();

        $default = [
            'unconfirmed' => 0.08,
            'delivery_failed' => 0.1, // against all deivered emails
            'open' => 0.45, // against delivery_success
            'click' => 0.85, // against open
            'unsubscribe' => 0.2, // against open
            'bounce' => 0.05,
            'feedback' => 0.05
        ];

        $params = array_merge($default, $params);

        // Tracking log
        $this->getPendingSubscribers(null, function ($subscribers, $page, $total) use (&$i, $params) {
            if ($total == 0) {
                throw new \Exception('No subscribers for campaign, are you sure mail list is set?');
            }
            MailLog::info("Fetching page $page (count: {$subscribers->count()})");

            $i = 0;
            foreach ($subscribers as $subscriber) {
                $i += 1;
                // Pick up an available sending server
                // Throw exception in case no server available
                $server = $this->pickSendingServer($this);
                $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from_email));

                $sent = [
                    'status' => 'sent',
                    'runtime_message_id' => $msgId
                ];

                $this->trackMessage($sent, $subscriber, $server, $msgId);

                // additional log
                MailLog::info("Sending to subscriber `{$subscriber->email}` ({$i}/{$total})");
            }
        });

        echo "All tracking log count: ". $this->trackingLogs()->count()."\n";
        // Failed tracking log
        $failureCount = round($params['delivery_failed'] * $this->trackingLogs()->count());
        $allLogs = $this->trackingLogs()->get()->map(function($e){ return $e->id; })->toArray();
        $failedLogs = array_values(array_intersect_key($allLogs, array_flip( $this->arrayRandFixed( $allLogs, $failureCount ))));
        echo "Failed log count: ".$this->trackingLogs()->whereIn('id', $failedLogs)->count()."\n";
        $this->trackingLogs()->whereIn('id', $failedLogs)->update([
            'status' => 'failed',
            'error' => 'RFC8343: mailbox does not exist',
        ]);

        // Open log
        $sentCount = $this->trackingLogs()->where('status', 'sent')->count();
        $openCount = round($params['open'] * $sentCount);
        $sentLogs = $this->trackingLogs()->where('status', 'sent')
                         ->select('tracking_logs.id')
                         ->get()->map(function($e){ return $e->id; })->toArray();
        $openLogs = array_values(array_intersect_key($sentLogs, array_flip( $this->arrayRandFixed( $sentLogs, $openCount ))));
        echo "Open log count: ".$openCount."\n";
        foreach($this->trackingLogs()->whereIn('id', $openLogs)->get() as $r) {
            $log = new \Acelle\Model\OpenLog();
            $log->message_id = $r->message_id;
            $location = \Acelle\Model\IpLocation::add(StringHelper::getRandomUSIpAddresses());
            $log->ip_address = $location->ip_address;
            $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
            $log->save();
        }

        // Open log
        $clickCount = round($params['click'] * $openCount);

        $openLogs = $this->trackingLogs()
                         ->join('open_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
                         ->select('tracking_logs.id')
                         ->get()->map(function($e){ return $e->id; })->toArray();
        $clickLogs = array_values(array_intersect_key($openLogs, array_flip( $this->arrayRandFixed( $openLogs, $clickCount ))));
        echo "Click log count: ".$this->trackingLogs()->whereIn('id', $clickLogs)->count()."\n";
        foreach($this->trackingLogs()->whereIn('id', $clickLogs)->get() as $r) {
            $log = new \Acelle\Model\ClickLog();
            $log->message_id = $r->message_id;
            $location = \Acelle\Model\IpLocation::add(StringHelper::getRandomUSIpAddresses());
            $log->ip_address = $location->ip_address;
            $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
            $log->url = 'https://app.clickfunnels.com/signupflow';
            $result = $log->save();
        }        

        // Unsubscribe log
        $unsubscribeCount = round($params['unsubscribe'] * $openCount);
        $unsubscribeLogs = array_values(array_intersect_key($openLogs, array_flip( $this->arrayRandFixed( $openLogs, $unsubscribeCount ))));
        echo "Unsubscribe log count: ".$unsubscribeCount."\n";
        foreach($this->trackingLogs()->whereIn('id', $unsubscribeLogs)->get() as $r) {
            // Unsubscribe log
            $log = new \Acelle\Model\UnsubscribeLog();
            $log->message_id = $r->message_id;
            $location = \Acelle\Model\IpLocation::add(StringHelper::getRandomUSIpAddresses());
            $log->ip_address = $location->ip_address;
            $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
            $log->save();
        }


        // Bounce log
        $bounceCount = round($params['bounce'] * $sentCount);
        $bounceLogs = array_values(array_intersect_key($sentLogs, array_flip( $this->arrayRandFixed( $sentLogs, $bounceCount ))));
        echo "Bounce log count: ".$bounceCount."\n";
        foreach($this->trackingLogs()->whereIn('id', $bounceLogs)->get() as $r) {
            // Unsubscribe log
            $bounceLog = new BounceLog();
            $bounceLog->runtime_message_id = $r->message_id;
            $bounceLog->message_id = $r->message_id;
            // SendGrid only notifies in case of HARD bounce
            $bounceLog->bounce_type = BounceLog::HARD;
            $bounceLog->raw = 'RFC308433: mailbox is no longer valid or blocked';
            $bounceLog->save();

            // add subscriber's email to blacklist
            $subscriber = $bounceLog->findSubscriberByRuntimeMessageId();
            $subscriber->sendToBlacklist($bounceLog->raw);
        }

        // Bounce log
        $feedbackCount = round($params['feedback'] * $sentCount);
        $feedbackLogs = array_values(array_intersect_key($sentLogs, array_flip( $this->arrayRandFixed( $sentLogs, $feedbackCount ))));
        echo "Feedback log count: ".$feedbackCount."\n";
        foreach($this->trackingLogs()->whereIn('id', $feedbackLogs)->get() as $r) {
            // Unsubscribe log
            $feedback = new FeedbackLog();
            $feedback->runtime_message_id = $r->message_id;
            $feedback->message_id = $r->message_id;
            $feedback->feedback_type = 'spam';
            $feedback->raw_feedback_content = 'RFC308433: mailbox is no longer valid or blocked';
            $feedback->save();

            // add subscriber's email to blacklist
            $subscriber = $feedback->findSubscriberByRuntimeMessageId();
            $subscriber->markAsSpamReported($feedback->raw);
        }

        // Adjust date/time
        $sample = [
            'subscriber_first_created' => '6 months ago',
            'subscriber_last_created' => '2 day ago',
            'first_open' => '24 hours ago',
            'last_open' => '2 minutes ago',
        ];

        foreach($sample as $key => $value) {
            $sample[$key] = new Carbon($value); 
        }

        DB::update(sprintf('UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('subscribers'),
            $sample['subscriber_first_created'],
            $sample['subscriber_last_created'],
            $sample['subscriber_first_created']
        ));

        DB::update(sprintf('UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('open_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf('UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('click_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf('UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('bounce_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf('UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('feedback_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf('UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('unsubscribe_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        // Finalize
        $this->updateCache();
    }

    public function cleanSampleData()
    {
        $this->defaultMailList->subscribers()->update(['status' => 'subscribed']);
        Blacklist::query()->delete();
        $this->trackingLogs()->delete();
    }

    public function occupiedByOtherAnotherProcess()
    {
        return (!is_null($this->running_pid) && posix_getpgid($this->running_pid));
    }
}
