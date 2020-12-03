<div class="mc_section">
    <h2 class="head">{{ trans('messages.sending_servers') }}</h2>
    <p>{{ trans('messages.plan.sending_servers.intro') }}</p>
    <div class="form-group control-radio">
        <div class="radio_box" data-popup='tooltip' title="">
            <label class="main-control">
                <input {{ ($options['sending_server_option'] == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM ? 'checked' : '') }} type="radio"
                    name="options[sending_server_option]"
                    value="{{ \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM }}" class="styled" /><rtitle>{{ trans('messages.plan_option.system_s_sending_server') }}</rtitle>
                <div class="desc text-normal mb-10">
                    {{ trans('messages.plan_option.system_s_sending_server.intro') }}
                </div>
            </label>
            <div class="radio_more_box">
                <input type="hidden" name="options[all_sending_servers]" value="no" />
                 @if ($plan->id && $options['sending_server_option'] == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM)
                    <a href="{{ $update_link }}" class="btn btn-primary bg-grey">
                        {{ trans('messages.plan.sending_servers.settings') }}
                    </a>
                @endif
            </div>
        </div>
        <hr>
        <div class="radio_box" data-popup='tooltip' title="">
            <label class="main-control">
                <input {{ ($options['sending_server_option'] == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN ? 'checked' : '') }} type="radio"
                    name="options[sending_server_option]"
                    value="{{ \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN }}" class="styled" /><rtitle>{{ trans('messages.plan_option.own_sending_server') }}</rtitle>
                <div class="desc text-normal mb-10">
                    {{ trans('messages.plan_option.own_sending_server.intro') }}
                </div>
            </label>
            <div class="radio_more_box">
                <div class="boxing">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => 'numeric',
                        'name' => 'options[sending_servers_max]',
                        'value' => $options['sending_servers_max'],
                        'label' => trans('messages.max_sending_servers'),
                        'help_class' => $help_class,
                        'options' => ['true', 'false'],
                        'rules' => $rules,
                        'unlimited_check' => true,
                    ])
                </div>
    
                <p>
                    @include('helpers.form_control', ['type' => 'checkbox2',
                        'class' => '',
                        'name' => 'options[all_sending_server_types]',
                        'value' => $options['all_sending_server_types'],
                        'label' => trans('messages.allow_adding_all_sending_server_types'),
                        'options' => ['no','yes'],
                        'help_class' => $help_class,
                        'rules' => $rules
                    ])
                </p>
                <div class="all_sending_server_types_no">
                    <hr>
                    <label class="text-semibold text-muted">{{ trans('messages.select_allowed_sending_server_types') }}</label>
                    <div class="row">
                        @foreach (Acelle\Model\SendingServer::types() as $key => $type)
                            <div class="col-md-4 pt-10">
                                &nbsp;&nbsp;<span class="text-semibold text-italic">{{ trans('messages.' . $key) }}</span>
                                <span class="notoping pull-left">
                                    @include('helpers.form_control', ['type' => 'checkbox',
                                        'class' => '',
                                        'name' => 'options[sending_server_types][' . $key . ']',
                                        'value' => isset($options['sending_server_types'][$key]) ? $options['sending_server_types'][$key] : 'no',
                                        'label' => '',
                                        'options' => ['no','yes'],
                                        'help_class' => $help_class,
                                        'rules' => $rules
                                    ])
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="radio_box" data-popup='tooltip' title="">
            <label class="main-control">
                <input {{ ($options['sending_server_option'] == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT ? 'checked' : '') }} type="radio"
                    name="options[sending_server_option]"
                    value="{{ \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT }}" class="styled" /><rtitle>{{ trans('messages.plan_option.sub_account') }}</rtitle>
                <div class="desc text-normal mb-10">
                    {{ trans('messages.plan_option.sub_account.intro') }}
                </div>
            </label>
            <div class="radio_more_box">
                @if (Auth()->user()->admin->getSubaccountSendingServers()->count())
                    <div class="row">
                        <div class="col-md-6">
                            @include('helpers.form_control', [
                                'type' => 'select',
                                'class' => 'numeric',
                                'name' => 'options[sending_server_subaccount_uid]',
                                'value' => $options['sending_server_subaccount_uid'],
                                'label' => '',
                                'help_class' => $help_class,
                                'include_blank' => trans('messages.select_sending_server_with_subaccount'),
                                'options' => Auth()->user()->admin->getSubaccountSendingServersSelectOptions(),
                                'rules' => $rules
                            ])
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger">
                        {!! trans('messages.plan_option.there_no_subaccount_sending_server') !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>