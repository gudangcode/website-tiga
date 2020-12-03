<?php

/**
 * CronJobNotification class.
 *
 * Notification for cronjob issue
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   Acelle Library
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

namespace Acelle\Library\Notification;

use Acelle\Model\Notification;
use Acelle\Cashier\Subscription as CashierSubscription;

class Subscription extends Notification
{
    /**
     * Check if CronJob is recently executed and log a notification if not.
     */
    public static function check()
    {
        self::cleanupSimilarNotifications();

        $count = CashierSubscription::where('status', CashierSubscription::STATUS_PENDING)->count();
        if ($count > 0) {
            self::info([
                'title' => trans('messages.admin.notification.subscription.title'),
                'message' => trans('messages.admin.notification.subscription.pending', ['count' => $count]),
            ]);
        }
    }
}
