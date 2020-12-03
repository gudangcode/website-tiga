@extends('layouts.popup.medium')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2>{{ trans('messages.campaign.choose_your_template_layout') }}</h2>
                
            <ul class="nav nav-tabs mc-nav campaign-template-tabs">
                <li class="active"><a href="{{ action('CampaignController@templateLayout', $campaign->uid) }}">Layouts</a></li>
                <li><a href="{{ action('CampaignController@templateTheme', $campaign->uid) }}">Themes</a></li>
                <li><a href="{{ action('CampaignController@templateUpload', $campaign->uid) }}">Upload</a></li>
            </ul>
                
            <div id="layout" class="tab-pane fade in active template-boxes layout" style="
                margin-left: -20px;
                margin-right: -20px;
            ">
                @foreach(Acelle\Model\Template::templateStyles() as $name => $style)
                    <div class="col-xxs-12 col-xs-6 col-sm-3 col-md-2">
                        <a href="javascript:;" class="select-template-layout" data-layout="{{ $name }}">
                            <div class="panel panel-flat panel-template-style">
                                <div class="panel-body">
									<a 
										href="{{ action('CampaignController@templateLayout', [
                                            'uid' => $campaign->uid,
                                            'layout' => $name]) }}"
                                        class="choose-layout"
									>
										<img src="{{ url('images/template_styles/'.$name.'.png') }}" />
										<label class="mb-20 text-center">{{ trans('messages.'.$name) }}</label>
									</a>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
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

        $('.choose-layout').click(function(e) {
            e.preventDefault();        
            var url = $(this).attr('href');

            addMaskLoading();

            // 
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                },
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