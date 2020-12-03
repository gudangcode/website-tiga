@extends('layouts.frontend')

@section('title', trans('messages.create_sending_domain'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SenderController@index") }}">{{ trans('messages.verified_senders') }}</a></li>
            <li><a href="{{ action("SendingDomainController@index") }}">{{ trans('messages.domains') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.verified_senders') }}</span>
        </h1>         
    </div>

@endsection

@section('content')
    
    @include('senders._menu')
    
    <h2>
        <span class="text-semibold"><i class="icon-plus-circle2"></i> {{ trans('messages.create_sending_domain') }}</span>
    </h1>

    <div class="row">
        <div class="col-sm-12 col-md-10 col-lg-10">
            <p>{!! trans('messages.sending_domain.wording') !!}</p>
        </div>
    </div>

    <form action="{{ action('SendingDomainController@store') }}" method="POST" class="form-validate-jqueryz">
        @include('sending_domains._form')
	</form>

@endsection
