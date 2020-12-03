<?php

namespace Acelle\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Acelle\Http\Requests;
use Acelle\Http\Controllers\Controller;
use Acelle\Model\Setting;
use Acelle\Model\Plan;
use Illuminate\Support\MessageBag;
use Acelle\Cashier\Cashier;
use Acelle\Cashier\Subscription;

class PaymentController extends Controller
{
    /**
     * Display all paymentt.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, MessageBag $message_bag)
    {
        return view('admin.payments.index', [
            'gateways' => Setting::getPaymentGateways(),
        ]);
    }

    /**
     * Editing payment gateways.
     *
     * @param int $name
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($name)
    {
        $service = Cashier::getPaymentGateway($name);
        $gateway = Setting::getPaymentGateway($name);

        try {
            $service->validate();
            $isValid = true;

            if ($name == 'paypal_subscription') {
                $isValid = $isValid && $service->getData()['product_id'];
            }
        } catch (\Exception $ex) {
            $isValid = false;
        }
        
        return view('admin.payments.edit', [
            'gateway' => $gateway,
            'service' => $service,
            'isValid' => $isValid,
        ]);
    }

    /**
     * Update payment gateway.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $name
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MessageBag $message_bag, $name)
    {
        $errors = new MessageBag;
        $gatewayService = Cashier::getPaymentGateway($name, $request->options);
        
        try {
            $gatewayService->validate();
            Setting::updatePaymentGateway($name, $request->options);

            if ($request->save_and_set_primary) {
                $sc = Subscription::count();

                if ($sc > 0) {
                    throw new \Exception(trans('messages.gateway.error.subscription_exist'));
                }

                Setting::set('system.payment_gateway', $name);
            }

            $request->session()->flash('alert-success', trans('messages.payment_gateway.updated'));
            if ($name == 'paypal_subscription') {
                // auto connect
                if (!$gatewayService->getData()['product_id']) {
                    $gatewayService->initPaypalProduct();
                }
                return redirect()->action('Admin\PaymentController@edit', $name);
            } else {
                return redirect()->action('Admin\PaymentController@index');
            }
        } catch (\Exception $ex) {
            // Add example error messages to the MessageBag instance.
            $errors->add(
                'payment',
                trans('messages.payment_gateway.not_valid', ['message' => $ex->getMessage()])
            );

            return view('admin.payments.edit', [
                'gateway' => Setting::getPaymentGateway($name),
                'errors' => $errors,
                'service' => $gatewayService,
                'isValid' => false,
            ]);
        }
    }

    /**
     * Set payment gateway as primary.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $name
     *
     * @return \Illuminate\Http\Response
     */
    public function setPrimary(Request $request, $name)
    {
        $service = Cashier::getPaymentGateway($name);

        try {
            $sc = Subscription::count();

            if ($sc > 0) {
                throw new \Exception(trans('messages.gateway.error.subscription_exist'));
            }
        } catch (\Exception $ex) {
            $request->session()->flash('alert-error', trans('messages.payment_gateway.not_valid', ['message' => $ex->getMessage()]));
            return redirect()->action('Admin\PaymentController@index');
        }

        try {
            $service->validate();

            Setting::set('system.payment_gateway', $name);
            
            // update global configs
            config([
                'cashier.gateway' => $name
            ]);

            // check all plan status
            foreach (Plan::all() as $plan) {
                $plan->checkStatus();
            }

            $request->session()->flash('alert-success', trans('messages.payment_gateway.updated'));
            return redirect()->action('Admin\PaymentController@index');
        } catch (\Exception $ex) {
            $request->session()->flash('alert-error', trans('messages.payment_gateway.not_valid', ['message' => $ex->getMessage()]));
            return redirect()->action('Admin\PaymentController@edit', $name);
        }
    }
    
    /**
     * Connect paypal plan.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $name
     *
     * @return \Illuminate\Http\Response
     */
    public function paypalSubscriptionConnectPlan(Request $request, $plan_uid)
    {
        $service = Cashier::getPaymentGateway('paypal_subscription');
        $plan = Plan::findByUid($plan_uid);

        try {
            $service->connectPlan($plan);

            // check plan status
            $plan->checkStatus();
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'PyPal Subscription service error: ' . $e->getMessage());
            return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
        }

        $request->session()->flash('alert-success', trans('messages.payment_gateway.paypal_subscription.plan.connected'));
        return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
    }

    /**
     * Disonnect paypal plan.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $name
     *
     * @return \Illuminate\Http\Response
     */
    public function paypalSubscriptionDisconnectPlan(Request $request, $plan_uid)
    {
        $service = Cashier::getPaymentGateway('paypal_subscription');
        $plan = Plan::findByUid($plan_uid);

        try {
            $service->disconnectPlan($plan);

            // check plan status
            $plan->checkStatus();
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'PyPal Subscription service error: ' . $e->getMessage());
            return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
        }

        $request->session()->flash('alert-success', trans('messages.payment_gateway.paypal_subscription.plan.disconnected'));
        return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
    }

    /**
     * Connect paypal.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $name
     *
     * @return \Illuminate\Http\Response
     */
    public function paypalSubscriptionConnect(Request $request)
    {
        $service = Cashier::getPaymentGateway('paypal_subscription');
        
        try {
            $service->initPaypalProduct();
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'PyPal Subscription service error: ' . $e->getMessage());
            return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
        }

        $request->session()->flash('alert-success', trans('messages.payment_gateway.paypal_subscription.connected'));
        return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
    }

    /**
     * Disconnect paypal.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $name
     *
     * @return \Illuminate\Http\Response
     */
    public function paypalSubscriptionDisconnect(Request $request)
    {
        $service = Cashier::getPaymentGateway('paypal_subscription');

        try {
            $service->removePaypalProduct();

            // check all plan status
            foreach (Plan::all() as $plan) {
                $plan->checkStatus();
            }
        } catch (\Exception $e) {
            $request->session()->flash('alert-error', 'PyPal Subscription service error: ' . $e->getMessage());
            return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
        }

        $request->session()->flash('alert-success', trans('messages.payment_gateway.paypal_subscription.disconnected'));
        return redirect()->action('Admin\PaymentController@edit', 'paypal_subscription');
    }
}
