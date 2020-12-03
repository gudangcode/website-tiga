<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use File;
use Validator;
use ZipArchive;
use KubAT\PhpSimple\HtmlDomParser;
use Acelle\Library\ExtendedSwiftMessage;
use Acelle\Library\Tool;
use Acelle\Library\Rss;
use Acelle\Library\StringHelper;
use Acelle\Library\Log as MailLog;
use Acelle\Model\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Acelle\Jobs\DeliverEmail;

class Email extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'from', 'from_name', 'reply_to', 'sign_dkim', 'track_open', 'track_click', 'action_id',
    ];

    // Cached HTML content
    protected $parsedContent = null;

    /**
     * Association with mailList through mail_list_id column.
     */
    public function automation()
    {
        return $this->belongsTo('Acelle\Model\Automation2', 'automation2_id');
    }

    /**
     * Association with attachments.
     */
    public function attachments()
    {
        return $this->hasMany('Acelle\Model\Attachment');
    }

    /**
     * Association with email links.
     */
    public function emailLinks()
    {
        return $this->hasMany('Acelle\Model\EmailLink');
    }

    /**
     * Association with open logs.
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog');
    }

    public function deliveryAttempts()
    {
        return $this->hasMany('Acelle\Model\DeliveryAttempt');
    }

    /**
     * Get email's associated tracking domain.
     */
    public function trackingDomain()
    {
        return $this->belongsTo('Acelle\Model\TrackingDomain', 'tracking_domain_id');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            $item->uid = $uid;
        });

        static::deleted(function ($item) {
            if (is_dir($item->getEmailStoragePath())) {
                File::deleteDirectory($item->getEmailStoragePath());
            }
        });
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
     * Create automation rules.
     *
     * @return array
     */
    public function rules($request=null)
    {
        $rules = [
            'subject' => 'required',
            'from' => 'required|email',
            'from_name' => 'required',
        ];

        // tracking domain
        if (isset($request) && $request->custom_tracking_domain) {
            $rules['tracking_domain_uid'] = 'required';
        }

        return $rules;
    }

    /**
     * Get public campaign upload uri.
     */
    public function getUploadUri()
    {
        return 'app/email/template_'.$this->uid;
    }

    /**
     * Get public campaign upload dir.
     */
    public function getUploadDir()
    {
        return storage_path('app/email/template_'.$this->uid);
    }

    /**
     * Create template from layout.
     *
     * All availabel template tags
     */
    public function addTemplateFromLayout($layout)
    {
        $sDir = database_path('layouts/'.$layout);
        $this->loadFromDirectory($sDir);
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderTemplates($customer)
    {
        $result = [];

        // Gallery
        $templates = Template::where('customer_id', '=', null)
            ->orWhere('customer_id', '=', $customer->id)
            ->orderBy('customer_id')
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'thumbnail' => $template->getThumbUrl(),
            ];
        }

        return $result;
    }

    /**
     * Upload attachment.
     */
    public function uploadAttachment($file)
    {
        $file_name = $file->getClientOriginalName();
        $att = $this->attachments()->make();
        $att->size = $file->getSize();
        $att->name = $file->getClientOriginalName();

        $path = $file->move(
            $this->getAttachmentPath(),
            $att->name
        );
        
        $att->file = $this->getAttachmentPath($att->name);
        $att->save();

        return $att;
    }

    /**
     * Get attachment path.
     */
    public function getAttachmentPath($sub = '')
    {
        return $this->getEmailStoragePath(join_paths('attachment', $sub));
    }

    /**
     * Copy new template.
     */
    public function copyFromTemplate($template)
    {
        Tool::xdelete($this->getStoragePath());

        \Acelle\Library\Tool::xcopy($template->getStoragePath(), $this->getStoragePath());

        // replace image url
        $this->content = $template->content;
        $this->save();
    }

    /**
     * Get thumb.
     */
    public function getThumbName()
    {
        // find index
        $names = array('thumbnail.png', 'thumbnail.jpg', 'thumbnail.png', 'thumb.jpg', 'thumb.png');
        foreach ($names as $name) {
            if (is_file($file = $this->getStoragePath($name))) {
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
            return $this->getStoragePath($this->getThumbName());
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumbUrl()
    {
        if ($this->getThumbName()) {
            return route('email_assets', ['uid' => $this->uid, 'path' => $this->getThumbName()]);
        } else {
            return url('assets/images/placeholder.jpg');
        }
    }

    /**
     * Load from directory.
     */
    public function loadFromDirectory($tmp_path)
    {
        // remove current folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($this->getStoragePath());

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
        $this->content = $html_content;
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
    public function getEmailStoragePath($sub = '')
    {
        $base = storage_path(join_paths('app/users/', $this->automation->customer->uid, '/emails/', $this->uid));
        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }
        return join_paths($base, $sub);
    }

    /**
     * Get public campaign upload dir.
     */
    public function getStoragePath($sub = '')
    {
        $base = $this->getEmailStoragePath('content');
        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }
        return join_paths($base, $sub);
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
            return [false, $validator];
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

            return [false, $validator];
        }

        // unzip template archive and remove zip file
        $zip->extractTo($tmp_path);
        $zip->close();
        unlink($tmp_zip);

        $this->loadFromDirectory($tmp_path);

        // remove tmp folder
        // exec("rm -r {$tmp_path}");
        Tool::xdelete($tmp_path);

        return [true, $validator];
    }

    /**
     * transform URL.
     */
    public function transform($useTrackingHost = false)
    {
        $this->content = $this->transformWithoutUpdate($this->content, $useTrackingHost);
        return $this->content;
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
        $host = config('app.url'); // Default host
        
        if ($useTrackingHost) {
            // Replace #2
            $html = str_replace($host, $this->getTrackingHost(), $html);
            $host = $this->getTrackingHost();
        }

        // Replace #1
        $path = join_url($host, route('email_assets', ['uid' => $this->uid, 'path' => ''], false));
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
        // #1 Email relative URL
        // #2 User assets (uploaded by file manager) absolute URL
        // 
        // and only #1 will be untransformed while #2 is kept as is

        // Replace relative URL with campaign URL
        $this->content = str_replace(
            route('email_assets', ['uid' => $this->uid, 'path' => '']).'/',
            '',
            tinyDocTypeUntransform($this->content)
        );

        // remove title tag
        $this->content = strip_tags_only($this->content, 'title');
    }

    /**
     * Render email content for BUILDER EDIT
     */
    public function render()
    {
        $body = $this->transformWithoutUpdate($this->content, $useTrackingHost = false);
        return $body;
    }

    /**
     * Upload asset.
     */
    public function uploadAsset($file)
    {
        return $this->automation->customer->user->uploadAsset($file);
    }

    /**
     * Find and update email links.
     */
    public function updateLinks()
    {
        if (!$this->content) {
            return false;
        }

        $links = [];

        // find all links from contents
        $document = HtmlDomParser::str_get_html($this->content);
        foreach ($document->find('a') as $element) {
            if (preg_match('/^http/', $element->href) != 0) {
                $links[] = trim($element->href);
            }
        }

        // delete al bold links
        $this->emailLinks()->whereNotIn('link', $links)->delete();

        foreach ($links as $link) {
            $exist = $this->emailLinks()->where('link', '=', $link)->count();

            if (!$exist) {
                $this->emailLinks()->create([
                    'link' => $link,
                ]);
            }
        }
    }

    public function queueDeliverTo($subscriber, $triggerId = null)
    {
        $deliveryAttempt = $this->deliveryAttempts()->create([
            'subscriber_id' => $subscriber->id,
            'auto_trigger_id' => $triggerId,
            // 'email_id' already included
        ]);
        
        dispatch(new DeliverEmail(
            $this,
            $subscriber,
            $triggerId,
            $deliveryAttempt->id,
            get_class($deliveryAttempt)
        ));

        return $deliveryAttempt;
    }

    // @note: complicated dependencies
    // It is just fine, we can think Email as an object depends on User/Customer
    public function deliverTo($subscriber, $triggerId = null)
    {
        // @todo: code smell here, violation of Demeter Law
        // @todo: performance
        while ($this->automation->customer->overQuota()) {
            MailLog::warning(sprintf('Email `%s` (%s) to `%s` halted, user exceeds sending limit', $this->subject, $this->uid, $subscriber->email));
            sleep(60);
        }

        $server = $subscriber->mailList->pickSendingServer();

        MailLog::info("Sending to subscriber `{$subscriber->email}`");

        list($message, $msgId) = $this->prepare($subscriber);

        $sent = $server->send($message);

        // additional log
        MailLog::info("Sent to subscriber `{$subscriber->email}`");
        $this->trackMessage($sent, $subscriber, $server, $msgId, $triggerId);
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @return MIME text message
     */
    public function prepare($subscriber)
    {
        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Acelle-Message-Id'];

        $message = new ExtendedSwiftMessage();
        $message->setId($msgId);

        // fixed: HTML type only
        $message->setContentType('text/html; charset=utf-8');

        foreach ($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        $server = $subscriber->mailList->pickSendingServer();
        if ($server->allowCustomReturnPath()) {
            $returnPath = $server->getVerp($subscriber->email);
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }
        }
        $message->setSubject($this->getSubject($subscriber, $msgId));
        $message->setFrom(array($this->from => $this->from_name));
        $message->setTo($subscriber->email);
        $message->setReplyTo($this->reply_to);
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($this->getHtmlContent($subscriber, $msgId, $server), 'text/html');

        if ($this->sign_dkim) {
            $message = $this->sign($message);
        }

        foreach ($this->attachments as $file) {
            $attachment = \Swift_Attachment::fromPath($file->file);
            $message->attach($attachment);
            // This is used by certain delivery services like ElasticEmail
            $message->extAttachments[] = [ 'path' => $file->file, 'type' => $attachment->getContentType()];
        }

        return array($message, $msgId);
    }

    /**
     * Build Email Custom Headers.
     *
     * @return Hash list of custom headers
     */
    public function getCustomHeaders($subscriber, $server)
    {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from));

        return array(
            'X-Acelle-Campaign-Id' => $this->uid,
            'X-Acelle-Subscriber-Id' => $subscriber->uid,
            'X-Acelle-Customer-Id' => $this->automation->customer->uid,
            'X-Acelle-Message-Id' => $msgId,
            'X-Acelle-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<'.$this->generateUnsubscribeUrl($msgId, $subscriber).'>',
            'Precedence' => 'bulk',
        );
    }

    public function generateUnsubscribeUrl($msgId, $subscriber)
    {
        // OPTION 1: immediately opt out
        $path = route('unsubscribeUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        // OPTION 2: unsubscribe form
        $path = route('unsubscribeForm', ['list_uid' => $subscriber->mailList->uid, 'code' => $subscriber->getSecurityToken('unsubscribe'), 'uid' => $subscriber->uid], false);

        return $this->buildPublicUrl($path);
    }

    /**
     * Log delivery message, used for later tracking.
     */
    public function trackMessage($response, $subscriber, $server, $msgId, $triggerId = null)
    {

        // @todo: customerneedcheck
        $params = array_merge(array(
                // 'email_id' => $this->id,
                'message_id' => $msgId,
                'subscriber_id' => $subscriber->id,
                'sending_server_id' => $server->id,
                'customer_id' => $this->automation->customer->id,
                'auto_trigger_id' => $triggerId, 
            ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }

        // create tracking log for message
        $this->trackingLogs()->create($params);

        // increment customer quota usage
        $this->automation->customer->countUsage();
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

    public function buildPublicUrl($path)
    {
        return join_url($this->getTrackingHost(), $path);
    }

    public function tagMessage($message, $subscriber, $msgId, $server = null)
    {
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }

        $tags = array(
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $subscriber->mailList->contact->company,
            'CONTACT_COUNTRY' => $subscriber->mailList->contact->country->name,
            'CONTACT_STATE' => $subscriber->mailList->contact->state,
            'CONTACT_CITY' => $subscriber->mailList->contact->city,
            'CONTACT_ADDRESS_1' => $subscriber->mailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $subscriber->mailList->contact->address_2,
            'CONTACT_PHONE' => $subscriber->mailList->contact->phone,
            'CONTACT_URL' => $subscriber->mailList->contact->url,
            'CONTACT_EMAIL' => $subscriber->mailList->contact->email,
            'LIST_NAME' => $subscriber->mailList->name,
            'LIST_SUBJECT' => $subscriber->mailList->default_subject,
            'LIST_FROM_NAME' => $subscriber->mailList->from_name,
            'LIST_FROM_EMAIL' => $subscriber->mailList->from_email,
        );

        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId, $subscriber);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($subscriber->mailList->fields as $field) {
                $tags['SUBSCRIBER_'.$field->tag] = $subscriber->getValueByField($field);
            }
        } else {
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_replace('{'.$tag.'}', $value, $message);
        }

        return $message;
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

    public function generateUpdateProfileUrl($subscriber)
    {
        $path = route('updateProfileUrl', ['list_uid' => $subscriber->mailList->uid, 'uid' => $subscriber->uid, 'code' => $subscriber->getSecurityToken('update-profile')], false);

        return $this->buildPublicUrl($path);
    }

    public function generateWebViewerUrl($msgId)
    {
        $path = route('webViewerUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        return $this->buildPublicUrl($path);
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
            $body = $this->content;

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
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);
        $url = $this->buildTrackingUrl($path);

        return $body.'<img src="'.$url.'" width="0" height="0" alt="" style="visibility:hidden" />';
    }

    public function buildTrackingUrl($path)
    {
        $host = $this->getTrackingHost();

        return join_url($host, $path);
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
     * Check if email footer enabled.
     *
     * @return string
     */
    public function footerEnabled()
    {
        return ($this->automation->customer->getCurrentSubscription()->plan->getOption('email_footer_enabled') == 'yes') ? true : false;
    }

    /**
     * Get HTML footer.
     *
     * @return string
     */
    public function getHtmlFooter()
    {
        return $this->automation->customer->getCurrentSubscription()->plan->getOption('html_footer');
    }

    /**
     * Append footer.
     *
     * @return string.
     */
    public function appendFooter($body, $footer)
    {
        $body.$footer;
        return preg_replace('/<\/\s*html\s*>/i', $footer.'</html>', $body);
    }

    /**
     * Convert html to inline.
     *
     * @todo not very OOP here, consider moving this to a Helper instead
     */
    public function inlineHtml($html)
    {
        return $html;

        // TODO: review this stuff!
        if ($this->isLayout($html)) {
            // Convert to inline css if template source is builder
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

    public function isOpened($subscriber)
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
                            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    public function isClicked($subscriber)
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
                            ->join('click_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    /**
     * Check if email has template.
     */
    public function hasTemplate()
    {
        return $this->content;
    }

    /**
     * Remove email template.
     */
    public function removeTemplate()
    {
        $this->content = null;
        \Acelle\Library\Tool::xdelete($this->getStoragePath());
        $this->save();
    }

    /**
     * Update email plain text.
     */
    public function updatePlainFromContent()
    {
        if (!$this->plain) {
            $this->plain = preg_replace('/\s+/', ' ', preg_replace('/\r\n/', ' ', strip_tags($this->content)));
            $this->save();
        }
    }

    /**
     * Fill email's fields from request.
     */
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
}
