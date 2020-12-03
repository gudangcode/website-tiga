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

    <div class="row sub_section">

        <div class="col-sm-12 col-md-8">
            @if ($sender->isPending())
                <form action="{{ action('SenderController@show', ['id' => $sender->uid ]) }}">
                <div data-type="admin-notification" class="alert alert-warning" style="display: flex; flex-direction: row; align-items: center; cursor: pointer; justify-content: space-between;">
                    <div style="display: flex; flex-direction: row; align-items: center;">
                        <div style="margin-right:15px">
                            <i class="lnr lnr-warning"></i>
                        </div>
                        <div style="padding-right: 40px">
                            <h4>{{ trans('messages.sender.status_info.pending') }}</h4>
                            <p>{{ trans('messages.sender.status_info.pending.note') }}</p>
                        </div>
                    </div>
                    <button type="submit" class="btn bg-grey-600">Refresh now!</button>
                </div>
                </form>
            @elseif ($sender->isVerified())
                <div data-type="admin-notification" class="alert alert-success" style="display: flex; flex-direction: row; align-items: center; cursor: pointer; justify-content: space-between;">
                    <div style="display: flex; flex-direction: row; align-items: center;">
                        <div style="margin-right:15px">
                            <i class="lnr lnr-checkmark-circle"></i>
                        </div>
                        <div style="padding-right: 40px">
                            <h4>{{ trans('messages.sender.status_info.verified') }}</h4>
                            <p>{{ trans('messages.sender.status_info.verified.note') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            <h2>
                <span class="text-semibold"><i class="icon-profile"></i> {{ $sender->name }} </span>
                <span class="label label-primary bg-{{ $sender->status }}">
                    {{ trans('messages.sender.status.' . $sender->status) }}
                </span>
            </h2>
			<p>{{ trans('messages.sender.show.wording') }}</p>
            <ul class="dotted-list topborder section section-flex">
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.name') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ $sender->name }}</span>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.email') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ $sender->email }}</span>
                    </div>
                </li>
                <li>
                    <div class="unit size1of3">
                        <strong>{{ trans('messages.created_at') }}</strong>
                    </div>
                    <div class="size2of3">
                        <span>{{ Tool::formatDateTime($sender->created_at) }}</span>
                    </div>
                </li>
            </ul>
            
            <form enctype="multipart/form-data" action="{{ action('SenderController@verify', $sender->uid) }}" method="POST" class="form-vsalidate-jquery">
                {{ csrf_field() }}
                @if ($sender->status == Acelle\Model\Sender::STATUS_NEW)
                    <button class="btn btn-primary">{{ trans('messages.sending_domain.verify') }}</button>
                @endif
                <a href="{{ action('SenderController@edit', $sender->uid) }}" class="btn btn-primary bg-grey" style="min-width: 100px"><i class="icon-pencil"></i> {{ trans('messages.sender.edit') }}</a>
            </form>
        </div>
    </div>
@endsection