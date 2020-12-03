@if (!$isValid)
	<h2 class="mt-0">{{ trans('messages.payment.options') }}</h2>

	<form enctype="multipart/form-data" action="{{ action('Admin\PaymentController@update', $gateway['name']) }}" method="POST" class="form-validate-jquery">
		{{ csrf_field() }}
		<div class="row">
			<div class="col-md-6">
				@include('helpers.form_control', [
					'type' => 'select',
					'name' => 'options[environment]',
					'value' => $gateway['fields']['environment'],
					'label' => trans('messages.payment.paypal.environment'),
					'help_class' => 'payment',
					'options' => [['text' => 'Sandbox', 'value' => 'sandbox'],['text' => 'Production', 'value' => 'production']],
					'rules' => ['options.environment' => 'required'],
				])

				@include('helpers.form_control', [
					'type' => 'text',
					'class' => '',
					'name' => 'options[client_id]',
					'value' => $gateway['fields']['client_id'],
					'label' => trans('messages.payment.paypal.client_id'),
					'help_class' => 'payment',
					'rules' => ['options.client_id' => 'required'],
				])	
				
				@include('helpers.form_control', [
					'type' => 'text',
					'class' => '',
					'name' => 'options[secret]',
					'value' => $gateway['fields']['secret'],
					'label' => trans('messages.payment.paypal.secret'),
					'help_class' => 'payment',
					'rules' => ['options.secret' => 'required'],
				])
			</div>
		</div>

		<hr>
		<div class="text-left">
			<button class="btn btn-mc_primary mr-5">{{ trans('messages.plan.save_and_connect') }}</button>
			@if (\Acelle\Model\Setting::get('system.payment_gateway') !== $gateway['name'])
				<input type="submit" class="btn btn-mc_primary bg-teal  mr-5" name="save_and_set_primary" value="{{ trans('messages.save_and_set_primary') }}" />
			@endif
			<a class="btn btn-mc_default" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
		</div>
	</form>

@else	
				
	<h2 class="mt-0">{{ trans('messages.payment.paypal.connect_your_plans') }}</h2>
	<p>{{ trans('messages.payment.paypal.connect_your_plans.intro') }}<p>

	@if (!Acelle\Model\Plan::getAll()->count())
		<div class="alert alert-warning">
			{!! trans('messages.payment.paypal_subscription.no_mapping_plans', ['link' => action('Admin\PlanController@index')]) !!}
		</div>
	@else

		<div class="mc-list mt-20">
			@foreach (Acelle\Model\Plan::getAll()->get() as $plan)
				
					<div class="list-item d-flex align-items-center pb-10">
						<div class="list-setting-main mr-3">
							@if (!$plan->visible)
								<a
									title="{{ trans('messages.plan.show') }}"
									data-method="POST"
									href="{{ action('Admin\PlanController@visibleOn', $plan->uid) }}"
									class="go_to_plans plan-off {{ (\Auth::user()->can('visibleOn', $plan) ? 'xtooltip' : 'cant_show') }}"
								>
									<i class="material-icons plan-off-icon">toggle_off</i>
								</a>
							@else
								<a
									title="{{ trans('messages.plan.hide') }}"
									data-method="POST"
									href="{{ action('Admin\PlanController@visibleOff', $plan->uid) }}"
									class="go_to_plans plan-on {{ (\Auth::user()->can('visibleOff', $plan) ? 'xtooltip' : 'disabled') }}"
								>
									<i class="material-icons plan-on-icon">toggle_on</i>
								</a>
							@endif
						</div>
						<div class="list-setting-main mr-auto" style="width: 50%">
							<h4 class="title text-bold">
								<a target="_blank" href="{{ action('Admin\PlanController@general', $plan->uid) }}">{{ $plan->name }}</a>
							</h4>
							<p class="mb-0">{{ $plan->description }}</p>
							@if (!$plan->useSystemSendingServer())
								<span class="text-muted small" class="">{{ trans('messages.plan.sending_server.' . $plan->getOption('sending_server_option')) }}  &bull; {{ trans('messages.plans.subscriber_count', ['count' => $plan->customersCount()]) }}</span>
							@elseif ($plan->hasPrimarySendingServer())
								<span class="text-muted small">{{ trans('messages.plans.send_via.wording', ['server' => trans('messages.' . $plan->primarySendingServer()->type)]) }} &bull; {{ trans('messages.plans.subscriber_count', ['count' => $plan->customersCount()]) }}</span>
							@endif

							@foreach ($plan->errors() as $error)
								<div class="text-muted"><span class="text-danger"><i class="fa fa-minus-circle"></i> {{ trans('messages.plan.' . $error . '.setting') }}</span></div>
							@endforeach
						</div>
						<div class="list-setting-main mr-auto">
							<h4 class="title text-bold">
								{{ $plan->getBillableFormattedPrice() }}
							</h4>
							<p class="text-muted">{{ $plan->displayFrequencyTime() }}</p>
						</div>
						<div class="list-setting-main mr-auto">
							<span class="text-muted2 list-status pull-left">
								<span class="label label-flat bg-{{ $plan->status }}">{{ trans('messages.plan_status_' . $plan->status) }}</span>
							</span>
						</div>
						<div class="list-setting-footer text-right" style="width: 20%">
							@if ($plan->price > 0)
								@if ($service->findPlanConnection($plan) && $plan->price > 0)
									<span class="xtooltip" title="{{ trans('messages.payment.paypal_subscription.plan.connected.intro', ['id' => $service->findPlanConnection($plan)['paypal_id']]) }}">{{ trans('messages.payment.paypal_subscription.plan.connected') }}</span>
									<a link-method="POST" link-confirm="{{ trans('messages.payment.paypal_subscription.disconnect_plan.confirm') }}"
										class="btn btn-mc_default ml-5"
										href="{{ action('Admin\PaymentController@paypalSubscriptionDisconnectPlan', $plan->uid) }}"
									>
										{{ trans('messages.payment.paypal_subscription.disconnect_plan') }}
									</a>
								@else
									<form
										action="{{ action('Admin\PaymentController@paypalSubscriptionConnectPlan', $plan->uid) }}"
										method="POST" class="form-validate-jquery"
									>
										{{ csrf_field() }}
										<button class="btn btn-mc_primary ml-5" href="">
											{{ trans('messages.payment.paypal_subscription.connect_plan') }}
										</button>
									</form>
								@endif
							@endif
						</div>
					</div>
				
			@endforeach
		</div>
	@endif

	<div class="row mb-4 mt-4">
		<div class="col-md-6">							
			<h2 class="mb-3">{{ trans('messages.payment.paypal_subscription.connected_to_paypal') }}</h2>
			<p>{!! trans('messages.payment.paypal_subscription.connected_to_paypal.wording', [
				'name' => \Acelle\Model\Setting::get('site_name'),
				'product_id' => $service->getData()['product_id'],
				'dashboard' => 'https://www.paypal.com/mep/dashboard',
				'disconnect' => action('Admin\PaymentController@paypalSubscriptionDisconnect'),
			]) !!}<p>							
		</div>
	</div>

	<script>
		$('.go_to_plans').click(function(e) {
			e.preventDefault();

			var confirm = `{{ trans('messages.plan.go_to_plans', ['link' => action('Admin\PlanController@index')]) }}`;
			var dialog = new Dialog('alert', {
				message: confirm
			})
		});
	</script>
@endif

