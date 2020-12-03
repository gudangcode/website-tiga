<div class="modal-header">
    <h5 class="modal-title">{{ trans('messages.plan.sending_limit') }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="mc_section">
        <form action="{{ action('Admin\PlanController@billingCycle', ['uid' => $plan->uid]) }}" method="POST">
            {{ csrf_field() }}
            
            <input type="hidden" name="plan[options][billing_cycle]" value="other" />
            
            <h2 class="text-semibold">{{ trans('messages.plan.billing_cycle') }}</h2>
            
            <p>{!! trans('messages.plans.billing_cycle.wording') !!}</p>
                
            <div class="row">
                <div class="col-md-6">
                    @include('helpers.form_control', [
                        'class' => 'numeric',
                        'type' => 'text',
                        'name' => 'plan[general][frequency_amount]',
                        'value' => $plan->frequency_amount,
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules(),
                    ])
                </div>
                <div class="col-md-6">                        
                    @include('helpers.form_control', ['type' => 'select',
                        'name' => 'plan[general][frequency_unit]',
                        'value' => $plan->frequency_unit,
                        'options' => $plan->timeUnitOptions(),
                        'help_class' => 'plan',
                    ])
                </div>
            </div>
            <hr>
            <button class="btn btn-mc_primary mr-10">{{ trans('messages.plans.billing_cycle.save') }}</button>
            <a href="javascript:;" class="btn btn-mc_inline mr-10" data-dismiss="modal">{{ trans('messages.plans.billing_cycle.close') }}</a>
        </form>
    </div>
</div>
    

                    