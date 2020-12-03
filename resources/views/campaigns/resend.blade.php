@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2 class="mt-0">{{ trans('messages.campaign.resend.title') }}</h2>
            <p>{{ trans('messages.campaign.resend.intro') }}</p>   

            <form enctype="multipart/form-data" action="{{ action('CampaignController@resend', $campaign->uid) }}" method="POST" class="resend-form form-validate-jqueryx">
                {{ csrf_field() }}

                @include('helpers.form_control', ['type' => 'radio',
					'name' => 'option',
					'label' => '',
					'value' => 'not_receive',
                    'rules' => ['option' => 'required'],
                    'options' => [
                        ['text' => trans('messages.campaign.resend.option.not_receive'), 'value' => 'not_receive'],
                        ['text' => trans('messages.campaign.resend.option.not_open'), 'value' => 'not_open'],
                        ['text' => trans('messages.campaign.resend.option.not_click'), 'value' => 'not_click'],
                    ],
                    'help_class' => 'campaign',
				])
                <hr>
                <div class="text-center">
                    <button class="btn btn-mc_primary bg-grey mt-3 mr-2">{{ trans('messages.campaign.resend') }}</button>
                    <button class="btn btn-link font-weight-semibold mt-3">{{ trans('messages.campaign.resend.cancel') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $('.resend-form').submit(function(e) {
            e.preventDefault();

            var url = $(this).attr('action');
            var data = $(this).serialize();

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                statusCode: {
                    // validate error
                    400: function (res) {
                        // newSubscription.loadHtml(res.responseText);
                        // notify
                        notify('error', '{{ trans('messages.notify.error') }}', res.responseText);
                    }
                },
                success: function (response) {
                    resendPopup.hide();

                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', response.message);

                    tableFilterAll();
                },
                error: function (res) {
                    // newSubscription.loadHtml(res.responseText);
                    // notify
                    alert(res.responseText);
                }
            });
        });
    </script>
@endsection