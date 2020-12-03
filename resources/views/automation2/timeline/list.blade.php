<div class="insight-topine d-flex small align-items-center">
    <div class="insight-desc mr-auto">
        {!! trans('messages.automation.timline.intro', ['count' => format_number($pagination["total"])]) !!}        
    </div>
    <div class="insight-action">
        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
            <div class="btn-group btn-group-sm" role="group">
              <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Action
              </button>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
                <a class="dropdown-item" href="#">Export this list</a>
                <a class="dropdown-item" href="#">Copy to a new list</a>
                <a class="dropdown-item" href="#">Tag those contacts</a>
              </div>
            </div>
        </div>
    </div>
</div>

@if ($timelines->count() > 0)
        <p class="insight-intro mb-2 mt-3 small">
        {{ trans('messages.automation.all_activities') }} ({{ format_number($pagination["total"]) }})
    </p>
        
    <div class="mc-table small border-top">
        @foreach ($timelines as $key => $timeline)
            <div class="mc-row d-flex align-items-center">
                <div class="media trigger">
                    <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@profile', [
                        'uid' => $automation->uid,
                        'contact_uid' => $timeline->subscriber->uid,
                    ]) }}')" class="font-weight-semibold d-block">
                        @if ($timeline->subscriber->avatar)
                            <img src="{{ action('SubscriberController@avatar',  $timeline->subscriber->uid) }}" />
                        @elseif(isSiteDemo())
                            <img src="https://i.pravatar.cc/30{{ $key }}" />
                        @else
                            <i style="opacity: 0.7" class="lnr lnr-user bg-{{ rand_item(['info', 'success', 'secondary', 'primary', 'danger', 'warning']) }}"></i>
                        @endif                        
                    </a>
                </div>
                <div class="flex-fill flex-grow-1" style="width: 50%">
                    <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@profile', [
                        'uid' => $automation->uid,
                        'contact_uid' => $timeline->subscriber->uid,
                    ]) }}')" class="font-weight-semibold d-block">
                        {{ $timeline->subscriber->getFullName() }}
                    </a>
                    <desc>{{ $timeline->activity }}</desc>
                </div>
                <div class="flex-fill text-center">
                    <desc>{{ $timeline->created_at->diffForHumans() }}</desc>
                </div>
            </div>
        @endforeach
    </div>
        
    @include('helpers._pagination')
@else
    <div class="empty-list">
        <i class="material-icons">timeline</i>
        <span class="line-1">
            {{ trans('messages.automation.timeline.no_activities') }}
        </span>
    </div>
@endif
    
