@extends('layouts.frontend')

@section('title', $sender->name)
    
@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
    
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("SenderController@index") }}">{{ trans('messages.verified_senders') }}</a></li>
            <li><a href="{{ action("SendingDomainController@index") }}">{{ trans('messages.email_addresses') }}</a></li>
        </ul>
        <h1>
            <span class="text-semibold">{{ trans('messages.verified_senders') }}</span>
        </h1> 
	</div>
                
@endsection

@section('content')
    @include('senders._menu')
    <h2>
        <span class="text-semibold"><i class="icon-profile"></i> {{ $sender->name }}</span>
    </h2>
    
    <form enctype="multipart/form-data" action="{{ action('SenderController@update', $sender->uid) }}" method="POST" class="form-validate-jquery">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PATCH">
        
        @include('senders._form')
        
    <form>
    
@endsection