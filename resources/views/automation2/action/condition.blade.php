<h5 class="mb-3">
    {{ trans('messages.automation.action.set_up_your_condition') }}
</h5>
<p class="mb-3">
    {{ trans('messages.automation.action.condition.intro') }}
</p>

<div class="mb-20">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => '',
        'label' => 'Select criterion',
        'name' => 'type',
        'value' => $element->getOption('type'),
        'help_class' => 'trigger',
        'options' => [
            ['text' => 'Subscriber read an Email', 'value' => 'open'],
            ['text' => 'Subscriber clicks on a Link', 'value' => 'click']
        ],
        'rules' => [],
    ])
</div>
    
<div class="mb-20" data-condition="open" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Which email subscriber reads',
        'name' => 'email',
        'value' => $element->getOption('email'),
        'help_class' => 'trigger',
        'include_blank' => trans('messages.automation.condition.choose_email'),
        'required' => true,
        'options' => $automation->getEmailOptions(),
        'rules' => [],
    ])
</div>
    
<div class="mb-20" data-condition="click" style="display:none">
    @include('helpers.form_control', [
        'type' => 'select',
        'class' => 'required',
        'label' => 'Which Link subscriber clicks',
        'name' => 'email_link',
        'value' => $element->getOption('email_link'),
        'help_class' => 'trigger',
        'options' => $automation->getEmailLinkOptions(),
        'include_blank' => trans('messages.automation.condition.choose_link'),
        'required' => true,
        'rules' => [],
    ])
</div>
    
<script>
    function toggleCriterion() {
        var value = $('[name=type]').val();
        
        $('[data-condition]').hide();
        $('[data-condition='+value+']').show();
    }

    // Toggle condition options
    $(document).on('change', '[name=type]', function() {
        toggleCriterion();
    });
    
    toggleCriterion();
</script>