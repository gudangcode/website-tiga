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
        </ul>
        <h1 class="mc-h1">
            <span class="text-semibold">{{ $plan->name }}</span>
        </h1>
    </div>

@endsection

@section('content')
    
    @include('admin.plans._menu')
    
    <form enctype="multipart/form-data" action="{{ action('Admin\PlanController@save', $plan->uid) }}" method="POST" class="form-validate-jqueryx">
        {{ csrf_field() }}
        
        <div class="row">
            <div class="col-md-6">
                <div class="mc_section">
                    <h2>{{ trans('messages.plan.email_verification') }}</h2>
                        
                    <p>{{ trans('messages.plan.email_verification.intro') }}</p>
                        
                    <div class="form-group control-radio">
                        <div class="radio_box" data-popup='tooltip' title="">
                            <label class="main-control">
                                <input {{ ($plan->getOption('create_email_verification_servers') == 'yes' ? 'checked' : '') }} type="radio"
                                    name="plan[options][create_email_verification_servers]"
                                    value="yes" class="styled" /><rtitle>{{ trans('messages.plan.email_verification.use_own') }}</rtitle>
                                <div class="desc text-normal mb-10">
                                    {{ trans('messages.plan.email_verification.use_own.intro') }}
                                </div>
                            </label>
                            <div class="radio_more_box">
                                <div class="boxing">
                                    @include('helpers.form_control', [
                                        'type' => 'text',
                                        'class' => 'numeric',
                                        'name' => 'plan[options][email_verification_servers_max]',
                                        'value' => $plan->getOption('email_verification_servers_max'),
                                        'label' => trans('messages.max_email_verification_servers'),
                                        'help_class' => 'plan',
                                        'options' => ['true', 'false'],
                                        'rules' => $plan->validationRules()['options'],
                                    ])
                                    <div class="checkbox inline unlimited-check text-semibold">
                                        <label>
                                            <input{{ $plan->getOption('email_verification_servers_max')  == -1 ? " checked=checked" : "" }} type="checkbox" class="styled">
                                            {{ trans('messages.unlimited') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="radio_box" data-popup='tooltip' title="">
                            <label class="main-control">
                                <input {{ ($plan->getOption('create_email_verification_servers') == 'no' ? 'checked' : '') }} type="radio"
                                    name="plan[options][create_email_verification_servers]"
                                    value="no" class="styled" />
                                        <rtitle>{{ trans('messages.plan.email_verification.use_system') }}</rtitle>
                                <div class="desc text-normal mb-10">
                                    {{ trans('messages.plan.email_verification.use_system.intro') }}
                                </div>
                            </label>
                            <div class="radio_more_box">
                                @include('helpers.form_control', ['type' => 'checkbox2',
                                    'class' => '',
                                    'name' => 'plan[options][all_email_verification_servers]',
                                    'value' => $plan->getOption('all_email_verification_servers'),
                                    'label' => trans('messages.use_all_email_verification_servers'),
                                    'options' => ['no','yes'],
                                    'help_class' => 'plan',
                                    'rules' => $plan->validationRules()['options'],
                                ])
                                @if(!Acelle\Model\EmailVerificationServer::getAllAdminActive()->count())
                                    <div class="empty-list">
                                        <i class="icon-database-check"></i>
                                        <span class="line-1">
                                            {{ trans('messages.email_verification_server_no_active') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="email-verification-servers">
                                        <hr>
                                        <p class="mb-5">{{ trans('messages.plan.email_verification.select_server') }}</p>
                                        <div class="row">                                        
                                            @foreach (Acelle\Model\EmailVerificationServer::getAllAdminActive()->orderBy("name")->get() as $server)
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            @include('helpers.form_control', [
                                                                'type' => 'checkbox2',
                                                                'name' => 'plan[email_verification_servers][' . $server->uid . '][check]',
                                                                'value' => $plan->plansEmailVerificationServers->contains('server_id', $server->id),
                                                                'label' => $server->name,
                                                                'options' => [false, true],
                                                                'help_class' => 'plan',
                                                                'rules' => $plan->validationRules()['options'],
                                                            ])
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>                        
                    </div>
                </div>
                <button class="btn btn-mc_primary mr-10">{{ trans('messages.save') }}</button>
                <a href="{{ action('Admin\PlanController@index') }}" type="button" class="btn btn-mc_inline">
                    {{ trans('messages.cancel') }}
                </a>
            </div>
        </div>
    </form>
        
    <script>
        $(document).ready(function() {
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
        });
    </script>
        
@endsection
