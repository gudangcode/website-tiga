@extends('layouts.frontend')

@section('title', trans('messages.subscription'))

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
        <div class="col-sm-12 col-md-6 col-lg-6">

            @if ($hasPendingPaymentForFuture)
                @include('account.subscription.future_pending._' . \Acelle\Model\Setting::get('system.payment_gateway'))
            @elseif ($goingToExpire && (\Acelle\Model\Setting::get('system.renew_free_plan') == 'yes' || (\Acelle\Model\Setting::get('system.renew_free_plan') == 'no' && !$subscription->plan->isFree())))
                <p class="alert alert-warning mb-40">
                    {!! trans('messages.subscription.renew.warning', [
                        'remain' => $currentPeriodEnd->diffForHumans(),
                        'next_on' => \Acelle\Library\Tool::formatDate($currentPeriodEnd),
                    ]) !!}
                </p>
            @endif

            <h2 class="text-semibold">{{ trans('messages.subscription') }}</h2>

            <div class="sub-section">
                <h3 class="text-semibold">{{ trans('messages.current_plan') }} </h3>

                @if ($subscription->isActive())
                    @if ($subscription->recurring())
                        <p>
                            {!! trans('messages.subscription.current_subscription.recurring.wording', [
                                'plan' => $plan->name,
                                'money' => Acelle\Library\Tool::format_price($plan->price, $plan->currency->format),
                                'remain' => $currentPeriodEnd->diffForHumans(),
                                'next_on' => \Acelle\Library\Tool::formatDate($currentPeriodEnd)
                            ]) !!}
                        </p>

                        @if (\Auth::user()->customer->can('cancel', $subscription))
                            <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel.confirm') }}"
                                href="{{ action('AccountSubscriptionController@cancel') }}"
                                class="btn bg-grey-600 mr-10"
                            >
                                {{ trans('messages.subscription.cancel') }}
                            </a>
                        @endif
                        @if (\Auth::user()->customer->can('cancelNow', $subscription))
                            <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
                                href="{{ action('AccountSubscriptionController@cancelNow') }}"
                                class="btn bg-grey-600 mr-10"
                            >
                                {{ trans('messages.subscription.cancel_now') }}
                            </a>
                        @endif
                        @if (\Auth::user()->customer->can('changePlan', $subscription))
                            <a
                                href="{{ action('AccountSubscriptionController@changePlan', ["id" => $subscription->uid]) }}"
                                class="btn bg-grey-600 mr-10 modal_link"
                                data-size="sm"
                            >
                                {{ trans('messages.subscription.change_plan') }}
                            </a>
                        @endif
                    @elseif ($gateway->isSupportRecurring())
                        <p>
                            {!! trans('messages.subscription.current_subscription.cancel_at_end_of_period.wording', [
                                'plan' => $plan->name,
                                'money' => Acelle\Library\Tool::format_price($plan->price, $plan->currency->format),
                                'remain' => $currentPeriodEnd->diffForHumans(),
                                'end_at' => \Acelle\Library\Tool::formatDate($currentPeriodEnd)
                            ]) !!}
                        </p>

                        @if (\Auth::user()->customer->can('resume', $subscription))
                            <a link-method="POST" link-confirm="{{ trans('messages.subscription.resume.confirm') }}"
                                href="{{ action('AccountSubscriptionController@resume') }}"
                                class="btn bg-teal-800 mr-10"
                            >
                                {{ trans('messages.subscription.resume') }}
                            </a>
                        @endif
                        @if (\Auth::user()->customer->can('cancelNow', $subscription))
                            <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
                                href="{{ action('AccountSubscriptionController@cancelNow') }}"
                                class="btn bg-grey-600 mr-10"
                            >
                                {{ trans('messages.subscription.cancel_now') }}
                            </a>
                        @endif
                    @else
                        <p>
                            {!! trans('messages.subscription.current_subscription.cancel_at_end_of_period_no_recurring_supported.wording', [
                                'plan' => $plan->name,
                                'money' => Acelle\Library\Tool::format_price($plan->price, $plan->currency->format),
                                'remain' => $currentPeriodEnd->diffForHumans(),
                                'end_at' => \Acelle\Library\Tool::formatDate($currentPeriodEnd)
                            ]) !!}
                        </p>

                        @if (\Auth::user()->customer->can('renew', $subscription) && !$hasPendingPaymentForFuture)
                            <a class="btn btn-mc_primary mr-10"
                                href="{{ action('AccountSubscriptionController@renew') }}">
                                {{ trans('messages.subscription.renew') }}
                            </a>
                        @endif
                        @if (\Auth::user()->customer->can('cancelNow', $subscription))
                            <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
                                href="{{ action('AccountSubscriptionController@cancelNow') }}"
                                class="btn bg-grey-600 mr-10"
                            >
                                {{ trans('messages.subscription.cancel_now') }}
                            </a>
                        @endif
                        @if (\Auth::user()->customer->can('changePlan', $subscription) && !$hasPendingPaymentForFuture)
                            <a
                                href="{{ action('AccountSubscriptionController@changePlan', ["id" => $subscription->uid]) }}"
                                class="btn bg-grey-600 mr-10 modal_link"
                                data-size="sm"
                            >
                                {{ trans('messages.subscription.change_plan') }}
                            </a>
                        @endif
                    @endif
                @endif
            </div>
            <div class="sub-section">
                <h3 class="text-semibold">{{ trans('messages.plan_details') }} </h3>

                @include('plans._details', ['plan' => $plan])
            </div>
        </div>
    </div>

    @include('account.subscription._invoices')

@endsection
