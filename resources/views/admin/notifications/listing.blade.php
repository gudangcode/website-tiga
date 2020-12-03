                        @if ($notifications->count() > 0)
							<table class="table table-box pml-table"
                                current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
                            >
								@foreach ($notifications as $key => $notification)									
									<tr>
										<td width="1%">
											<div class="text-nowrap">
												<div class="checkbox inline">
													<label>
														<input type="checkbox" class="node styled"
															name="ids[]"
															value="{{ $notification->uid }}"
														/>
													</label>
												</div>
											</div>
										</td>
                                        <td width="1%">
											@if ($notification->level == \Acelle\Model\Notification::LEVEL_WARNING)
												<i class="lnr lnr-warning bg-warning admin-notification-media"></i>
											@elseif ( false &&$notification->level == \Acelle\Model\Notification::LEVEL_ERROR)
												<i class="lnr lnr-cross bg-danger admin-notification-media"></i>
											@else
												<i class="lnr lnr-menu bg-info admin-notification-media"></i>
											@endif
										</td>
										<td>
											<p class="mb-0">                                                
                                                {!! $notification->title !!}<br />
												<span class="text-muted2">{{ $notification->message }}</span>
                                            </p>
										</td>
										<td>
											<div class="pull-right">
												<div class="text-semibold">{{ $notification->created_at->diffForHumans() }}</div>
												<span class="text-muted2">{{ Acelle\Library\Tool::formatDateTime($notification->created_at) }}</span>
											</div>
										</td>
									</tr>
								@endforeach
							</table>
                            @include('elements/_per_page_select', ["notifications" => $notifications])
							{{ $notifications->links() }}                            
						@elseif (!empty(request()->keyword) || !empty(request()->filters["type"]))
							<div class="empty-list">
								<i class="material-icons">message</i> 
								<span class="line-1">
									{{ trans('messages.no_search_result') }}
								</span>
							</div>
						@else					
							<div class="empty-list">
								<i class="material-icons">message</i> 
								<span class="line-1">
									{{ trans('messages.no_action_notifications') }}
								</span>
							</div>
						@endif