<h3>{{ trans('messages.payment.options') }}</h3>

<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
    {{ csrf_field() }}	
	<div class="row">
		<div class="col-md-6">
			@include('helpers.form_control', [
				'type' => 'text',
				'name' => 'options[publishable_key]',
				'value' => $gateway['fields']['publishable_key'],
				'label' => trans('messages.payment.stripe.publishable_key'),
				'help_class' => 'payment',
				'rules' => ['options.publishable_key' => 'required'],
			])
			
			@include('helpers.form_control', [
				'type' => 'text',
				'name' => 'options[secret_key]',
				'value' => $gateway['fields']['secret_key'],
				'label' => trans('messages.payment.stripe.secret_key'),
				'help_class' => 'payment',
				'rules' => ['options.secret_key' => 'required'],
			])

			<h2 class="mt-40 mb-4">{{ trans('messages.stripe.require_valid_card') }}</h2>
			<p>{{ trans('messages.stripe.require_valid_card.intro') }}</p>

			@include('helpers.form_control', ['type' => 'checkbox2',
                'class' => '',
                'name' => 'options[always_ask_for_valid_card]',
                'value' => $gateway['fields']['always_ask_for_valid_card'],
                'label' => trans('messages.stripe.always_ask_for_valid_card'),
                'options' => ['no','yes'],
                'help_class' => 'payment',
			])
			
			@include('helpers.form_control', ['type' => 'checkbox2',
                'class' => '',
                'name' => 'options[billing_address_required]',
                'value' => $gateway['fields']['billing_address_required'],
                'label' => trans('messages.stripe.billing_address_required'),
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

	
</form>