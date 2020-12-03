@if ($templates->count() >0)
    <div class="template-list-items mt-3 clearfix">
        @foreach ($templates as $key => $template)
            <a href="javascript:;" class="d-block template-list-item template-choose" data-template="{{ $template->uid }}">
                <img href="javascript:;" src="{{ $template->getThumbUrl() }}?v={{ rand(0,10) }}" />
                <label class="mb-20 text-center d-block mt-3">{{ $template->name }}</label>
            </a>
        @endforeach
    </div>
        
    @include('helpers._pagination')
@else
    <div class="empty-list">
        <i class="material-icons-outlined">featured_play_list</i>
        <span class="line-1">
            {{ trans('messages.automation.email.empty_template_list') }}
        </span>
    </div>
@endif

<script>
    var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            
    }});

    $('.template-choose').click(function() {
        var url = '{{ action('Automation2Controller@templateTheme', [
            'uid' => $automation->uid,
            'email_uid' => $email->uid,
        ]) }}';
        var template_uid = $(this).attr('data-template');
        
        // loading popup
        popup.loading();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                template_uid: template_uid
            }
        }).always(function(response) {
            popup.load('{{ action('Automation2Controller@emailTemplate', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}');

            builderSelectPopup.load('{{ action('Automation2Controller@templateBuilderSelect', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}');

            // notify
            notify('success', '{{ trans('messages.notify.success') }}', response.message);
        });
    });
</script>