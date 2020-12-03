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
    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6">
            <div class="sub-section">
                @if ($subscription)
                    @include('elements._notification', [
                        'level' => 'warning',
                        'message' => trans('messages.subscription.ended_intro', [
                            'ended_at' => Acelle\Library\Tool::formatDate($subscription->ends_at),
                            'plan' => $subscription->plan->name,
                        ])
                    ])
                    
                    <p>{{ trans('messages.select_plan.wording') }}</p>
                @else
                    @include('elements._notification', [
                        'level' => 'warning',
                        'message' => trans('messages.no_plan.title')
                    ])

                    <p>{{ trans('messages.select_plan.wording') }}</p>
                @endif

                    
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="sub-section">

                @if (empty($plans))
                    <div class="row">
                        <div class="col-md-6">
                            @include('elements._notification', [
                                'level' => 'danger',
                                'message' => trans('messages.plan.no_available_plan')
                            ])
                        </div>
                    </div>
                @else
                    <div class="price-box">
                        <div class="price-table" style="margin-top: -30px;">

                            @foreach ($plans as $plan)
                                <div class="price-line">
                                    <div class="price-header">
                                        <lable class="plan-title">{{ $plan->name }}</lable>
                                        <p>{{ $plan->description }}</p>
                                    </div>

                                    <div class="price-item text-center">
                                        <div>{{ trans('messages.plan.starting_at') }}</div>
                                        <div class="plan-price">
                                            {{ \Acelle\Library\Tool::format_price($plan->price, $plan->currency->format) }}
                                        </div>
                                        <div>{{ $plan->displayFrequencyTime() }}</div>

                                        <a
                                            href="{{ action('AccountSubscriptionController@review', ['plan_uid' => $plan->uid]) }}"
                                            class="btn btn-mc_primary btn-mc_mk mt-30">
                                                {{ trans('messages.plan.select') }}
                                        </a>

                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>


@endsection