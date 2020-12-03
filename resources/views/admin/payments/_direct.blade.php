<h3>{{ trans('messages.payment.options') }}</h3>

<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
    {{ csrf_field() }}
	<div class="row">
		<div class="col-md-6">
			@include('helpers.form_control', [
				'type' => 'textarea',
				'class' => 'setting-editor',
				'name' => 'options[payment_instruction]',
				'value' => $service->getPaymentInstruction(),
				'label' => trans('messages.payment.direct.payment_instruction'),
				'help_class' => 'payment',
				'rules' => ['options.payment_instruction' => 'required'],
			])
			
			
		</div>

		<div class="col-md-6">
			@include('helpers.form_control', [
				'type' => 'textarea',
				'class' => 'setting-editor',
				'name' => 'options[confirmation_message]',
				'value' => $service->getPaymentConfirmationMessage(),
				'label' => trans('messages.payment.direct.confirmation_message'),
				'help_class' => 'payment',
				'rules' => ['options.confirmation_message' => 'required'],
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