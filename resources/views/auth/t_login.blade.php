@extends('layouts.login')

@section('title', trans('messages.login'))

@section('content')

    <link type="text/css" rel="stylesheet" href="{{ URL::asset('assets/lib/lightslider/css/lightslider.css') }}" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="{{ URL::asset('assets/lib/lightslider/js/lightslider.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(".login-slider").lightSlider({
                item: 1,
                speed: 400,
                controls: true,
                auto: true,
                loop: true,
                pause: 4000,
            });
        });
    </script>

    <div class="login-container dark full-height flex">
        <div class="col-md-7 phone-hide right-col">
            <div class="full-height full-width" style="display: flexs">
                <!--<h1 class="login-bg-title full-width pd-lvl3 text-white">Acelle Funnel</h1>-->
                <div class="login-slider-container">
                    <ul class="lightSlider login-slider">
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="height: 500px;">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/track_every_message.png') }}" />
                                </div>
                                <h2>Complete Delivery Tracking</h2>
                                <h4>Track every single message sent out for your campaign</h4>
                                <ul>
                                    <li><i class="ion-checkmark-round"></i> Track your messages opens & clicks</li>
                                    <li><i class="ion-checkmark-round"></i> Automatically handle bounce & feedback</li>
                                    <li><i class="ion-checkmark-round"></i> Measure your campaign performance with insight reports</li>
                                </ul>
                            </div>
                        </li>
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/open-to-customization-and-evolve.png') }}" />
                                </div>
                                <h2>Written in PHP, on top of LARAVEL 5</h2>
                                <h4>Open to full customization and rebranding</h4>
                                <p>Laravel is an free and open-source PHP framework, designed for the development of robust web applications succeeding the MVC pattern. With PHP/Laravel, code maintenance is easier than ever as your business is growing</p>
                                <ul>
                                    <li><i class="ion-checkmark-round"></i> Coded in PHP 5.6, 7.0 on top of Laravel 5</li>
                                    <li><i class="ion-checkmark-round"></i> Backed by MySQL 5.x</li>
                                </ul>
                            </div>
                        </li>
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/automation-illustration.png') }}" />
                                </div>
                                <h2>Full-featured Automation / Auto-responder</h2>
                                <h4>Automate your campaign with email workflow editor</h4>
                                <ul>
                                    <li><i class="ion-checkmark-round"></i> Start your email campaigns in response to event triggers</li>
                                    <li><i class="ion-checkmark-round"></i> Automatically respond to your recipient activities (open/click)</li>
                                    <li><i class="ion-checkmark-round"></i> Design your marketing with email automation workflow</li>
                                </ul>
                            </div>
                        </li>
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/acelle_mail_payment_transparency.png') }}" />
                                </div>
                                <h2>Designed as an SaaS framework</h2>
                                <h4>Provide your own email service to the world</h4>
                                <p>Manage your service plans and subscription. Get paid by your users via PayPal or Credit Card to your Stripe / Braintree / Paddle account</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>


        </div>
        <div class="col-md-5 pd-lvl4 left-col flex flex-center" style="min-height: 590px;">
            <form class="pd-lvl3 login-box-content" method="POST" action="{{ url('/login') }}">
                {{ csrf_field() }}

                <h1 class="text-semibold text-center full-width mb-lvl4" style="margin: 0 0 60px 0">
                    <img width="240" src="{{ url('images/logo_big.png') }}" alt="">
                </h1>

                @include('helpers.form_control', [
                    'type' => 'email',
                    'class' => '',
                    'name' => 'email',
                    'label' => trans('messages.login.email'),
                    'value' => old('email') ? old('email') : demo_auth()['email'],
                ])

                @include('helpers.form_control', [
                    'type' => 'password',
                    'class' => '',
                    'name' => 'password',
                    'label' => trans('messages.login.password'),
                    'value' => demo_auth()['password'],
                ])

                <div class="form-group">
                    <div class="control-container">
                        <div class="">
                            <label class="custom-control custom-checkbox">
                                <input name="remember" {{ old('remember') ? 'checked' : '' }} value="checked" type="checkbox" class="custom-control-input">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description text-small">
                                    {{ trans('messages.login.remember_me') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-primary full-width">
                            {{ trans("messages.login") }}
                        </button>
                        <div class="text-center mt-lvl3">
                            <a class="btn btn-link text-center" href="{{ url('/password/reset') }}">
                                {{ trans("messages.forgot_password") }}
                            </a>
                            <a href="{{ url('/users/register') }}" class="btn btn-link">Create an account</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

