<form action="{{ action('Automation2Controller@emailAttachmentUpload', [
  'uid' => $automation->uid,
  'email_uid' => $email->uid,
]) }}" class="dropzone">
  {{ csrf_field() }}

  <div class="fallback">
    <input name="files[]" type="file" multiple />
  </div>
</form>
  
@if($email->attachments()->count())
    <h5 class="mt-4 mb-3">{{ trans('messages.automation.email.attached_files') }}</h5>
        
    <div class="row">
        <div class="col-md-12">
            <ul class="key-value-list list-small">
                @foreach ($email->attachments as $attachment)
                    <li class="flex">
                        <div class="icon">
                            <i class="lnr lnr-file-empty"></i>
                        </div>
                        <div class="content mr-auto">
                            <label>
                                {{ $attachment->name }}
                            </label>
                            <div class="value">
                                {{ trans('messages.campaign.attachment.file_size_is', ['size' => formatSizeUnits($attachment->size)]) }}
                            </div>
                        </div>
                        <div class="action">
                            <a                                
                                href="{{ action('Automation2Controller@emailAttachmentDownload', [
                                    'uid' => $automation->uid,
                                    'email_uid' => $email->uid,
                                    'attachment_uid' => $attachment->uid,
                                ]) }}"
                                class=""
                                title="{{ trans('messages.automation.email.attachment.download') }}"
                            >
                                <i class="lnr lnr-download"></i>
                            </a>
                            <a                                
                                href="{{ action('Automation2Controller@emailAttachmentRemove', [
                                    'uid' => $automation->uid,
                                    'email_uid' => $email->uid,
                                    'attachment_uid' => $attachment->uid,
                                ]) }}"
                                class="attachment-remove"
                                title="{{ trans('messages.automation.email.attachment.remove') }}"
                            >
                                <i class="lnr lnr-trash"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
  
<script>
    //
    // Dropzone
    // ------------------------------
    $(".dropzone").dropzone({
        uploadMultiple: true,
        success: function() {
            popup.load();
        }
    });
    
    $('.attachment-remove').click(function(e) {
        e.preventDefault();
        
        var link = $(this);
        var url = link.attr('href');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                "_token": CSRF_TOKEN
            },
            success: function (response) {
                popup.load();
                
                notify('success', '{{ trans('messages.notify.success') }}', response.message);
             }
        });
    });
</script>