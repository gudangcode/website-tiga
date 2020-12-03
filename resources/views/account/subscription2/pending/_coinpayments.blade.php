<h3 class="text-semibold">{{ trans('messages.subscription.checkout') }}</h3>
<p class="">
    {!! trans('messages.subscription.pending.coinpayments.wording', [
        'plan' => $subscription->plan->name,
        'money' => Acelle\Library\Tool::format_price($subscription->plan->price, $subscription->plan->currency->format),
    ]) !!}
</p>
@if (\Auth::user()->customer->can('cancelNow', $subscription))
    <a link-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
        href="{{ action('AccountSubscriptionController@cancelNow') }}"
        class="btn bg-grey-600 mr-10"
    >
        {{ trans('messages.subscription.cancel_now') }}
    </a>
@endif