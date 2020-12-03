<?php

/**
 * SendingServer class.
 *
 * An abstract class for different types of sending servers
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
use Acelle\Library\Log as MailLog;
use Acelle\Library\QuotaTrackerFile;
use Acelle\Library\IdentityStore;
use Carbon\Carbon;
use Acelle\Library\StringHelper;
use Acelle\Library\Notification\BackendError as BackendErrorNotification;

class SendingServer extends Model
{
    const DELIVERY_STATUS_SENT = 'sent';
    const DELIVERY_STATUS_FAILED = 'failed';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // TYPE
    const TYPE_AMAZON_API = 'amazon-api';
    const TYPE_AMAZON_SMTP = 'amazon-smtp';
    const TYPE_SENDGRID_API = 'sendgrid-api';
    const TYPE_SENDGRID_SMTP = 'sendgrid-smtp';
    const TYPE_MAILGUN_API = 'mailgun-api';
    const TYPE_MAILGUN_SMTP = 'mailgun-smtp';
    const TYPE_ELASTICEMAIL_API = 'elasticemail-api';
    const TYPE_ELASTICEMAIL_SMTP = 'elasticemail-smtp';
    const TYPE_SPARKPOST_API = 'sparkpost-api';
    const TYPE_SPARKPOST_SMTP = 'sparkpost-smtp';
    const TYPE_PHP_MAIL = 'php-mail';
    const TYPE_SENDMAIL = 'sendmail';
    const TYPE_SMTP = 'smtp';

    protected $quotaTracker;
    protected $subAccount;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * @note important! consider updating the $fillable variable, it will affect some other methods
     */
    protected $fillable = [
        'name', 'type', 'host', 'aws_access_key_id', 'aws_secret_access_key', 'aws_region', 'domain', 'api_key', 'api_secret_key', 'smtp_username',
        'smtp_password', 'smtp_port', 'smtp_protocol', 'quota_value', 'sendmail_path', 'quota_base', 'quota_unit',
        'bounce_handler_id', 'feedback_loop_handler_id', 'status', 'default_from_email',
    ];

    // Supported server types
    public static $serverMapping = array(
        self::TYPE_AMAZON_API => 'SendingServerAmazonApi',
        self::TYPE_AMAZON_SMTP => 'SendingServerAmazonSmtp',
        self::TYPE_SMTP => 'SendingServerSmtp',
        self::TYPE_SENDMAIL => 'SendingServerSendmail',
        self::TYPE_PHP_MAIL => 'SendingServerPhpMail',
        self::TYPE_MAILGUN_API => 'SendingServerMailgunApi',
        self::TYPE_MAILGUN_SMTP => 'SendingServerMailgunSmtp',
        self::TYPE_SENDGRID_API => 'SendingServerSendGridApi',
        self::TYPE_SENDGRID_SMTP => 'SendingServerSendGridSmtp',
        self::TYPE_ELASTICEMAIL_API => 'SendingServerElasticEmailApi',
        self::TYPE_ELASTICEMAIL_SMTP => 'SendingServerElasticEmailSmtp',
        self::TYPE_SPARKPOST_API => 'SendingServerSparkPostApi',
        self::TYPE_SPARKPOST_SMTP => 'SendingServerSparkPostSmtp',
    );

    /**
     * Tracking logs.
     *
     * @return collection
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog', 'sending_server_id')->orderBy('created_at', 'asc');
    }

    /**
     * Plans.
     *
     * @return collection
     */
    public function plans()
    {
        return $this->belongsToMany('Acelle\Model\Plan', 'plans_sending_servers');
    }

    /**
     * Plans.
     *
     * @return collection
     */
    public function plansSendingServers()
    {
        return $this->hasMany('Acelle\Model\PlansSendingServer', 'sending_server_id');
    }

    /**
     * Get the bounce handler.
     */
    public function bounceHandler()
    {
        return $this->belongsTo('Acelle\Model\BounceHandler');
    }

    public function sendingDomains()
    {
        return $this->hasMany('Acelle\Model\SendingDomain', 'sending_server_id');
    }

    /**
     * Senders.
     *
     * @return collection
     */
    public function senders()
    {
        return $this->hasMany('Acelle\Model\Sender', 'sending_server_id');
    }

    /**
     * Map a server to its class type and retrive an instance from the database.
     *
     * @return mixed
     *
     * @param campaign
     */
    public static function mapServerType($server)
    {
        $class_name = '\Acelle\Model\\'.self::$serverMapping[$server->type];

        if ($server->id) {
            $instance = $class_name::find($server->id);
        } else {
            $instance = new $class_name(['type' => $server->type]);
        }

        $instance->fill($server->getAttributes());

        return $instance;
    }

    /**
     * Map a server to its class type and initiate an instance.
     *
     * @return object sending server of its particular type
     */
    public static function getInstance($server)
    {
        $class_name = '\Acelle\Model\\'.self::$serverMapping[$server->type];
        $attributes = $server->toArray();
        if (array_key_exists('id', $attributes)) {
            unset($attributes['id']);
        }

        return new $class_name($attributes);
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public function getVerp($recipient)
    {
        if (is_object($this->bounceHandler)) {
            $validator = \Validator::make(
                ['email' => $this->bounceHandler->username],
                ['email' => 'required|email']
            );

            if ($validator->passes()) {
                // @todo disable VERP as it is not supported by all mailbox
                // return str_replace('@', '+'.str_replace('@', '=', $recipient).'@', $this->bounceHandler->username);
                return $this->bounceHandler->username;
            } else {
                // @todo raise an error here, hold off the entire campaign
                return $this->bounceHandler->email;
            }
        }

        return;
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::where('status', '=', 'active');
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public function admin()
    {
        return $this->belongsTo('Acelle\Model\Admin');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('sending_servers.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('sending_servers.name', 'like', '%'.$keyword.'%')
                        ->orWhere('sending_servers.type', 'like', '%'.$keyword.'%')
                        ->orWhere('sending_servers.host', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['type'])) {
                $query = $query->where('sending_servers.type', '=', $filters['type']);
            }
        }

        // Other filter
        if (!empty($request->customer_id)) {
            $query = $query->where('sending_servers.customer_id', '=', $request->customer_id);
        }

        if (!empty($request->admin_id)) {
            $query = $query->where('sending_servers.admin_id', '=', $request->admin_id);
        }

        // remove customer sending servers
        if (!empty($request->no_customer)) {
            $query = $query->whereNull('customer_id');
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

        if (!empty($request->sort_order)) {
            $query = $query->orderBy($request->sort_order, $request->sort_direction);
        }

        return $query;
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
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

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
            while (SendingServer::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // SendingServer custom order
            SendingServer::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });
    }

    /**
     * Type of server.
     *
     * @return object
     */
    public static function types()
    {
        return [
            self::TYPE_AMAZON_SMTP => [
                'cols' => [
                    'host' => 'required',
                    'aws_access_key_id' => 'required',
                    'aws_secret_access_key' => 'required',
                    'aws_region' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_AMAZON_API => [
                'cols' => [
                    'aws_access_key_id' => 'required',
                    'aws_secret_access_key' => 'required',
                    'aws_region' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SENDGRID_SMTP => [
                'cols' => [
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SENDGRID_API => [
                'cols' => [
                    'api_key' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_MAILGUN_API => [
                'cols' => [
                    'api_key' => 'required',
                    'domain' => 'required',
                    'host' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_MAILGUN_SMTP => [
                'cols' => [
                    'domain' => 'required',
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_ELASTICEMAIL_API => [
                'cols' => [
                    'api_key' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_ELASTICEMAIL_SMTP => [
                'cols' => [
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SPARKPOST_API => [
                'cols' => [
                    'host' => 'required',
                    'api_key' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SPARKPOST_SMTP => [
                'cols' => [
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => '',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SMTP => [
                'cols' => [
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => '',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                    'bounce_handler_id' => '',
                    'feedback_loop_handler_id' => '',
                ],
            ],
            self::TYPE_SENDMAIL => [
                'cols' => [
                    'sendmail_path' => 'required',
                ],
                'settings' => [
                    'name' => 'required',
                    'default_from_email' => 'email',
                    'bounce_handler_id' => '',
                    'feedback_loop_handler_id' => '',
                ],
            ],
        ];
    }

    /**
     * Type of server.
     *
     * @return object
     */
    public static function frontendTypes()
    {
        return [
            self::TYPE_AMAZON_SMTP => [
                'cols' => [
                    'name' => 'required',
                    'host' => 'required',
                    'aws_access_key_id' => 'required',
                    'aws_secret_access_key' => 'required',
                    'aws_region' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_AMAZON_API => [
                'cols' => [
                    'name' => 'required',
                    'aws_access_key_id' => 'required',
                    'aws_secret_access_key' => 'required',
                    'aws_region' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SENDGRID_SMTP => [
                'cols' => [
                    'name' => 'required',
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SENDGRID_API => [
                'cols' => [
                    'name' => 'required',
                    'api_key' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_MAILGUN_API => [
                'cols' => [
                    'name' => 'required',
                    'api_key' => 'required',
                    'domain' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_MAILGUN_SMTP => [
                'cols' => [
                    'name' => 'required',
                    'domain' => 'required',
                    'api_key' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_ELASTICEMAIL_API => [
                'cols' => [
                    'name' => 'required',
                    'api_key' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_ELASTICEMAIL_SMTP => [
                'cols' => [
                    'name' => 'required',
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SPARKPOST_API => [
                'cols' => [
                    'name' => 'required',
                    'host' => 'required',
                    'api_key' => 'required',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SPARKPOST_SMTP => [
                'cols' => [
                    'name' => 'required',
                    'api_key' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => '',
                    'default_from_email' => 'email',
                ],
            ],
            self::TYPE_SMTP => [
                'cols' => [
                    'name' => 'required',
                    'host' => 'required',
                    'smtp_username' => 'required',
                    'smtp_password' => 'required',
                    'smtp_port' => 'required',
                    'smtp_protocol' => '',
                    'default_from_email' => 'email',
                    'bounce_handler_id' => '',
                    'feedback_loop_handler_id' => '',
                ],
            ],
            self::TYPE_SENDMAIL => [
                'cols' => [
                    'name' => 'required',
                    'sendmail_path' => 'required',
                    'default_from_email' => 'email',
                    'bounce_handler_id' => '',
                    'feedback_loop_handler_id' => '',
                ],
            ],
        ];
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions()
    {
        $query = self::getAll();
        $options = $query->orderBy('name')->get()->map(function ($item) {
            return ['value' => $item->uid, 'text' => $item->name];
        });

        return $options;
    }

    /**
     * Get sparkpost select options.
     *
     * @return array
     */
    public static function getSparkpostHostnameSelectOptions()
    {
        $options = [
            ['text' => trans('messages.choose'), 'value' => ''],
            ['text' => 'SparkPost Global', 'value' => 'api.sparkpost.com'],
            ['text' => 'SparkPost EU', 'value' => 'api.eu.sparkpost.com'],
        ];

        return $options;
    }

    /**
     * Get sending server's quota.
     *
     * @return string
     */
    public function getSendingQuota()
    {
        return $this->quota_value;
    }

    /**
     * Get sending server's sending quota.
     *
     * @return string
     */
    public function getSendingQuotaUsage()
    {
        $tracker = $this->getQuotaTracker();

        return $tracker->getUsage();
    }

    /**
     * Get rules.
     *
     * @return string
     */
    public static function rules($type)
    {
        $rules = self::types()[$type]['cols'];
        $rules['quota_value'] = 'required|numeric';
        $rules['quota_base'] = 'required|numeric';
        $rules['quota_unit'] = 'required';

        return $rules;
    }

    /**
     * Get rules.
     *
     * @return string
     */
    public static function frontendRules($type)
    {
        $rules = self::frontendTypes()[$type]['cols'];
        $rules['quota_value'] = 'required|numeric';
        $rules['quota_base'] = 'required|numeric';
        $rules['quota_unit'] = 'required';

        return $rules;
    }

    /**
     * Get rules.
     *
     * @return string
     */
    public function getFrontendRules()
    {
        $rules = self::frontendTypes()[$this->type]['cols'];

        return $rules;
    }

    /**
     * Get rules.
     *
     * @return string
     */
    public function getRules()
    {
        $rules = self::types()[$this->type]['cols'];

        return $rules;
    }

    /**
     * Test connection.
     *
     * @return object
     */
    public function validConnection($request)
    {
        $validator = \Validator::make($request->all(), $this->getRules());

        // test amazon api connection
        $validator->after(function ($validator) {
            try {
                $this->test();
            } catch (\Exception $e) {
                $validator->errors()->add('connection', $e->getMessage());
            }
        });

        return $validator;
    }

    /**
     * Get configuration rules.
     *
     * @return string
     */
    public function getConfigRules()
    {
        $rules = self::types()[$this->type]['settings'];

        return $rules;
    }

    /**
     * Quota display.
     *
     * @return string
     */
    public function displayQuota()
    {
        if ($this->quota_value == -1) {
            return trans('messages.unlimited');
        }

        return $this->quota_value.'/'.$this->quota_base.' '.trans('messages.'.\Acelle\Library\Tool::getPluralPrase($this->quota_unit, $this->quota_base));
    }

    /**
     * Quota display.
     *
     * @return string
     */
    public function displayQuotaHtml()
    {
        if ($this->quota_value == -1) {
            return trans('messages.unlimited');
        }

        return '<b>'.$this->quota_value.'</b>/<b>'.$this->quota_base.' '.trans('messages.'.\Acelle\Library\Tool::getPluralPrase($this->quota_unit, $this->quota_base)).'</b>';
    }

    /**
     * Select options for aws region.
     *
     * @return array
     */
    public static function awsRegionSelectOptions()
    {
        return [
            ['value' => '', 'text' => trans('messages.choose')],
            ['value' => 'us-east-1', 'text' => 'US East (N. Virginia)', 'host' => 'email-smtp.us-east-1.amazonaws.com'],
            ['value' => 'us-east-2', 'text' => 'US East (Ohio)', 'host' => 'email-smtp.us-east-2.amazonaws.com'],
            ['value' => 'us-west-2', 'text' => 'US West (Oregon)', 'host' => 'email-smtp.us-west-2.amazonaws.com'],
            ['value' => 'ap-south-1', 'text' => 'Asia Pacific (Mumbai)', 'host' => 'email-smtp.ap-south-1.amazonaws.com'],
            ['value' => 'ap-southeast-1', 'text' => 'Asia Pacific (Singapore)', 'host' => 'email-smtp.ap-southeast-1.amazonaws.com'],
            ['value' => 'ap-southeast-2', 'text' => 'Asia Pacific (Sydney)', 'host' => 'email-smtp.ap-southeast-2.amazonaws.com'],
            ['value' => 'ap-northeast-1', 'text' => 'Asia Pacific (Tokyo)', 'host' => 'email-smtp.ap-northeast-1.amazonaws.com'],
            ['value' => 'ap-northeast-2', 'text' => 'Asia Pacific (Seoul)', 'host' => 'email-smtp.ap-northeast-2.amazonaws.com'],
            ['value' => 'eu-central-1', 'text' => 'Europe (Frankfurt)', 'host' => 'email-smtp.eu-central-1.amazonaws.com'],
            ['value' => 'eu-west-1', 'text' => 'EU (Ireland)', 'host' => 'email-smtp.eu-west-1.amazonaws.com'],
            ['value' => 'eu-west-2', 'text' => 'Europe (London)', 'host' => 'email-smtp.eu-west-2.amazonaws.com'],
            ['value' => 'ca-central-1', 'text' => 'Canada (Central)', 'host' => 'email-smtp.ca-central-1.amazonaws.com'],
            ['value' => 'sa-east-1', 'text' => 'South America (São Paulo)', 'host' => 'email-smtp.sa-east-1.amazonaws.com'],
        ];
    }

    /**
     * Select options for aws region.
     *
     * @return array
     */
    public static function mailgunRegionSelectOptions()
    {
        return [
            ['value' => '', 'text' => trans('messages.choose')],
            ['value' => 'https://api.mailgun.net/v3', 'text' => 'US/Global Server'],
            ['value' => 'https://api.eu.mailgun.net/v3', 'text' => 'EU Server'],
        ];
    }

    /**
     * Disable sending server.
     *
     * @return array
     */
    public function disable()
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Enable sending server.
     *
     * @return array
     */
    public function enable()
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Get sending server's QuotaTracker.
     *
     * @return array
     */
    public function getQuotaTracker()
    {
        if (!$this->quotaTracker) {
            $this->initQuotaTracker();
        }

        return $this->quotaTracker;
    }

    /**
     * Initialize the quota tracker.
     */
    public function initQuotaTracker()
    {
        $this->quotaTracker = new QuotaTrackerFile($this->getSendingQuotaLockFile(), ['start' => $this->created_at->timestamp, 'max' => -1], [$this->getQuotaIntervalString() => $this->getSendingQuota()]);
        $this->quotaTracker->cleanupSeries();
        // @note: in case of multi-process, the following command must be issued manually
        //     $this->renewQuotaTracker();
    }

    /**
     * Clean up the quota tracking files to prevent it from growing too large.
     */
    public function cleanupQuotaTracker()
    {
        // @todo: hard-coded for 1 month
        $this->getQuotaTracker()->cleanupSeries(null, '1 month');
    }

    /**
     * Get sending quota lock file.
     *
     * @return string file path
     */
    public function getSendingQuotaLockFile()
    {
        return storage_path("app/server/quota/{$this->uid}");
    }

    /**
     * Get quota starting time.
     *
     * @return string
     */
    public function getQuotaIntervalString()
    {
        return "{$this->quota_base} {$this->quota_unit}";
    }

    /**
     * Get quota starting time.
     *
     * @return array
     */
    public function getQuotaStartingTime()
    {
        return "{$this->getQuotaIntervalString()} ago";
    }

    /**
     * Increment quota usage.
     */
    public function countUsage(Carbon $timePoint = null)
    {
        return $this->getQuotaTracker($timePoint)->add();
    }

    /**
     * Check if user has used up all quota allocated.
     *
     * @return string
     */
    public function overQuota()
    {
        return !$this->getQuotaTracker()->check();
    }

    /**
     * Check if sending server supports custom ReturnPath header (used for bounced/feedback handling).
     *
     * @return bool
     */
    public function allowCustomReturnPath()
    {
        return  $this->type == 'smtp' || $this->type == 'sendmail' || $this->type == 'php-mail';
    }

    /**
     * Get all active items.
     *
     * @return collect
     */
    public static function getAllActive()
    {
        return self::where('status', '=', self::STATUS_ACTIVE);
    }

    /**
     * Get all active system items.
     *
     * @return collect
     */
    public static function getAllAdminActive()
    {
        return self::getAllActive()->whereNotNull('admin_id');
    }

    /**
     * Add customer action log.
     */
    public function log($name, $customer, $add_datas = [])
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        $data = array_merge($data, $add_datas);

        Log::create([
            'customer_id' => $customer->id,
            'type' => 'sending_server',
            'name' => $name,
            'data' => json_encode($data),
        ]);
    }

    /**
     * Send a test email for the sending server.
     */
    public function sendTestEmail($params)
    {
        /*
         * Required keys include
         *     + from_email
         *     + to_email
         *     + subject
         *     + plain
         */
        MailLog::info(sprintf('Sending test email to %s for sending server `%s`', $params['to_email'], $this->name));
        $message = new \Swift_Message();
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($params['from_email']));
        $message->setId($msgId);
        $message->getHeaders()->addTextHeader('X-Acelle-Message-Id', $msgId); // this header is required for SendGrid API sending server
        $message->setContentType('text/plain; charset=utf-8');
        $message->setSubject($params['subject']);
        $message->setFrom($params['from_email']);
        $message->setTo($params['to_email']);
        $message->setReplyTo($params['from_email']);
        // $message->setEncoder(\Swift_Encoding::get8bitEncoding());
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($params['plain'], 'text/plain');
        $result = self::getInstance($this)->send($message);

        if (array_key_exists('error', $result)) {
            throw new \Exception($result['error']);
        }

        return true;
    }

    /**
     * Check if the sending server is ElasticEmailAPI or ElasticEmailSmtp.
     *
     * @return bool
     */
    public function isElasticEmailServer()
    {
        return $this->type == 'elasticemail-api' || $this->type == 'elasticemail-smtp';
    }

    /**
     * Get all sub-account supported sending server types.
     *
     * @return array
     */
    public static function getSubAccountTypes()
    {
        return [
            'sendgrid-api',
            'sendgrid-smtp',
        ];
    }

    public function setSubAccount($subAccount)
    {
        $this->subAccount = $subAccount;
    }

    /**
     * Get sending server select2 select options.
     *
     * @return array
     */
    public static function select2($request)
    {
        $data = ['items' => [], 'more' => true];

        $query = self::getAll();
        if (isset($request->q)) {
            $keyword = $request->q;
            $query = $query->where(function ($q) use ($keyword) {
                $q->orwhere('sending_servers.name', 'like', '%'.$keyword.'%');
            });
        }

        // plan
        if ($request->plan_uid) {
            $plan = \Acelle\Model\Plan::findByUid($request->plan_uid);
            $existIds = $plan->plansSendingServers()->pluck('sending_server_id')->toArray();
        }

        foreach ($query->limit(20)->get() as $server) {
            if ($request->plan_uid && in_array($server->id, $existIds)) {
                $data['items'][] = [
                    'id' => $server->uid,
                    'text' => $server->name.' ('.trans('messages.sending_server.added').')'.'|||'.trans('messages.'.$server->type),
                    'disabled' => true,
                ];
            } else {
                $data['items'][] = ['id' => $server->uid, 'text' => $server->name.'|||'.trans('messages.'.$server->type)];
            }
        }

        return json_encode($data);
    }

    /**
     * Get sending server select2 select options.
     *
     * @return array
     */
    public static function adminSelect2($request)
    {
        $data = ['items' => [], 'more' => true];

        $query = self::getAll()->whereNull('customer_id');
        if (isset($request->q)) {
            $keyword = $request->q;
            $query = $query->where(function ($q) use ($keyword) {
                $q->orwhere('sending_servers.name', 'like', '%'.$keyword.'%');
            });
        }

        // plan
        if ($request->plan_uid) {
            $plan = \Acelle\Model\Plan::findByUid($request->plan_uid);
            $existIds = $plan->plansSendingServers()->pluck('sending_server_id')->toArray();
        }

        foreach ($query->limit(20)->get() as $server) {
            if ($request->plan_uid && in_array($server->id, $existIds)) {
                $data['items'][] = [
                    'id' => $server->uid,
                    'text' => $server->name.' ('.trans('messages.sending_server.added').')'.'|||'.trans('messages.'.$server->type),
                    'disabled' => true,
                ];
            } else {
                $data['items'][] = ['id' => $server->uid, 'text' => $server->name.'|||'.trans('messages.'.$server->type)];
            }
        }

        return json_encode($data);
    }

    /**
     * Get sending limit types.
     *
     * @return array
     */
    public static function sendingLimitValues()
    {
        return [
            'unlimited' => [
                'quota_value' => -1,
                'quota_base' => -1,
                'quota_unit' => 'day',
            ],
            '100_per_minute' => [
                'quota_value' => 100,
                'quota_base' => 1,
                'quota_unit' => 'minute',
            ],
            '1000_per_hour' => [
                'quota_value' => 1000,
                'quota_base' => 1,
                'quota_unit' => 'hour',
            ],
            '10000_per_day' => [
                'quota_value' => 10000,
                'quota_base' => 1,
                'quota_unit' => 'day',
            ],
        ];
    }

    /**
     * Get sending limit select options.
     *
     * @return array
     */
    public function getSendingLimitSelectOptions()
    {
        $options = [];
        $current = trans('messages.sending_servers.sending_limit.phrase', [
            'quota_value' => \Acelle\Library\Tool::format_number($this->quota_value),
            'quota_base' => \Acelle\Library\Tool::format_number($this->quota_base),
            'quota_unit' => $this->quota_unit,
        ]);
        if ($this->quota_value == -1) {
            $current = trans('messages.sending_server.quota.unlimited');
        }

        $exist = false;
        foreach (self::sendingLimitValues() as $key => $data) {
            $wording = trans('messages.sending_servers.sending_limit.phrase', [
                'quota_value' => \Acelle\Library\Tool::format_number($data['quota_value']),
                'quota_base' => \Acelle\Library\Tool::format_number($data['quota_base']),
                'quota_unit' => $data['quota_unit'],
            ]);

            if ($data['quota_value'] == -1) {
                $wording = trans('messages.sending_server.quota.unlimited');
            }

            $options[] = ['text' => $wording, 'value' => $key];

            if ($wording == $current) {
                $exist = true;
                $this->setOption('sending_limit', $key);
            }
        }

        // exist
        if (!$exist) {
            $options[] = ['text' => $current, 'value' => 'current'];
            $this->setOption('sending_limit', 'current');
        }

        // Custom
        $options[] = ['text' => trans('messages.sending_servers.quota.custom'), 'value' => 'custom'];

        return $options;
    }

    /**
     * Default options.
     *
     * @return array
     */
    public static function defaultOptions()
    {
        return [
            'domains' => [],
            'emails' => [],
            'allow_unverified_from_email' => 'no',
            'allow_verify_domain_remotely' => 'no',
            'allow_verify_email_remotely' => 'no',
            'allow_verify_domain_remotely' => 'no',
            'allow_verify_email_remotely' => 'no',
        ];
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        $savedOptions = isset($this->options) ? json_decode($this->options, true) : [];

        return array_merge(self::defaultOptions(), $savedOptions);
    }

    /**
     * Get option.
     *
     * @return array
     */
    public function getOption($name)
    {
        $options = $this->getOptions();

        $value = isset($options[$name]) ? $options[$name] : null;

        // default value
        if (!$value) {
            // default verification email
            if ($name == 'custom_verification_email') {
                $value = trans('messages.sending_server.default_email_verification.content');
                ;
            }
            
            // default verification email
            if ($name == 'custom_verification_email_subject') {
                $value = trans('messages.sending_server.default_email_verification.subject');
                ;
            }
        }


        return $value;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function setOptions($options)
    {
        $savingOptions = $this->getOptions();
        foreach ($options as $key => $option) {
            $savingOptions[$key] = $option;
        }

        $this->options = json_encode($savingOptions);
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function setOption($name, $value)
    {
        if (!isset($this->options)) {
            $options = [];
        } else {
            $options = json_decode($this->options, true);
        }

        $options[$name] = $value;

        $this->options = json_encode($options);
    }

    /**
     * Get Mailgun domains info.
     *
     * @return array
     */
    public function getMailgunDomainInfo()
    {
        return [
            [
                'domain' => 'acellemail.com',
                'created_at' => \Carbon\Carbon::now()->subDay(2),
            ],
            [
                'domain' => 'bolero.vn',
                'created_at' => \Carbon\Carbon::now()->subDay(13),
            ],
        ];
    }

    /**
     * Get local identity info.
     *
     * @return array
     */
    public function getLocalIdentityInfo()
    {
        return [
            [
                'type' => 'domain',
                'name' => 'acellemail.com',
                'created_at' => \Carbon\Carbon::now()->subDay(2),
            ],
            [
                'type' => 'domain',
                'name' => 'bolero.vn',
                'created_at' => \Carbon\Carbon::now()->subDay(13),
            ],
            [
                'type' => 'email',
                'name' => 'system@acellemail.com',
                'created_at' => \Carbon\Carbon::now()->subDay(13),
            ],
        ];
    }

    /**
     * Add domain.
     *
     * @return array
     */
    public function addIdentity($domain)
    {
        $identityStore = $this->getIdentityStore();
        $identityStore->add([ $domain => ['VerificationStatus' => true ]]);
        $this->setOption('identities', $identityStore->get());
        $this->save();
    }

    /**
     * Add domain.
     *
     * @return array
     */
    public function removeIdentity($identity)
    {
        $identityStore = $this->getIdentityStore();
        $identityStore->remove($identity);
        $this->setOption('identities', $identityStore->get());
        $this->save();
    }

    /**
     * Check if domain is enabled.
     *
     * @return array
     */
    public function isDomainEnabled($domain)
    {
        $domains = $this->getDomains();

        return in_array($domain, $domains);
    }

    /**
     * Check if emails is enabled.
     *
     * @return array
     */
    public function isEmailEnabled($email)
    {
        $emails = $this->getEmails();

        return in_array($email, $emails);
    }

    /**
     * Check if domain is enabled.
     *
     * @return array
     */
    public function isIdentityEnabled($type, $value)
    {
        $values = $this->getOption($type.'s');

        return in_array($values, $value);
    }

    /**
     * Allow user to verify his/her own sending domain against Acelle Mail.
     *
     * @return bool
     */
    public function allowVerifyingOwnDomains()
    {
        $options = json_decode($this->options, true);

        if (is_null($options)) {
            return false;
        }

        return array_key_exists('allow_verify_domain_against_acelle', $options) && $options['allow_verify_domain_against_acelle'] == 'yes';
    }

    /**
     * Allow user to verify his/her own sending domain against Acelle Mail.
     *
     * @return bool
     */
    public function allowVerifyingOwnEmails()
    {
        $options = json_decode($this->options, true);

        if (is_null($options)) {
            return false;
        }

        return array_key_exists('allow_verify_email_against_acelle', $options) && $options['allow_verify_email_against_acelle'] == 'yes';
    }

    /**
     * Allow user to verify his/her own emails against AWS.
     *
     * @return bool
     */
    public function allowVerifyingOwnDomainsRemotely()
    {
        $options = json_decode($this->options, true);

        if (is_null($options)) {
            return false;
        }

        return array_key_exists('allow_verify_domain_remotely', $options) && $options['allow_verify_domain_remotely'] == 'yes';
    }

    /**
     * Allow user to verify his/her own emails against AWS.
     *
     * @return bool
     */
    public function allowVerifyingOwnEmailsRemotely()
    {
        $options = json_decode($this->options, true);

        if (is_null($options)) {
            return false;
        }

        return array_key_exists('allow_verify_email_remotely', $options) && $options['allow_verify_email_remotely'] == 'yes';
    }

    /**
     * Allow user send from unverified FROM email address.
     *
     * @return bool
     */
    public function allowUnverifiedFromEmailAddress()
    {
        $options = json_decode($this->options, true);

        if (is_null($options)) {
            return false;
        }

        return array_key_exists('allow_unverified_from_email', $options) && $options['allow_unverified_from_email'] == 'yes';
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        return true;
    }

    /**
     * Get all verified identities.
     *
     * @return array
     */
    public function verifiedIdentitiesDroplist($keyword = null)
    {
        $droplist = [];
        $topList = [];
        $bottomList = [];

        if (!$keyword) {
            $keyword = '###';
        }

        foreach ($this->getVerifiedIdentities() as $item) {
            // check if email
            if (extract_email($item) !== null) {
                $email = extract_email($item);
                if (strpos(strtolower($email), $keyword) === 0) {
                    $topList[] = [
                            'text' => extract_name($item),
                            'value' => $email,
                            'desc' => str_replace($keyword, '<span class="text-semibold text-primary"><strong>'.$keyword.'</strong></span>', $email),
                        ];
                } else {
                    $bottomList[] = [
                            'text' => extract_name($item),
                            'value' => $email,
                            'desc' => $email,
                        ];
                }
            } else { // domains are alse
                $dKey = explode('@', $keyword);
                $dKey = isset($dKey[1]) ? $dKey[1] : null;
                // if ( (!isset($dKey) || $dKey == '') || ($dKey && strpos(strtolower($item), $dKey) === 0 )) {
                $topList[] = [
                            'text' => '****@'.str_replace($dKey, '<span class="text-semibold text-primary"><strong>'.$dKey.'</strong></span>', $item),
                            'subfix' => $item,
                            'desc' => null,
                        ];
                // }
            }
        }

        $droplist = array_merge($topList, $bottomList);

        return $droplist;
    }

    /**
     * Show up sending notification to admin dashboard.
     *
     * @return array
     * @note the scope of this function is private, use public only for inheritance
     */
    public function raiseSendingError($exception)
    {
        MailLog::warning('Sending failed');
        MailLog::warning($exception->getMessage());

        $errorTitle = "Server `{$this->name}` ({$this->uid}) failed to send";
        BackendErrorNotification::cleanupDuplicateNotifications($errorTitle);
        BackendErrorNotification::warning(['title' => $errorTitle, 'message' => date('Y-m-d').' '.$errorTitle.': '.$exception->getMessage()], false);
    }

    /**
     * Delete sending server.
     */
    public function doDelete()
    {
        $plans = $this->plans;

        // delete
        $this->delete();

        // check all plans status
        foreach ($plans as $plan) {
            $plan->checkStatus();
        }
    }

    public function updateIdentitiesList($selected)
    {
        // For now, it is for Amazon only
        $options = $this->getOptions();
        if (!array_key_exists('identities', $options)) {
            return;
        }

        $selectedEmails = array_key_exists('emails', $selected) ? $selected['emails'] : [];
        $selectedDomains = array_key_exists('domains', $selected) ? $selected['domains'] : [];
        $identityStore = new IdentityStore($options['identities']);
        $identityStore->select(array_merge($selectedEmails, $selectedDomains));

        $options['identities'] = $identityStore->get();
        $this->setOptions($options);
    }

    public function getVerifiedIdentities()
    {
        // by default, only return SELECTED | VERIRIED | NON-PRIVATE identities
        $filtered = $this->getIdentityStore()->get(['Selected' => true, 'UserId' => null, 'VerificationStatus' => 'Success']);
        return array_keys($filtered);
    }

    public function getIdentityStore() : IdentityStore
    {
        $options = $this->getOptions();
        $identityStore = new IdentityStore(array_key_exists('identities', $options) ? $options['identities'] : []);
        return $identityStore;
    }

    public function mapType()
    {
        return self::mapServerType($this);
    }

    /**
     * Check an identity (email or domain) if it is verified against AWS.
     *
     * @return bool
     */
    public function verifyIdentity($identity)
    {
        $this->syncIdentities();
        $verifiedIdentities = array_keys($this->getIdentityStore()->get(['VerificationStatus' => IdentityStore::VERIFICATION_STATUS_SUCCESS]));
        return in_array($identity, $verifiedIdentities);
    }

    public function sendWithDefaultFromAddress($message, $params = [])
    {
        if (empty($this->from_name)) {
            $message->setFrom([ $this->from_name => $this->from_address ]);
        } else {
            $message->setFrom($this->from_address);
        }

        return  $this->send($message, $params);
    }

    public function setDefaultFromEmailAddress()
    {
        if (!empty($this->default_from_email)) {
            //    return;
        }

        $identityStore = $this->getIdentityStore();
        $names = array_keys($identityStore->get(['VerificationStatus' => 'Success']));
        
        $emails = array_values(array_filter($names, function ($name) {
            return checkEmail($name);
        }));
        $domains = array_values(array_filter($names, function ($name) {
            return !checkEmail($name);
        }));

        $default = null;

        if (!empty($domains)) {
            $default = 'noreply@'.$domains[0];
        } elseif (!empty($emails)) {
            $default = $emails[0];
        }

        if (!is_null($default)) {
            $this->default_from_email = $default;
            $this->save();
        }
    }
}
