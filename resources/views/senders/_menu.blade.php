<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs nav-tabs-top page-second-nav">
            @if (Auth::user()->customer->subscription->plan->useOwnSendingServer())
                <li rel0="SenderController">
                    <a href="{{ action('SenderController@index') }}" class="level-1">
                    <i class="icon-envelop2 mr-10"></i> {{ trans('messages.email_addresses') }}
                    </a>
                </li>
            @elseif ( Auth::user()->customer->subscription->plan->primarySendingServer()->allowVerifyingOwnEmails() || Auth::user()->customer->subscription->plan->primarySendingServer()->allowVerifyingOwnEmailsRemotely() ) {
                <li rel0="SenderController">
                    <a href="{{ action('SenderController@index') }}" class="level-1">
                    <i class="icon-envelop2 mr-10"></i> {{ trans('messages.email_addresses') }}
                    </a>
                </li>
            @endif

            @if (Auth::user()->customer->subscription->plan->useOwnSendingServer())
                <li rel0="SendingDomainController">
                    <a href="{{ action('SendingDomainController@index') }}" class="level-1">
                    <i class="icon-earth mr-10"></i> {{ trans('messages.domains') }}
                    </a>
                </li>
            @elseif ( Auth::user()->customer->subscription->plan->useOwnSendingServer() || Auth::user()->customer->subscription->plan->primarySendingServer()->allowVerifyingOwnDomains() || Auth::user()->customer->subscription->plan->primarySendingServer()->allowVerifyingOwnDomainsRemotely() ) {
                <li rel0="SendingDomainController">
                    <a href="{{ action('SendingDomainController@index') }}" class="level-1">
                    <i class="icon-earth mr-10"></i> {{ trans('messages.domains') }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
