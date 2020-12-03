@extends('layouts.frontend')

@section('title', trans('messages.verified_senders'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')
    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SenderController@index") }}">{{ trans('messages.verified_senders') }}</a></li>
            <li><a href="{{ action("SenderController@index") }}">{{ trans('messages.email_addresses') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.verified_senders') }}</span>
        </h1>    
    </div>
@endsection

@section('content')
    
    @include('senders._menu')
    
    <h2 class="text-semibold"><i class="icon-list2 mr-10"></i> {{ trans('messages.email_addresses') }}</h2>
    
    <p>{{ trans('messages.sender.wording') }}</p>

    <form class="listing-form"
        data-url="{{ action('SenderController@listing') }}"
        per-page="{{ Acelle\Model\Sender::$itemsPerPage }}"
    >

        <div class="row top-list-controls">
            <div class="col-md-9">
                @if ($senders->count() >= 0)
                    <div class="filter-box">
                        @include('helpers.select_tool')
                        <div class="btn-group list_actions hide">
                            <button type="button" class="btn btn-xs btn-grey-600 dropdown-toggle" data-toggle="dropdown">
                                {{ trans('messages.actions') }} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a delete-confirm="{{ trans('messages.remove_blacklist_confirm') }}" href="{{ action('SenderController@delete') }}"><i class="icon-trash"></i> {{ trans('messages.delete') }}</a></li>
                            </ul>
                        </div>
                        <span class="filter-group">
                            <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                            <select class="select" name="sort-order">
                                <option value="senders.created_at">{{ trans('messages.created_at') }}</option>
                                <option value="senders.email">{{ trans('messages.email') }}</option>
                                <option value="senders.email">{{ trans('messages.name') }}</option>
                            </select>
                            <button class="btn btn-xs sort-direction" rel="desc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                                <i class="icon-sort-amount-desc"></i>
                            </button>
                        </span>
                        <span class="text-nowrap">
                            <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                            <i class="icon-search4 keyword_search_button"></i>
                        </span>
                    </div>
                @endif
            </div>
            <div class="col-md-3">
                @if (Auth::user()->can('create', new Acelle\Model\Sender()))
                    <div class="text-right">
                        <a href="{{ action('SenderController@create') }}" type="button" class="btn bg-info-800">
                            <i class="icon-download4"></i> {{ trans('messages.sender.create') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="pml-table-container">
        </div>
    </form>
@endsection
