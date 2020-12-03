@extends('layouts.popup.medium')

@section('content')
	
	@include('automation2.email._tabs', ['tab' => 'template'])
		
	<div class="row">
        <div class="col-md-8">
			@include('automation2.email.template._tabs', ['tab' => 'upload'])
			
            <h5 class="mb-3 mt-3">{{ trans('messages.automation.email.upload_template') }}</h4>
				
			<div class="alert alert-info mt-4">
                {!! trans('messages.template_upload_guide', ["link" => 'https://s3.amazonaws.com/acellemail/newsletter-template-green.zip']) !!}
            </div>
                
            <form enctype="multipart/form-data" action="{{ action('Automation2Controller@templateUpload', [
				'uid' => $automation->uid,
				'email_uid' => $email->uid,
			]) }}" method="POST" class="template-upload-form">
                {{ csrf_field() }}

                <div class="input-group mb-4 mt-4">
					<div class="input-group-prepend">
					  <span class="input-group-text">{{ trans('messages.automation.email.template.upload') }}</span>
					</div>
					<div class="custom-file">
					  <input type="file" name="file" class="custom-file-input" id="templateFile" required>
					  <label class="custom-file-label" for="templateFile">{{ trans('messages.automation.email.template.choose_file') }}</label>
					</div>
				</div>
				
                <div class="mt-4">
                    <button class="btn btn-primary bg-grey-600 mr-5">{{ trans('messages.automation.email.template.upload') }}</button>
                </div>

            </form>
        </div>
    </div>
		
	<script>
		var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
				
		}});
		
		$('.template-upload-form').submit(function(e) {
			e.preventDefault();
			
			if (!$('#templateFile').val()) {
				notify('error', '{{ trans('messages.notify.error') }}', '{{ trans('messages.automation.email.template.no_file_select') }}');
				
				return;
			}
		
			var url = $(this).attr('action');
			var fd = new FormData($(this)[0]);
			
			popup.loading();
			
			$.ajax({
				url: url,  
				type: 'POST',
				data: fd,
				cache: false,
				contentType: false,
				processData: false,
				statusCode: {
                    // validate error
                    400: function (res) {
                       popup.loadHtml(res.responseText);
                    }
                },
				success: function(data) {
					popup.load('{{ action('Automation2Controller@emailTemplate', [
						'uid' => $automation->uid,
						'email_uid' => $email->uid,
					]) }}');

					builderSelectPopup.load('{{ action('Automation2Controller@templateBuilderSelect', [
						'uid' => $automation->uid,
						'email_uid' => $email->uid,
					]) }}');

					// notify
					notify(data.status, '{{ trans('messages.notify.success') }}', data.message);
				}				
			});
		});
		
		$('#templateFile').change(function(e) {
			$('[for="templateFile"]').html($(this).val());
		});
	</script>
@endsection