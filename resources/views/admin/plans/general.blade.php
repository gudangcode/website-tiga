@extends('layouts.backend')

@section('title', $plan->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PlanController@index") }}">{{ trans('messages.plans') }}</a></li>
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    
    @include('admin.plans._menu')

    <form enctype="multipart/form-data" action="{{ action('Admin\PlanController@save', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
        {{ csrf_field() }}                
        <div class="mc_section">
            <div class="row">
                <div class="col-md-7">
                    <h2>{{ trans('messages.plan.general.overview') }}</h2>
                    <!--
                    @include('elements._notification', [
                        'level' => 'info',
                        'message' => trans('messages.plan.info.subscriber_count', [
                            'count' => $plan->customersCount(),
                            'link' => action('Admin\SubscriptionController@index', ['plan_uid' => $plan->uid]),
                        ])
                    ])
                    -->
                    <p>{{ trans('messages.plan.general.details.intro') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    
                    <div class="stats-boxes">
                        <div class="width1of4 stats-box">
                            <h3>
                                {{ $plan->displayPrice() }}
                            </h3>
                            <p>{{ trans('messages.plan.quota_time.wording', [
                                'amount' => format_number($plan->frequency_amount),
                                'unit' => trans('messages.' . $plan->frequency_unit),
                            ]) }}</p>
                        </div>
                        <div class="width1of4 stats-box">
                            <h3>
                                <a href="{{ action('Admin\PlanController@quota', $plan->uid) }}">{{ $plan->displayTotalQuota() }}</a>
                            </h3>
                            <p>{{ trans('messages.plan.sending_credits') }}</p>
                        </div>
                        <div class="width1of4 stats-box">
                            <h3>
                                
                                @if (Acelle\Model\Setting::get('system.payment_gateway'))
                                    <a href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.payment.' . Acelle\Model\Setting::get('system.payment_gateway')) }}</a>
                                @else
                                    <a class="text-warning" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.payment.not_set') }}</a>
                                @endif
                                
                            </h3>
                            <p>{{ trans('messages.plan.payment') }}</p>
                        </div>
                        <div class="width1of4 stats-box">
                            <h3>
                                @if ($plan->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM)
                                    @if ($plan->primarySendingServer())
                                        <a href="{{ action('Admin\PlanController@sendingServers', $plan->uid) }}">
                                            {{ trans('messages.' . $plan->primarySendingServer()->type) }}
                                        </a>
                                    @else
                                        <a class="text-warning" href="{{ action('Admin\PlanController@sendingServers', $plan->uid) }}">
                                            {{ trans('messages.plan.sending_server.not_set') }}
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ action('Admin\PlanController@sendingServer', $plan->uid) }}">
                                        {{ trans('messages.plan.sending_server.custom') }}
                                    </a>
                                @endif
                            </h3>
                            <p>{{ trans('messages.plan.delivery') }}</p>
                        </div>
                    </div>
                        
                    <h2>{{ trans('messages.plan.general.details') }}</h2>
                    
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'name' => 'plan[general][name]',
                        'label' => trans('messages.plan.name'),
                        'value' => $plan->name,
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules()
                    ])

                    @include('helpers.form_control', [
                        'type' => 'text',
                        'name' => 'plan[general][description]',
                        'label' => trans('messages.plan.description'),
                        'value' => $plan->description,
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules(),
                    ])
                    
                    @include('helpers.form_control', [
                        'class' => 'numeric',
                        'type' => 'text',
                        'name' => 'plan[general][price]',
                        'label' => trans('messages.plan.price'),
                        'value' => $plan->price,
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules()
                    ])
                    
                    @include('helpers.form_control', [
                        'type' => 'select_ajax',
                        'name' => 'plan[general][currency_id]',
                        'label' => trans('messages.plan.currency'),
                        'selected' => [
                            'value' => $plan->currency_id,
                            'text' => is_object($plan->currency) ? $plan->currency->displayName() : ''
                        ],
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules(),
                        'url' => action('Admin\CurrencyController@select2'),
                        'placeholder' => trans('messages.select_currency')
                    ])
                    
                    <div class="select-custom" data-url="{{ action('Admin\PlanController@billingCycle', $plan->uid) }}">
                        @include ('admin.plans._billing_cycle', [
                            'plan' => $plan,
                        ])
                    </div>                    
                    <!--
                    @include('helpers.form_control', [
                        'type' => 'select',
                        'class' => '',
                        'name' => 'plan[general][color]',
                        'label' => trans('messages.plan.color'),
                        'value' => $plan->color,
                        'help_class' => 'admin',
                        'options' => $plan->colors("color"),
                        'rules' => '',
                    ])
                    -->
                    
                    @include('helpers.form_control', [
                        'class' => '',
                        'type' => 'checkbox2',
                        'name' => 'plan[general][tax_billing_required]',
                        'label' => trans('messages.plan.tax_billing_required'),
                        'help' => trans('messages.plan.tax_billing_required.help'),
                        'value' => $plan->tax_billing_required,
                        'options' => [false,true],
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules()
                    ])
                 </div>
            </div>
        </div>
        <button class="btn btn-mc_primary mr-10">{{ trans('messages.save') }}</button>
        <a href="{{ action('Admin\PlanController@index') }}" type="button" class="btn btn-mc_inline">
            {{ trans('messages.cancel') }}
        </a>
    </form>
@endsection
