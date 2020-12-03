@extends('layouts.frontend')

@section('title', trans('messages.subscriptions'))

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
            <span class="text-semibold">{{ Auth::user()->customer->displayName() }}</span>
        </h1>
    </div>

@endsection

@section('content')

    @include("account._menu")

    <div class="row">
        <div class="col-md-12">
            <h2 class="text-semibold">{{ trans('messages.subscription') }}</h2>

            <div class="sub-section">
                <div class="row">
                    <div class="col-md-6">
                        <p>{!! trans('messages.subscribe_to_a_plan_intro') !!}</p>

                        <form enctype="multipart/form-data" action="" method="POST" class="form-validate-jqueryz subscription-form">
                            {{ csrf_field() }}

                            @include('helpers.form_control', [
                                'type' => 'select_ajax',
                                'class' => 'subsciption-plan-select hook',
                                'name' => 'plan_uid',
                                'label' => trans('messages.select_plan'),
                                'help_class' => 'subscription',
                                'url' => action('PlanController@select2'),
                                'placeholder' => trans('messages.select_plan')
                            ])
                        </form>
                    </div>
                </div>
                <div class="ajax-detail-box" data-url="{{ action('AccountSubscriptionController@preview') }}" data-form=".subscription-form">
                    @include('account.subscription.preview', [
                        'plan' => null,
                    ])
                </div>
            </div>
        </div>
    </div>

@endsection
