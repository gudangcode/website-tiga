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
            <span class="text-semibold">{{ Auth::user()->customer->displayName() }}</span>
        </h1>
    </div>

@endsection

@section('content')

    @include("account._menu")

    <div class="row">
        <div class="col-md-12">
            <h2 class="text-semibold">{{ trans('messages.subscription') }}</h2>

            <div class="">
                <div class="row">
                    <div class="col-md-6">

                        @include('account.subscription.pending._' . \Acelle\Model\Setting::get('system.payment_gateway'))

                    </div>
                </div>

            </div>

            <div class="sub-section">
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

            </div>
        </div>
    </div>

    @include('account.subscription._invoices', ['subscription' => $subscription, 'gateway' => $gatewayService])

@endsection
