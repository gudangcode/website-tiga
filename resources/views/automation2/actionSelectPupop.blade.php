@extends('layouts.popup.small')

@section('content')
	<div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h3 class="mb-3">{{ trans('messages.automation.add_an_action') }}</h3>
            <p>{{ trans('messages.automation.action.intro') }}</p>
                
            <div class="line-list">
                @foreach ($types as $type)
                    @php
                        $disabled = ($type == 'condition' && $hasChildren == "true") ? 'd-disabled' : '';
                    @endphp
                    <div class="line-item action-select-but {{ $disabled }}" data-key="{{ $type }}">
                        <div class="line-icon">
                            <i class="lnr lnr-{{ trans('messages.automation.action.' . $type . '.icon') }}"></i>
                        </div>
                        <div class="line-body">
                            <h5>{{ trans('messages.automation.action.' . $type) }}</h5>
                            <p>{{ trans('messages.automation.action.' . $type . '.desc') }}</p>
                            @if ($type == 'condition' && $hasChildren == "true")
                                <p class="text-warning small mt-1">
                                    <i class="material-icons-outlined">warning</i> {{ trans('messages.automation.action.can_not_add_condition') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
