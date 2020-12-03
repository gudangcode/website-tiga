<div class="modal-header">
    <h5 class="modal-title">{{ trans('messages.plan.sending_limit') }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="mc_section">
        <form action="{{ action('Admin\PlanController@sendingLimit', ['uid' => $plan->uid]) }}" method="POST">
            {{ csrf_field() }}
            
            <input type="hidden" name="plan[options][sending_limit]" value="other" />
        
            <h2 class="text-semibold">{{ trans('messages.plan.sending_limit') }}</h2>
            
            <p>{!! trans('messages.plans.sending_limit.wording') !!}</p>
            
            <div class="row boxing">
                <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => 'numeric',
                        'name' => 'plan[options][sending_quota]',
                        'value' => $plan->getOption('sending_quota'),
                        'label' => trans('messages.sending_quota'),
                        'help_class' => 'plan',
                        'rules' => $plan->rules()
                    ])
                    <div class="checkbox inline unlimited-check text-semibold">
                        <label>
                            <input{{ $plan->getOption('sending_quota')  == -1 ? " checked=checked" : "" }} type="checkbox" class="styled">
                            {{ trans('messages.unlimited') }}
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'class' => 'numeric',
                        'name' => 'plan[options][sending_quota_time]',
                        'value' => $plan->getOption('sending_quota_time'),
                        'label' => trans('messages.quota_time'),
                        'help_class' => 'plan',
                        'rules' => $plan->rules()
                    ])
                </div>
                <div class="col-md-4">
                    @include('helpers.form_control', ['type' => 'select',
                        'name' => 'plan[options][sending_quota_time_unit]',
                        'value' => $plan->getOption('sending_quota_time_unit'),
                        'label' => trans('messages.quota_time_unit'),
                        'options' => Acelle\Model\Plan::quotaTimeUnitOptions(),
                        'help_class' => 'plan',
                        'rules' => $plan->rules()
                    ])
                </div>
            </div>
            <hr>
            <button class="btn btn-mc_primary mr-10">{{ trans('messages.sending_limit.save') }}</button>
            <a href="javascript:;" class="btn btn-mc_inline mr-10" data-dismiss="modal">{{ trans('messages.sending_limit.close') }}</a>
        </form>
    </div>
</div>
