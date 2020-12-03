@if ($plans->count() > 0)
    <table class="table table-box pml-table"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($plans as $key => $plan)
            <tr>
                <td width="1%">
                    <div class="text-nowrap d-flex align-items-canter">
                        @if (request()->sort_order == 'custom_order' && empty(request()->keyword))
                            <i data-action="move" class="icon icon-more2 list-drag-button"></i>
                        @endif
                        @if (!$plan->visible)
                            <a
                                title="{{ trans('messages.plan.show') }}"
                                data-method="POST"
                                href="{{ action('Admin\PlanController@visibleOn', $plan->uid) }}"
                                class="ajax_link plan-off {{ (\Auth::user()->can('visibleOn', $plan) ? 'xtooltip' : 'cant_show') }}"
                            >
                                <i class="material-icons plan-off-icon">toggle_off</i>
                            </a>
                        @else
                            <a
                                title="{{ trans('messages.plan.hide') }}"
                                link-confirm="{{ trans('messages.plans.hide.confirm') }}"
                                data-method="POST"
                                href="{{ action('Admin\PlanController@visibleOff', $plan->uid) }}"
                                class="ajax_link plan-on {{ (\Auth::user()->can('visibleOff', $plan) ? 'xtooltip' : 'disabled') }}"
                            >
                                <i class="material-icons plan-on-icon">toggle_on</i>
                            </a>
                        @endif                        
                    </div>
                </td>
                <td>
                    <h5 class="no-margin text-bold">
                        <span class="kq_search" href="{{ action('Admin\PlanController@general', $plan->uid) }}">
                            {{ $plan->name }}
                        </span>
                    </h5>
                    <p class="mb-0">{{ $plan->description }}</p>
                    @if (!$plan->useSystemSendingServer())
                        <span class="text-muted small" class="">{{ trans('messages.plan.sending_server.' . $plan->getOption('sending_server_option')) }}  &bull; {{ trans('messages.plans.subscriber_count', ['count' => $plan->customersCount()]) }}</span>
                    @elseif ($plan->hasPrimarySendingServer())
                        <span class="text-muted small">{{ trans('messages.plans.send_via.wording', ['server' => trans('messages.' . $plan->primarySendingServer()->type)]) }} &bull; {{ trans('messages.plans.subscriber_count', ['count' => $plan->customersCount()]) }}</span>
                    @endif

                    @foreach ($plan->errors() as $error)
                        <div class="text-muted"><span class="text-danger"><i class="fa fa-minus-circle"></i>
                            {{ trans('messages.plan.' . $error) }}
                        </span></div>
                    @endforeach
                </td>
                <td>
                    <h5 class="no-margin text-bold kq_search">
                        {{ \Acelle\Library\Tool::format_price($plan->price, $plan->currency->format) }}
                    </h5>
                    <span class="text-muted">{{ $plan->displayFrequencyTime() }}</span>
                </td>
                <td>
                    <h5 class="no-margin text-bold kq_search">
                        {{ $plan->displayTotalQuota() }}
                    </h5>
                    <span class="text-muted">{{ trans('messages.sending_total_quota_label') }}</span>
                </td>
                <td>
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-{{ $plan->status }}">{{ trans('messages.plan_status_' . $plan->status) }}</span>
                    </span>
                </td>
                <td class="text-right text-nowrap" width="5%">
                    @can('update', $plan)
                        <a href="{{ action('Admin\PlanController@general', $plan->uid) }}" type="button" class="btn bg-grey btn-icon"> <i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
                    @endcan
                    @if (\Auth::user()->can('delete', $plan) ||
                         \Auth::user()->can('copy', $plan)
                    )
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @can('copy', $plan)
                                    <li>
                                        <a data-name="{{'Copy of '}}{{$plan->name}}" data-uid="{{$plan->uid}}" title="{{ trans('messages.copy') }}" class="copy-plan-link">
                                            <i class="icon icon-copy4"></i> {{ trans('messages.copy') }}
                                        </a>
                                    </li>
                                  @endcan
                                @can('delete', $plan)
                                    <li>
                                        <a list-delete-confirm="{{ action('Admin\PlanController@deleteConfirm', ['uids' => $plan->uid]) }}" href="{{ action('Admin\PlanController@delete', ['uids' => $plan->uid]) }}" title="{{ trans('messages.delete') }}" class="">
                                            <i class="icon icon-trash"></i> {{ trans('messages.delete') }}
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select', ["items" => $plans])
    {{ $plans->links() }}

    <script>
        $('.cant_show').click(function(e) {
            e.preventDefault();

            var confirm = `{{ trans('messages.plan.cant_show') }}`;
            var dialog = new Dialog('alert', {
                message: confirm
            })
        });

        $('.enable-plan').click(function(e) {
            e.preventDefault();

            var confirm = `{{ trans('messages.plan.enable_and_visible.confirm') }}`;
            var href_yes = $(this).attr('href_yes');
            var href_no = $(this).attr('href_no');

            var dialog = new Dialog('yesno', {
                message: confirm,
                no: function(dialog) {
                    $.ajax({
                        url: href_no,
                        method: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                        },
                        statusCode: {
                            // validate error
                            400: function (res) {
                                alert('Something went wrong!');
                            }
                        },
                        success: function (response) {
                            // notify
                            notify('success', '{{ trans('messages.notify.success') }}', response.message);

                            // 
                            tableFilterAll();
                        }
                    });
                },
                yes: function(dialog) {                    
                    $.ajax({
                        url: href_yes,
                        method: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                        },
                        statusCode: {
                            // validate error
                            400: function (res) {
                                alert('Something went wrong!');
                            }
                        },
                        success: function (response) {
                            // notify
                            notify('success', '{{ trans('messages.notify.success') }}', response.message);

                            // 
                            tableFilterAll();
                        }
                    });
                },
            });
        });
    </script>
@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-clipboard2"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-clipboard2"></i>
        <span class="line-1">
            {{ trans('messages.plan_empty_line_1') }}
        </span>
    </div>
@endif
