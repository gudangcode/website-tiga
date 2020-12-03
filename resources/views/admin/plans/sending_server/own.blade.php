@extends('layouts.backend')

@section('title', $plan->name)

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>

    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li><a href="{{ action("Admin\PlanController@index") }}">{{ trans('messages.plans') }}</a></li>
            <li><a href="{{ action('Admin\PlanController@sendingServer', $plan->uid) }}">
                    {{ $plan->name }}
                </a>
            </li>
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    
    @include('admin.plans._menu')
    
    <form action="{{ action('Admin\PlanController@sendingServerOwn', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
        {{ csrf_field() }}
        
        <div class="mc-section">
            <div class="announce_box">
                <div class="row flex-center">
                    <div class="col-md-8">
                        <i class="announce_box-icon lnr lnr-user"></i>
                        <label>{{ trans('messages.plan_option.delivery_setting') }}</label>
                        <h4>{{ trans('messages.plan_option.own_sending_server') }}</h4>
                        <p>{{ trans('messages.plan_option.own_sending_server.intro') }}</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a class="btn btn-mc_primary mr-20 mc-modal-control" modal-size="lg" href="{{ action('Admin\PlanController@sendingServerOption', [
                            'id' => $plan->uid]) }}">
                                {{ trans('messages.plan_option.change') }}</a>
                    </div>
                </div>
            </div>
                
            <div class="row flex-center">
                <div class="col-md-12">
                    <h2 class="mc-h2 mt-0 mb-10">
                        <span class="text-semibold">{{ trans('messages.plan.sending_servers.own.setting') }}</span>
                    </h2>
                    <p>{{ trans('messages.plan.sending_servers.own.setting.intro') }}</p>
                </div>
            </div>
            
            <div class="boxing">
                @include('helpers.form_control', [
                    'type' => 'text',
                    'class' => 'numeric',
                    'name' => 'plan[options][sending_servers_max]',
                    'value' => $plan->getOption('sending_servers_max'),
                    'label' => trans('messages.max_sending_servers'),
                    'help_class' => 'plan',
                    'options' => ['true', 'false'],
                    'rules' => $plan->validationRules()['options'],
                    'unlimited_check' => true,
                ])
            </div>
    
            <p>
                @include('helpers.form_control', ['type' => 'checkbox2',
                    'class' => '',
                    'name' => 'plan[options][all_sending_server_types]',
                    'value' => $plan->getOption('all_sending_server_types'),
                    'label' => trans('messages.allow_adding_all_sending_server_types'),
                    'options' => ['no','yes'],
                    'help_class' => 'plan',
                    'rules' => $plan->validationRules()['options'],
                ])
            </p>
            <div class="all_sending_server_types_no">
                <hr>
                <label class="text-semibold text-muted">{{ trans('messages.select_allowed_sending_server_types') }}</label>
                <div class="row">
                    @foreach (Acelle\Model\SendingServer::types() as $key => $type)
                        <div class="col-md-4 pt-10">
                            &nbsp;&nbsp;<span class="text-semibold text-italic">{{ trans('messages.' . $key) }}</span>
                            <span class="notoping pull-left">
                                @include('helpers.form_control', ['type' => 'checkbox',
                                    'class' => '',
                                    'name' => 'plan[options][sending_server_types][' . $key . ']',
                                    'value' => isset($plan->getOption('sending_server_types')[$key]) ? $plan->getOption('sending_server_types')[$key] : 'no',
                                    'label' => '',
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->validationRules()['options'],
                                ])
                            </span>
                        </div>
                    @endforeach
                </div>
                <hr>
            </div>
        </div>
        <button class="btn btn-mc_primary mr-10">{{ trans('messages.save') }}</button>
    </form>
    
    <script>
        var current_option = $("input[name='plan[options][sending_server_option]']:checked").val();
    
        $(document).ready(function() {
            // all sending servers checking
            $(document).on("change", "input[name='plan[options][all_sending_servers]']", function(e) {
                if($("input[name='plan[options][all_sending_servers]']:checked").length) {
                    $(".sending-servers").find("input[type=checkbox]").each(function() {
                        if($(this).is(":checked")) {
                            $(this).parents(".form-group").find(".switchery").eq(1).click();
                        }
                    });
                    $(".sending-servers").hide();
                } else {
                    $(".sending-servers").show();
                }
            });
            $("input[name='plan[options][all_sending_servers]']").trigger("change");
    
            // Sending domains checking setting
            $(document).on("change", "input[name='plan[options][create_sending_domains]']", function(e) {
                if($('input[name="plan[options][create_sending_domains]"]:checked').val() == 'yes') {
                    $(".sending-domains-yes").show();
                    $(".sending-domains-no").hide();
                } else {
                    $(".sending-domains-no").show();
                    $(".sending-domains-yes").hide();
                }
            });
            $('input[name="plan[options][create_sending_domains]"]').trigger("change");
    
            // all email verification servers checking
            $(document).on("change", "input[name='plan[options][all_email_verification_servers]']", function(e) {
                if($("input[name='plan[options][all_email_verification_servers]']:checked").length) {
                    $(".email-verification-servers").find("input[type=checkbox]").each(function() {
                        if($(this).is(":checked")) {
                            $(this).parents(".form-group").find(".switchery").eq(1).click();
                        }
                    });
                    $(".email-verification-servers").hide();
                } else {
                    $(".email-verification-servers").show();
                }
            });
            $("input[name='plan[options][all_email_verification_servers]']").trigger("change");
    
    
            // Email verification servers checking setting
            $(document).on("change", "input[name='plan[options][create_email_verification_servers]']", function(e) {
                if($('input[name="plan[options][create_email_verification_servers]"]:checked').val() == 'yes') {
                    $(".email-verification-servers-yes").show();
                    $(".email-verification-servers-no").hide();
                } else {
                    $(".email-verification-servers-no").show();
                    $(".email-verification-servers-yes").hide();
                }
            });
            $('input[name="plan[options][create_email_verification_servers]"]').trigger("change");
    
            // Sending servers type checking setting
            $(document).on("change", "input[name='plan[options][all_sending_server_types]']", function(e) {
                if($('input[name="plan[options][all_sending_server_types]"]:checked').val() == 'yes') {
                    $(".all_sending_server_types_yes").show();
                    $(".all_sending_server_types_no").hide();
                } else {
                    $(".all_sending_server_types_no").show();
                    $(".all_sending_server_types_yes").hide();
                }
            });
            $('input[name="plan[options][all_sending_server_types]"]').trigger("change");
            
        });
    </script>
    
@endsection


