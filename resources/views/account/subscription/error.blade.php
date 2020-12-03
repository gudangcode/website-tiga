@extends('layouts.frontend')

@section('title', trans('messages.subscription'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="active">{{ trans('messages.subscription') }}</li>
        </ul>
        <h1>
            <span class="text-semibold">{{ __('messages.something_went_wrong') }}!</span>
        </h1>
    </div>

@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6">
            @include('elements._notification', [
                'level' => 'danger',
                'message' => $message,
            ])
        </div>
    </div>

@endsection
