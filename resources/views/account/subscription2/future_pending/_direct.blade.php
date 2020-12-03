@if (!$subscription->payment_claimed)
    <p class="alert alert-warning">
        {!! trans('messages.subscription.future_pending.' . \Acelle\Model\Setting::get('system.payment_gateway'), [
            'plan' => $subscription->plan->name,
            'money' => Acelle\Library\Tool::format_price($subscription->plan->price, $subscription->plan->currency->format),
        ]) !!}
        <br/>
    </p>
        
    <p class="alert alert-info">
        {!! $gateway->notice !!}
    </p>
        
    <a link-method="POST" link-confirm="{{ trans('messages.subscription.claim_payment.confirm') }}"
        href="{{ action('AccountSubscriptionController@paymentClaim') }}"
        class="btn bg-grey-600 mr-10"
    >{{ trans('messages.subscription.claim_payment') }}</a>
@else
    <p class="alert alert-warning">
        {!! trans('messages.subscription.claimed.' . \Acelle\Model\Setting::get('system.payment_gateway'), [
            'plan' => $subscription->plan->name,
            'money' => Acelle\Library\Tool::format_price($subscription->plan->price, $subscription->plan->currency->format),
        ]) !!}
        <br/>
    </p>
    <a href="javascript:;" onclick="$(this).next().show();$(this).hide()">{{ trans('messages.subscription.review_payment_notice') }}</a>
    <p class="alert alert-info" style="display: none">
        {!! $gateway->notice !!}
    </p>
@endif
<hr>
