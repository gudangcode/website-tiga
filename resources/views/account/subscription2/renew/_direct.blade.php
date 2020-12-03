<form action="{{ action('AccountSubscriptionController@renew', $subscription->uid) }}" method="post">
    {{ csrf_field() }}
	
    <button class="btn btn-mc_primary"><i class="icon-check"></i> {{ trans('messages.subscription.renew.checkout') }}</button>
</form>