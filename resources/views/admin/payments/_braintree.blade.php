<h3>{{ trans('messages.payment.options') }}</h3>

<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="braintree form-validate-jquery">
    {{ csrf_field() }}
	<div class="sub-section">
		<div class="row">
			<div class="col-md-6">
				@include('helpers.form_control', [
					'type' => 'select',
					'name' => 'options[environment]',
					'value' => $gateway['fields']['environment'],
					'label' => trans('messages.payment.braintree.environment'),
					'help_class' => 'payment',
					'options' => [['text' => 'Sandbox', 'value' => 'sandbox'],['text' => 'Production', 'value' => 'production']],
					'rules' => ['options.environment' => 'required'],
				])
				
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[merchant_id]',
					'value' => $gateway['fields']['merchant_id'],
					'label' => trans('messages.payment.braintree.merchant_id'),
					'help_class' => 'payment',
					'rules' => ['options.merchant_id' => 'required'],
				])
				
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[public_key]',
					'value' => $gateway['fields']['public_key'],
					'label' => trans('messages.payment.braintree.public_key'),
					'help_class' => 'payment',
					'rules' => ['options.public_key' => 'required'],
				])
				
				@include('helpers.form_control', [
					'type' => 'text',
					'name' => 'options[private_key]',
					'value' => $gateway['fields']['private_key'],
					'label' => trans('messages.payment.braintree.private_key'),
					'help_class' => 'payment',
					'rules' => ['options.private_key' => 'required'],
				])
				
				<h2 class="mt-40 mb-4">{{ trans('messages.braintree.require_valid_card') }}</h2>
				<p>{{ trans('messages.braintree.require_valid_card.intro') }}</p>

				@include('helpers.form_control', ['type' => 'checkbox2',
					'class' => '',
					'name' => 'options[always_ask_for_valid_card]',
					'value' => $gateway['fields']['always_ask_for_valid_card'],
					'label' => trans('messages.braintree.always_ask_for_valid_card'),
					'options' => ['no','yes'],
					'help_class' => 'payment',
				])

				<hr>
				<div class="text-left">
					<button class="btn btn-mc_primary mr-5">{{ trans('messages.save') }}</button>
					@if (\Acelle\Model\Setting::get('system.payment_gateway') !== $gateway['name'])
						<input type="submit" class="btn btn-mc_primary bg-teal  mr-5" name="save_and_set_primary" value="{{ trans('messages.save_and_set_primary') }}" />
					@endif
					<a class="btn btn-mc_default" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
				</div>
			</div>
		</div>
	</div>
</form>

<script>
	$(document).ready(function() {
		$(document).on('change keyup', 'form.braintree input, form.braintree select', function() {
			$('form.mapping').hide();
		});
	});
</script>