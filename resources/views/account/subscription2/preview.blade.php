@if (isset($plan))
    <div class="row">
        <div class="col-md-6 plan-left">
            @include('plans._details', ['plan' => $plan])
        </div>
    </div>

    @if (false && $plan->isFree())
        <div class="text-left">
            <a link-method="POST" href="{{ action('AccountSubscriptionController@create', ['plan_uid' => $plan->uid]) }}" class="btn bg-teal">
            {{ trans('messages.get_started') }}
            <i class="icon-arrow-right7"></i></a>
        </div>
    @else
        <a link-method="POST"
            class="btn btn-mc_primary"
            href="{{ action('AccountSubscriptionController@create', ['plan_uid' => $plan->uid]) }}">
            {{ trans('messages.subscription.payment.next') }}
        </a>
    @endif
@endif
