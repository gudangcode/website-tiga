@if ($senders->count() > 0)
	<table class="table table-box pml-table table-log mt-10"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		<tr>
			<th>
				<div class="checkbox inline check_all_list">
					<label>
						<input type="checkbox" class="styled check_all">
					</label>
				</div>
			</th>
			<th>{{ trans('messages.name') }}</th>
			<th>{{ trans('messages.email') }}</th>
			<th>{{ trans('messages.created_at') }}</th>
			<th>{{ trans('messages.status') }}</th>
			<th class="text-right">{{ trans('messages.action') }}</th>
		</tr>
		@foreach ($senders as $key => $sender)
			<tr>
				<td width="1%">
					<div class="checkbox inline">
						<label>
							<input type="checkbox" class="node styled"
								name="ids[]"
								value="{{ $sender->uid }}"
							/>
						</label>
					</div>
				</td>
				<td>
					<a
						href="{{ action('SenderController@show', $sender->uid) }}"
					>
						<span class="no-margin kq_search">{{ $sender->name }}</span>
					</a>
					<span class="text-muted second-line-mobile">{{ trans('messages.name') }}</span>
				</td>
				<td>
					<span class="no-margin kq_search">{{ $sender->email }}</span>
					<span class="text-muted second-line-mobile">{{ trans('messages.email') }}</span>
				</td>
				<td>
					<span class="no-margin kq_search">{{ Tool::formatDateTime($sender->created_at) }}</span>
					<span class="text-muted second-line-mobile">{{ trans('messages.created_at') }}</span>
				</td>
				<td>
					<span class="label label-primary bg-{{ $sender->status }}">
						{{ trans('messages.sender.status.' . $sender->status) }}
					</span>
					<span class="text-muted second-line-mobile">{{ trans('messages.sender.status.' . $sender->status) }}</span>
				</td>
				<td class="text-right">
					<div class="btn-group">
						@if (Auth::user()->can('read', $sender))
							<a href="{{ action('SenderController@show', $sender->uid) }}"
								type="button" class="btn btn-default">
									{{ trans('messages.sender.view') }}
							</a>
						@endif
						<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
							@if (Auth::user()->customer->can('delete', $sender))
								<li>
									<a delete-confirm="{{ trans('messages.sender.delete.confirm') }}"
										href="{{ action('SenderController@delete', ["uids" => $sender->uid]) }}"
									>
										<i class="icon-trash"></i> {{ trans('messages.delete') }}
									</a>
								</li>
							@endif
						</ul>
					</div>
				</td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $senders])
	{{ $senders->links() }}
@elseif (!empty(request()->keyword) || !empty(request()->filters["campaign_uid"]))
	<div class="empty-list">
		<i class="icon icon-user-check"></i>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<i class="icon icon-user-check"></i>
		<span class="line-1">
			{{ trans('messages.senders.empty.message') }}
		</span>
	</div>
@endif
