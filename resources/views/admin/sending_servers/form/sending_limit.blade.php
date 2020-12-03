<div class="modal-header">
    <h5 class="modal-title">{{ trans('messages.plan.fitness') }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="mc_section">
        <form action="{{ action('Admin\SendingServerController@sendingLimit', ['uid' => ($server->uid ? $server->uid : 0)]) }}" method="POST">
            {{ csrf_field() }}
        
            <h2 class="text-semibold">{{ trans('messages.sending_quota') }}</h2>
            
            <p>{!! trans('messages.options.wording') !!}</p>
                
            <div class="row boxing">
                <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => 'numeric',
                        'name' => 'quota_value',
                        'value' => $server->quota_value,
                        'help_class' => 'sending_server',
                        'default_value' => '1000',
                    ])
                </div>
                <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => 'numeric',
                        'name' => 'quota_base',
                        'value' => $server->quota_base,
                        'help_class' => 'sending_server',
                        'default_value' => '1',
                    ])
                </div>
                <div class="col-md-4">
                    @include('helpers.form_control', ['type' => 'select',
                        'name' => 'quota_unit',
                        'value' => $server->quota_unit,
                        'label' => trans('messages.quota_time_unit'),
                        'options' => Acelle\Model\Plan::quotaTimeUnitOptions(),
                        'include_blank' => trans('messages.choose'),
                        'help_class' => 'sending_server',
                    ])
                </div>
            </div>
            <hr>
            <button class="btn btn-mc_primary mr-10">{{ trans('messages.sending_limit.save') }}</button>
            <a href="javascript:;" class="btn btn-mc_inline mr-10" data-dismiss="modal">{{ trans('messages.sending_limit.close') }}</a>
        </form>
    </div>
</div>
