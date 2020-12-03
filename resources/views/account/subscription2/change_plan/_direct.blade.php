<p>
	{!! trans('messages.subscription.change_plan.checkout.coinpayments', [
		'price' => $customer->calcChangePlan($newPlan)['amount'],
		'current_plan' => $subscription->plan->name,
		'new_plan' => $newPlan->name,
		'end_on' => Acelle\Library\Tool::formatDate($subscription->ends_at),
		'amount' => Acelle\Library\Tool::format_price($customer->calcChangePlan($newPlan)['amount'], $newPlan->currency->format)
	]) !!}
</p>

<form action="{{ action('AccountSubscriptionController@changePlanNoRecurring', $newPlan->uid) }}" method="post">
    {{ csrf_field() }}
	
    <button class="btn btn-mc_primary"><i class="icon-check"></i> {{ trans('messages.subscription.change_now') }}</button>
</form>