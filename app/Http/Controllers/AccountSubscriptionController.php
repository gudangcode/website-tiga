<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as LaravelLog;
use Acelle\Cashier\Subscription;
use Acelle\Model\Setting;
use Acelle\Model\Plan;
use Acelle\Cashier\Cashier;
use Acelle\Cashier\Services\StripeGatewayService;
use Carbon\Carbon;
use Acelle\Cashier\SubscriptionLog;

class AccountSubscriptionController extends Controller
{
    /**
     * Customer subscription main page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function index(Request $request)
    {
        // Check if system dosen't have payment gateway
        if (!Setting::get('system.payment_gateway')) {
            return view('noPrimaryPayment');
        }

        $customer = $request->user()->customer;
        $gateway = Cashier::getPaymentGateway();
        
        // Get current subscription
        $subscription = $customer->subscription;

        // Customer dose not have subscription
        if (!is_object($subscription) || $subscription->isEnded()) {
            $plans = Plan::getAvailablePlans();
            $planCount = Plan::getAllActive()->count();
            $colWidth = ($planCount == 0) ? 0 :  round(85 / $planCount);
            return view('account.subscription.select_plan', [
                'plans' => $plans,
                'colWidth' => $colWidth,
                'subscription' => $subscription,
            ]);
        }

        if (!$subscription->plan->isActive()) {
            return view('account.subscription.error', ['message' => __('messages.subscription.error.plan-not-active', [ 'name' => $subscription->plan->name])]);
        }
        
        // Check if subscription is new
        if ($subscription->isNew()) {
            return redirect()->away($gateway->getCheckoutUrl($subscription, action('AccountSubscriptionController@index')));
        }
        
        // Check if subscription is new
        if ($subscription->isPending()) {
            return redirect()->away($gateway->getPendingUrl($subscription, action('AccountSubscriptionController@index')));
        }
        
        return view('account.subscription.index', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plan' => $subscription->plan,
        ]);
    }
    
    /**
     * Store customer subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function create(Request $request)
    {
        // Get current customer
        $customer = $request->user()->customer;
        $plan = Plan::findByUid($request->plan_uid);
        $gateway = Cashier::getPaymentGateway();

        // Create subscription
        $subscription = $gateway->create($customer, $plan);

        try {
            \Mail::to($customer->user->email)->send(new \Acelle\Mail\SubscriptionDoneMailer($subscription));
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'Can not send email: ' . $e->getMessage());
        }
        
        // Check if subscriotion is new
        return redirect()->action('AccountSubscriptionController@index');
    }
    
    /**
     * Review customer subscription.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function review(Request $request)
    {
        // Get current customer
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $plan = Plan::findByUid($request->plan_uid);
        
        if (isset($subscription) && !$subscription->isEnded()) {
            $request->session()->flash('alert-error', 'Already subscribed a plan!');
            return redirect()->action('AccountSubscriptionController@index');
        }
        
        return view('account.subscription.review', [
            'plan' => $plan,
        ]);
    }
    
    /**
     * Change plan.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function changePlan(Request $request)
    {
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();
        $plans = Plan::getAvailablePlans();
        
        // Authorization
        if (!$request->user()->customer->can('changePlan', $subscription)) {
            return $this->notAuthorized();
        }
        
        return view('account.subscription.change_plan', [
            'subscription' => $subscription,
            'gateway' => $gateway,
            'plans' => $plans,
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
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();

        if ($request->user()->customer->can('cancel', $subscription)) {
            $gateway->cancel($subscription);
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled'));
        return redirect()->action('AccountSubscriptionController@index');
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
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();

        if ($request->user()->customer->can('resume', $subscription)) {
            $gateway->resume($subscription);
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.resumed'));
        return redirect()->action('AccountSubscriptionController@index');
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
        $customer = $request->user()->customer;
        // Get current subscription
        $subscription = $customer->subscription;
        $gateway = Cashier::getPaymentGateway();
        
        if ($request->user()->customer->can('cancelNow', $subscription)) {
            $gateway->cancelNow($subscription);
        }

        // Redirect to my subscription page
        $request->session()->flash('alert-success', trans('messages.subscription.cancelled_now'));
        return redirect()->action('AccountSubscriptionController@index');
    }
}
