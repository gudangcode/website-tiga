<ul class="key-value-list mt-2">
    {{-- <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-success">check</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.recipients_count', ['count' => $automation->mailList->subscribersCount()]) }}
            </label>
            <div class="value">
                {{ $automation->mailList->name }}
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailSetup', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li> --}}
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">textsms</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.subject') }}
            </label>
            <div class="value">
                {{ $email->subject }}
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailSetup', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">my_location</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.from') }}
            </label>
            <div class="value">
                {{ $email->from }}
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailSetup', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">reply</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.reply_to') }}
            </label>
            <div class="value">
                @if($email->reply_to)
                    {{ $email->reply_to }}
                @else
                    <span class="text-warning small">
                        <i class="material-icons-outlined">warning</i>
                        {{ trans('messages.email.no_reply_to') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailSetup', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            @if($email->content)
                <i class="material-icons-outlined text-muted">vertical_split</i>
            @else
                <i class="material-icons-outlined text-muted">vertical_split</i>
            @endif
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.summary.content') }}
            </label>
            <div class="value">
                @if($email->content)
                    {{ trans('messages.automation.email.content.last_edit', [
                        'time' => $email->updated_at->diffForHumans(),
                    ]) }}
                @else
                    <span class="text-danger small">
                        <i class="material-icons-outlined">error_outline</i>
                        {{ trans('messages.automation.email.no_content') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailTemplate', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.summary.content.update') }}
            </a>
        </div>
    </li>
    <li class="d-flex align-items-center">
        <div class="list-media mr-4">
            <i class="material-icons-outlined text-muted">track_changes</i>
        </div>
        <div class="values mr-auto">
            <label>
                {{ trans('messages.automation.email.tracking') }}
            </label>
            <div class="value">
                @if ($email->track_open)
                    {{ trans('messages.automation.email.opens') }}@if ($email->track_click),@endif
                @endif
                @if ($email->track_click)
                    {{ trans('messages.automation.email.clicks') }}
                @endif
            </div>
        </div>
        <div class="list-action">
            <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@emailSetup', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')" class="btn btn-outline-secondary btn-sm">
                {{ trans('messages.automation.email.setup') }}
            </a>
        </div>
    </li>
</ul> 