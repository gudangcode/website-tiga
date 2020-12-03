<h3>{{ trans('messages.payment.options') }}</h3>

<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
    {{ csrf_field() }}
	<div class="row">
		<div class="col-md-6">
			@include('helpers.form_control', [
				'type' => 'text',
				'class' => '',
				'name' => 'options[key_id]',
				'value' => $gateway['fields']['key_id'],
				'label' => trans('messages.payment.razorpay.key_id'),
				'help_class' => 'payment',
				'rules' => ['options.key_id' => 'required'],
			])	
            
			@include('helpers.form_control', [
				'type' => 'text',
				'class' => '',
				'name' => 'options[key_secret]',
				'value' => $gateway['fields']['key_secret'],
				'label' => trans('messages.payment.razorpay.key_secret'),
				'help_class' => 'payment',
				'rules' => ['options.key_secret' => 'required'],
			])
		</div>
    </div>

    <hr>
    <div class="text-left">
        <button class="btn btn-mc_primary mr-5">{{ trans('messages.save') }}</button>
        @if (\Acelle\Model\Setting::get('system.payment_gateway') !== $gateway['name'])
            <input type="submit" class="btn btn-mc_primary bg-teal  mr-5" name="save_and_set_primary" value="{{ trans('messages.save_and_set_primary') }}" />
        @endif
        <a class="btn btn-mc_default" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
    </div>

</form>