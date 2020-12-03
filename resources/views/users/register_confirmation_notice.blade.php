@extends('layouts.register')

@section('title', trans('messages.create_your_account'))

@section('page_script')    
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/visualization/echarts/echarts.js') }}"></script>
    
    <script type="text/javascript" src="{{ URL::asset('js/chart.js') }}"></script>
@endsection

@section('content')

    <div class="row mt-40">
        <div class="col-md-2"></div>
        <div class="col-md-2 text-right mt-60">
            <a class="main-logo-big" href="{{ action('HomeController@index') }}">
                @if (\Acelle\Model\Setting::get('site_logo_big'))
                    <img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_big')) }}" alt="">
                @else
                    <img src="{{ URL::asset('images/logo_square.png') }}" alt="">
                @endif
            </a>
        </div>
        <div class="col-md-5">
            
            <h1 class="mb-10">{{ trans('messages.email_confirmation') }}</h1>
            <p>{!! trans('messages.activation_email_sent_content') !!}</p>
                
        </div>
        <div class="col-md-1"></div>
    </div>
@endsection
