    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/dropzone/js/dropzone.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ URL::asset('assets/js/plugins/dropzone/css/dropzone.css') }}" />

  <div class="image_upload_div">
    <form action="{{ action('CampaignController@uploadAttachment', $campaign->uid) }}" class="dropzone">
       {{ csrf_field() }}
    </form>
  </div>
  <div class="attachments_pnl">
    <h3 class='mt-40'>{{ trans('messages.campaign.attached_files') }}<br/></h3>

    @if (!empty($campaign->getAttachments()))
      <ul>
        @foreach ($campaign->getAttachments() as $k=>$filename)
          @php
            $fileszie = formatSizeUnits(filesize($campaign->getAttachmentPath($filename)));
          @endphp
          <li class="d-flex align-items-canter">
            <div class="d-flex mr-auto">
              <i class="lnr lnr-file-empty icon-file"></i>
              <span><name>{{ $filename }}</name><size>{{ trans('messages.campaign.attachment.file_size_is', ['size' => $fileszie]) }}</size></span> 
            </div>
            <div class="text-nowrap">
              <a title="" class="tip-right" href="{{ action('CampaignController@downloadAttachment', [
                'uid' => $campaign->uid,
                'name' => $filename,
              ]) }}">
                {{ trans('messages.download') }}
              </a>
              |
              <a href="{{ action('CampaignController@removeAttachment', [
                'uid' => $campaign->uid,
                'name' => $filename,
              ]) }}" class="remove-attachment">
                {{ trans('messages.remove') }}
              </a>
            </div>
          </li>
        @endforeach
      </ul>
    @endif
  </div>

  <script>
  Dropzone.autoDiscover = false;

    var myDropzone = $(".dropzone").dropzone({
      uploadMultiple: true,
      success: function() {
        reloadList();
      }
    });

    function reloadList() {
      $.ajax({
          method: 'GET',
          url: '',
      })
      .done(function(msg) {
          $('.attachments_pnl').html($(msg).find('.attachments_pnl').html());
      });
    }

    $(document).on('click', '.remove-attachment', function(e) {
      e.preventDefault();

      var url = $(this).attr('href');

      $.ajax({
          method: 'POST',
          url: url,
          data: {
            _token: CSRF_TOKEN
          }
      })
      .done(function(msg) {
        reloadList();
      });
    });
  </script>
