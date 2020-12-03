@extends('layouts.frontend')

@section('title', trans('messages.checkout'))

@section('page_script')
@endsection

@section('page_header')

	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold">{{ trans('messages.your_subscriptions') }}</span>
		</h1>
	</div>

@endsection

@section('content')

	@include("account._menu")

	<div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6">
            <h2 class="text-semibold">{{ trans('messages.subscription') }}</h2>

			@include("account.subscription._billing_information")

            <p>
				{!! trans('messages.purchasing_intro_' . $gateway['name'], [
					'plan' => $subscription->plan->name,
					'price' => Acelle\Library\Tool::format_price($subscription->plan->price, $subscription->plan->currency->format)
				]) !!}
			</p>

            @include("account.subscription.card._" . $gateway['name'])


            @if (\Auth::user()->customer->can('cancelNow', $subscription))
                <hr class="mt-40">
                <p>
                    <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
                        href="{{ action('AccountSubscriptionController@cancelNow') }}"
                        class=""
                    >
                        <i>{{ trans('messages.subscription.click_here_to_cancel_now') }}</i>
                    </a>
                </p>
            @endif

		</div>
	</div>

@endsection
