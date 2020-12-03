<?php

namespace Acelle\Http\Controllers\Api;

use Illuminate\Http\Request;
use Acelle\Http\Controllers\Controller;

/**
 * /api/v1/subscriptions - API controller for managing subscriptions.
 */
class SubscriptionController extends Controller
{
    /**
     * Subscribe customer to a plan (For admin only).
     *
     * POST /api/v1/subscriptions
     *
     * @param \Illuminate\Http\Request $request         All supscription information
     * @param string                   $customer_uid    Customer's uid
     * @param string                   $plan_uid        Plan's uid
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::guard('api')->user();
        $customer = \Acelle\Model\Customer::findByUid($request->customer_uid);
        $plan = \Acelle\Model\Plan::findByUid($request->plan_uid);

        // check if customer exists
        if (!is_object($customer)) {
            return \Response::json(array('status' => 0, 'message' => 'Customer not found'), 404);
        }

        // check if plan exists
        if (!is_object($plan)) {
            return \Response::json(array('status' => 0, 'message' => 'Plan not found'), 404);
        }

        // authorize
        if (!$user->can('assignPlan', $customer)) {
            return \Response::json(array('status' => 0, 'message' => 'Unauthorized'), 401);
        }

        // check if item active
        if (!$plan->isActive()) {
            return \Response::json(array('status' => 0, 'message' => 'Plan is not active'), 404);
        }

        $customer->assignPlan($plan);

        return \Response::json(array(
            'status' => 1,
            'message' => 'Assigned '.$customer->displayName().' plan to '.$plan->name.' successfully.',
            'customer_uid' => $customer->uid,
            'plan_uid' => $plan->uid
        ), 200);
    }

    /**
     * Approve subscription pending.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function setActive(Request $request, $uid)
    {
        // Get current subscription
        $subscription = \Acelle\Cashier\Subscription::findByUid($uid);
        $gateway = \Acelle\Cashier\Cashier::getPaymentGateway();

        // check if item exists
        if (!is_object($subscription)) {
            return \Response::json(array('status' => 0, 'message' => 'Can not find subscription with uid: ' . $uid), 404);
        }

        // check if item is pending
        if (!$subscription->isPending()) {
            return \Response::json(array('status' => 0, 'message' => 'Can not set active for subscription. Subscription is not pending.'), 403);
        }

        // authorize
        if (!$request->user()->admin->can('setActive', $subscription)) {
            return \Response::json(array('message' => 'Unauthorized'), 401);
        }

        try {
            $gateway->setActive($subscription);
        } catch (\Exception $ex) {
            return \Response::json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 403);
        }

        return \Response::json([
            'status' => 'success',
            'message' => trans('messages.subscription.set_active.success'),
        ]);
    }
}
