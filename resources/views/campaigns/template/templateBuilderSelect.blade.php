@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h2>{{ trans('messages.campaign.template.builder.select') }}</h2>
            <p>{{ trans('messages.campaign.template.builder.select.intro') }}</p>
            
            @if (in_array(Acelle\Model\Setting::get('builder'), ['both','pro']))
                <a href="{{ action('CampaignController@templateEdit', $campaign->uid) }}" class="btn btn-primary mr-10 template-compose">
                    {{ trans('messages.campaign.email_builder_pro') }}
                </a>
            @endif
            @if (in_array(Acelle\Model\Setting::get('builder'), ['both','classic']))
                <a href="{{ action('CampaignController@builderClassic', $campaign->uid) }}" class="btn btn-default template-compose-classic">
                    {{ trans('messages.campaign.email_builder_classic') }}
                </a>
            @endif
        </div>
    </div>
@endsection