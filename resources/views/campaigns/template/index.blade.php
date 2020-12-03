@extends('layouts.frontend')

@section('title', trans('messages.campaigns') . " - " . trans('messages.template'))
    
@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
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

        @include('campaigns._steps', ['current' => 3])
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mb-40">
            <h2 class="mt-0">{{ trans('messages.campaign.content_management') }}</h2>
            <div class="sub-section d-flex">
                <div class=" mr-auto pr-2">                    
                    <p>{{ trans('messages.campaign.email_content.intro') }}</p>
                        
                    <div class="media-left">
                        <div class="main">
                            <label>{{ trans('messages.campaign.html_email') }}</label>
                            <p>{{ trans('messages.campaign.html_email.last_edit', [
                                'date' => Acelle\Library\Tool::formatDateTime($campaign->updated_at),
                            ]) }}</p>

                            <p class="mt-20">
                                <a href="{{ action('CampaignController@templateCreate', $campaign->uid) }}" class="btn btn-primary bg-grey-600 mr-5">
                                    {{ trans('messages.campaign.change_template') }}
                                </a>
                                @if (in_array(Acelle\Model\Setting::get('builder'), ['both','pro']))
                                    <a href="{{ action('CampaignController@templateEdit', $campaign->uid) }}" class="btn btn-primary mr-5 template-compose">
                                        {{ trans('messages.campaign.email_builder_pro') }}
                                    </a>
                                @endif
                                @if (in_array(Acelle\Model\Setting::get('builder'), ['both','classic']))
                                    <a href="{{ action('CampaignController@builderClassic', $campaign->uid) }}" class="btn btn-default template-compose-classic">
                                        {{ trans('messages.campaign.email_builder_classic') }}
                                    </a>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="template-thumb-container ml-4">
                        <img class="automation-template-thumb" src="{{ $campaign->getThumbUrl() }}?v={{ Carbon\Carbon::now() }}" />
                        <a
                            onclick="popupwindow('{{ action('CampaignController@preview', $campaign->uid) }}', '{{ $campaign->name }}', 800, 800)"
                            href="javascript:;"
                            class="btn btn-primary" style="display:none"
                        >
                            {{ trans('messages.automation.template.preview') }}
                        </a>                           
                    </div>
                </div>
            </div>

            @if ($spamscore)
                <div class="sub-section">
                    <h2 class="mt-0">{{ trans('messages.campaign.spam_score') }}</h2>
                    <p>{!! trans('messages.campaign.score.intro') !!}</p>
                    <a href="#" id="calculate-score" class="btn btn-primary bg-grey-600 mr-5">
                        {{ trans('messages.campaign.check_spam_score') }}
                    </a>
                </div>
            @endif

            <div class="sub-section">   
                <h2 class="mt-0">{{ trans('messages.campaign.attachment') }}</h2>
                <p>{{ trans('messages.campaign.attachment.intro') }}</p>
                    
                @include('campaigns._attachment')
            </div>
            
            
        </div>
    </div>
        
    <hr>
    <a href="{{ action('CampaignController@schedule', ['uid' => $campaign->uid]) }}" class="btn bg-teal-800">
        {{ trans('messages.next') }} <i class="icon-arrow-right7"></i>
    </a>
        
    <script>
        var templatePopup = new Popup();        
    
        $(document).ready(function() {
            $('.template-start').click(function() {
                var url = $(this).attr('data-url');
                
                templatePopup.load(url);
            });
        });

        $('#calculate-score').click(function() {
            spamPopup = new Popup("{{ action('CampaignController@spamScore', ['uid' => $campaign->uid]) }}");
            spamPopup.load();
            return false;
        });
    </script>

@endsection
