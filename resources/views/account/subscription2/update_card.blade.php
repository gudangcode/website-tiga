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

            <div class="sub-section">
                <h3 class="text-semibold">{!! trans('messages.pay_by_credit_card') !!}</h3>
				<p>
                    {!! trans('messages.purchasing_intro_' . $gateway['name'], [
                        'plan' => $plan->name,
                        'price' => Acelle\Library\Tool::format_price($plan->price, $plan->currency->format)
                    ]) !!}
                </p>

                @include("account.subscription.card._" . $gateway['name'])

			</div>
		</div>
	</div>

@endsection
