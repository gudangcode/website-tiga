<ul class="nav nav-pills email-template-tabs">
    <li class="nav-item">
        <a href="javascript:;" class="nav-link layout" onclick="popup.load('{{ action('Automation2Controller@templateLayout', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')"
        >
            {{ trans('messages.automation.email.layouts') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="javascript:;" class="nav-link theme" onclick="popup.load('{{ action('Automation2Controller@templateTheme', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')"
        >
            {{ trans('messages.automation.email.themes') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="javascript:;" class="nav-link upload" onclick="popup.load('{{ action('Automation2Controller@templateUpload', [
                'uid' => $automation->uid,
                'email_uid' => $email->uid,
            ]) }}')"
        >
            {{ trans('messages.automation.email.upload') }}
        </a>
    </li>
</ul>
    
<script>
    @if (isset($tab))
        $('.email-template-tabs .nav-link.{{ $tab }}').addClass('active');
    @endif
    @if (isset($sub))
        $('.email-template-tabs .dropdown-item.{{ $sub }}').addClass('active');
    @endif
</script>