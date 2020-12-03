@include('automation2._info')

@include('automation2._tabs', ['tab' => 'settings'])
    
<h5 class="mb-20 mt-3"h5>
    {{ trans('messages.automation.action.send-an-email') }}
</h5>
<p class="mb-10">
    {{ trans('messages.automation.action.send-an-email.intro') }}
</p>

<form action="{{ action('Automation2Controller@emailSetup', $automation->uid) }}" method="POST" class="form-validate-jqueryz">
    {{ csrf_field() }}
    
    @include('automation2.email._summary')
    
    <div class="trigger-action mt-4">    
        <span class="btn btn-secondary email-settings-change mr-1"
        >
            {{ trans('messages.automation.email.settings') }}
        </span>
        <a
                        onclick="popupwindow('{{ action('Automation2Controller@templatePreview', [
                            'uid' => $automation->uid,
                            'email_uid' => $email->uid,
                        ]) }}', '{{ $automation->name }}', 800, 800)"
                        href="javascript:;"
                        class="btn btn-default"
                    >
                        {{ trans('messages.automation.template.preview') }}
                    </a>
    </div>
    
<form>

<h6 class="mb-2 mt-5 text-danger">
    {{ trans('messages.automation.dangerous_zone') }}
</h6>
<p class="">
    {{ trans('messages.automation.action.delete.confirm') }}        
</p>
<div class="mt-3">
    <a href="{{ action('Automation2Controller@emailDelete', [
        'uid' => $automation->uid,
        'email_uid' => $email->uid,
    ]) }}" data-confirm="{{ trans('messages.automation.email.delete.confirm') }}" class="btn btn-danger email-action-delete">
        <i class='lnr lnr-trash mr-2'></i> {{ trans('messages.automation.delete_this_action') }}
    </a>
</div>

<script>
    // Click on exist action
    $('.email-settings-change').click(function(e) {
        e.preventDefault();
        
        var url = '{{ action('Automation2Controller@emailTemplate', ['uid' => $automation->uid, 'email_uid' => $email->uid]) }}';
        
        popup.load(url);
    });
    
    $('.email-action-delete').click(function(e) {
        e.preventDefault();
        
        var confirm = $(this).attr('data-confirm');
        var url = $(this).attr('href');

        var dialog = new Dialog('confirm', {
            message: confirm,
            ok: function(dialog) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    statusCode: {
                        // validate error
                        400: function (res) {
                            response = JSON.parse(res.responseText);
                            // notify
                            notify('notice', '{{ trans('messages.notify.warning') }}', response.message);
                        }
                    },
                    success: function (response) {
                        // remove current node
                        tree.getSelected().remove();
                        
                        // save tree
                        saveData(function() {                            
                            // notify
                            notify('success', '{{ trans('messages.notify.success') }}', response.message);
                            
                            // load default sidebar
                            sidebar.load('{{ action('Automation2Controller@settings', $automation->uid) }}');
                        });
                    }
                });                        
            },
        });
    });
</script>