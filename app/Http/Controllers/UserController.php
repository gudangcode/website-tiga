<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Library\Log as MailLog;

class UserController extends Controller
{

    /**
     * Render user image.
     */
    public function avatar(Request $request)
    {
        // Get current user
        if ($request->uid != '0') {
            $user = \Acelle\Model\User::findByUid($request->uid);
        } else {
            $user = new \Acelle\Model\User();
        }
        if (!empty($user->imagePath()) && file_exists($user->imagePath())) {
            $img = \Image::make($user->imagePath());
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
        $id = \Session::pull('orig_user_id');
        $orig_user = \Acelle\Model\User::findByUid($id);

        \Auth::login($orig_user);

        return redirect()->action('Admin\UserController@index');
    }

    /**
     * Activate user account.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request, $token)
    {
        $userActivation = \Acelle\Model\UserActivation::where('token', '=', $token)->first();

        if (!$userActivation) {
            return view('notAuthorized');
        } else {
            $userActivation->user->setActivated();

            $request->session()->put('user-activated', trans('messages.user.activated'));

            if (isset($request->redirect)) {
                return redirect()->away(urldecode($request->redirect));
            } else {
                return redirect()->action('HomeController@index');
            }
        }
    }

    /**
     * Resen activation confirmation email.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function resendActivationEmail(Request $request)
    {
        $user = \Acelle\Model\User::findByUid($request->uid);

        try {
            $user->sendActivationMail($user->email, action('HomeController@index'));
        } catch (\Exception $e) {
            return view('somethingWentWrong', ['message' => trans('messages.something_went_wrong_with_email_service').': '.$e->getMessage() ]);
        }

        return view('users.registration_confirmation_sent');
    }
    
    /**
     * User registration.
     */
    public function register(Request $request)
    {
        if (\Acelle\Model\Setting::get('enable_user_registration') == 'no') {
            return $this->notAuthorized();
        }

        // Customer account
        $user = $request->user();
        $is_customer_logged_in = is_object($user) && is_object($user->customer);
        if ($is_customer_logged_in) {
            return redirect()->action('AccountSubscriptionController@index');
        } else {
            $customer = new \Acelle\Model\Customer();
            $customer->uid = 0;
            $customer->status = \Acelle\Model\Customer::STATUS_ACTIVE;
            if (!empty($request->old())) {
                $customer->fill($request->old());
                // User info
                $customer->user = new \Acelle\Model\User();
                $customer->user->fill($request->old());
            }
        }

        // save posted data
        if ($request->isMethod('post')) {
            // Validation
            if ($is_customer_logged_in) {
            } else {
                $rules = $customer->registerRules();

                // Captcha check
                if (\Acelle\Model\Setting::get('registration_recaptcha') == 'yes') {
                    $success = \Acelle\Library\Tool::checkReCaptcha($request);
                    if (!$success) {
                        $rules['recaptcha_invalid'] = 'required';
                    }
                }

                $this->validate($request, $rules);
            }

            // Save customer
            if (!$is_customer_logged_in) {
                $customer->updateInformation($request);
            }

            // Send registration confirmation email
            if ($is_customer_logged_in) {
            } else {
                $user = \Acelle\Model\User::find($customer->user_id);

                try {
                    $user->sendActivationMail($customer->displayName());
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    MailLog::error($error);
                    return view('somethingWentWrong', ['message' => trans('messages.something_went_wrong_with_email_service') . ": " . $error]);
                }

                return view('users.register_confirmation_notice');
            }
        }

        return view('users.register', [
            'customer' => $customer,
        ]);
    }
}
