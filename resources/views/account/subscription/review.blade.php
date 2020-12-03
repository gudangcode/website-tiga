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
            <span class="text-semibold">{{ trans('messages.plan.review') }}</span>
        </h1>
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="sub-section mb-30">
                <p>{{ trans('messages.plan.review.wording') }}</p>
                
                <ul class="mc-inline-list">
                    <li>                        
                        <desc>{{ trans('messages.plan_name') }}</desc>
                        <value>{{ $plan->name }}</value>
                    </li>
                    <li>                        
                        <desc>{{ trans('messages.price') }}</desc>
                        <value>{{ Acelle\Library\Tool::format_price($plan->price, $plan->currency->format) }}/{{ $plan->displayFrequencyTime() }}</value>
                    </li>
                    <li>                        
                        <desc>{{ trans('messages.sending_total_quota_label') }}</desc>
                        <value>{{ $plan->displayTotalQuota() }}</value>
                    </li>
                    <li>                        
                        <desc>{{ trans('messages.max_lists_label') }}</desc>
                        <value>{{ $plan->displayMaxList() }}</value>
                    </li>
                    <li>                        
                        <desc>{{ trans('messages.max_subscribers_label') }}</desc>
                        <value>{{ $plan->displayMaxSubscriber() }}</value>
                    </li>
                    <li>                        
                        <desc>{{ trans('messages.max_campaigns_label') }}</desc>
                        <value>{{ $plan->displayMaxCampaign() }}</value>
                    </li>
                </ul>
            </div>
            <div class="sub-section">
                <div class="row">
                    <div class="col-md-4">
                        <a link-method="POST"
                            href="{{ action('AccountSubscriptionController@create', ['plan_uid' => $plan->uid]) }}"
                            class="btn btn-mc_primary">
                                {{ trans('messages.payment.proceed_with_payment') }}
                        </a>                         
                    </div>
                    <div class="col-md-8">
                        {!! trans('messages.payment.agree_service_intro', ['plan' => $plan->name]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    

@endsection