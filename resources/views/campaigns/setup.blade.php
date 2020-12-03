@extends('layouts.frontend')

@section('title', trans('messages.campaigns') . " - " . trans('messages.setup'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
			<li><a href="{{ action("CampaignController@index") }}">{{ trans('messages.campaigns') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-paperplane"></i> {{ $campaign->name }}</span>
		</h1>

		@include('campaigns._steps', ['current' => 2])
	</div>

@endsection

@section('content')
	<form action="{{ action('CampaignController@setup', $campaign->uid) }}" method="POST" class="form-validate-jqueryz">
		{{ csrf_field() }}
		
		<div class="row">
			<div class="col-md-6 list_select_box" target-box="segments-select-box" segments-url="{{ action('SegmentController@selectBox') }}">
				@include('helpers.form_control', ['type' => 'text',
					'name' => 'name',
					'label' => trans('messages.name_your_campaign'),
					'value' => $campaign->name,
					'rules' => $rules,
					'help_class' => 'campaign'
				])
												
				@include('helpers.form_control', ['type' => 'text',
					'name' => 'subject',
					'label' => trans('messages.email_subject'),
					'value' => $campaign->subject,
					'rules' => $rules,
					'help_class' => 'campaign'
				])
												
				@include('helpers.form_control', ['type' => 'text',
					'name' => 'from_name',
					'label' => trans('messages.from_name'),
					'value' => $campaign->from_name,
					'rules' => $rules,
					'help_class' => 'campaign'
				])
				
				<div class="hiddable-box" data-control="[name=use_default_sending_server_from_email]" data-hide-value="1">
					@include('helpers.form_control', [
						'type' => 'autofill',
						'id' => 'sender_from_input',
						'name' => 'from_email',
						'label' => trans('messages.from_email'),
						'value' => $campaign->from_email,
						'rules' => $rules,
						'help_class' => 'campaign',
						'url' => action('SenderController@dropbox'),
						'empty' => trans('messages.sender.dropbox.empty'),
						'error' => trans('messages.sender.dropbox.error.' . Auth::user()->customer->allowUnverifiedFromEmailAddress(), [
							'sender_link' => action('SenderController@index'),
						]),
						'header' => trans('messages.verified_senders'),
					])
				</div>

				@include('helpers.form_control', ['type' => 'checkbox2',
					'name' => 'use_default_sending_server_from_email',
					'label' => trans('messages.use_sending_server_default_value'),
					'value' => $campaign->use_default_sending_server_from_email,
					'rules' => $rules,
					'help_class' => 'campaign',
					'options' => ['0','1'],
				])
												
				@include('helpers.form_control', [
                    'type' => 'autofill',
                    'id' => 'sender_reply_to_input',
                    'name' => 'reply_to',
                    'label' => trans('messages.reply_to'),
                    'value' => $campaign->reply_to,
                    'url' => action('SenderController@dropbox'),
                    'rules' => $campaign->rules(),
                    'help_class' => 'campaign',
                    'empty' => trans('messages.sender.dropbox.empty'),
                    'error' => trans('messages.sender.dropbox.reply.error.' . Auth::user()->customer->allowUnverifiedFromEmailAddress(), [
                        'sender_link' => action('SenderController@index'),
                    ]),
                    'header' => trans('messages.verified_senders'),
                ])
				
			</div>
			<div class="col-md-6 segments-select-box">
				<div class="form-group checkbox-right-switch">
					@if ($campaign->type != 'plain-text')
						@include('helpers.form_control', ['type' => 'checkbox',
													'name' => 'track_open',
													'label' => trans('messages.track_opens'),
													'value' => $campaign->track_open,
													'options' => [false,true],
													'help_class' => 'campaign',
													'rules' => $rules
												])
					
						@include('helpers.form_control', ['type' => 'checkbox',
													'name' => 'track_click',
													'label' => trans('messages.track_clicks'),
													'value' => $campaign->track_click,
													'options' => [false,true],
													'help_class' => 'campaign',
													'rules' => $rules
												])
					@endif
					
					@include('helpers.form_control', ['type' => 'checkbox',
													'name' => 'sign_dkim',
													'label' => trans('messages.sign_dkim'),
													'value' => $campaign->sign_dkim,
													'options' => [false,true],
													'help_class' => 'campaign',
													'rules' => $rules
												])
					@include('helpers.form_control', [
						'type' => 'checkbox',
						'name' => 'custom_tracking_domain',
						'label' => trans('messages.custom_tracking_domain'),
						'value' => $campaign->tracking_domain_id,
						'options' => [false,true],
						'help_class' => 'campaign',
						'rules' => $rules
					])
					
					<div class="select-tracking-domain">
						@include('helpers.form_control', [
							'type' => 'select',
							'name' => 'tracking_domain_uid',
							'label' => '',
							'value' => $campaign->trackingDomain? $campaign->trackingDomain->uid : null,
							'options' => Auth::user()->customer->getVerifiedTrackingDomainOptions(),
							'include_blank' => trans('messages.campaign.select_tracking_domain'),
							'help_class' => 'campaign',
							'rules' => $rules
						])
					</div>
												
					@if ($campaign->type == 'plain-text')
						<div class="alert alert-warning">
							{!! trans('messages.campaign.plain_text.open_click_tracking_wanring') !!}
						</div>
					@endif
				</div>
			</div>
		</div>
		<hr>
		<div class="text-right {{ Auth::user()->customer->allowUnverifiedFromEmailAddress() ? '' : 'unverified_next_but' }}">
			<button class="btn bg-teal-800">{{ trans('messages.save_and_next') }} <i class="icon-arrow-right7"></i> </button>
		</div>
		
	<form>
	
	<script>
		function checkUnverified() {
			if(!$('.autofill-error:visible').length) {
				$('.unverified_next_but').show();
			} else {
				$('.unverified_next_but').hide();
			}
		}

		setInterval(function() { checkUnverified() }, 1000);

		$(document).ready(function() {
			// auto fill
			var box = $('#sender_from_input').autofill({
				messages: {
					header_found: '{{ trans('messages.sending_identity') }}',
					header_not_found: '{{ trans('messages.sending_identity.not_found.header') }}'
				}
			});
			box.loadDropbox(function() {
				$('#sender_from_input').focusout();
				box.updateErrorMessage();
			})

			// auto fill 2
			var box2 = $('#sender_reply_to_input').autofill({
				messages: {
					header_found: '{{ trans('messages.sending_identity') }}',
					header_not_found: '{{ trans('messages.sending_identity.reply.not_found.header') }}'
				}
			});
			box2.loadDropbox(function() {
				$('#sender_reply_to_input').focusout();
				box2.updateErrorMessage();
			})

			$('[name="from_email"]').blur(function() {
				$('[name="reply_to"]').val($(this).val()).change();
			});
			$('[name="from_email"]').change(function() {
				$('[name="reply_to"]').val($(this).val()).change();
			});

			// select custom tracking domain
			$('[name=custom_tracking_domain]').change(function() {
				var value = $('[name=custom_tracking_domain]:checked').val();

				if (value) {
					$('.select-tracking-domain').show();
				} else {
					$('.select-tracking-domain').hide();
				}
			});
			$('[name=custom_tracking_domain]').change();
		})
	</script>
				
@endsection
