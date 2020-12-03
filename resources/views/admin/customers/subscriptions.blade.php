@extends('layouts.backend')

@section('title', $customer->displayName())

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
		
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

			<div class="page-title">
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
					<li><a href="{{ action("Admin\CustomerController@index") }}">{{ trans('messages.customers') }}</a></li>
					<li class="active">{{ trans('messages.update') }}</li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-profile"></i> {{ $customer->displayName() }}</span>
				</h1>
			</div>

@endsection

@section('content')
	@include('admin.customers._tabs')

    <form class="listing-form"
        sort-url="{{ action('Admin\SubscriptionController@sort') }}"
        data-url="{{ action('Admin\SubscriptionController@listing') }}"
        per-page="15"
    >
        <input type="hidden" name="customer_uid" value="{{ $customer->uid }}" />
        <div class="row top-list-controls">
            <div class="col-md-11">
                <div class="filter-box">
                    <span class="filter-group">
                            <!--<span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>-->
                            <select class="select" name="sort-order">
                                <option value="subscriptions.updated_at">{{ trans('messages.updated_at') }}</option>
                                <option value="subscriptions.created_at">{{ trans('messages.created_at') }}</option>
                                <option value="subscriptions.ends_at">{{ trans('messages.ends_at') }}</option>
                            </select>
                            <button class="btn btn-xs sort-direction" rel="desc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                                <i class="icon-sort-amount-desc"></i>
                            </button>
                        </span>
                        <span class="mr-10 input-medium">
                            <select placeholder="{{ trans('messages.plan') }}"
                                class="select2-ajax"
                                name="plan_uid"
                                data-url="{{ action('Admin\PlanController@select2') }}">
                            </select>
                        </span>
                </div>
            </div>
        </div>

        <div class="pml-table-container">



        </div>
    </form>
@endsection
