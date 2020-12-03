    <div class="iframe-modal-header">
        <div class="title">{{ trans('messages.subscription.change_plan') }}</div>
        <div class="close" onclick="parent.GlobalIframeModal.hide()"><i class="lnr lnr-cross"></i></div>
    </div>
    
    <div class="iframe-modal-body">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <div class="sub-section">
                
                    <h2>{{ trans('messages.subscription.select_a_plan') }}</h2>
                    <p>{{ trans('messages.subscription.change_plan.select_below') }}</p>
                
                    @if (empty($plans))
                        <div class="row">
                            <div class="col-md-6">
                                @include('elements._notification', [
                                    'level' => 'danger',
                                    'message' => trans('messages.plan.no_available_plan')
                                ])
                            </div>
                        </div>
                    @else
                        <div class="price-box price-selectable">
                            <div class="price-table">
                        
                                @foreach ($plans as $plan)
                                    <div class="price-line {{ $subscription->plan->uid == $plan->uid ? 'current' : '' }}">
                                        <div class="price-header">
                                            <lable class="plan-title">{{ $plan->name }}</lable>
                                            <p>{{ $plan->description }}</p>
                                        </div>
                                        
                                        <div class="price-item text-center">
                                            <div>{{ trans('messages.plan.starting_at') }}</div>
                                            <div class="plan-price">
                                                {{ \Acelle\Library\Tool::format_price($plan->price, $plan->currency->format) }}
                                            </div>
                                            <div>{{ $plan->displayFrequencyTime() }}</div>
                                            
                                            @if ($subscription->plan->uid == $plan->uid)
                                                <a
                                                    href="javascript:;"
                                                    class="btn btn-mc_default mt-30" disabled>
                                                        {{ trans('messages.plan.current_subscribed') }}
                                                </a>
                                            @else
                                                <a
                                                    href="{{ $gateway->getChangePlanUrl($subscription, $plan->uid, action('AccountSubscriptionController@index')) }}"
                                                    class="btn btn-mc_primary btn-mc_mk mt-30">
                                                        {{ trans('messages.plan.select') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            
                            </div>
                        </div>
                    @endif
        
                </div>
            </div>
            <div class="col-md-1"></div>
        </div>
    </div>