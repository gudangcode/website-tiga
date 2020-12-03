<form enctype="multipart/form-data" action="{{ action('Admin\PlanController@addSendingServer', $plan->uid) }}" method="POST" class="form-validate-jquery">
    {{ csrf_field() }}
    <div class="modal-header">
      <h5 class="modal-title">{{ trans('messages.plan.sending_servers.add') }}</h5>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    @if ($noSendingServer)
        <div class="modal-body">
          @include('helpers.form_control', [
              'type' => 'select_ajax',
              'name' => 'sending_server_uid',
              'class' => 'hook required',
              'label' => trans('messages.plan.sending_servers.select'),
              'selected' => [
                'value' => '',
                'text' => '',
              ],
              'help_class' => 'subscription',
              'rules' => [],
              'url' => action('Admin\SendingServerController@select2', ['plan_uid' => $plan->uid]),
              'placeholder' => trans('messages.plan.sending_servers.choose')
          ])
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-mc_primary">{{ trans('messages.plan.sending_servers.add.ok') }}</button>
          <button type="button" class="btn btn-mc_inline" data-dismiss="modal">{{ trans('messages.close') }}</button>
        </div>
    @else
        <div class="modal-body">
            @include('elements._notification', [
                'level' => 'warning',
                'message' => trans('messages.plan.sending_servers.add_empty_warning', ['link' => action('Admin\SendingServerController@index')])
            ])
        </div>
    @endif
<form>
