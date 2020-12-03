@extends('layouts.frontend')

@section('title', trans('messages.subscriptions'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="active">{{ trans('messages.subscription') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-profile"></i> {{ Auth::user()->customer->displayName() }}</span>
        </h1>
    </div>

@endsection

@section('content')

    @include("account._menu")

    <div class="row">
        <div class="col-md-12">
            <h2 class="text-semibold">{{ trans('messages.subscription') }}</h2>

            <div class="sub-section">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="text-semibold">{{ trans('messages.subscription.checkout') }}</h3>
                        <p>{!! trans('messages.subscription.checkout.processing_payment') !!}</p>

                        <a id="pay_now" style="display: none" link-method="POST"
                            class="btn btn-mc_primary"
                            href="{{ action('AccountSubscriptionController@checkout') }}">
                            {{ trans('messages.payment.pay_now') }}
                        </a>

                        <script>
                            // setTimeout(function() { $('#pay_now').click() }, 2000);
                        </script>
                    </div>
                </div>

            </div>

            <!--<div class="sub-section">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="text-semibold">{{ trans('messages.subscription.plan') }}</h3>
                        <p>{!! trans('messages.subscription.plan.wording', [
                            'plan' => $subscription->plan->name,
                        ]) !!}</p>

                        <div class="row">
                            <div class="col-md-12 plan-left">
                                @include('plans._details', ['plan' => $subscription->plan])
                            </div>
                        </div>
                    </div>
                </div>

            </div>-->
        </div>
    </div>

@endsection
