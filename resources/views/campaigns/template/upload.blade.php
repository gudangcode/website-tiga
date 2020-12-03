@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2>{{ trans('messages.campaign.choose_your_template_layout') }}</h2>
                
            <ul class="nav nav-tabs mc-nav campaign-template-tabs">
                <li><a href="{{ action('CampaignController@templateLayout', $campaign->uid) }}">Layouts</a></li>
                <li><a href="{{ action('CampaignController@templateTheme', $campaign->uid) }}">Themes</a></li>
                <li class="active"><a href="{{ action('CampaignController@templateUpload', $campaign->uid) }}">Upload</a></li>
            </ul>
            
            <div class="alert alert-info">
                {!! trans('messages.template_upload_guide', ["link" => 'https://s3.amazonaws.com/acellemail/newsletter-template-green.zip']) !!}
            </div>
            
            <form enctype="multipart/form-data" action="{{ action('CampaignController@templateUpload', $campaign->uid) }}" method="POST" class="ajax_upload_form form-validate-jquery">
                {{ csrf_field() }}

                @include('helpers.form_control', ['required' => true, 'type' => 'file', 'label' => trans('messages.upload_file'), 'name' => 'file'])
				
                <div class="mt-20">
                    <button class="btn btn-primary bg-grey-600 mr-5">{{ trans('messages.upload') }}</button>
                </div>

            </form>
        </div>
    </div>
        
    <script>
        $('.campaign-template-tabs a').click(function(e) {
            e.preventDefault();
        
            var url = $(this).attr('href');
        
            templatePopup.load(url);
        });

        var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            window.location = '{{ action('CampaignController@template', $campaign->uid) }}';
        }});

        $('.ajax_upload_form').submit(function(e) {
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
                        alert('Something went wrong!');
                    }
                },
                success: function (response) {
                    removeMaskLoading();

                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', response.message);

                    builderSelectPopup.load(response.url);
                    templatePopup.hide();
                }
            });
        });
    </script>
@endsection