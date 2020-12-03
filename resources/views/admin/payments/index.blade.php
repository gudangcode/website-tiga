@extends('layouts.backend')

@section('title', trans('messages.payment_gateways'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-credit-card2"></i> {{ trans('messages.payment_gateways') }}</span>
        </h1>
    </div>

@endsection

@section('content')

    <div class="row">
        <div class="col-md-8">
            <p>{{ trans('messages.payment_gateways.wording') }}</p>
        </div>
        <div class="col-md-12">
            <div class="mc-list-setting mt-20">
                @foreach ($gateways as $name => $gateway)
                    <div class="list-setting bg-{{ $gateway['name'] }} {{ \Acelle\Model\Setting::get('system.payment_gateway') == $name ? 'current' : '' }}">
                        <div class="list-setting-main">
                            <div class="title">
                                <label>{{ trans('messages.payments.' . $name) }}</label>
                                @if (\Acelle\Model\Setting::get('system.payment_gateway') == $name)
                                    <span class="label label-info bg-primary ml-4">{{ trans('messages.payment.primary') }}</span>
                                @endif
                            </div>
                            <p>{{ trans('messages.payments.' . $name . '.list_intro') }}</p>
                        </div>
                        <div class="list-setting-footer">
                            @if (\Acelle\Model\Setting::get('system.payment_gateway') !== $name)
                                <a class="btn btn-mc_primary" link-method="POST" href="{{ action('Admin\PaymentController@setPrimary', $name) }}">
                                    {{ trans('messages.payment.set_primary') }}
                                </a>
                            @endif

                            <a class="btn btn-mc_default ml-5" href="{{ action('Admin\PaymentController@edit', $name) }}">
                                {{ trans('messages.payment.setting') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="sub-section mt-40">
        <h2>{{ trans('messages.payment.settings') }}</h2>
        <form action="{{ action('Admin\SettingController@payment') }}" method="POST" class="form-validate-jqueryz">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    @include('helpers.form_control', [
                        'type' => 'number',
                        'name' => 'end_period_last_days',
                        'value' => config('cashier.end_period_last_days'),
                        'label' => trans('messages.system.end_period_last_days'),
                        'help_class' => 'setting',
                        'rules' => ['end_period_last_days' => 'required'],
                    ])
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group checkbox-right-switch">
                        @include('helpers.form_control', [
                            'type' => 'checkbox',
                            'name' => 'renew_free_plan',
                            'value' => config('cashier.renew_free_plan'),
                            'label' => trans('messages.system.renew_free_plan'),
                            'help_class' => 'setting',
                            'options' => ['no', 'yes'],
                            'rules' => ['renew_free_plan' => 'required'],
                        ])
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">  
                        @include('helpers.form_control', [
                            'type' => 'number',
                            'name' => 'recurring_charge_before_days',
                            'value' => config('cashier.recurring_charge_before_days'),
                            'label' => trans('messages.system.recurring_charge_before_days'),
                            'help_class' => 'setting',
                            'options' => ['no', 'yes'],
                            'rules' => ['recurring_charge_before_days' => 'required'],
                        ])
                    </div>
                </div>
            </div>
            <button class="btn btn-mc_primary">
                {{ trans('messages.save') }}
            </a>
        </form>
    </div>

@endsection
