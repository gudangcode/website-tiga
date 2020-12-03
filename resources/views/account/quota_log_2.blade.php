@extends('layouts.popup.small')

@section('content')
    <h4 class="mt-0 mb-4 d-flex align-items-center">
        <i class="material-icons mr-2">multiline_chart</i>
        <span>{{ trans("messages.used_quota") }}</span>
    </h4>

    <!-- Alert if customer don't have any subscription -->
    @if (is_object(\Auth::user()->customer) &&
        !is_object(\Auth::user()->customer->subscription))
        <div class="alert alert-warning mt-20">
            <h4 class="ui-pnotify-title text-nowrap">
            {!! trans('messages.not_have_any_plan_notification', [
                'link' => action('AccountSubscriptionController@index'),
            ]) !!}
            </h4>
            <div style="margin-top: 10px; clear: both; text-align: right; display: none;"></div>
        </div>
    @else
        <div class="row quota_box">
            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->getSendingQuotaUsage()) }}/{{ (Auth::user()->customer->getSendingQuota() == -1) ? '∞' : \Acelle\Library\Tool::format_number(Auth::user()->customer->getSendingQuota()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displaySendingQuotaUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.sending_quota') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->getSendingQuotaUsagePercentage() }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="content-group-sm">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->listsCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxLists()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displayListsUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.list') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->listsUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted progress-xxs">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->campaignsCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxCampaigns()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displayCampaignsUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.campaign') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->campaignsUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-4">
                <div class="content-group-sm">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->readCache('SubscriberCount')) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxSubscribers()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displaySubscribersUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.subscriber') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->readCache('SubscriberUsage') }}%">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted progress-xxs">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->automationsCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxAutomations()) }}</span>
                        &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displayAutomationsUsage() }}
                    </div>
                    <label class="text-semibold">{{ trans('messages.automation') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->automationsUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-4">
                <div class="content-group-sm mt-20">
                    <div class="pull-right text-teal-800 text-semibold">
                        <span class="text-muted progress-xxs">{{ \Acelle\Library\Tool::format_number(round(Auth::user()->customer->totalUploadSize(),2)) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxTotalUploadSize()) }} (MB)</span>
                        &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->totalUploadSizeUsage() }}%
                    </div>
                    <label class="text-semibold">{{ trans('messages.total_upload_size') }}</label>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->totalUploadSizeUsage() }}%">
                        </div>
                    </div>
                </div>
            </div>

            @if (Auth::user()->customer->can("create", new Acelle\Model\SendingServer()))
                <div class="col-md-12 mb-4">
                    <div class="content-group-sm">
                        <div class="pull-right text-teal-800 text-semibold">
                            <span class="text-muted">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->sendingServersCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxSendingServers()) }}</span>
                            &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displaySendingServersUsage() }}
                        </div>
                        <label class="text-semibold">{{ trans('messages.sending_server') }}</label>
                        <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->sendingServersUsage() }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (Auth::user()->customer->can("create", new Acelle\Model\SendingDomain()))
                <div class="col-md-12 mb-4">
                    <div class="content-group-sm">
                        <div class="pull-right text-teal-800 text-semibold">
                            <span class="text-muted">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->sendingDomainsCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxSendingDomains()) }}</span>
                            &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displaySendingDomainsUsage() }}
                        </div>
                        <label class="text-semibold">{{ trans('messages.sending_domain') }}</label>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->sendingDomainsUsage() }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (Auth::user()->customer->can("create", new Acelle\Model\EmailVerificationServer()))
                <div class="col-md-12 mb-4">
                    <div class="content-group-sm">
                        <div class="pull-right text-teal-800 text-semibold">
                            <span class="text-muted">{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->emailVerificationServersCount()) }}/{{ \Acelle\Library\Tool::format_number(Auth::user()->customer->maxEmailVerificationServers()) }}</span>
                            &nbsp;&nbsp;&nbsp;{{ Auth::user()->customer->displayEmailVerificationServersUsage() }}
                        </div>
                        <label class="text-semibold">{{ trans('messages.email_verification_server') }}</label>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: {{ Auth::user()->customer->emailVerificationServersUsage() }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

@endsection