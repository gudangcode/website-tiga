@extends('layouts.black')

@section('title', trans('messages.edit_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('content')
    <header>
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="height: 56px;">
			<a class="navbar-brand left-logo mr-0" href="#">
				@if (\Acelle\Model\Setting::get('site_logo_small'))
					<img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
				@else
					<img height="22" src="{{ URL::asset('images/logo_light.png') }}" alt="">
				@endif
			</a>
			<div class="d-inline-block d-flex mr-auto align-items-center">
                <a style="" href="javascript;" class="close-button action black-back-button mr-3">
					<i class="material-icons-outlined">arrow_back</i>
				</a>
                <h1 class="">{{ $automation->name }}</h1>
				<i class="material-icons-outlined automation-head-icon ml-2">web</i>
			</div>
			<div class="automation-top-menu">
                <a class="action mr-2" href="{{ action('Automation2Controller@templateEditClassic', [
                    'uid' => $automation->uid,
                    'email_uid' => $email->uid,
                ]) }}">
                    {{ trans('messages.email.html_editor') }}
                </a>
				<button class="btn btn-primary" onclick="$('#classic-builder-form').submit()">{{ trans('messages.save') }}</button>
			</div>
            <a href="javascript;"
                class="close-button action black-close-button ml-2" style="margin-right: -15px">
                <i class="material-icons-outlined">close</i>
            </a>
		</nav>
	</header>
    <form style="margin-top: 56px;" id="classic-builder-form" action="{{ action('Automation2Controller@templateEditPlain', [
        'uid' => $automation->uid,
        'email_uid' => $email->uid,
    ]) }}" method="POST" class="ajax_upload_form builder-classic-form form-validate-jquery">
        {{ csrf_field() }}

        <div class="row mr-0 ml-0">
            <div class="col-md-9 pl-0 pb-0 pr-0">
                @include('helpers.form_control', [
                    'class' => 'campaign-plain-text',
                    'required' => true,
                    'label' => '',
                    'type' => 'textarea',
                    'name' => 'plain',
                    'value' => $email->plain,
                    'rules' => ['plain' => 'required']
                ])        
            </div>
            <div class="col-md-3 pr-0 pb-0 sidebar pr-4 pt-4 pl-4" style="overflow:auto;background:#f5f5f5">
                @include('elements._tags', ['tags' => Acelle\Model\Template::tags($automation->mailList)])
            </div>            
        </div>   
    <form>

    <script>
        $('.close-button').click(function() {
            parent.$('.full-iframe-popup').remove();
            popup.load();
        });

        $('.builder-classic-form').submit(function(e) {
            e.preventDefault();

            tinymce.triggerSave();

            var url = $(this).attr('action');
            var data = $(this).serialize();

            if ($(this).valid()) {
                // open builder effects
                addMaskLoading("{{ trans('messages.automation.template.saving') }}", function() {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: data,
                        statusCode: {
                            // validate error
                            400: function (res) {
                                console.log('Something went wrong!');
                            }
                        },
                        success: function (response) {
                            removeMaskLoading();

                            // notify
                            parent.notify('success', '{{ trans('messages.notify.success') }}', response.message);
                        }
                    });
                });         
            }     
        });

        $('.sidebar').css('height', parent.$('.full-iframe-popup').height()-56);
        $('[name=plain]').css('height', parent.$('.full-iframe-popup').height()-56);
    </script>
@endsection