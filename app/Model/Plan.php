<?php

/**
 * Plan class.
 *
 * Model class for Plan
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
use Validator;
use Illuminate\Validation\ValidationException;
use Acelle\Cashier\Interfaces\BillablePlanInterface;
use Acelle\Cashier\Subscription;
use Acelle\Cashier\Cashier;
use Acelle\Model\SendingServer;

class Plan extends Model implements BillablePlanInterface
{
    // Plan status
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';

    // Plan status
    const SENDING_SERVER_OPTION_SYSTEM = 'system';
    const SENDING_SERVER_OPTION_OWN = 'own';
    const SENDING_SERVER_OPTION_SUBACCOUNT = 'subaccount';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'frequency_amount',
        'frequency_unit',
        'price',
        'color',
        'currency_id',
        'tax_billing_required',
        'paddle_plan_id',
        'admin_id',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function rules()
    {
        $rules = array(
            'name' => 'required',
            'currency_id' => 'required',
            'frequency_amount' => 'required|min:1',
            'frequency_unit' => 'required',
            'price' => 'required|min:0',
            'color' => 'required',
        );

        $options = self::defaultOptions();
        foreach ($options as $type => $option) {
            if ($type != 'sending_server_subaccount_uid' && $type != 'email_footer_enabled' && $type != 'email_footer_trial_period_only' && $type != 'html_footer' && $type != 'plain_text_footer') {
                $rules['options.'.$type] = 'required';
            }
        }

        if ($this->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT) {
            $rules['options.sending_server_subaccount_uid'] = 'required';
        }

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function validationRules()
    {
        $rules = [
            'general' => [
                'plan.general.name' => 'required',
                'plan.general.currency_id' => 'required',
                'plan.general.frequency_amount' => 'sometimes|required|min:1',
                'plan.general.frequency_unit' => 'sometimes|required',
                'plan.general.price' => 'required|min:0',
                'plan.general.color' => 'sometimes|required',
            ],
            'options' => [],
        ];

        $options = self::defaultOptions();
        foreach ($options as $type => $option) {
            if ($type != 'sending_server_subaccount_uid' && !in_array($type, ['plain_text_footer', 'html_footer'])) {
                $rules['options']['plan.options.'.$type] = 'sometimes|required';
            }
        }

        if ($this->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT) {
            $rules['options']['plan.options.sending_server_subaccount_uid'] = 'sometimes|required';
        }

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function generalRules()
    {
        $rules = array(
            'name' => 'required',
            'currency_id' => 'required',
            'frequency_amount' => 'required|min:1',
            'frequency_unit' => 'required',
            'price' => 'required|min:0',
            'color' => 'required',
        );

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function resourcesRules()
    {
        $rules = array(
            'options.email_max' => 'required',
            'options.list_max' => 'required',
            'options.subscriber_max' => 'required',
            'options.subscriber_per_list_max' => 'required',
            'options.segment_per_list_max' => 'required',
            'options.campaign_max' => 'required',
            'options.automation_max' => 'required',
            'options.max_process' => 'required',
            'options.max_size_upload_total' => 'required',
            'options.max_file_size_upload' => 'required',
        );

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function sendingLimitRules()
    {
        $rules = array(
            'options.sending_limit' => 'required',
            'options.sending_quota' => 'required',
            'options.sending_quota_time' => 'required',
            'options.sending_quota_time_unit' => 'required',
        );

        return $rules;
    }

    /**
     * The rules for validation.
     *
     * @var array
     */
    public function apiRules()
    {
        $rules = array(
            'name' => 'required',
            'currency_id' => 'required',
            'frequency_amount' => 'required|min:1',
            'frequency_unit' => 'required',
            'price' => 'required|min:0',
            'color' => 'required',
        );

        if ($this->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT) {
            $rules['options.sending_server_subaccount_uid'] = 'required';
        }

        return $rules;
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

    public function admin()
    {
        return $this->belongsTo('Acelle\Model\Admin');
    }

    public function plansSendingServers()
    {
        return $this->hasMany('Acelle\Model\PlansSendingServer');
    }

    public function currency()
    {
        return $this->belongsTo('Acelle\Model\Currency');
    }

    public function subscriptions()
    {
        return $this->hasMany('Acelle\Cashier\Subscription', 'plan_id', 'uid')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', \Carbon\Carbon::now());
            })
            ->orderBy('created_at', 'desc');
    }

    public function plansEmailVerificationServers()
    {
        return $this->hasMany('Acelle\Model\PlansEmailVerificationServer');
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
            while (Plan::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Update custom order
            Plan::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $query = self::select('plans.*');

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('plans.name', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;

        if (!empty($request->admin_id)) {
            $query = $query->where('plans.admin_id', '=', $request->admin_id);
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
     * Disable plan.
     *
     * @return bool
     */
    public function disable()
    {
        $this->status = PaymentMethod::STATUS_INACTIVE;
        $this['visible'] = false;

        return $this->save();
    }

    /**
     * Enable plan.
     *
     * @return bool
     */
    public function enable()
    {
        $this->status = PaymentMethod::STATUS_ACTIVE;

        return $this->save();
    }

    /**
     * Color array.
     *
     * @return array
     */
    public static function colors($default)
    {
        return [
            ['value' => '#1482a0', 'text' => trans('messages.blue')],
            ['value' => '#008c6e', 'text' => trans('messages.green')],
            ['value' => '#917319', 'text' => trans('messages.brown')],
            ['value' => '#aa5064', 'text' => trans('messages.pink')],
            ['value' => '#555', 'text' => trans('messages.grey')],
        ];
    }

    /**
     * Frequency time unit options.
     *
     * @return array
     */
    public static function timeUnitOptions()
    {
        return [
            ['value' => 'day', 'text' => trans('messages.day')],
            ['value' => 'week', 'text' => trans('messages.week')],
            ['value' => 'month', 'text' => trans('messages.month')],
            ['value' => 'year', 'text' => trans('messages.year')],
            // ['value' => 'unlimited', 'text' => trans('messages.plan_time_unlimited')],
        ];
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
            '10000_per_hour' => [
                'quota_value' => 10000,
                'quota_base' => 1,
                'quota_unit' => 'hour',
            ],
            '50000_per_hour' => [
                'quota_value' => 50000,
                'quota_base' => 1,
                'quota_unit' => 'hour',
            ],
            '10000_per_day' => [
                'quota_value' => 10000,
                'quota_base' => 1,
                'quota_unit' => 'day',
            ],
            '100000_per_day' => [
                'quota_value' => 100000,
                'quota_base' => 1,
                'quota_unit' => 'day',
            ],
        ];
    }

    /**
     * Get billing recurs available values.
     *
     * @return array
     */
    public static function billingCycleValues()
    {
        return [
            'daily' => [
                'frequency_amount' => 1,
                'frequency_unit' => 'day',
            ],
            'monthly' => [
                'frequency_amount' => 1,
                'frequency_unit' => 'month',
            ],
            'yearly' => [
                'frequency_amount' => 1,
                'frequency_unit' => 'year',
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

        foreach (self::sendingLimitValues() as $key => $data) {
            $wording = trans('messages.plan.sending_limit.'.$key);
            $options[] = ['text' => $wording, 'value' => $key];
        }

        // exist
        if ($this->getOption('sending_limit') == 'other') {
            $wording = trans('messages.plan.sending_limit.phrase', [
                'quota_value' => format_number($this->getOption('sending_quota')),
                'quota_base' => format_number($this->getOption('sending_quota_time')),
                'quota_unit' => $this->getOption('sending_quota_time_unit'),
            ]);

            $options[] = ['text' => $wording, 'value' => 'other'];
        }

        // Custom
        $options[] = ['text' => trans('messages.plan.sending_limit.custom'), 'value' => 'custom'];

        return $options;
    }

    /**
     * Get billing recurs select options.
     *
     * @return array
     */
    public function getBillingCycleSelectOptions()
    {
        $options = [];

        foreach (self::billingCycleValues() as $key => $data) {
            $wording = trans('messages.plan.billing_cycle.'.$key);
            $options[] = ['text' => $wording, 'value' => $key];
        }

        // exist
        if ($this->getOption('billing_cycle') == 'other') {
            $wording = trans('messages.plan.billing_cycle.phrase', [
                'frequency_amount' => format_number($this->frequency_amount),
                'frequency_unit' => $this->frequency_unit,
            ]);

            $options[] = ['text' => $wording, 'value' => 'other'];
        }

        // Custom
        $options[] = ['text' => trans('messages.plan.billing_cycle.custom'), 'value' => 'custom'];

        return $options;
    }

    /**
     * Default options for new plan.
     *
     * @return array
     */
    public static function defaultOptions()
    {
        $options = [
            'email_max' => '-1',
            'list_max' => '-1',
            'subscriber_max' => '-1',
            'subscriber_per_list_max' => '-1',
            'segment_per_list_max' => '-1',
            'campaign_max' => '-1',
            'automation_max' => '-1',
            'billing_cycle' => 'monthly',
            'sending_limit' => '1000_per_hour',
            'sending_quota' => '-1',
            'sending_quota_time' => '-1',
            'sending_quota_time_unit' => 'day',
            'max_process' => '1',
            'all_sending_servers' => 'yes',
            'max_size_upload_total' => '500',
            'max_file_size_upload' => '5',
            'unsubscribe_url_required' => 'yes',
            'access_when_offline' => 'no',
            //'create_sending_servers' => 'no',
            'create_sending_domains' => 'yes',
            'sending_servers_max' => '-1',
            'sending_domains_max' => '-1',
            'all_email_verification_servers' => 'yes',
            'create_email_verification_servers' => 'no',
            'email_verification_servers_max' => '-1',
            'list_import' => 'yes',
            'list_export' => 'yes',
            'all_sending_server_types' => 'yes',
            'sending_server_types' => [],
            'sending_server_option' => self::SENDING_SERVER_OPTION_SYSTEM,
            'sending_server_subaccount_uid' => null,
            'api_access' => 'yes',
            'email_footer_enabled' => 'no',
            'email_footer_trial_period_only' => 'no',
            'html_footer' => '',
            'plain_text_footer' => '',
            'payment_gateway' => '',
        ];

        // Sending server types
        foreach (\Acelle\Model\SendingServer::types() as $key => $type) {
            $options['sending_server_types'][$key] = 'yes';
        }

        return $options;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->options)) {
            return self::defaultOptions();
        } else {
            $defaul_options = self::defaultOptions();
            $saved_options = json_decode($this->options, true);
            foreach ($defaul_options as $x => $group) {
                if (isset($saved_options[$x])) {
                    $defaul_options[$x] = $saved_options[$x];
                }
            }

            return $defaul_options;
        }
    }

    /**
     * Get option.
     *
     * @return string
     */
    public function getOption($name)
    {
        return $this->getOptions()[$name];
    }

    /**
     * Update sending servers.
     *
     * @return array
     */
    public function updateSendingServers($servers)
    {
        $this->plansSendingServers()->delete();
        foreach ($servers as $key => $param) {
            if ($param['check']) {
                $server = SendingServer::findByUid($key);
                $row = new PlansSendingServer();
                $row->plan_id = $this->id;
                $row->sending_server_id = $server->id;
                $row->fitness = $param['fitness'];
                $row->save();
            }
        }
    }

    /**
     * Multi process select options.
     *
     * @return array
     */
    public static function multiProcessSelectOptions()
    {
        $options = [['value' => 1, 'text' => trans('messages.one_single_process')]];
        for ($i = 2; $i < 4; ++$i) {
            $options[] = ['value' => $i, 'text' => $i];
        }

        return $options;
    }

    /**
     * Display group quota.
     *
     * @return array
     */
    public function displayQuota()
    {
        if ($this->getOption('sending_quota') == -1) {
            return trans('messages.unlimited');
        } elseif ($this->getOption('sending_quota_time') == -1) {
            return $this->getOption('sending_quota');
        } else {
            return strtolower(\Acelle\Library\Tool::format_number($this->getOption('sending_quota')).' '.trans('messages.'.\Acelle\Library\Tool::getPluralPrase('email', $this->getOption('sending_quota'))).' / '.$this->getOption('sending_quota_time').' '.trans('messages.'.\Acelle\Library\Tool::getPluralPrase($this->getOption('sending_quota_time_unit'), $this->getOption('sending_quota'))));
        }
    }

    /**
     * Display plan price.
     *
     * @return array
     */
    public function displayPrice()
    {
        return format_price($this->price, $this->currency->format);
    }

    /**
     * Display total quota.
     *
     * @return array
     */
    public function displayTotalQuota()
    {
        if ($this->getOption('email_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return \Acelle\Library\Tool::format_number($this->getOption('email_max'));
        }
    }

    /**
     * Display max lists.
     *
     * @return array
     */
    public function displayMaxList()
    {
        if ($this->getOption('list_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return \Acelle\Library\Tool::format_number($this->getOption('list_max'));
        }
    }

    /**
     * Display max subscribers.
     *
     * @return array
     */
    public function displayMaxSubscriber()
    {
        if ($this->getOption('subscriber_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return \Acelle\Library\Tool::format_number($this->getOption('subscriber_max'));
        }
    }

    /**
     * Display max campaign.
     *
     * @return array
     */
    public function displayMaxCampaign()
    {
        if ($this->getOption('campaign_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return \Acelle\Library\Tool::format_number($this->getOption('campaign_max'));
        }
    }

    /**
     * Display max campaign.
     *
     * @return array
     */
    public function displayMaxSizeUploadTotal()
    {
        if ($this->getOption('max_size_upload_total') == -1) {
            return trans('messages.unlimited');
        } else {
            return \Acelle\Library\Tool::format_number($this->getOption('max_size_upload_total'));
        }
    }

    /**
     * Display max campaign.
     *
     * @return array
     */
    public function displayFileSizeUpload()
    {
        if ($this->getOption('max_file_size_upload') == -1) {
            return trans('messages.unlimited');
        } else {
            return $this->getOption('max_file_size_upload');
        }
    }

    /**
     * Display sending ervers permission.
     *
     * @return array
     */
    public function displayAllowCreateSendingServer()
    {
        if ($this->getOption('sending_server_option') != \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN) {
            return trans('messages.feature_not_allow');
        }

        if ($this->getOption('sending_servers_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return $this->getOption('sending_servers_max');
        }
    }

    /**
     * Display sending domains permission.
     *
     * @return array
     */
    public function displayAllowCreateSendingDomain()
    {
        if ($this->getOption('create_sending_domains') == 'no') {
            return trans('messages.feature_not_allow');
        }

        if ($this->getOption('sending_domains_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return $this->getOption('sending_domains_max');
        }
    }

    /**
     * Get customer select2 select options.
     *
     * @return array
     */
    public static function select2($request)
    {
        $data = ['items' => [], 'more' => true];

        $query = \Acelle\Model\Plan::getAllActive()->orderBy('custom_order', 'asc');
        if (isset($request->q)) {
            $keyword = $request->q;
            $query = $query->where(function ($q) use ($keyword) {
                $q->orwhere('plans.name', 'like', '%'.$keyword.'%');
            });
        }

        // Read all check
        if ($request->user()->admin && !$request->user()->admin->can('readAll', new \Acelle\Model\Plan())) {
            $query = $query->where('plans.admin_id', '=', $request->user()->admin->id);
        }

        if ($request->change_from_uid) {
            $plan = \Acelle\Model\Plan::findByUid($request->change_from_uid);

            $query = $query->where('id', '<>', $request->change_from_uid);
            $query = $query->where('uid', '<>', $request->change_from_uid);
            $query = $query->where('frequency_amount', '=', $plan->frequency_amount);
            $query = $query->where('frequency_unit', '=', $plan->frequency_unit);
        }

        foreach ($query->limit(20)->get() as $plan) {
            $data['items'][] = ['id' => $plan->uid, 'text' => htmlspecialchars($plan->name).'|||'.htmlspecialchars(\Acelle\Library\Tool::format_price($plan->price, $plan->currency->format))];
        }

        return json_encode($data);
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAllActiveWithDefault($admin = null)
    {
        $query = self::getAll()
            ->where('plans.status', '=', self::STATUS_ACTIVE);

        if (isset($admin) && !$admin->can('readAll', new \Acelle\Model\Plan())) {
            $query = $query->where('plans.admin_id', '=', $admin->id);
        }

        $query = $query->orderBy('custom_order', 'asc');

        return $query;
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getActive($admin = null)
    {
        $query = self::getAll()
            ->where('plans.status', '=', self::STATUS_ACTIVE);
        $query = $query->orderBy('custom_order', 'asc');

        return $query;
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAllActiveVisible($admin = null)
    {
        $query = self::getAll()
            ->where('plans.status', '=', self::STATUS_ACTIVE)
            ->where('plans.visible', '=', true);

        if (isset($admin) && !$admin->can('readAll', new \Acelle\Model\Plan())) {
            $query = $query->where('plans.admin_id', '=', $admin->id);
        }

        $query = $query->orderBy('custom_order', 'asc');

        return $query;
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAllActive($admin = null)
    {
        $query = self::getAllActiveVisible();

        return $query;
    }

    /**
     * Display plan time.
     *
     * @return collect
     */
    public function displayFrequencyTime()
    {
        // unlimited
        if ($this->isTimeUnlimited()) {
            return trans('messages.plan_time_unlimited');
        }

        return $this->frequency_amount.' '.\Acelle\Library\Tool::getPluralPrase($this->frequency_unit, $this->frequency_amount);
    }

    /**
     * Subscriptions count.
     *
     * @return int
     */
    public function subscriptionsCount()
    {
        return $this->subscriptions()->count();
    }

    /**
     * Customers count.
     *
     * @return int
     */
    public function customersCount()
    {
        return $this->subscriptions()->distinct('user_id')->count('user_id');
    }

    /**
     * Frequency time unit options.
     *
     * @return array
     */
    public static function quotaTimeUnitOptions()
    {
        return [
            ['value' => 'minute', 'text' => trans('messages.minute')],
            ['value' => 'hour', 'text' => trans('messages.hour')],
            ['value' => 'day', 'text' => trans('messages.day')],
        ];
    }

    /**
     * Check if plan time is unlimited.
     *
     * @return bool
     */
    public function isTimeUnlimited()
    {
        return $this->frequency_unit == 'unlimited';
    }

    /**
     * Fill email verification servers.
     */
    public function fillPlansEmailVerificationServers($params)
    {
        $this->plansEmailVerificationServers = collect([]);
        foreach ($params as $key => $param) {
            if ($param['check']) {
                $server = \Acelle\Model\EmailVerificationServer::findByUid($key);
                $row = new \Acelle\Model\PlansEmailVerificationServer();
                $row->plan_id = $this->id;
                $row->server_id = $server->id;
                $this->plansEmailVerificationServers->push($row);
            }
        }
    }

    /**
     * Update email verification servers.
     *
     * @return array
     */
    public function updateEmailVerificationServers($servers)
    {
        $this->plansEmailVerificationServers()->delete();
        foreach ($servers as $key => $param) {
            if ($param['check']) {
                $server = \Acelle\Model\EmailVerificationServer::findByUid($key);
                $row = new \Acelle\Model\PlansEmailVerificationServer();
                $row->plan_id = $this->id;
                $row->server_id = $server->id;
                $row->save();
            }
        }
    }

    /**
     * Display sending ervers permission.
     *
     * @return array
     */
    public function displayAllowCreateEmailVerificationServer()
    {
        if ($this->getOption('create_email_verification_servers') == 'no') {
            return trans('messages.feature_not_allow');
        }

        if ($this->getOption('email_verification_servers_max') == -1) {
            return trans('messages.unlimited');
        } else {
            return $this->getOption('email_verification_servers_max');
        }
    }

    /**
     * Set option.
     */
    public function setOption($name, $value)
    {
        $options = json_decode($this->options, true);
        $options[$name] = $value;

        $this->options = json_encode($options);
        $this->save();
    }

    /**
     * Set option.
     */
    public function asignOption($name, $value)
    {
        $options = json_decode($this->options, true);
        $options[$name] = $value;

        $this->options = json_encode($options);
    }

    /**
     * Fill option from request.
     */
    public function fillOptions($options = [])
    {
        $defaultOptions = self::defaultOptions();
        $saveOptions = $this->options ? array_merge($defaultOptions, json_decode($this->options, true)) : $defaultOptions;
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $saveOptions[$key] = $value;
            }
        }

        // workaround
        if (empty($this->description)) {
            $this->description = '[ plan description goes here ]';
        }

        $this->options = json_encode($saveOptions);
    }

    /**
     * Fill from request.
     */
    public function fillAll($request)
    {
        // if has general params
        if (isset($request->plan['general'])) {
            $params = $request->plan['general'];
            if ($params['frequency_amount'] == '') {
                $params['frequency_amount'] = 1;
            }
            $this->fill($params);
        }

        // if has options params
        if (isset($request->plan['options'])) {
            $this->fillOptions($request->plan['options']);
        }

        //// if has email verifications
        //if (isset($request->plan['email_verification_servers'])) {
        //    $this->fillPlansEmailVerificationServers($request->plan['email_verification_servers']);
        //}

        // old request
        if ($request->old()) {
            if (isset($request->old()['plan']['general'])) {
                // fill all attributes
                $this->fill($request->old()['plan']['general']);
            }
            if (isset($request->old()['plan']['options'])) {
                // fill all options
                $this->fillOptions($request->old()['plan']['options']);
            }
            //if (isset($request->old()['plan']['email_verification_servers'])) {
            //    $this->fillPlansEmailVerificationServers($request->old()['plan']['email_verification_servers']);
            //}
        }

        // billing cycle
        $billingCycle = $this->getOption('billing_cycle');
        if (isset($billingCycle) && $billingCycle != 'custom' && $billingCycle != 'other') {
            $limits = self::billingCycleValues()[$billingCycle];
            $this->frequency_amount = $limits['frequency_amount'];
            $this->frequency_unit = $limits['frequency_unit'];
        }

        // billing cycle
        $sendingLimit = $this->getOption('sending_limit');
        if (isset($sendingLimit) && $sendingLimit != 'custom' && $sendingLimit != 'other') {
            $limits = self::sendingLimitValues()[$sendingLimit];
            $this->asignOption('sending_quota', $limits['quota_value']);
            $this->asignOption('sending_quota_time', $limits['quota_base']);
            $this->asignOption('sending_quota_time_unit', $limits['quota_unit']);
        }
    }

    /**
     * Fill from request.
     */
    public function validate($request)
    {
        $rules = [];
        foreach (array_keys($request->plan) as $key) {
            if (isset($this->validationRules()[$key])) {
                $rules = array_merge($rules, $this->validationRules()[$key]);
            }
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Fill from request.
     */
    public function saveAll($request)
    {
        $oldPlan = $this->replicate();

        $this->fillAll($request);

        // sync remote plan
        if (config('cashier.gateway') == 'paypal_subscription' && isset($request->plan) && isset($request->plan['general'])) {
            $service = \Acelle\Cashier\Cashier::getPaymentGateway('paypal_subscription');
            $service->syncPlan($this, $oldPlan);
        }

        $validator = $this->validate($request);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->save();

        // For email verification servers
        if (isset($request->plan['email_verification_servers'])) {
            $this->updateEmailVerificationServers($request->plan['email_verification_servers']);
        }
    }

    /**
     * Copy new plan.
     */
    public function copy($name)
    {
        $copy = $this->replicate(['cache', 'last_error', 'run_at']);
        $copy->name = $name;
        $copy->created_at = \Carbon\Carbon::now();
        $copy->updated_at = \Carbon\Carbon::now();
        $copy->status = PaymentMethod::STATUS_ACTIVE;
        $copy->custom_order = 0;
        $copy->save();

        // check status
        $copy->checkStatus();
    }

    /**
     **All Payment Methods: create plan on server.
     **/
    public function createPlanOnServer($data, $plan)
    {
        $planMethodlist = \Acelle\Model\PaymentMethod::getAllActive();
        if (count($planMethodlist) > 0) {
            foreach ($planMethodlist as $payment_method) {
                if ($payment_method->type == PaymentMethod::TYPE_PADDLE_CARD) {
                    if ($data !== 'unlimited') {
                        $vendorId = $payment_method->getOption('vendor_id');
                        $vendor_auth_code = $payment_method->getOption('vendor_auth_code');
                        // plan_type: accepts day, week, month, year
                        $json_body = 'vendor_id='.$vendorId.'&vendor_auth_code='.$vendor_auth_code.'&plan_name='.$data['name'].'&plan_trial_days=0&plan_type='.$data['frequency_unit'].'&plan_length='.$data['frequency_amount'].'&main_currency_code=USD&initial_price_usd=0.00&recurring_price_usd='.$data['price'];
                        $this->createPaddlePlan($json_body, $plan);
                    }
                }
            }
        }

        return true;
    }

    /**
     ** Create paddle plan on servers.
     **/
    public function createPaddlePlan($json_body, $plan)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://vendors.paddle.com/api/2.0/subscription/plans_create');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_body);
        curl_setopt($ch, CURLOPT_POST, 1);
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        $result = json_decode($result);
        if ($result->success) {
            $paddle_plan_id = $result->response->product_id;
            $plan->paddle_plan_id = $paddle_plan_id;
            $plan->save();
        }

        return true;
    }

    /**
     ** Add sending server by uid.
     **/
    public function addSendingServerByUid($sendinServerUid)
    {
        $server = SendingServer::findByUid($sendinServerUid);
        $row = new PlansSendingServer();
        $row->plan_id = $this->id;
        $row->sending_server_id = $server->id;
        $row->fitness = '50';

        // First primary
        if (!$this->plansSendingServers()->where('is_primary', '=', true)->count()) {
            $row->is_primary = true;
        }

        $row->save();
    }

    /**
     ** Remove sending server by uid.
     **/
    public function removeSendingServerByUid($sendinServerUid)
    {
        $server = SendingServer::findByUid($sendinServerUid);
        $this->plansSendingServers()->where('sending_server_id', '=', $server->id)->delete();

        // First primary
        $query = $this->plansSendingServers()->where('is_primary', '=', true);
        if (!$query->count()) {
            $first = $this->plansSendingServers()->first();
            if (is_object($first)) {
                $first->is_primary = true;
                $first->save();
            }
        }
    }

    /**
     ** Remove sending server by uid.
     **/
    public function setPrimarySendingServer($sendinServerUid)
    {
        $this->plansSendingServers()->update(['is_primary' => false]);

        $server = SendingServer::findByUid($sendinServerUid);
        $this->plansSendingServers()->where('sending_server_id', '=', $server->id)->update(['is_primary' => true]);
    }

    /**
     ** Update fitness by sending servers.
     **/
    public function updateFitnesses($hash)
    {
        foreach ($hash as $uid => $value) {
            $sendingServer = SendingServer::findByUid($uid);
            \Acelle\Model\PlansSendingServer::where('sending_server_id', '=', $sendingServer->id)
                ->update(['fitness' => $value]);
        }
    }

    /**
     ** Get Primary sending server.
     **/
    public function primarySendingServer()
    {
        if (!$this->useSystemSendingServer()) {
            throw new \Exception('ACELLE ERROR: 120000700392');
        }

        $pss = $this->plansSendingServers()->where('is_primary', '=', true)->first();
        // @todo: raise 1 cái exception nếu $pss null, ko return null sẽ gây lỗi tiềm ẩn
        return is_object($pss) ? $pss->sendingServer->mapType() : null;
    }

    /**
     ** Get Primary sending server.
     **/
    public function primarySendingServerAdded()
    {
        $pss = $this->plansSendingServers()->where('is_primary', '=', true)->first();
        // @todo: quăng 1 cái exception nếu $pss null, ko return null sẽ gây lỗi tiềm ẩn
        return is_object($pss) ? $pss->updated_at : null;
    }

    /**
     * Get all verified identities.
     *
     * @return array
     */
    public function getVerifiedIdentities()
    {
        $result = [];
        foreach ($this->sendingServers()->get() as $sendingServer) {
            $sendingServer = SendingServer::mapServerType($sendingServer);
            $result = array_merge($result, $sendingServer->getVerifiedIdentities());
        }
        return array_unique($result);
    }

    /**
     * Check if plan is free.
     *
     * @return bool
     */
    public function isFree()
    {
        return $this->price == 0;
    }

    /**
     * Get stripe price.
     *
     * @return string
     */
    public function stripePrice()
    {
        $currency_rates = [
            'CLP' => 1,
            'DJF' => 1,
            'JPY' => 1,
            'KMF' => 1,
            'RWF' => 1,
            'VUV' => 1,
            'XAF' => 1,
            'XOF' => 1,
            'BIF' => 1,
            'GNF' => 1,
            'KRW' => 1,
            'MGA' => 1,
            'PYG' => 1,
            'VND' => 1,
            'XPF' => 1,
        ];
        $rate = isset($currency_rates[$this->currency->code]) ? $currency_rates[$this->currency->code] : 100;

        return $this->price * $rate;
    }

    /**
     * PlanInterface: get remote plan id.
     *
     * @return string
     */
    public function getBillableId()
    {
        return $this->uid;
    }

    /**
     * PlanInterface: get name.
     *
     * @return string
     */
    public function getBillableName()
    {
        return $this->name;
    }

    /**
     * PlanInterface: get interval.
     *
     * @return string
     */
    public function getBillableInterval()
    {
        return $this->frequency_unit;
    }

    /**
     * PlanInterface: get interval count.
     *
     * @return string
     */
    public function getBillableIntervalCount()
    {
        return $this->frequency_amount;
    }

    /**
     * PlanInterface: get currency.
     *
     * @return string
     */
    public function getBillableCurrency()
    {
        return $this->currency->code;
    }

    /**
     * PlanInterface: get interval count.
     *
     * @return string
     */
    public function getBillableAmount()
    {
        return $this->price;
    }

    /**
     * PlanInterface: get interval count.
     *
     * @return string
     */
    public function getBillableFormattedPrice()
    {
        return \Acelle\Library\Tool::format_price($this->price, $this->currency->format);
    }

    /**
     * Get all .
     *
     * @var bool
     */
    public function activePlansEmailVerificationServers()
    {
        return $this->plansEmailVerificationServers();
    }

    /**
     * Get list of available email verification servers.
     *
     * @var bool
     */
    public function getEmailVerificaionServers()
    {
        if ($this->getOption('all_email_verification_servers') == 'yes') {
            $result = \Acelle\Model\EmailVerificationServer::getAllAdminActive()->get()->map(function ($server) {
                return $server;
            });
        } else {
            $result = $this->activePlansEmailVerificationServers()->get()->map(function ($server) {
                return $server->emailVerificationServer;
            });
        }

        return $result;
    }

    /**
     * Check if plan has primary sending server.
     *
     * @var bool
     */
    public function hasPrimarySendingServer()
    {
        return is_object($this->primarySendingServer());
    }

    /**
     * Check if plan sending server type is system.
     *
     * @var bool
     */
    public function useSystemSendingServer()
    {
        return $this->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM;
    }

    public function useOwnSendingServer()
    {
        return $this->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN;
    }

    /**
     * Check if plan is active.
     *
     * @var bool
     */
    public function isActive()
    {
        return $this->status == PlansEmailVerificationServer::STATUS_ACTIVE;
    }

    /**
     * Check if plan is available.
     *
     * @var bool
     */
    public function isAvailable()
    {
        return $this->isActive();
    }

    /**
     * Get all available plans for customer register.
     *
     * @var bool
     */
    public static function getAvailablePlans()
    {
        $plans = self::getAllActiveVisible()->get();
        $service = \Acelle\Cashier\Cashier::getPaymentGateway();

        $aPlans = [];
        foreach ($plans as $plan) {
            if ($plan->isAvailable()) {
                if (config('cashier.gateway') !== 'paypal_subscription' ||
                    $service->findPlanConnection($plan)
                ) {
                    $aPlans[] = $plan;
                }
            }
        }

        return $aPlans;
    }

    /**
     * Get all sending servers.
     *
     * @var collect
     */
    public function sendingServers()
    {
        return SendingServer::whereIn('id', $this->plansSendingServers()->pluck('sending_server_id')->toArray());
    }

    public function activeSendingServers()
    {
        // do not care if sending server is enabled or not
        return $this->plansSendingServers()->join('sending_servers', 'sending_servers.id', 'plans_sending_servers.sending_server_id');
    }

    /**
     * Check if plan is valid to active.
     *
     * @var bool
     */
    public function isValid()
    {
        // use system sending server but has no primary sending server
        if ($this->useSystemSendingServer() && !$this->hasPrimarySendingServer()) {
            return false;
        }

        // else return true
        return true;
    }

    /**
     * Check plan error status.
     *
     * @var array
     */
    public function errors()
    {
        $errors = [];

        // use system sending server but has no primary sending server
        if ($this->useSystemSendingServer() && !$this->hasPrimarySendingServer()) {
            $errors[] = 'sending_server_empty';
        }

        if (config('cashier.gateway') == 'paypal_subscription') {
            $service = Cashier::getPaymentGateway();

            if (!$service->findPlanConnection($this)) {
                $errors[] = 'plan_not_connected';
            }
        }

        // else return true
        return $errors;
    }

    /**
     * Check status of sending server.
     *
     * @var void
     */
    public function checkStatus()
    {
        // disable sending server if it is not valid
        if (!$this->isValid()) {
            $this->disable();

            $this->visibleOff();
        } else {
            $this->enable();
        }

        if (config('cashier.gateway') == 'paypal_subscription') {
            $service = Cashier::getPaymentGateway();

            if (!$service->findPlanConnection($this)) {
                $this->disable();
                $this->visibleOff();
            }
        }
    }

    /**
     * Show plan.
     *
     * @return bool
     */
    public function visibleOff()
    {
        $this['visible'] = 0;
        $this->save();
    }

    /**
     * Hide plan.
     *
     * @return bool
     */
    public function visibleOn()
    {
        $this['visible'] = true;
        $this->save();
    }
}
