<h5 class="mb-3">
    {{ trans('messages.automation.action.wait') }}
</h5>
<p class="mb-3">
    {{ trans('messages.automation.action.wait.intro') }}
</p>

<div class="row">
    <div class="col-md-6">    
        @include('helpers.form_control', [
            'type' => 'select',
            'class' => '',
            'label' => '',
            'name' => 'time',
            'value' => $element->getOption('time'),
            'help_class' => 'trigger',
            'options' => $automation->getDelayOptions(),
            'rules' => [],
        ])
    </div>
</div>
