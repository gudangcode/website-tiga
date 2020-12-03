@extends('layouts.backend')

@section('title', trans('messages.notifications'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')	
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
			<li class="active">{{ trans('messages.notifications') }}</li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="material-icons">message</i>  {{ trans('messages.all_notifications') }}</span>
		</h1>
	</div>				
@endsection

@section('content')
	
	@include("admin.account._menu")	

	<form class="listing-form"
		data-url="{{ action('Admin\NotificationController@listing') }}"
		per-page="20"				
	>				
		<div class="row top-list-controls">
			<div class="col-md-10">			
				<div class="filter-box">
					<div class="btn-group list_actions hide">
						<button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
							{{ trans('messages.actions') }} <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<a delete-confirm="{{ trans('messages.notification.delete.confirm') }}" href="{{ action('Admin\NotificationController@delete') }}"><i class="icon-trash"></i> {{ trans('messages.delete') }}</a>
							</li>
						</ul>
					</div>
					<div class="checkbox inline check_all_list">
						<label>
							<input type="checkbox" class="styled check_all">
						</label>
					</div>
					<span class="filter-group">
						<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
						<select class="select" name="sort-order">
							<option value="created_at">{{ trans('messages.created_at') }}</option>
						</select>										
						<button class="btn btn-xs sort-direction" rel="desc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
							<i class="icon-sort-amount-desc"></i>
						</button>
					</span>
					<span class="filter-group ml-10">
						<span class="title text-semibold text-muted">{{ trans('messages.notification.level') }}</span>
						<select class="select" name="level">
							<option value="">{{ trans('messages.all') }}</option>
							<option value="{{ \Acelle\Model\Notification::LEVEL_INFO }}">{{ trans('messages.notification.level.' . \Acelle\Model\Notification::LEVEL_INFO) }}</option>
							<option value="{{ \Acelle\Model\Notification::LEVEL_WARNING }}">{{ trans('messages.notification.level.' . \Acelle\Model\Notification::LEVEL_WARNING) }}</option>
							<option value="{{ \Acelle\Model\Notification::LEVEL_ERROR }}">{{ trans('messages.notification.level.' . \Acelle\Model\Notification::LEVEL_ERROR) }}</option>
						</select>										
					</span>
					<span class="text-nowrap">
						<input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
						<i class="icon-search4 keyword_search_button"></i>
					</span>
				</div>
			</div>
		</div>
		
		<div class="pml-table-container">
			
			
			
		</div>
	</form>	
@endsection