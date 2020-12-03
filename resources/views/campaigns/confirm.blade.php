@extends('layouts.frontend')

@section('title', trans('messages.campaigns') . " - " . trans('messages.confirm'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/pickers/anytime.min.js') }}"></script>
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

        @include('campaigns._steps', ['current' => 5])
    </div>

@endsection

@section('content')

    <div class="confirm-campaign-box">
        <form action="{{ action('CampaignController@confirm', $campaign->uid) }}" method="POST" class="form-validate-jqueryz">
            {{ csrf_field() }}

            <div class="head">
                <h2 class="text-semibold mb-5">{{ trans('messages.you_are_all_send') }}</h2>
                <p>{{ trans('messages.review_campaign_feeback') }}</p>
            </div>

            <ul class="modern-listing">
                @if (!is_null($score))
                    <li>
                        <a href="{{ action('CampaignController@templatePreview', $campaign->uid) }}" class="btn btn-info bg-grey">{{ trans('messages.edit') }}</a>
                        <!-- {{ $count = $campaign->subscribersCount() }} -->
                        @if ($score['result'] == true)
                            <i class="icon-checkmark4"></i>
                        @else
                            @if (!Acelle\Model\Setting::isYes('spamassassin.required'))
                                <i class="icon-exclamation text-warning"></i>
                            @else
                                <i class="icon-cross2 text-danger"></i>
                            @endif
                        @endif
                        <h4>{{ trans('messages.campaign.spam_score') }}</h4>
                        <p>
                            @if ($score['result'] == true)
                                <span class="badge badge-success">{{ trans('messages.campaign.score.passed') }} {{ $score['score'] }}</span>
                            @else
                                <span class="badge badge-warning">{{ trans('messages.campaign.score.failed') }} {{ $score['score'] }}</span>
                            @endif
                        </p>
                    </li>
                @endif
                <li>
                    <a href="{{ action('CampaignController@recipients', $campaign->uid) }}" class="btn btn-info bg-grey">{{ trans('messages.edit') }}</a>
                    <!-- {{ $count = $campaign->subscribersCount() }} -->
                    @if ($count)
                        <i class="icon-checkmark4"></i>
                    @else
                        <i class="icon-cross2 text-danger"></i>
                    @endif
                    <h4>{{ number_with_delimiter($count) }} {{ trans('messages.recipients') }}</h4>
                    <p>
                        {!! $campaign->displayRecipients() !!}
                    </p>
                </li>
                <li>
                    <a href="{{ action('CampaignController@setup', $campaign->uid) }}" class="btn btn-info bg-grey">{{ trans('messages.edit') }}</a>
                    <i class="icon-checkmark4"></i>
                    <h4>{{ trans('messages.email_subject') }}</h4>
                    <p>
                        {{ $campaign->subject }}
                    </p>
                </li>
                <li>
                    <a href="{{ action('CampaignController@setup', $campaign->uid) }}" class="btn btn-info bg-grey">{{ trans('messages.edit') }}</a>
                    <i class="icon-checkmark4"></i>
                    <h4>{{ trans('messages.reply_to') }}</h4>
                    <p>
                        {{ $campaign->reply_to }}
                    </p>
                </li>
                <li>
                    <a href="{{ action('CampaignController@setup', $campaign->uid) }}" class="btn btn-info bg-grey">{{ trans('messages.edit') }}</a>
                    <i class="icon-checkmark4"></i>
                    <h4>{{ trans('messages.tracking') }}</h4>
                    <p>
                        @if ($campaign->track_open)
                            {{ trans('messages.opens') }}<pp>,</pp>
                        @endif
                        @if ($campaign->track_click)
                            {{ trans('messages.clicks') }}<pp>,</pp>
                        @endif
                    </p>
                </li>
                <li>
                    <a href="{{ action('CampaignController@schedule', $campaign->uid) }}" class="btn btn-info bg-grey">{{ trans('messages.edit') }}</a>
                    <i class="icon-checkmark4"></i>
                    <h4>{{ trans('messages.run_at') }}</h4>
                    <p>
                        {{ isset($campaign->run_at) ? Acelle\Library\Tool::formatDateTime($campaign->run_at) : "" }}
                    </p>
                </li>
            </ul>

            @if ($campaign->step() >= 5)
                @if (!is_null($score) && !$score['result'])
                    @if (!Acelle\Model\Setting::isYes('spamassassin.required'))
                        <!--1.	Show ra error như dưới nhưng vẫn cho gửi campaign
                        2.	Khi user click Send thì show 1 cái confirm box: “Your message does not pass SpamScore test, are you sure you want to proceed anyway?”-->

                        <br />
                        <div class="text-right">
                            <span
                                onclick="popupwindow('{{ action('CampaignController@preview', $campaign->uid) }}', '{{ $campaign->name }}', 800, 800)"
                                href="#preview" class="btn btn-lg bg-grey mr-5" data-uid="{{ $campaign->uid }}">
                                {{ trans('messages.preview') }} <i class="icon-eye"></i>
                            </span>
                            <button class="btn btn-lg bg-grey mr-5 send-a-test-email-link" data-uid="{{ $campaign->uid }}">{{ trans('messages.send_a_test_email') }} <i class="icon-envelop3 ml-5"></i> </button>
                            @if (!is_null($score))
                                <span onclick="$('#spam_score_confirm_model').modal('show')" class="btn btn-lg bg-teal-800">{{ trans('messages.send') }} <i class="icon-paperplane ml-5"></i> </span>
                                <button style="display: none" class="btn btn-lg bg-teal-800 send-campaign-button">{{ trans('messages.send') }} <i class="icon-paperplane ml-5"></i> </button>
                            @else
                                <button class="btn btn-lg bg-teal-800 send-campaign-button">{{ trans('messages.send') }} <i class="icon-paperplane ml-5"></i> </button>
                            @endif
                        </div>

                        <!-- Basic modal -->
                        <div id="spam_score_confirm_model" class="modal fade">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form class="confirm-link-form" onkeypress="return event.keyCode != 13;">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h3 class="modal-title"><strong>{{ trans('messages.are_you_sure') }}</strong></h3>
                                        </div>

                                        <div class="modal-body">
                                            <p>{{ trans('messages.campaign.send_without_score_passed.warning') }}</p>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link" data-dismiss="modal">{{ trans('messages.cancel') }}</button>
                                            <a class="btn bg-grey link-confirm-button" onclick="$('.send-campaign-button').click()">{{ trans('messages.confirm') }}</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- /basic modal -->
                    @endif
                @else
                    <br />
                    <div class="text-right">
                        <span
                            onclick="popupwindow('{{ action('CampaignController@preview', $campaign->uid) }}', '{{ $campaign->name }}', 800, 800)"
                            href="#preview" class="btn btn-lg bg-grey mr-5" data-uid="{{ $campaign->uid }}">
                            {{ trans('messages.preview') }} <i class="icon-eye"></i>
                        </span>
                        <button class="btn btn-lg bg-grey mr-5 send-a-test-email-link" data-uid="{{ $campaign->uid }}">{{ trans('messages.send_a_test_email') }} <i class="icon-envelop3 ml-5"></i> </button>
                        <button class="btn btn-lg bg-teal-800">{{ trans('messages.send') }} <i class="icon-paperplane ml-5"></i> </button>
                    </div>
                @endif


            @endif
        </form>
        <br><br><br>
    </div>
@endsection
