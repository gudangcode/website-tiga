<div class="modal-header">    
    <h5 class="modal-title">{{ trans('messages.plan.new_plan') }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="mc_section mb-0">
        <form id="wizard" action="{{ action('Admin\PlanController@wizard') }}" method="POST">
            {{ csrf_field() }}
                
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <!-- display flash message -->
                    @include('common.errors')
    
                    <h2 class="mt-0">{{ trans('messages.plan.general.details') }}</h2>
            
                    <p>{{ trans('messages.plan.general.details.intro') }}</p>
                        
                    @include('helpers.form_control', [
                        'type' => 'text',
                        'name' => 'plan[general][name]',
                        'label' => trans('messages.plan.name'),
                        'value' => $plan->name,
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules()
                    ])
                    
                    @include('helpers.form_control', [
                        'class' => 'numeric',
                        'type' => 'text',
                        'name' => 'plan[general][price]',
                        'label' => trans('messages.plan.price'),
                        'value' => $plan->price,
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules()
                    ])
                    
                    <div class="select-custom-2" data-url="{{ action('Admin\PlanController@billingCycle', '00') }}">
                        @include ('admin.plans._billing_cycle', [
                            'plan' => $plan,
                        ])
                    </div>
                    
                    @include('helpers.form_control', [
                        'type' => 'select_ajax',
                        'name' => 'plan[general][currency_id]',
                        'label' => trans('messages.plan.currency'),
                        'selected' => [
                            'value' => $plan->currency_id,
                            'text' => is_object($plan->currency) ? $plan->currency->displayName() : ''
                        ],
                        'help_class' => 'plan',
                        'rules' => $plan->generalRules(),
                        'url' => action('Admin\CurrencyController@select2'),
                        'placeholder' => trans('messages.select_currency')
                    ])
                 </div>
            </div>
        </form>
    </div>
</div>
<div class="modal-footer text-center">
    <button onClick="$('#wizard').submit();" class="btn btn-mc_primary mr-10">{{ trans('messages.plan.wizard.next') }}</button>
    <a href="javascript:;" class="btn btn-mc_inline mr-10" data-dismiss="modal">{{ trans('messages.plan.wizard.cancel') }}</a>
</div>
    
<script>
    $('#wizard').submit(function() {
        var form = $(this);
        
        // ajax load url
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            dataType: 'html',
        }).success(function(response) {
            mcModal.fill(response);
        });
        
        return false;
    });
    
    $('.select-custom-2').select_custom();
</script>
