@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <div class="text-center">
                <ul class="nav nav-tabs mc-nav campaign-template-tabs inline-tabs">
                    <li><a class="thumb-url-tab" href="{{ action('TemplateController@updateThumb', $template->uid) }}">
                        {{ trans('messages.template.thumb.upload') }}
                    </a></li>
                    <li class="active"><a href="javascript:;">
                        {{ trans('messages.template.thumb.url') }}
                    </a></li>
                </ul>
            </div>

            <h2 class="mt-0 mb-4">{{ trans('messages.template.thumb.upload_thumbnail_url') }}</h2>
            <p>{{ trans('messages.template.thumb.upload_thumbnail_url.intro') }}</p>
            
            
            <form enctype="multipart/form-data" action="{{ action('TemplateController@updateThumbUrl', $template->uid) }}"
                method="POST" class="template_upload_form form-validate-jquery"
            >
                {{ csrf_field() }}

                @include('helpers.form_control', [
                    'required' => true,
                    'type' => 'text',
                    'label' => trans('messages.upload_file'),
                    'name' => 'url'
                ])
				
                <div class="mt-20">
                    <button class="btn btn-primary bg-grey-600 mr-5">{{ trans('messages.save') }}</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        $('.thumb-url-tab').click(function(e) {
            e.preventDefault();    

            var url = $(this).attr("href");

            thumbPopup.load(url);
        });

        $('.template_upload_form').submit(function(e) {
            e.preventDefault();        
            var url = $(this).attr('action');
            var formData = new FormData($(this)[0]);

            addMaskLoading();

            // 
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                async: false,
                cache: false,
                contentType: false,
                processData: false,
                statusCode: {
                    // validate error
                    400: function (res) {
                        thumbPopup.loadHtml(res.responseText);

                        // remove masking
                        removeMaskLoading();
                    }
                },
                success: function (response) {
                    removeMaskLoading();

                    // notify
                    notify(response.status, '{{ trans('messages.notify.success') }}', response.message); 

                    thumbPopup.hide();  

                    tableFilterAll();      
                }
            });
        });
    </script>
@endsection