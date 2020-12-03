<div class="row">
    <div class="col-md-6">
        <input type="hidden" name="options[type]" value="event" />
        <input type="hidden" name="options[field]" value="date_of_birth" />
        
        @include('helpers.form_control', [
            'type' => 'select',
            'class' => '',
            'label' => trans('messages.automation.before'),
            'name' => 'options[before]',
            'value' => $trigger->getOption('before'),
            'help_class' => 'trigger',
            'options' => $automation->getDelayBeforeOptions(),
            'rules' => $rules,
        ])

        @include('helpers.form_control', [
            'type' => 'time2',
            'name' => 'options[at]',
            'label' => trans('messages.automation.at'),
            'value' => ($trigger->getOption('at') ? $trigger->getOption('at') : '10:00 AM'),
            'rules' => $rules,
            'help_class' => 'trigger'
        ])
    </div>
</div>
<p>{{ trans('messages.automation.choose_birthday_field') }}</p>
<div class="row">
    <div class="col-md-6">
        @include('helpers.form_control', [
            'type' => 'select',
            'class' => '',
            'include_blank' => trans('messages.automation.choose_list_field'),
            'name' => 'options[field]',
            'value' => $trigger->getOption('field'),
            'help_class' => 'trigger',
            'options' => $automation->getListFieldOptions(),
            'rules' => $rules,
        ])
    </div>
</div>