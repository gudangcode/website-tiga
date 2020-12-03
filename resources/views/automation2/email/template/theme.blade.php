@extends('layouts.popup.medium')

@section('content')
	
	@include('automation2.email._tabs', ['tab' => 'template'])
	
	<div class="row">
        <div class="col-md-12">
            <h5 class="mb-3 mt-2">{{ trans('messages.automation.choose_your_template_layout') }}</h5>
			<p class="mb-4">{{ trans('messages.automation.choose_your_template_layout.intro') }}</p>
                
            @include('automation2.email.template._tabs', ['tab' => 'theme'])
			
			<div class="template-filter pt-4">
				<div class="d-flex align-items-center">
					<label class="font-weight-semibold mr-2">From</label>
					<select class="select" name="from">
						<option value="all" selected='selected'>{{ trans('messages.all') }}</option>
						<option value="mine">{{ trans('messages.my_templates') }}</option>
						<option value="gallery">{{ trans('messages.gallery') }}</option>
					</select>	
				</div>
			</div>
			<div class="template-list ajax-list">
				
			</div>
        </div>
    </div>
        
    <script>
		var listTheme = new List( $('.template-list'), {
			url: '{{ action('Automation2Controller@templateThemeList', [
					'uid' => $automation->uid,
					'email_uid' => $email->uid,
				]) }}',
			per_page: 12,
			data: function() {
					return {
						from: $('[name=from]').val(),
					};
				},
		});
		
		listTheme.load();
		
		// filters
		$('[name=from]').change(function() {
			listTheme.load();
		});
		
    </script>
@endsection