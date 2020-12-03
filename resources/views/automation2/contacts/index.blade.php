@include('automation2._info')
				
@include('automation2._tabs', ['tab' => 'statistics', 'sub' => (request()->action_id ? 'contacts_action_' . request()->action_id : 'all_contacts')])
    
<div class="insight-topine d-flex small align-items-center">
    <div class="insight-desc mr-auto">
        @if (request()->action_id)
            {!! trans_choice('messages.automation.action.contacts_intro', $automation->subscribers(request()->action_id)->count(), [
                'count' => format_number($subscribers->count()),
                'name' => $automation->getElement(request()->action_id)->getName(),
            ]) !!}
        @else
            {!! trans_choice('messages.automation.contacts_intro', $automation->subscribers()->count(), [
                'count' => format_number($subscribers->count()),
                'name' => $automation->name,
            ]) !!}
        @endif        
    </div>
    <div class="insight-action">
        @if ($subscribers->count())
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <div class="btn-group btn-group-sm" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
                    <a class="dropdown-item list-export-contacts"
                        href="{{ action('Automation2Controller@exportContacts', [
                            'uid' => $automation->uid,
                            'action_id' => request()->action_id,
                        ]) }}"
                    >{{ trans('messages.automation.export_this_list') }}</a>
                    <a class="dropdown-item list-copy-contacts-new-list"
                        href="{{ action('Automation2Controller@copyToNewList', [
                            'uid' => $automation->uid,
                            'action_id' => request()->action_id,
                        ]) }}"
                    >{{ trans('messages.automation.copy_to_new_list') }}</a>
                    <a class="dropdown-item list-tag-contacts"
                        href="{{ action('Automation2Controller@tagContacts', [
                            'uid' => $automation->uid,
                            'action_id' => request()->action_id,
                        ]) }}"
                    >{{ trans('messages.automation.tag_those_contacts') }}</a>
                </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="input-with-icon mb-4">
    <i class="material-icons">search</i>
    <input class="form-control mt-3" name="contact_keyword" placeholder='Enter to search contacts...' />
</div>
    
<div class="contacts_list ajax-list"></div>
    
<script>
    var listContact = new List( $('.contacts_list'), {
        url: '{{ action('Automation2Controller@contactsList', [
                'uid' => $automation->uid,
                'action_id' => request()->action_id,
            ]) }}',
        data: function() {
                return {
                    keyword: $('[name=contact_keyword]').val(),
                };
            },
        per_page: 5,
    });		
    listContact.load();
    
    // filters
    $('[name=contact_keyword]').keyup(function() {
        listContact.load();
    });

    // tag contacts
    var tagContact = new Popup(undefined, undefined, {
        onclose: function() {
            sidebar.load();
        }
    });
    $('.list-tag-contacts').click(function(e) {
        e.preventDefault();

        var url = $(this).attr('href');

        tagContact.load(url, function() {
            console.log('Tag action type popup loaded!');				
        });
    });

    // export contacts
    $('.list-export-contacts').click(function(e) {
        e.preventDefault();

        var url = $(this).attr('href');

        addMaskLoading('{{ trans('messages.automation.exporting_contacts') }}');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: CSRF_TOKEN,
            },
            statusCode: {
                // validate error
                400: function (res) {
                    alert('Something went wrong!');
                }
            },
            success: function (response) {
                // notify
                notify('success', '{{ trans('messages.notify.success') }}', response.message);

                // remove effects
                removeMaskLoading();

                popup.hide();
            }
        });
    });
    
    // copy contacts
    var copyContact = new Popup(undefined, undefined, {
        onclose: function() {
            sidebar.load();
        }
    });
    $('.list-copy-contacts-new-list').click(function(e) {
        e.preventDefault();

        var url = $(this).attr('href');

        copyContact.load(url, function() {
            console.log('Copy to new list popup loaded!');				
        });
    });
</script>