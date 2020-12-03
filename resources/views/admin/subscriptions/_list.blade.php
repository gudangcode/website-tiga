@if ($subscriptions->count() > 0)
	<table class="table table-box pml-table table-log"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($subscriptions as $key => $subscription)
			<tr>
				<td width="1%">
					@if ($subscription->isEnded())
						<i class="icon-dash subscription-status-icon"></i>
					@elseif ($subscription->isPending() || $subscription->isNew())
						<i class="lnr lnr-sync subscription-status-icon"></i>
					@else
						<i class="lnr lnr-checkmark-circle subscription-status-icon"></i>
					@endif
				</td>
				<td>
					<h5 class="no-margin text-bold">
						<span class="kq_search" href="#">
							{{ $subscription->plan->name }}
						</span>
					</h5>
					<div class="text-muted">{!! trans('messages.subscribed_by', [
						'name' => $subscription->user->displayName(),
						'customer_link' => action('Admin\CustomerController@edit', $subscription->user->uid)
					]) !!}</div>
				</td>
				<td width="15%">
                    <h5 class="no-margin">
                        <span class="kq_search">{{ Acelle\Library\Tool::dateTime($subscription->created_at)->format('M d, Y') }}</span>
                    </h5>
                    <span class="text-muted2">{{ trans('messages.subscribed_on') }}</span>
                </td>
				<td width="15%">
					@if ($subscription->isEnded())
						<h5 class="no-margin">
								<span class="kq_search">{{ Acelle\Library\Tool::formatDate($subscription->ends_at) }}</span>
							</h5>
						<span class="text-muted2">{{ trans('messages.subscription.subscription_ended_at') }}</span>
					@elseif ($subscription->cancelled())
						<h5 class="no-margin">
							@if ($subscription->current_period_ends_at)
								<span class="kq_search">{{ Acelle\Library\Tool::dateTime($subscription->current_period_ends_at)->diffForHumans() }}</span>
							@else
								<span class="kq_search">--</span>
							@endif
						</h5>
						<span class="text-muted2">{{ trans('messages.subscription.subscription_end') }}</span>
					@elseif ($subscription->isRecurring())
						<h5 class="no-margin">
							<span class="kq_search">
								@if ($subscription->current_period_ends_at)
									{{ Acelle\Library\Tool::dateTime($subscription->current_period_ends_at)->diffForHumans() }}
								@else
									--
								@endif									
							</span>
						</h5>
						<span class="text-muted2">{{ trans('messages.subscription.next_billing') }}</span>
					@endif
				</td>
				<td>
                    <span class="text-muted2 list-status pull-left">
						@if ($subscription->isActive() && $subscription->hasRenewPending())
							<span href="{{ action('Admin\SubscriptionController@invoices', $subscription->uid) }}"
								class="modal_link label bg-m-warning"
							>
								{{ trans('messages.subscription.status.renew_pending') }}
							</span>
						@elseif ($subscription->isActive() && $subscription->hasChangePlanPending())
							<span href="{{ action('Admin\SubscriptionController@invoices', $subscription->uid) }}"
								class="modal_link label bg-m-warning"
							>
								{{ trans('messages.subscription.status.change_plan_pending') }}
							</span>
						@else
							<span style="cursor:pointer" href="{{ action('Admin\SubscriptionController@invoices', $subscription->uid) }}"
								class="view_invoices label bg-{{ $subscription->status }}"
							>{{ trans('messages.subscription.status.' . $subscription->status) }}</span>
						@endif
					</span>
                </td>
				<td class="text-right">
					@if (\Auth::user()->admin->can('cancel', $subscription))
						<a data-method="POST" link-confirm="{{ trans('messages.subscription.cancel.confirm') }}"
						  href="{{ action('Admin\SubscriptionController@cancel', $subscription->uid) }}" class="btn bg-grey-600">
							{{ trans('messages.subscription.cancel') }}
						</a>
					@endif
					@if (\Auth::user()->admin->can('resume', $subscription))
						<a data-method="POST" link-confirm="{{ trans('messages.subscription.resume.confirm') }}"
						  href="{{ action('Admin\SubscriptionController@resume', $subscription->uid) }}" class="btn bg-grey-600">
							{{ trans('messages.subscription.resume') }}
						</a>
					@endif
					@if(
						\Auth::user()->admin->can('cancelNow', $subscription) ||
						\Auth::user()->admin->can('changePlan', $subscription) ||
						\Auth::user()->admin->can('invoices', $subscription) ||
						\Auth::user()->admin->can('setActive', $subscription) ||
						\Auth::user()->admin->can('approvePending', $subscription) ||
						\Auth::user()->admin->can('rejectPending', $subscription)
					)
						<div class="btn-group">
							<button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret ml-0"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">								
								@if (\Auth::user()->admin->can('setActive', $subscription))
									<li>
										<a data-method="POST" link-confirm="{{ trans('messages.subscription.set_active.confirm') }}"
										  href="{{ action('Admin\SubscriptionController@setActive', $subscription->uid) }}">
											{{ trans('messages.subscription.set_active') }}
										</a>
									</li>
								@endif
								@if (\Auth::user()->admin->can('approvePending', $subscription))
									<li>
										<a data-method="POST" link-confirm="{{ trans('messages.subscription.set_active.confirm') }}"
										  href="{{ action('Admin\SubscriptionController@approvePending', $subscription->uid) }}">
											{{ trans('messages.subscription.approve_pending') }}
										</a>
									</li>
								@endif
								@if (\Auth::user()->admin->can('rejectPending', $subscription))
									<li>
										<a data-method="POST"
											class="rejectPending"
											{{-- link-confirm="{{ trans('messages.subscription.reject_pending.confirm') }}" --}}
										  	href="{{ action('Admin\SubscriptionController@rejectPending', $subscription->uid) }}"
										>
											{{ trans('messages.subscription.reject_pending') }}
										</a>
									</li>
								@endif
								@if (\Auth::user()->admin->can('invoices', $subscription))
									<li>
										<a class="view_invoices"
										  href="{{ action('Admin\SubscriptionController@invoices', $subscription->uid) }}">
											{{ trans('messages.subscription.logs') }}
										</a>
									</li>
								@endif
								@if (\Auth::user()->admin->can('cancelNow', $subscription))
									<li>
										<a data-method="POST" link-confirm="{{ trans('messages.subscription.cancel_now.confirm') }}"
										  href="{{ action('Admin\SubscriptionController@cancelNow', $subscription->uid) }}">
											{{ trans('messages.subscription.cancel_now') }}
										</a>
									</li>
								@endif
								@if (\Auth::user()->admin->can('delete', $subscription))
									<li>
										<a data-method="DELETE" link-confirm="{{ trans('messages.subscription.delete.confirm') }}"
										  href="{{ action('Admin\SubscriptionController@delete', ['id' => $subscription->uid]) }}">
											{{ trans('messages.subscription.delete') }}
										</a>
									</li>
								@endif
							</ul>
						</div>
					@endif
                </td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $subscriptions])
	{{ $subscriptions->links() }}

	<script>        
        $(function() {
            $('.rejectPending').click(function(e) {
                e.preventDefault();

                var src = $(this).attr('href');
                rejectPendingSub.load(src);
            });
        });

		var invoices = new Popup();
		$('.view_invoices').click(function(e) {
			e.preventDefault();
			invoices.load($(this).attr('href'));
		});
    </script>

@elseif (!empty(request()->keyword) || !empty(request()->filters))
	<div class="empty-list">
		<i class="icon-quill4"></i>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<i class="icon-quill4"></i>
		<span class="line-1">
			{{ trans('messages.subscription_empty_line_1_admin') }}
		</span>
	</div>
@endif
