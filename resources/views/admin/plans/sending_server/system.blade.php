@extends('layouts.backend')

@section('title', $plan->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PlanController@index") }}">{{ trans('messages.plans') }}</a></li>
            <li><a href="{{ action('Admin\PlanController@sendingServer', $plan->uid) }}">
                    {{ $plan->name }}
                </a>
            </li>
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    
    @include('admin.plans._menu')
    
    <div class="mc-section">
        <div class="announce_box">
            <div class="row flex-center">
                <div class="col-md-8">
                    <i class="announce_box-icon lnr lnr-apartment"></i>
                    <label>{{ trans('messages.plan_option.delivery_setting') }}</label>
                    <h4>{{ trans('messages.plan_option.system_s_sending_server') }}</h4>
                    <p>{{ trans('messages.plan_option.system_s_sending_server.intro') }}</p>
                </div>
                <div class="col-md-4 text-right">
                    <a class="btn btn-mc_primary mr-20 mc-modal-control" modal-size="lg" href="{{ action('Admin\PlanController@sendingServerOption', [
                        'id' => $plan->uid]) }}">
                            {{ trans('messages.plan_option.change') }}</a>
                </div>
            </div>
        </div>
            
        <div class="row flex-center">
            <div class="col-md-8">
                <h2 class="mc-h2 mt-0 mb-10">
                    <span class="text-semibold">{{ trans('messages.plan.sending_servers.listing') }}</span>
                </h2>
                <p>{{ trans('messages.plan.sending_servers.list_intro') }}</p>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ action('Admin\PlanController@addSendingServer', $plan->uid) }}" class="btn btn-primary mr-5 bg-grey mc-modal-control">
                    {{ trans('messages.plan.sending_servers.add') }}
                </a>
            </div>
        </div>
        
        @if(!$plan->plansSendingServers()->count())
            <div class="empty-list">
                <i class="icon-server"></i>
                <span class="line-1">
                    {{ trans('messages.sending_server_empty_line_1') }}
                </span>
            </div>
        @else
            <div class="sending-servers">
                <ul class="mc_list mt-20">
                    @foreach ($plan->plansSendingServers as $planSendingServer)
                        <li>
                            <content width="50%">
                                <span class="mc_list_media mc-server-avatar server-avatar server-avatar-{{ $planSendingServer->sendingServer->type }} mr-0">
                                    <i class="icon-server"></i>
                                </span>
                                <h4>{{ $planSendingServer->sendingServer->name }}
                                    @if ($planSendingServer->isPrimary())
                                        &nbsp;&nbsp;<span class="badge badge-info bg-grey">{{ trans('messages.sending_servers.primary') }}</span>
                                    @endif
                                </h4>
                                <p>
                                    {!! trans('messages.sending_server.speed', ['limit' => $planSendingServer->sendingServer->displayQuotaHtml()]) !!}
                                </p>
                            </content>
                            <stat>
                                <div class="single-stat-box pull-left">
                                    <span class="stat-head percent-list">{{ $planSendingServer->showFitness() }}%</span>
                                    <br />
                                    <span class="text-muted xtooltip" title="{{ trans("messages.sending_servers.fitness.explain") }}">{{ trans("messages.sending_servers.fitness") }}
                                    @if ($plan->plansSendingServers()->count() > 1)
                                        - <a modal-size="lg" class="mc-modal-control" href="{{ action('Admin\PlanController@fitness', ['id' => $plan->uid]) }}">{{ trans('messages.edit') }}</a></span>
                                    @endif
                                </div>
                            </stat>
                            <actions style='width: 30%; text-align: right'>                                
                                @if (!$planSendingServer->isPrimary())
                                    <a link-method="POST" href="{{ action('Admin\PlanController@setPrimarySendingServer', ['id' => $plan->uid, 'sending_server_id' => $planSendingServer->sendingServer->uid]) }}" class="btn btn-mc_default">{{ trans('messages.sending_servers.set_primary') }}</a>
                                @endif
                                @if (Auth::user()->admin->can('update', $planSendingServer->sendingServer))
                                    <a href="{{ action('Admin\SendingServerController@edit', ["uid" => $planSendingServer->sendingServer->uid, "type" => $planSendingServer->sendingServer->type]) }}" data-popup="tooltip" title="{{ trans('messages.edit') }}" type="button" class="btn btn-mc_default">{{ trans('messages.edit') }}</a>
                                @endif
                                <a link-method="POST" href="{{ action('Admin\PlanController@removeSendingServer', ['id' => $plan->uid, 'sending_server_id' => $planSendingServer->sendingServer->uid]) }}" class="btn btn-mc_default">{{ trans('messages.remove') }}</a>
                            </actions>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    
    @if (is_object($plan->primarySendingServer()))
        <div class="mc-section">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="mc-h2 mt-0">
                        <span class="text-semibold">{{ trans('messages.plan.sending_identify') }}</span>
                    </h2>
                
                    <p>{!! trans('messages.plan.sending_identify.intro.' . $plan->primarySendingServer()->type) !!}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-box pml-table table-log mt-10">
                        <tbody>
                            <tr>
                                <th>{{ trans('messages.sending_server.domain_email') }}</th>
                                <th>{{ trans('messages.sending_server.server') }}</th>
                                <th>{{ trans('messages.sending_server.date_added') }}</th>
                                <th>{{ trans('messages.sending_server.status') }}</th>
                                <th>{{ trans('messages.sending_server.action') }}</th>
                            </tr>
                        </tbody>
                        <tbody>
                            @forelse ($plan->getVerifiedIdentities() as $domain)
                                <tr class="odd">
                                    <td>
                                        <strong>{{ $domain }}</strong>
                                        <div class="text-muted2">{{ trans('messages.identity.supported') }}</div>
                                    </td>
                                    <td>
                                        <span>{{ $plan->primarySendingServer()->name }}</span>
                                    </td>
                                    <td>
                                        <span>
                                            {{ Acelle\Library\Tool::formatDate($plan->primarySendingServer()->updated_at) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success badge-lg">{{ trans('messages.sending_identity.status.active') }}</span>
                                    </td>
                                    <td>
                                        <a class="direct-link"
                                          confirm-button='{{ trans('messages.go_to') }}'
                                          link-confirm="{{ trans('messages.go_to_sending_server_page') }}"
                                          href="{{ action('Admin\SendingServerController@edit', ["uid" => $plan->primarySendingServer()->uid, "type" => $plan->primarySendingServer()->type]) }}">
                                            <input type="checkbox" name="options[domains][]" value="{{ $domain }}" class="switchery"
                                                checked readonly
                                            />
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="5">
                                        {{ trans('messages.subscription.logs.empty') }}
                                    </td>
                                </te>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
