@if ($contacts->count() > 0)
    <p class="insight-intro mb-2 small">
        {{ trans('messages.automation.contact.all_count', ['count' => format_number($pagination["total"])]) }}
    </p>
        
    <div class="mc-table small border-top">
        @foreach ($contacts as $key => $contact)
            <div class="mc-row d-flex align-items-center">
                <div class="media trigger">
                    <a href="javascript:;" onclick="popup.load('{{ action('Automation2Controller@profile', [
                        'uid' => $automation->uid,
                        'contact_uid' => $contact->uid,
                    ]) }}')" class="font-weight-semibold d-block">
                        @if ($contact->avatar)
                            <img src="{{ action('SubscriberController@avatar',  $contact->uid) }}" haha="https://i.pravatar.cc/30{{ $key }}" />
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
                        'contact_uid' => $contact->uid,
                    ]) }}')" class="font-weight-semibold d-block">
                        {{ $contact->email }}
                    </a>
                    <desc>{{ trans('messages.automation.contact.added_at', ['time' => $contact->created_at->diffForHumans()]) }}</desc>
                </div>
                    
                @php
                    $field = $automation->mailList->getOneField();
                @endphp
                @if (is_object($field))
                    <?php $value = $contact->getValueByField($field); ?>
                    <div class="flex-fill text-center">
                        <label title="{{ empty($value) ? "--" : $value }}" class="font-weight-semibold text-center">
                            <span class="text-truncate d-block m-auto" style="max-width: 100px;">{{ empty($value) ? "--" : $value }}</span>
                        </label>
                        <desc>{{ $field->label }}</desc>
                    </div>
                @endif
            </div>
        @endforeach
        
    @include('helpers._pagination')
@else
    <div class="empty-list">
        <i class="lnr lnr-users"></i>
        <span class="line-1">
            {{ trans('messages.automation.empty_contacts') }}
        </span>
    </div>
@endif