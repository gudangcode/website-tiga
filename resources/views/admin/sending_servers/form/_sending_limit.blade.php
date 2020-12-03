@include('helpers.form_control', [
    'type' => 'select',
    'class' => '',
    'name' => 'options[sending_limit]',
    'label' => trans('messages.sending_servers.speed_limit'),
    'value' => $server->getOption('sending_limit'),
    'help_class' => 'sending_server',
    'rules' => [],
    'options' => $server->getSendingLimitSelectOptions(),
])

<input type="hidden" name="quota_value" value="{{ $quotaValue }}" />
<input type="hidden" name="quota_base" value="{{ $quotaBase }}" />
<input type="hidden" name="quota_unit" value="{{ $quotaUnit }}" />