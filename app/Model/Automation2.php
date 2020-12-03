<?php

/**
 * Automation class.
 *
 * Model for automations
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

use DB;
use Acelle\Library\Automation\Action;
use Acelle\Library\Automation\Trigger;
use Acelle\Library\Automation\Send;
use Acelle\Library\Automation\Wait;
use Acelle\Library\Automation\Evaluate;
use Acelle\Library\Automation\Operate;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Acelle\Library\Lockable;
use Acelle\Model\SystemJob as SystemJobModel;
use DateTime;
use DateTimeZone;

class Automation2 extends Model
{
    // Automation status
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Items per page.
     *
     * @var array
     */
    const ITEMS_PER_PAGE = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Association with mailList through mail_list_id column.
     */
    public function mailList()
    {
        return $this->belongsTo('Acelle\Model\MailList');
    }

    /**
     * Get all of the links for the automation.
     */
    public function emailLinks()
    {
        return $this->hasManyThrough('Acelle\Model\EmailLink', 'Acelle\Model\Email');
    }

    /**
     * Get all of the emails for the automation.
     */
    public function emails()
    {
        return $this->hasMany('Acelle\Model\Email');
    }

    /**
     * Association.
     */
    public function autoTriggers()
    {
        return $this->hasMany('Acelle\Model\AutoTrigger');
    }

    /**
     * Association: triggers that have not finished
     */
    public function pendingAutoTriggers()
    {
        $leaves = $this->getLeafActions();
        $condition = '('.implode(' AND ', array_map(function($e) { return "executed_index NOT LIKE '%{$e}'"; }, $leaves)).')';
        $query = $this->autoTriggers()->whereRaw($condition);
        return $query;
    }

    /**
     * Association.
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Association.
     */
    public function timelines()
    {
        return $this->hasMany('Acelle\Model\Timeline')->orderBy('created_at', 'DESC');
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
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
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
    public function rules()
    {
        return [
            'name' => 'required',
            'mail_list_uid' => 'required',
        ];
    }

    public function getElementById($id)
    {
        $data = $this->getElements()[$id];

        // search by id

        return $element;
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::where('customer_id', '=', $user->customer->id);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
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
     * enable automation.
     */
    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function disable()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function saveData($data)
    {
        $this->data = $data;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function getData()
    {
        return isset($this->data) ? preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $this->data) : '[]';
    }

    /**
     * get all tree elements.
     */
    public function getElements($hash = false) # true => return hash, false (default) => return stdObject
    {
        return isset($this->data) && !empty($this->data) ? json_decode($this->data, $hash) : [];
    }

    /**
     * get trigger.
     */
    public function getTrigger()
    {
        $elements = $this->getElements();

        return empty($elements) ? new AutomationElement(null) : new AutomationElement($elements[0]);
    }

    /**
     * get element by id.
     */
    public function getElement($id = null)
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            if ($element->id == $id) {
                return new AutomationElement($element);
            }
        }

        return new AutomationElement(null);
    }

    /**
     * Get started time.
     */
    public function getStartedTime()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get delay options.
     */
    public function getDelayOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.minute', 1), 'value' => '1 minute'],
            ['text' => trans_choice('messages.automation.delay.minute', 30), 'value' => '30 minutes'],
            ['text' => trans_choice('messages.automation.delay.hour', 1), 'value' => '1 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 2), 'value' => '2 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 4), 'value' => '4 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 8), 'value' => '8 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 12), 'value' => '12 hours'],

            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    /**
     * Get delay or before options.
     */
    public function getDelayBeforeOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.0_day', 0), 'value' => '0 day'],
            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    /**
     * Get email links options.
     */
    public function getEmailLinkOptions()
    {
        $data = [];

        foreach ($this->emailLinks as $link) {
            $data[] = ['text' => $link->link, 'value' => $link->uid];
        }

        return $data;
    }

    /**
     * Get emails options.
     */
    public function getEmailOptions()
    {
        $data = [];

        foreach ($this->emails as $email) {
            $data[] = ['text' => $email->subject, 'value' => $email->uid];
        }

        return $data;
    }

    /**
     * Initiate a trigger with a given subscriber.
     */
    public function initTrigger($subscriber)
    {
        $trigger = null;
        DB::transaction(function () use (&$trigger, $subscriber) {
            $trigger = $this->autoTriggers()->create();
            $trigger->subscriber()->associate($subscriber); // assign to subscriber_id field
            $trigger->updateWorkflow();
            $trigger->save();
        });

        return $trigger;
    }

    /**
     * Scan for triggers updates and new triggers.
     */
    public static function run()
    {
        $customers = Customer::all();
        foreach ($customers as $customer) {
            // Only one automation process to run at one given time

            $automations = $customer->activeAutomation2s;
            foreach ($automations as $automation) {
                $automationLockFile = $customer->getLockPath('automation-lock-'.$automation->uid);
                $lock = new Lockable($automationLockFile);
                $timeout = 5; // seconds

                $lock->getExclusiveLock(function ($f) use ($customer, $automation) {
                    try {
                        if ($customer->isSubscriptionActive()) {
                            $automation->logger()->info(sprintf('Checking automation "%s"', $automation->name));
                            $automation->checkForNewTriggers();
                            $automation->checkForExistingTriggersUpdate();
                            $automation->updateCache();
                            $automation->setLastError(null);
                        } else {
                            $automation->logger()->warning(sprintf('Automation "%s" skipped, user "%s" not on active subscription', $automation->name, $customer->displayName()));
                        }
                    } catch (\Exception $ex) {
                        $automation->setLastError($ex->getMessage());
                        $automation->logger()->warning(sprintf('Error while executing automation "%s". %s', $automation->name, $ex->getMessage()));
                    }
                }, $timeout);
            }
        }
    }

    /**
     * Check for new triggers.
     */
    public function checkForNewTriggers()
    {
        $this->logger()->info(sprintf('NEW > Start checking for new trigger'));

        switch ($this->getTriggerAction()->getOption('key')) {
            case 'welcome-new-subscriber':
                $this->checkForListSubscription();
                break;
            case 'specific-date':
                $this->checkForSpecificDatetime();
                break;
            case 'say-goodbye-subscriber':
                $this->checkForListUnsubscription();
                break;
            case 'say-happy-birthday':
                $this->checkForDateOfBirth();
                break;
            case 'api-3-0':
                // Just wait for API call
                break;
            case 'weekly-recurring':
                $this->checkForWeeklyRecurring();
                break;
            case 'monthly-recurring':
                $this->checkForMonthlyRecurring();
                break;
            case 'others':
                // others
                break;
            default:
                throw new \Exception('Unknown Automation trigger type '.$this->getTriggerAction()->getOption('key'));
        }
        $this->logger()->info(sprintf('NEW > Finish checking for new trigger'));
    }

    public function checkForWeeklyRecurring()
    {
        $thisId = $this->id;
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        $selectedWeekDays = $this->getTriggerAction()->getOptions()['days_of_week'];

        $today = Carbon::now($this->customer->timezone);

        if (!in_array($today->dayOfWeek, $selectedWeekDays)) {
            $this->logger()->info(sprintf('Not the right week day: CURRENT %s (%s) != %s', $today->dayOfWeek, $today->toString(), implode(' ', $selectedWeekDays)));
            return;
        }

        // Only trigger once a day
        // Counting by user timezone
        $startOfDayUtc = $today->startOfDay()->timezone('UTC');

        $subscribers = $this->subscribers()->leftJoin('auto_triggers', function($join) use ($thisId, $startOfDayUtc) {
            $join->on('auto_triggers.subscriber_id', 'subscribers.id');
            $join->where('auto_triggers.automation2_id', $thisId);
            $join->where('auto_triggers.created_at', '>=', $startOfDayUtc);
        })
        ->whereNull('auto_triggers.subscriber_id')->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Weekly recurring for %s, at %s', $subscriber->email, $startOfDayUtc->toString()));
        }
    }

    public function checkForMonthlyRecurring()
    {
        $thisId = $this->id;
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        $selectedDays = $this->getTriggerAction()->getOptions()['days_of_month'];

        $today = Carbon::now($this->customer->timezone);

        if (!in_array($today->day, $selectedDays)) {
            $this->logger()->info(sprintf('Not the right day: CURRENT %s (%s) != %s', $today->day, $today->toString(), implode(' ', $selectedDays)));
            return;
        }

        // Only trigger once a day
        // Counting by user timezone
        $startOfDayUtc = $today->startOfDay()->timezone('UTC');

        $subscribers = $this->subscribers()->leftJoin('auto_triggers', function($join) use ($thisId, $startOfDayUtc) {
            $join->on('auto_triggers.subscriber_id', 'subscribers.id');
            $join->where('auto_triggers.automation2_id', $thisId);
            $join->where('auto_triggers.created_at', '>=', $startOfDayUtc);
        })
        ->whereNull('auto_triggers.subscriber_id')->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Monthly recurring for %s, at %s', $subscriber->email, $startOfDayUtc->toString()));
        }
    }

    public function checkForDateOfBirth()
    {
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        // Get the modify interval: 1 days, 2 days... for example
        $interval = $this->getTriggerAction()->getOptions()['before'];
        $thisId = $this->id;

        $today = Carbon::now($this->customer->timezone)->modify($interval);
        $dobFieldUid = $this->getTriggerAction()->getOptions()['field'];
        $dobFieldId = Field::findByUid($dobFieldUid)->id;
        $subscribers = $this->subscribers()
            ->join('subscriber_fields', 'subscribers.id', 'subscriber_fields.subscriber_id')
            ->where('subscriber_fields.field_id', $dobFieldId)
            ->whereIn(DB::raw(sprintf('SUBSTRING(%s, 1, 10)', table('subscriber_fields.value'))), [
                $today->format('Y-m-d'),
                $today->format('Y:m:d'),
                $today->format('m/d/Y'),
                $today->format('m-d-Y'),
            ])
            ->leftJoin('auto_triggers', function($join) use ($thisId) {
                $join->on('auto_triggers.subscriber_id', 'subscribers.id');
                $join->where('auto_triggers.automation2_id', $thisId);
            })
            ->whereNull('auto_triggers.subscriber_id')->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Say happy birthday (%s) to %s', $interval, $subscriber->email));
        }
    }

    public function checkForDateOfBirthDebug()
    {
        $debug = [];
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $debug[] = sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s'));
        }

        // Get the modify interval: 1 days, 2 days... for example
        $interval = $this->getTriggerAction()->getOptions()['before'];
        $today = Carbon::now($this->customer->timezone)->modify($interval);
        $debug[] = 'Before: '.$this->getTriggerAction()->getOptions()['before'];
        $debug[] = 'So, look for those whose DOB is: '.$today->format('Y-m-d');

        $dobFieldUid = $this->getTriggerAction()->getOptions()['field'];
        $dobFieldId = Field::findByUid($dobFieldUid)->id;
        $subscribers = $this->subscribers()
            ->join('subscriber_fields', 'subscribers.id', 'subscriber_fields.subscriber_id')
            ->where('subscriber_fields.field_id', $dobFieldId)
            ->whereIn(DB::raw(sprintf('SUBSTRING(%s, 1, 10)', table('subscriber_fields.value'))), [
                $today->format('Y-m-d'),
                $today->format('Y:m:d'),
                $today->format('m/d/Y'),
                $today->format('m-d-Y'),
            ])
            ->leftJoin('auto_triggers', 'auto_triggers.subscriber_id', 'subscribers.id')
            ->whereNull('auto_triggers.subscriber_id')->get();

        $sql = "select `".table('subscribers')."`.* from `".table('subscribers')."` inner join `".table('subscriber_fields')."` on `".table('subscribers')."`.`id` = `".table('subscriber_fields')."`.`subscriber_id` left join `".table('auto_triggers')."` on `".table('auto_triggers')."`.`subscriber_id` = `".table('subscribers')."`.`id` and `".table('auto_triggers')."`.`automation2_id` = ".$this->id." where `".table('subscribers')."`.`mail_list_id` = ".$this->mailList->id." and `".table('subscribers')."`.`mail_list_id` is not null and `".table('subscriber_fields')."`.`field_id` = ".$dobFieldId." and SUBSTRING(".table('subscriber_fields').".value, 1, 10) in ('".$today->format('Y-m-d')."', '".$today->format('Y:m:d')."', '".$today->format('m/d/Y')."', '".$today->format('m-d-Y')."') and `".table('auto_triggers')."`.`subscriber_id` is null;";
        $debug[] = $sql;

        return $debug;
    }

    /**
     * Check for existing triggers update.
     */
    public function checkForExistingTriggersUpdate()
    {
        $this->logger()->info(sprintf('UPDATE > Start checking for trigger update'));
        
        /* FORCE RECHECK
        foreach ($this->autoTriggers as $trigger) {
            $trigger->check();
        }*/

        foreach($this->pendingAutoTriggers as $trigger) {
            $trigger->check();
        }

        $this->logger()->info(sprintf('UPDATE > Finish checking for trigger update'));
    }

    /**
     * Check for list-subscription events.
     */
    public function checkForListSubscription()
    {
        $this->logger()->info(sprintf('NEW > Check for List Subscription'));
        $subscribers = $this->getNewSubscribersToFollow();
        $total = count($subscribers);
        $this->logger()->info(sprintf('NEW > There are %s new subscriber(s) found', $total));

        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Check for specific-datetimetime events.
     */
    public function checkForSpecificDatetime()
    {
        $this->logger()->info(sprintf('NEW > Check for Specific Date/Time'));
        // this is a one-time triggered automation event
        // just abort if it is already triggered
        if ($this->autoTriggers()->exists()) {
            $this->logger()->info(sprintf('NEW > Already triggered'));

            return;
        }

        $now = Carbon::now($this->customer->timezone);
        $trigger = $this->getTriggerAction();
        $eventDate = Carbon::parse(sprintf('%s %s %s', $trigger->getOption('date'), $trigger->getOption('at'), $this->customer->timezone));
        $checked = $now->gte($eventDate);

        $total = $this->subscribers()->count();
        $i = 0;
        if ($checked) {
            $this->logger()->info(sprintf('NEW > It is %s hours due! triggering!', $now->diffInHours($eventDate)));
            foreach ($this->subscribers()->get() as $subscriber) {
                $i += 1;
                $this->initTrigger($subscriber);
                $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
            }
        }
    }

    /**
     * Check for follow-up-clicked events.
     */
    public function checkForListUnsubscription()
    {
        $this->logger()->info(sprintf('NEW > Check for List Unsubscription'));
        $subscribers = $this->getUnsubscribersToFollow();
        $total = count($subscribers);
        $this->logger()->info(sprintf('NEW > %s new unsubscribers found', $total));
        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Get previous event's opened messages to follow up.
     *
     * @return collection
     */
    public function getNewSubscribersToFollow()
    {
        // Boot performance with temporary table + index
        try {
            \DB::statement('DROP TEMPORARY TABLE `_new_subscribers_to_follow`');
        } catch (\Exception $ex) {
            // just ignore, in case 2 queries in the same MySQL connection session
        }

        \DB::statement(
            sprintf('
            CREATE TEMPORARY TABLE `_new_subscribers_to_follow` AS
            SELECT COALESCE(subscriber_id, 0) AS subscriber_id
            FROM %s WHERE automation2_id = %s;

            CREATE INDEX _new_subscribers_to_follow_index ON _new_subscribers_to_follow(subscriber_id);', table('auto_triggers'), $this->id)
        );

        return $this->subscribers()->whereRaw(table('subscribers.id').' NOT IN (SELECT subscriber_id FROM _new_subscribers_to_follow)')
                ->where('subscribers.created_at', '>=', $this->created_at)
                ->where('mail_list_id', $this->mailList->id)
                ->whereRaw(sprintf('COALESCE('.table('subscribers.subscription_type').", '') <> %s", db_quote(Subscriber::SUBSCRIPTION_TYPE_IMPORTED)))->get();
    }

    /**
     * Get previous event's opened messages to follow up.
     *
     * @return collection
     */
    public function getNewSubscribersToFollowDebug()
    {
        $tmpSql = sprintf('
            CREATE TEMPORARY TABLE `_new_subscribers_to_follow` AS
            SELECT COALESCE(subscriber_id, 0) AS subscriber_id
            FROM %s WHERE automation2_id = %s;

            CREATE INDEX _new_subscribers_to_follow_index ON _new_subscribers_to_follow(subscriber_id);', table('auto_triggers'), $this->id);
     
        $newSubscribersSql = $this->subscribers()->whereRaw(table('subscribers.id').' NOT IN (SELECT subscriber_id FROM _new_subscribers_to_follow)')
                ->where('subscribers.created_at', '>=', $this->created_at)
                ->where('mail_list_id', $this->mailList->id)
                ->whereRaw(sprintf('COALESCE('.table('subscribers.subscription_type').", '') <> %s", db_quote(Subscriber::SUBSCRIPTION_TYPE_IMPORTED)))
                ->toSql();

        return prettifystr($tmpSql."; ".$newSubscribersSql);
    }

    /**
     * Get previous event's unsubscribed messages to follow up.
     *
     * @return collection
     */
    public function getUnsubscribersToFollow()
    {
        return $this->subscribers()
                    ->select('subscribers.*')
                    ->join('unsubscribe_logs', 'subscribers.id', '=', 'unsubscribe_logs.subscriber_id')
                    ->leftJoin('auto_triggers', 'subscribers.id', '=', 'auto_triggers.subscriber_id')
                    ->whereNull('auto_triggers.id')
                    ->where('unsubscribe_logs.created_at', '>=', $this->created_at)
                    ->get();
    }

    public function logger()
    {
        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");
        if (getenv('LOGFILE') != false) {
            $stream = new RotatingFileHandler(getenv('LOGFILE'), 0, Logger::DEBUG);
        } else {
            $logfile = storage_path('logs/' . php_sapi_name() . '/automation-'.$this->uid.'.log');
            $stream = new RotatingFileHandler($logfile, 0, Logger::WARNING);
        }

        $stream->setFormatter($formatter);

        $pid = getmypid();
        $logger = new Logger($pid);
        $logger->pushHandler($stream);

        return $logger;
    }

    public function timelinesBy($subscriber)
    {
        $trigger = $this->autoTriggers()->where('subscriber_id', $subscriber->id)->first();

        return (is_null($trigger)) ? Timeline::whereRaw('1=2') : $trigger->timelines(); // trick - return an empty Timeline[] array
    }

    public function getInsight()
    {
        if (!$this->data) {
            return [];
        }

        $actions = json_decode($this->data, true);
        $insights = [];
        foreach ($actions as $action) {
            $insights[$action['id']] = $this->getActionStats($action);
        }

        return $insights;
    }

    public function getActionStats($attributes)
    {
        $total = $this->mailList->readCache('SubscriberCount');

        // The following implementation is prettier but 'countBy' is supported in Laravel 5.8 only
        // $count = $this->autoTriggers()->countBy(function($trigger) {
        //    return $trigger->isActionExecuted($action['id']);
        // });

        // IMPORTANT: this action does not associate with a particular trigger
        $action = $this->getAction($attributes);

        // @DEPRECATED
        // $count = 0;
        // foreach ($this->autoTriggers as $trigger) {
        //     $count += ($trigger->isActionExecuted($action->getId())) ? 1 : 0;
        // }

        // Count the number subscribers whose action has been executed
        $count = $this->subscribersByExecutedAction($action->getId())->count();

        if ($action->getType() == 'ElementTrigger') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.triggered', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementOperation') {
            $count = 0;
            foreach ($this->autoTriggers as $trigger) {
                $count += ($trigger->isLatest($action->getId())) ? 1 : 0;
            }

            $insight = [
                'count' => $count,
                'subtitle' => '#Operation TBD',
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementWait') {
            // Count the numbe contacts with this action as last executed one
            $queue = $this->subscribersByLatestAction($action->getId())->count();

            // since the previous $count also covers $queue ones, so get the already passed one by subtracting
            $passed = $count - $queue;

            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.in-queue2', ['queue' => $queue, 'passed' => $passed]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementAction') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.sent', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementCondition') {
            $yes = $this->subscribersByExecutedAction($action->getChildYesId())->count();
            $no = $this->subscribersByExecutedAction($action->getChildNoId())->count();

            $insight = [
                'count' => $yes + $no,
                'subtitle' => __('messages.automation.stats.condition', ['yes' => $yes, 'no' => $no]),
                'percentage' => ($total != 0) ? (($yes + $no) / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        }

        return $insight;
    }

    public function getSummaryStats()
    {
        $total = $this->mailList->subscribersCount();
        $involved = $this->autoTriggers()->count();

        $leaves = $this->getLeafActions();
        $complete = 0;
        foreach ($leaves as $leaf) {
            $complete += $this->subscribersByLatestAction($leaf)->count();    
        }

        $completePercentage = ($total == 0) ? 0 : $complete / $total;

        return [
            'total' => $total,
            'involed' => $involved,
            'complete' => $completePercentage,
        ];
    }

    // for debugging only
    public function getTriggerAction() : Trigger
    {
        $trigger = null;
        $this->getActions(function ($e) use (&$trigger) {
            if ($e->getType() == 'ElementTrigger') {
                $trigger = $e;
            }
        });

        return  $trigger;
    }

    // by Louis
    public function getActions($callback)
    {
        $actions = $this->getElements(true);

        foreach ($actions as $action) {
            $instance = $this->getAction($action);
            $callback($instance);
        }
    }

    // by Louis
    public function getLeafActions()
    {
        $leaves = [];
        $actions = $this->getElements(true);

        $this->getActions(function ($e) use (&$leaves) {
            if ($e->getNextActionId() == null) {
                $leaves[] = $e->getId();
            }
        });

        return $leaves;
    }

    // IMPORTANT: object returned by this function is not associated with a particular AutoTrigger
    public function getAction($attributes) : Action
    {
        switch ($attributes['type']) {
            case 'ElementTrigger':
                $instance = new Trigger($attributes);
                break;
            case 'ElementAction':
                $instance = new Send($attributes);
                break;
            case 'ElementCondition':
                $instance = new Evaluate($attributes);
                break;
            case 'ElementWait':
                $instance = new Wait($attributes);
                break;
            case 'ElementOperation':
                $instance = new Operate($attributes);
                break;
            default:
                throw new \Exception('Unknown Action type '.$attributes['type']);
        }

        return $instance;
    }

    // get all subscribers
    // if $actionId is provided, get only subscribers who have been triggered and have gone through the action
    public function subscribers($actionId = null)
    {
        $query = $this->mailList->subscribers()->select('subscribers.*');

        if (!is_null($actionId)) {
            // @deprecated, use the subscribersByLatestAction() method instead
            $query->join('auto_triggers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')
                 ->where('auto_triggers.automation2_id', $this->id);
        }

        return $query;
    }

    public function subscribersByLatestAction($actionId)
    {
        $query = $this->autoTriggers()->join('subscribers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId)->select('subscribers.*');
        return $query;
    }

    public function subscribersByExecutedAction($actionId)
    {
        $query = $this->autoTriggers()->join('subscribers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')->select('subscribers.*');
        return $query;
    }

    public function getIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.intro.'.$triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function getBriefIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.brief-intro.'.$triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function countEmails()
    {
        $count = 0;
        $this->getActions(function ($e) use (&$count) {
            if ($e->getType() == 'ElementAction') {
                $count += 1;
            }
        });

        return $count;
    }

    /**
     * Get recent automations for switch.
     */
    public function getSwitchAutomations($customer)
    {
        return $customer->automation2s()->where('id', '<>', $this->id)->orderBy('updated_at', 'desc')->limit(50);
    }

    /**
     * Get list fields options.
     */
    public function getListFieldOptions()
    {
        $data = [];

        foreach ($this->mailList->getFields()->get() as $field) {
            $data[] = ['text' => $field->label, 'value' => $field->uid];
        }

        return $data;
    }

    /**
     * Produce sample data.
     */
    public function produceSampleData()
    {
        // Reset all
        $this->resetListRelatedData();

        $count = $this->mailList->readCache('SubscriberCount');

        $min = (int) ($count * 0.2);
        $max = (int) ($count * 0.7);

        // Generate triggers
        $subscribers = $this->subscribers()->inRandomOrder()->limit(rand($min, $max))->get();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }

        // Run through trigger check
        $this->checkForExistingTriggersUpdate();
    }

    /**
     * Clean up after list change.
     */
    public function resetListRelatedData()
    {
        // Delete autoTriggers will also delete
        // + tracking_logs
        // + open logs
        // + click logs
        // + timelines
        $this->autoTriggers()->delete();
        $this->updateCache();
    }

    /**
     * Change mail list.
     */
    public function updateMailList($new_list)
    {
        if ($this->mail_list_id != $new_list->id) {
            $this->mail_list_id = $new_list->id;
            $this->save();

            // reset automation list
            $this->resetListRelatedData();
        }
    }

    /**
     * Fill from request.
     */
    public function fillRequest($request)
    {
        // fill attributes
        $this->fill($request->all());

        // fill segments
        $segments = [];
        $this->segment_id = null;
        if (!empty($request->segment_uid)) {
            foreach ($request->segment_uid as $segmentUid) {
                $segments[] = \Acelle\Model\Segment::findByUid($segmentUid)->id;
            }

            if (!empty($segments)) {
                $this->segment_id = implode(',', $segments);
            }
        }
    }

    /**
     * Get segments.
     */
    public function getSegments()
    {
        if (!$this->segment_id) {
            return collect([]);
        }

        $segments = \Acelle\Model\Segment::whereIn('id', explode(',', $this->segment_id))->get();

        return $segments;
    }

    /**
     * Get segments uids.
     */
    public function getSegmentUids()
    {
        return $this->getSegments()->map->uid->toArray();
    }

    // for debugging only
    public function updateActionOptions($actionId, $data = [])
    {
        $json = json_decode($this->data, true);

        for ($i = 0; $i < sizeof($json); $i += 1) {
            $action = $json[$i];
            if ($action['id'] != $actionId) {
                continue;
            }

            $action['options'] = array_merge($action['options'], $data);

            $json[$i] = $action;
            $this->data = json_encode($json);
            $this->save();
        }
    }

    /**
     * Get segments uids.
     */
    public function execute()
    {
        $subscribers = $this->getNotTriggeredSubscribers();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }
    }

    public function getNotTriggeredSubscribers()
    {
        return $this->mailList->activeSubscribers()
                                      ->leftJoin('auto_triggers', function ($join) {
                                          $join->on('subscribers.id', '=', 'auto_triggers.subscriber_id')->where('auto_triggers.automation2_id', '=', $this->id);
                                      })
                                      ->whereNull('auto_triggers.id')->select('subscribers.*')->get();
    }

    public function allowApiCall()
    {
        // Usually invoked by API call
        $type = $this->getTriggerAction()->getOption('key');

        return $type == 'api-3-0';
    }

    /**
     * Update Campaign cached data.
     */
    public function updateCache($key = null)
    {
        // cache indexes
        $index = [
            // @note: SubscriberCount must come first as its value shall be used by the others
            'SummaryStats' => function (&$auto) {
                return $auto->getSummaryStats();
            }
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

    public function updateCacheInBackground()
    {
        $existed = SystemJobModel::getNewJobs()
                       ->where('name', \Acelle\Jobs\UpdateAutomation::class)
                       ->where('data', $this->id)
                       ->exists();

        if (!$existed) {
            dispatch(new \Acelle\Jobs\UpdateAutomation($this));
        }
    }

    public function setLastError($message)
    {
        $this->last_error = $message;
        $this->save();
    }
}
