@php
	$metadata = $subscription->getMetadata();
	$transactions = isset($metadata->transactions) ? $metadata->transactions : [];
	
	$tid = 'Transaction ID: ' . uniqid();
	
	$transactions[] = [
		'id' => $tid,
		'createdAt' => \Carbon\Carbon::now()->timestamp,
		'planId' => $newPlan->getBillableId(),
		'periodEndsAt' => $subscription->ends_at->timestamp,
		'amount' => Acelle\Library\Tool::format_price($customer->calcChangePlan($newPlan)['amount'], $newPlan->currency->format),
	];
	
	$subscription->updateMetadata(['transactions' => $transactions]);
@endphp

<p>
	{!! trans('messages.subscription.change_plan.checkout.coinpayments', [
		'price' => $customer->calcChangePlan($newPlan)['amount'],
		'current_plan' => $subscription->plan->name,
		'new_plan' => $newPlan->name,
		'end_on' => Acelle\Library\Tool::formatDate($subscription->ends_at),
		'amount' => Acelle\Library\Tool::format_price($customer->calcChangePlan($newPlan)['amount'], $newPlan->currency->format)
	]) !!}
</p>

<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="{{ $gateway['fields']['merchant_id'] }}">
	<input type="hidden" name="currency" value="{{ $newPlan->currency->code }}">
	<input type="hidden" name="amountf" value="{{ $customer->calcChangePlan($newPlan)['amount'] }}">
	<input type="hidden" name="item_name" value="{{ trans('messages.invoice.change_plan', [
			'current_plan' => $subscription->plan->name,
			'new_plan' => $newPlan->name,
	]) }}">
	<input type="hidden" name="item_number" value="{{ $subscription->uid }}">
	<input type="hidden" name="item_desc" value="{{ $tid }}">
	<input type="hidden" name="success_url" value="{{ action('AccountSubscriptionController@index') }}">
	<input type="image" src="https://www.coinpayments.net/images/pub/CP-main-large.png" alt="Buy Now with CoinPayments.net">
</form>