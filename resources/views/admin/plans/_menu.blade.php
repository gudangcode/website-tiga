<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs nav-tabs-top page-second-nav mc-nav-tabs">
            <li rel0="PlanController/general">
                <a href="{{ action('Admin\PlanController@general', $plan->uid) }}" class="level-1">
                    {{ trans('messages.plan.general') }}
                </a>
            </li>
            <li  class="dropdown"
                rel0="PlanController/quota"
                rel1="PlanController/security"
                rel2="PlanController/emailFooter"
            >
                <a href="" class="level-1" data-toggle="dropdown">
                    {{ trans('messages.plan.settings') }}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li rel0="PlanController/quota">
                        <a href="{{ action('Admin\PlanController@quota', $plan->uid) }}">
                            {{ trans('messages.plan.quota') }}
                        </a>
                    </li>
                    <li rel0="PlanController/security">
                        <a href="{{ action('Admin\PlanController@security', $plan->uid) }}">
                            {{ trans('messages.plan.security') }}
                        </a>
                    </li>
                    <li rel0="PlanController/emailFooter">
                        <a href="{{ action('Admin\PlanController@emailFooter', $plan->uid) }}">
                            {{ trans('messages.plan.email_footer') }}
                        </a>
                    </li>
                </ul>
            </li>
            <!--<li  class="dropdown"
                rel0="PlanController/payment"
                rel1="PlanController/billingHistory"
            >
                <a href="" class="level-1" data-toggle="dropdown">
                    {{ trans('messages.plan.billing') }}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li rel0="PlanController/payment">
                        <a href="{{ action('Admin\PlanController@payment', $plan->uid) }}">
                            {{ trans('messages.plan.payment') }}
                        </a>
                    </li>
                    <li rel0="PlanController/billingHistory">
                        <a href="{{ action('Admin\PlanController@billingHistory', $plan->uid) }}">
                            {{ trans('messages.plan.billing_history') }}
                        </a>
                    </li>
                </ul>
            </li>-->
            <li rel0="PlanController/sendingServer" rel1="PlanController/sendingServers">                
                @if ($plan->useSystemSendingServer() && !$plan->hasPrimarySendingServer())
                    <a href="{{ action('Admin\PlanController@sendingServer', $plan->uid) }}" class="level-1 xtooltip"
                         title="{{ trans('messages.plans.send_via.empty') }}"
                    >
                        {{ trans('messages.plan.sending_server') }}
                        <i class="material-icons-outlined tabs-warning-icon text-danger">info</i>
                    </a>
                @else
                    <a href="{{ action('Admin\PlanController@sendingServer', $plan->uid) }}" class="level-1">
                        {{ trans('messages.plan.sending_server') }}
                    </a>
                @endif
                
            </li>
            <li rel0="PlanController/emailVerification">
                <a href="{{ action('Admin\PlanController@emailVerification', $plan->uid) }}" class="level-1">
                    {{ trans('messages.plan.email_verification') }}
                </a>
            </li>
        </ul>
    </div>
</div>
