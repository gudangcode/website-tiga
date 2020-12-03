<form enctype="multipart/form-data" action="{{ action('Admin\PlanController@sendingServerOption', $plan->uid) }}" method="POST" class="form-validate-jquery">
    {{ csrf_field() }}
    <div class="modal-header">
      <h5 class="modal-title">{{ trans('messages.plan.sending_server.option') }}</h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
        <h2 class="mt-0">{{ trans('messages.plan.sending_server') }}</h2>
                        
        <p>{{ trans('messages.plan.sending_server.intro') }}</p>
        
        <div class="form-group control-radio">
            <div class="radio_box" data-popup='tooltip' title="">
                <label class="main-control">
                    <input {{ ($plan->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM ? 'checked' : '') }} type="radio"
                        name="plan[options][sending_server_option]"
                        value="{{ \Acelle\Model\Plan::SENDING_SERVER_OPTION_SYSTEM }}" class="styled" /><rtitle>{{ trans('messages.plan_option.system_s_sending_server') }}</rtitle>
                    <div class="desc text-normal mb-10">
                        {{ trans('messages.plan_option.system_s_sending_server.intro') }}
                    </div>
                </label>
            </div>
            <hr>
            <div class="radio_box" data-popup='tooltip' title="">
                <label class="main-control">
                    <input {{ ($plan->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN ? 'checked' : '') }} type="radio"
                        name="plan[options][sending_server_option]"
                        value="{{ \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN }}" class="styled" /><rtitle>{{ trans('messages.plan_option.own_sending_server') }}</rtitle>
                    <div class="desc text-normal mb-10">
                        {{ trans('messages.plan_option.own_sending_server.intro') }}
                    </div>
                </label>
            </div>
            <hr>
            <div class="radio_box" data-popup='tooltip' title="">
                <label class="main-control">
                    <input {{ ($plan->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT ? 'checked' : '') }} type="radio"
                        name="plan[options][sending_server_option]"
                        value="{{ \Acelle\Model\Plan::SENDING_SERVER_OPTION_SUBACCOUNT }}" class="styled" /><rtitle>{{ trans('messages.plan_option.sub_account') }}</rtitle>
                    <div class="desc text-normal mb-10">
                        {{ trans('messages.plan_option.sub_account.intro') }}
                    </div>
                </label>
            </div>
        </div>
        <hr>
        <button class="btn btn-mc_primary mr-10">{{ trans('messages.save') }}</button>
    </div>
<form>