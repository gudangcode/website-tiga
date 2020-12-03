@php
	$metadata = $subscription->getMetadata();
	$transactions = isset($metadata->transactions) ? $metadata->transactions : [];
	
	$tid = 'Transaction ID: ' . uniqid();
	
	$transactions[] = [
		'id' => $tid,
		'createdAt' => \Carbon\Carbon::now()->timestamp,
		'periodEndsAt' => $subscription->nextPeriod()->timestamp,
		'amount' => Acelle\Library\Tool::format_price($subscription->plan->price, $subscription->plan->currency->format),
	];
	
	$subscription->updateMetadata(['transactions' => $transactions]);
@endphp
<form action="https://www.coinpayments.net/index.php" method="post">
	<input type="hidden" name="cmd" value="_pay_simple">
	<input type="hidden" name="reset" value="1">
	<input type="hidden" name="merchant" value="{{ $gateway['fields']['merchant_id'] }}">
	<input type="hidden" name="currency" value="{{ $subscription->plan->currency->code }}">
	<input type="hidden" name="amountf" value="{{ $subscription->plan->price }}">
	<input type="hidden" name="item_name" value="{{ trans('messages.invoice.renew_plan', [
		'plan' => $subscription->plan->name,
	]) }}">
	<input type="hidden" name="item_number" value="{{ $subscription->uid }}">
	<input type="hidden" name="item_desc" value="{{ $tid }}">
	<input type="hidden" name="success_url" value="{{ action('AccountSubscriptionController@index') }}">
	<input type="image" src="https://www.coinpayments.net/images/pub/CP-main-large.png" alt="Buy Now with CoinPayments.net">
</form>