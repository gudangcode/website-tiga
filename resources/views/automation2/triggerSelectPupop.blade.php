@extends('layouts.popup.medium')

@section('content')
	<div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">{{ trans('messages.automation.automation_trigger') }}</h2>
            <p>{{ trans('messages.automation.trigger.intro') }}</p>
                
            <div class="box-list mt-3">
				<div class="box-list mt-40">
					@foreach ($types as $type)
						<a class="box-item trigger-select-but
								{{ $trigger->getOption('key') == $type ? 'current' : '' }}
							"
							data-key="{{ $type }}"						
						>							
							<h6 class="d-flex align-items-center text-center justify-content-center">
								<i class="material-icons-outlined mr-2">{{ trans('messages.automation.trigger.icon.' . $type) }}</i>
								{{ trans('messages.automation.trigger.' . $type) }}</h6>
							<p>{{ trans('messages.automation.trigger.' . $type . '.desc') }}</p>
						</a>
					@endforeach                
            </div>
        </div>
    </div>
@endsection
