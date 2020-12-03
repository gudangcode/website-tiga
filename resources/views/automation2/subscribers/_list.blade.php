@if ($total > 0)
	<table class="table table-box pml-table table-sub"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($subscribers as $key => $subscriber)
			<tr>
				<td width="1%">
					<div class="text-nowrap">
						<div class="checkbox inline">
							<label>
								<input type="checkbox" class="node styled"
									name="ids[]"
									value="{{ $subscriber->uid }}"
								/>
							</label>
						</div>
					</div>
				</td>
				<td>
					<div class="d-flex">
						<div class="subscriber-avatar">
							<a href="{{ action('SubscriberController@edit', ['list_uid' => $list->uid ,'uid' => $subscriber->uid]) }}">
								<img src="{{ (isSiteDemo() ? 'https://i.pravatar.cc/300?v=' . $key : action('SubscriberController@avatar',  $subscriber->uid)) }}" />
							</a>
						</div>
						<div class="no-margin text-bold">
							<a class="kq_search" href="{{ action('SubscriberController@edit', ['list_uid' => $list->uid ,'uid' => $subscriber->uid]) }}">
								{{ $subscriber->email }}
							</a>
							<br />
							<span class="label label-flat bg-{{ $subscriber->status }}">{{ trans('messages.' . $subscriber->status) }}</span>
							<span class="label label-flat bg-{{ $subscriber->verify_result }}">{{ trans('messages.email_verification_result_' . $subscriber->verify_result) }}</span>
						</div>
					</div>
				</td>

				@foreach ($fields as $field)
					<?php $value = $subscriber->getValueByField($field); ?>
					<td>
						<span class="no-margin stat-num kq_search">{{ empty($value) ? "--" : $value }}</span>
						<br>
						<span class="text-muted2">{{ $field->label }}</span>
					</td>
				@endforeach

				@if (in_array("created_at", explode(",", request()->columns)))
					<td>
						<span class="no-margin stat-num">{{ Tool::formatDateTime($subscriber->created_at) }}</span>
						<br>
						<span class="text-muted2">{{ trans('messages.created_at') }}</span>
					</td>
				@endif

				@if (in_array("updated_at", explode(",", request()->columns)))
					<td>
						<span class="no-margin stat-num">{{ Tool::formatDateTime($subscriber->updated_at) }}</span>
						<br>
						<span class="text-muted2">{{ trans('messages.updated_at') }}</span>
					</td>
				@endif

				<td class="text-right text-nowrap">
					@if (\Gate::allows('update', $subscriber))
						<a href="{{ action('Automation2Controller@subscribersShow', [
							'uid' => $automation->uid,
							'subscriber_uid' => $subscriber->uid
						]) }}" type="button" class="btn bg-grey btn-icon">
							{{ trans('messages.automation.subscriber.view') }}
						</a>
					@endif
					<div class="btn-group">
						<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							@if (\Gate::allows('update', $automation))
								<li><a data-method="POST" class="ajax_link" href="{{ action('Automation2Controller@subscribersRemove', [
									'uid' => $automation->uid,
									'subscriber_uid' => $subscriber->uid
								]) }}">
									<i class="icon-exit"></i> {{ trans('messages.automation.subscriber.remove') }}
								</a></li>
							@endif
							@if (\Gate::allows('update', $subscriber))
								<li><a data-method="POST" class="ajax_link" href="{{ action('Automation2Controller@subscribersRestart', [
									'uid' => $automation->uid,
									'subscriber_uid' => $subscriber->uid
								]) }}">
									<i class="icon-sync"></i> {{ trans('messages.automation.subscriber.restart') }}
								</a></li>
							@endif
						</ul>
					</div>
				</td>

			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $subscribers])
	{{ $subscribers->links() }}
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<i class="icon-users4"></i>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<i class="icon-users4"></i>
		<span class="line-1">
			{{ trans('messages.subscriber_empty_line_1') }}
		</span>
	</div>
@endif
