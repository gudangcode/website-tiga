@if ($templates->count() > 0)
	<table class="table table-box pml-table"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($templates as $key => $template)
			<tr>
				<td width="1%">
					<div class="text-nowrap">
						<div class="checkbox inline">
							<label>
								<input type="checkbox" class="node styled"
									custom-order="{{ $template->custom_order }}"
									name="ids[]"
									value="{{ $template->uid }}"
								/>
							</label>
						</div>
						@if (request()->sort_order == 'custom_order' && empty(request()->keyword))
							<i data-action="move" class="icon icon-more2 list-drag-button"></i>
						@endif
					</div>
				</td>
				<td width="1%">
					<a href="#"  onclick="popupwindow('{{ action('Admin\TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)">
						<img class="template-thumb" width="100" height="120" src="{{ $template->getThumbUrl() }}?v={{ rand(0,10) }}" />
					</a>
				</td>
				<td>
					<h5 class="no-margin text-bold">
						<a class="kq_search" href="#" onclick="popupwindow('{{ action('Admin\TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)">
							{{ $template->name }}
						</a>
					</h5>
					<span class="text-muted">
						{!! is_object($template->admin) ? '<i class="icon-user-tie"></i>' . $template->admin->displayName() : '' !!}
						{!! is_object($template->customer) ? '<i class="icon-user"></i>' . $template->customer->displayName() : '' !!}
					</span>
					<br />
					<span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($template->created_at) }}</span>
				</td>
				<td>
					<div class="single-stat-box pull-left">
						<span class="no-margin stat-num">{{ trans('messages.template_type_' . $template->source) }}</span>
						<br>
						<span class="text-muted text-nowrap">{{ trans('messages.type') }}</span>
					</div>
				</td>
				<td class="text-right">
					@if (Auth::user()->admin->can('preview', $template))
						<a href="#preview" class="btn bg-teal-600 btn-icon" onclick="popupwindow('{{ action('Admin\TemplateController@preview', $template->uid) }}', '{{ $template->name }}', 800, 800)"><i class="icon-zoomin3"></i></a>
					@endif
					@if (Auth::user()->admin->can('update', $template))
						@if (in_array(Acelle\Model\Setting::get('builder'), ['both','pro']))
							<a href="{{ action('Admin\TemplateController@builderEdit', $template->uid) }}"
								type="button" class="btn btn-default btn-icon template-compose">
								{{ trans('messages.template.pro_builder') }}
							</a>
                        @endif
                        @if (in_array(Acelle\Model\Setting::get('builder'), ['both','classic']))
                            <a href="{{ action('Admin\TemplateController@edit', $template->uid) }}"
								type="button" class="btn bg-grey btn-icon template-compose-classic">
								{{ trans('messages.template.classic_builder') }}
							</a>
                        @endif							
					@endif
					@if (Auth::user()->admin->can('preview', $template) ||
						Auth::user()->admin->can('copy', $template) ||
						Auth::user()->admin->can('delete', $template))
						<div class="btn-group">
							<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
							<ul class="dropdown-menu dropdown-menu-right">
								@if (Auth::user()->admin->can('update', $template))
                                    <li>
                                        <a class="upload-thumb-button" href="{{ action('Admin\TemplateController@updateThumb', $template->uid) }}">
                                            <i class="icon-file-picture"></i> {{ trans("messages.template.upload_thumbnail") }}
                                        </a>
                                    </li>
                                @endif
								@if (Auth::user()->admin->can('copy', $template))
									<li>
										<a
											href="{{ action('Admin\TemplateController@copy', $template->uid) }}"
											type="button"
											class="modal_link"
											data-method="GET"
										>
											<i class="icon-copy4"></i> {{ trans("messages.template.copy") }}
										</a>
									</li>
								@endif
								@if (Auth::user()->admin->can('delete', $template))
									<li><a delete-confirm="{{ trans('messages.delete_templates_confirm') }}" href="{{ action('Admin\TemplateController@delete', ["uids" => $template->uid]) }}"><i class="icon-trash"></i> {{ trans("messages.delete") }}</a></li>
								@endif
							</ul>
						</div>
					@endif
				</td>
			</tr>
		@endforeach
	</table>
	@include('elements/_per_page_select', ["items" => $templates])
	{{ $templates->links() }}

	<script>
		var thumbPopup = new Popup();        
    
        $('.upload-thumb-button').click(function(e) {
            e.preventDefault();
            
            var url = $(this).attr('href');
            
            thumbPopup.load(url);
        });
    </script>
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<i class="icon-magazine"></i>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<i class="icon-magazine"></i>
		<span class="line-1">
			{{ trans('messages.template_empty_line_1') }}
		</span>
	</div>
@endif
