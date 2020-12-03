@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2 class="mt-0">{{ trans('messages.subscription.reject_pending_payment') }}</h2>
            <p>{{ trans('messages.subscription.reject_pending_payment.leave_note') }}</p>

            <form enctype="multipart/form-data" action="{{ action('Admin\SubscriptionController@rejectPending', $subscription->uid) }}" method="POST" class="form-validate-jquery reject-form">
		        {{ csrf_field() }}
                @include('helpers.form_control', [
                    'type' => 'textarea',
                    'name' => 'reason',
                    'label' => '',
                    'value' => '',
                    'help_class' => 'payment_method',
                    'rules' => ['reason' => 'required']
                ])

                <button class="btn btn-mc_primary">{{ trans('messages.subscription.reject_pending') }}</button>
            </form>
        </div>
    </div>
        
    <script>
        $('.reject-form').submit(function(e) {
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
                        rejectPendingSub.loadHtml(res.responseText);
                    }
                },
                success: function (response) {
                    rejectPendingSub.hide();

                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', response.message);

                    tableFilterAll();
                }
            });
        });
    </script>
@endsection