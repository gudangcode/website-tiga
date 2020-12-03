@if ($trackingDomains->count() > 0)
    <table class="table table-box pml-table"
        current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
    >
        @foreach ($trackingDomains as $key => $trackingDomain)
            <tr>
                <td width="1%">
                    <div class="text-nowrap">
                        <div class="checkbox inline">
                            <label>
                                <input type="checkbox" class="node styled"
                                    custom-order="{{ $trackingDomain->custom_order }}"
                                    name="ids[]"
                                    value="{{ $trackingDomain->uid }}"
                                />
                            </label>
                        </div>
                    </div>
                </td>
                <td>
                    <h5 class="no-margin text-bold">
                        <a class="kq_search" href="{{ action('TrackingDomainController@show', $trackingDomain->uid) }}">{{ $trackingDomain->name }}</a>
                    </h5>
                    <span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($trackingDomain->created_at) }}</span>
                </td>
                <td>
                    <div class="single-stat-box pull-left">
                        <span class="no-margin stat-num">{{ strtoupper($trackingDomain->scheme) }}</span>
                        <br>
                        <span class="text-muted text-nowrap">{{ trans('messages.tracking_domain.scheme') }}</span>
                    </div>
                </td>
                <td class="text-right">
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-{{ $trackingDomain->status }}">
                            {{ trans('messages.tracking_domain.status.' . $trackingDomain->status) }}</span>
                    </span>
                    @if (Auth::user()->customer->can('read', $trackingDomain))
                        <a href="{{ action('TrackingDomainController@show', $trackingDomain->uid) }}" data-popup="tooltip"
                            title="{{ trans('messages.tracking_domain.view') }}" type="button" class="btn bg-grey btn-icon">
                                {{ trans('messages.tracking_domain.view') }}
                        </a>
                    @endif
                    @if (Auth::user()->customer->can('delete', $trackingDomain))
                        <div class="btn-group">
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a delete-confirm="{{ trans('messages.delete_tracking_domains_confirm') }}" href="{{ action('TrackingDomainController@delete', ["uids" => $trackingDomain->uid]) }}">
                                        <i class="icon-trash"></i> {{ trans('messages.delete') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    @include('elements/_per_page_select')
    {{ $trackingDomains->links() }}
@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-earth"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-earth"></i>
        <span class="line-1">
            {{ trans('messages.tracking_domain_empty_line_1') }}
        </span>
    </div>
@endif
