@if ($gatewayService->getCardInformation($customer) !== NULL)
    <div class="sub-section">
        <h3 class="text-semibold">{!! trans('messages.subscription.card_list') !!}</h3>
        <ul class="dotted-list topborder section mb-20">
            <li>
                <div class="unit size1of2">
                    <strong>{{ trans('messages.card.holder') }}</strong>
                </div>
                <div class="lastUnit size1of2">
                    <mc:flag><strong>{{ $gatewayService->getCardInformation($customer)->name }}</strong></mc:flag>
                </div>
            </li>
            <li>
                <div class="unit size1of2">
                    <strong>{{ trans('messages.card.last4') }}</strong>
                </div>
                <div class="lastUnit size1of2">
                    <mc:flag><strong>{{ $gatewayService->getCardInformation($customer)->last4 }}</strong></mc:flag>
                </div>
            </li>
        </ul>

        <a href="{{ action('AccountSubscriptionController@pay') }}" class="btn btn-mc_primary mr-10">{{ trans('messages.subscription.pay_with_this_card') }}</a>
        <a href="javascript:;" class="btn btn-mc_default" onclick="$('#stripe_button button').click()">{{ trans('messages.subscription.pay_with_new_card') }}</a>
    </div>
@else
    <a href="javascript:;" class="btn btn-mc_primary" onclick="$('#stripe_button button').click()">{{ trans('messages.subscription.pay_with_stripe') }}</a>
@endif

<form id="stripe_button" style="display: none" action="{{ action('AccountSubscriptionController@updateCard', [
    '_token' => csrf_token(),
]) }}" method="POST">
    <script
      src="https://checkout.stripe.com/checkout.js" class="stripe-button"
      data-key="{{ $gateway['fields']['publishable_key'] }}"
      data-amount="{{ $subscription->plan->stripePrice() }}"
      data-currency="{{ $subscription->plan->currency->code }}"
      data-email="{{ Auth::user()->email }}"
      data-name="{{ \Acelle\Model\Setting::get('site_name') }}"
      data-description="{{ \Acelle\Model\Setting::get('site_description') }}"
      data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
      data-locale="{{ language_code() }}"
      data-zip-code="true"
      data-label="{{ trans('messages.pay_with_strip_label_button') }}">
    </script>
</form>