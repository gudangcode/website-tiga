<div class="mb-20">
    <input type="hidden" name="options[type]" value="datetime" />

    @include('helpers.form_control', [
        'type' => 'date2',
        'class' => '',
        'label' => trans('messages.automation.date'),
        'name' => 'options[date]',
        'value' => ($trigger->getOption('date') ? $trigger->getOption('date') : toDateString(\Carbon\Carbon::now())),
        'help_class' => 'trigger',
        'rules' => $rules,
    ])
    
    @include('helpers.form_control', [
        'type' => 'time2',
        'name' => 'options[at]',
        'label' => trans('messages.automation.at'),
        'value' => ($trigger->getOption('at') ? $trigger->getOption('at') : toTimeString(\Carbon\Carbon::now())),
        'rules' => $rules,
        'help_class' => 'trigger'
    ])
</div>