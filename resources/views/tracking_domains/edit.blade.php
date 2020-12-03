@extends('layouts.frontend')

@section('title', $domain->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("TrackingDomainController@index") }}">{{ trans('messages.tracking_domains') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ $domain->name }}</span>
        </h1>       
    </div>

@endsection

@section('content')
    <h2>
        <span class="text-semibold"><i class="icon-pencil"></i> {{ $domain->name }}</span>
    </h2>

    <div class="row">
        <div class="col-sm-12 col-md-10 col-lg-10">
            <p>{!! trans('messages.tracking_domain.wording') !!}</p>
        </div>
    </div>

    <form enctype="multipart/form-data" action="{{ action('TrackingDomainController@update', $domain->uid) }}" method="POST" class="form-validate-jqueryz">
        <input type="hidden" name="_method" value="PATCH">
        @include('tracking_domains._form')
    </form>

@endsection
