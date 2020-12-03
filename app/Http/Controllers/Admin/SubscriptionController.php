<?php

namespace Acelle\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Acelle\Http\Requests;
use Acelle\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log as LaravelLog;
use Acelle\Cashier\Cashier;
use Acelle\Cashier\Subscription;
use Acelle\Cashier\SubscriptionLog;
use Acelle\Model\Plan;
use Acelle\Model\Customer;

class SubscriptionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // authorize
        if (!$request->user()->admin->can('read', new Subscription())) {
            return $this->notAuthorized();
        }

        // If admin can view all subscriptions of their customer
        if (!$request->user()->admin->can('readAll', new Subscription())) {
            $request->merge(array("customer_admin_id" => $request->user()->admin->id));
        }
        $subscriptions = Subscription::all();

        $plan = null;
        if ($request->plan_uid) {
            $plan = Plan::findByUid($request->plan_uid);
        }

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'plan' => $plan,
        ]);
    }

    /**
     * Admin create new subscription for customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // authorize
        if (!$request->user()->admin->can('create', new Subscription())) {
            return $this->notAuthorized();
        }

        $subscription = new Subscription();
        $rules = [
            'customer_uid' => 'required',
            'plan_uid' => 'required',
        ];

        // save posted data
        if ($request->isMethod('post')) {
            $validator = \Validator::make($request->all(), $rules);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('admin.subscriptions.create', [
                    'subscription' => $subscription,
                    'rules' => $rules,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // asign plan
            $customer = Customer::findByUid($request->customer_uid);
            $plan = Plan::findByUid($request->plan_uid);
            $customer->assignPlan($plan);
            
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.subscription.created'),
            ]);
        }

        return view('admin.subscriptions.create', [
            'subscription' => $subscription,
            'rules' => $rules,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listing(Request $request)
    {
        $gateway = Cashier::getPaymentGateway();

        if (!$gateway) {
            return view('admin.subscriptions.no_gateway');
        }

        // authorize
        if (!$request->user()->admin->can('read', new Subscription())) {
            return $this->notAuthorized();
        }

        // If admin can view all subscriptions of their customer
        if (!$request->user()->admin->can('readAll', new Subscription())) {
            $request->merge(array("customer_admin_id" => $request->user()->admin->id));
        }

        $subscriptions = Subscription::select('subscriptions.*');

        if ($request->filters) {
            if (isset($request->filters["customer_uid"])) {
                $subscriptions = $subscriptions->where('user_id', $request->filters["customer_uid"]);
            }

            if (isset($request->filters["plan_uid"])) {
                $subscriptions = $subscriptions->where('plan_id', $request->filters["plan_uid"]);
            }
        }

        if (!empty($request->sort_order)) {
            $subscriptions = $subscriptions->orderBy($request->sort_order, $request->sort_direction);
        }

        $subscriptions = $subscriptions->paginate($request->per_page);

        return view('admin.subscriptions._list', [
            'subscriptions' => $subscriptions,
            'gateway' => $gateway,
        ]);
    }

    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        if ($request->user()->admin->can('cancel', $subscription)) {
            $gateway->cancel($subscription);
        }

        echo trans('messages.subscription.cancelled');
    }

    /**
     * Cancel subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function resume(Request $request)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        if ($request->user()->admin->can('resume', $subscription)) {
            $gateway->resume($subscription);
        }

        echo trans('messages.subscription.resumed');
    }

    /**
     * Cancel now subscription at the end of current period.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelNow(Request $request)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        if ($request->user()->admin->can('cancelNow', $subscription)) {
            try {
                $gateway->cancelNow($subscription);
            } catch (\Exception $ex) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $ex->getMessage(),
                ]);
                return;
            }
        }

        echo trans('messages.subscription.cancelled_now');
    }

    /**
     * Change plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function changePlan(Request $request, $id)
    {
    }

    /**
     * Subscription invoices.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function invoices(Request $request, $id)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        return view('admin.subscriptions.invoices', [
            'subscription' => $subscription,
            'gateway' => $gateway,
        ]);
    }

    /**
     * Approve subscription pending.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function setActive(Request $request, $id)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        // authorize
        if (!$request->user()->admin->can('setActive', $subscription)) {
            return $this->notAuthorized();
        }

        try {
            $gateway->setActive($subscription);
        } catch (\Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => trans('messages.subscription.set_active.success'),
        ]);
        return;
    }

    /**
     * Approve subscription pending.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function approvePending(Request $request, $id)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        // authorize
        if (!$request->user()->admin->can('approvePending', $subscription)) {
            return $this->notAuthorized();
        }

        try {
            $gateway->approvePending($subscription);
        } catch (\Exception $ex) {
            echo json_encode([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => trans('messages.subscription.approve_pending.success'),
        ]);
        return;
    }

    /**
     * Reject subscription pending.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function rejectPending(Request $request, $id)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);
        $gateway = Cashier::getPaymentGateway();

        // authorize
        if (!$request->user()->admin->can('rejectPending', $subscription)) {
            return $this->notAuthorized();
        }

        if ($request->isMethod('post')) {
            $validator = \Validator::make($request->all(), ['reason' => 'required']);

            // redirect if fails
            if ($validator->fails()) {
                return response()->view('admin.subscriptions.rejectPending', [
                    'subscription' => $subscription,
                    'gateway' => $gateway,
                    'errors' => $validator->errors(),
                ], 400);
            }
            
            // try reject
            try {
                $gateway->rejectPending($subscription, $request->reason);
            } catch (\Exception $ex) {
                $validator->errors()->add('reason', $ex->getMessage());
                
                return response()->view('admin.subscriptions.rejectPending', [
                    'subscription' => $subscription,
                    'gateway' => $gateway,
                    'errors' => $validator->errors(),
                ], 400);
            }

            // success
            return response()->json([
                'status' => 'success',
                'message' => trans('messages.subscription.reject_pending.success'),
            ]);
        }

        return view('admin.subscriptions.rejectPending', [
            'subscription' => $subscription,
            'gateway' => $gateway,
        ]);
    }

    /**
     * Delete subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // Get current subscription
        $subscription = Subscription::findByUid($request->id);

        if ($request->user()->admin->can('delete', $subscription)) {
            $subscription->delete();
        }

        echo trans('messages.subscription.deleted');
    }
}
