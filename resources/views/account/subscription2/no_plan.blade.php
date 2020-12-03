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
            <span class="text-semibold">{{ Auth::user()->customer->displayName() }}</span>
        </h1>
    </div>

@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6">
            <div class="sub-section">
                @include('elements._notification', [
                    'level' => 'warning',
                    'message' => trans('messages.no_plan.title')
                ])

                <p>{{ trans('messages.no_plan.wording') }}</p>
                    
                <a href="{{ action('AccountSubscriptionController@index') }}" class="btn btn-mc_primary">
					{{ trans('messages.manage_your_subscription') }}
				</a>
            </div>
        </div>
    </div>
@endsection
