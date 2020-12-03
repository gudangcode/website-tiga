@extends('layouts.frontend')

@section('title', trans('messages.campaigns') . " - " . trans('messages.template'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>	
@endsection

@section('page_header')
	
	<div class="page-title">
		<ul class="breadcrumb breadcrumb-caret position-right">
			<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
			<li><a href="{{ action("CampaignController@index") }}">{{ trans('messages.campaigns') }}</a></li>
		</ul>
		<h1>
			<span class="text-semibold"><i class="icon-paperplane"></i> {{ $campaign->name }}</span>
		</h1>

		@include('campaigns._steps', ['current' => 3])
	</div>

@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <h2 class="mt-0">{{ trans('messages.campaign.content_management') }}</h2>
            <h3>{{ trans('messages.campaign.email_content_plain') }}</h3>                
            <p>{{ trans('messages.campaign.email_content_plain.intro') }}</p>
                
            <form action="{{ action('CampaignController@plain', $campaign->uid) }}" method="POST">
                {{ csrf_field() }}

                @include('helpers.form_control', [
                    'type' => 'textarea',
                    'class' => 'campaign-plain-text',
                    'name' => 'plain',
                    'value' => $campaign->plain,
                    'label' => '',
                    'help_class' => 'campaign',
                    'rules' => ['plain' => 'required'],
                ])

                <button class="btn btn-mc_primary">{{ trans('messages.campaign.plain.save') }}</button>
            </form>
        </div>
    </div>
@endsection
