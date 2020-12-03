<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Cashier\Subscription;
use Acelle\Cashier\Cashier;

class CustomerController extends Controller
{

    /**
     * Render customer image.
     */
    public function avatar(Request $request)
    {
        // Get current customer
        if ($request->uid != '0') {
            $customer = \Acelle\Model\Customer::findByUid($request->uid);
        } else {
            $customer = new \Acelle\Model\Customer();
        }
        if (!empty($customer->imagePath())) {
            try {
                $img = \Image::make($customer->imagePath());
            } catch (\Intervention\Image\Exception\NotReadableException $ex) {
                // file not found
                $customer->image = null;
                $customer->save();
                $img = \Image::make(public_path('assets/images/placeholder.jpg'));
            }
        } else {
            $img = \Image::make(public_path('assets/images/placeholder.jpg'));
        }

        return $img->response();
    }
    
    /**
     * User uid for editor
     */
    public function showUid(Request $request)
    {
        $user = $request->user();
        echo $user->uid;
    }
    
    /**
     * Log in back user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function loginBack(Request $request)
    {
        $id = \Session::pull('orig_customer_id');
        $orig_user = \Acelle\Model\User::findByUid($id);
        
        \Auth::login($orig_user);
        
        return redirect()->action('Admin\CustomerController@index');
    }
    
    /**
     * Admin area.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function adminArea(Request $request)
    {
        $id = \Session::get('orig_customer_id');
        $orig_user = \Acelle\Model\User::findByUid($id);
        $gateway = Cashier::getPaymentGateway();
        
        // Get current subscription
        $customer = $request->user()->customer;
        $subscription = $customer->subscription;
        
        $next_billing_date = null;
        if (is_object($subscription)) {
            $next_billing_date = $subscription->ends_at;
            if ($subscription->isActive()) {
                $next_billing_date = $subscription->current_period_ends_at;
            }
        }
        
        return view('customers.admin_area', [
            'customer' => $customer,
            'subscription' => $subscription,
            'next_billing_date' => $next_billing_date,
            'gateway' => $gateway,
        ]);
    }
}
