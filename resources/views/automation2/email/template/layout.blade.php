@extends('layouts.popup.medium')

@section('content')
	
	@include('automation2.email._tabs', ['tab' => 'template'])
		
	<div class="row">
        <div class="col-md-12">
            <h5 class="mb-3 mt-2">{{ trans('messages.automation.choose_your_template_layout') }}</h5>
			<p class="mb-4">{{ trans('messages.automation.choose_your_template_layout.intro') }}</p>
                
            @include('automation2.email.template._tabs', ['tab' => 'layout'])
                
            <div id="layout" class="tab-pane row template-boxes layout mt-4" style="
                margin-left: -15px;
                margin-right: -15px;
            ">
                @foreach(Acelle\Model\Template::templateStyles() as $name => $style)
                    <div class="col-xxs-6 col-xs-4 col-sm-2 col-md-2">
                        <a data-method="POST"
							href="{{ action('Automation2Controller@templateLayout', [
								'uid' => $automation->uid,
								'email_uid' => $email->uid,
								'layout' => $name,
							]) }}"
							class="select-layout"
						>
                            <div class="panel panel-flat panel-template-style mb-4">
                                <div class="panel-body">									
									<img src="{{ url('images/template_styles/'.$name.'.png') }}" />
									<label class="mb-20 text-center">{{ trans('messages.'.$name) }}</label>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
        
    <script>
		var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            
        }});

        $('a.select-layout').click(function(e) {
            e.preventDefault();
        
            var url = $(this).attr('href');
			
			popup.loading();
        
            $.ajax({
				url: url,
				type: 'POST',
				data: {
					_token: CSRF_TOKEN
				}
			}).always(function(response) {
				//// set node title
				//tree.getSelected().setTitle(response.title);
				//// merge options with reponse options
				//tree.getSelected().setOptions($.extend(tree.getOptions(), response.options));
				//tree.getSelected().setOptions($.extend(tree.getOptions(), {init: true}));
				//
				//popup.hide();
				//
				//notify('success', '{{ trans('messages.notify.success') }}', response.message);
				
				popup.load('{{ action('Automation2Controller@emailTemplate', [
					'uid' => $automation->uid,
					'email_uid' => $email->uid,
				]) }}');

				builderSelectPopup.load(response.url);

				// notify
				notify('success', '{{ trans('messages.notify.success') }}', response.message);
			});
        });
    </script>
@endsection