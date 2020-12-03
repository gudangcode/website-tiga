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

            @if ($subscription->hasError())
                @include('elements._notification', [
                    'level' => $subscription->getError()["status"],
                    'message' => $subscription->getError()["message"]
                ])
            @endif

            <h2 class="text-semibold">{{ trans('messages.subscription') }}</h2>

            <div class="sub-section">
                @if ($subscription->isActive())
                    @if ($subscription->isRecurring())
                        <p>
                            {!! trans('messages.subscription.current_subscription.recurring.wording', [
                                'plan' => $plan->name,
                                'money' => Acelle\Library\Tool::format_price($plan->price, $plan->currency->format),
                                'remain' => $subscription->current_period_ends_at->diffForHumans(),
                                'next_on' => \Acelle\Library\Tool::formatDate($subscription->current_period_ends_at)
                            ]) !!}
                        </p>
                    @else
                        <p>
                            {!! trans('messages.subscription.current_subscription.cancel_at_end_of_period.wording', [
                                'plan' => $plan->name,
                                'money' => Acelle\Library\Tool::format_price($plan->price, $plan->currency->format),
                                'remain' => $subscription->current_period_ends_at->diffForHumans(),
                                'end_at' => \Acelle\Library\Tool::formatDate($subscription->current_period_ends_at)
                            ]) !!}
                        </p>
                    @endif

                    @if (\Auth::user()->customer->can('cancel', $subscription))
                        <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel.confirm') }}"
                            href="{{ action('AccountSubscriptionController@cancel') }}"
                            class="btn bg-grey-600 mr-10"
                        >
                            {{ trans('messages.subscription.cancel') }}
                        </a>
                    @endif

                    @if (\Auth::user()->customer->can('resume', $subscription))
                        <a link-method="POST" link-confirm="{{ trans('messages.subscription.resume.confirm') }}"
                            href="{{ action('AccountSubscriptionController@resume') }}"
                            class="btn btn-mc_default mr-10"
                        >
                            {{ trans('messages.subscription.resume') }}
                        </a>
                    @endif

                    @if (\Auth::user()->customer->can('changePlan', $subscription))
                        <a
                            href="{{ action('AccountSubscriptionController@changePlan', ["id" => $subscription->uid]) }}"
                            class="btn btn-mc_default change_plan_button mr-10"
                            data-size="sm"
                        >
                            {{ trans('messages.subscription.change_plan') }}
                        </a>
                    @endif

                    @if (\Auth::user()->customer->can('cancelNow', $subscription))
                        <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
                            href="{{ action('AccountSubscriptionController@cancelNow') }}"
                            class="btn btn-mc_inline mr-10"
                        >
                            {{ trans('messages.subscription.cancel_now') }}
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @include('account.subscription._invoices')

    <div class="sub-section">
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-6">
                <h2 class="text-semibold">{{ trans('messages.plan_details') }} </h2>
                <p>{{ trans('messages.plan_details.intro') }}</p>

                @include('plans._details', ['plan' => $plan])
            </div>
        </div>
    </div>

    <script>
        var changePlanModal = new IframeModal();

        $(function() {
            $('.change_plan_button').click(function(e) {
                e.preventDefault();

                var src = $(this).attr('href');
                changePlanModal.load(src);
            });
        });
    </script>

@endsection
