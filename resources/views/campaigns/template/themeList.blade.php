@if ($templates->count() > 0)
    <div id="layout" class="tab-pane fade in active template-boxes layout mt-20" style="
        margin-left: -20px;
        margin-right: -20px;
    ">
        @foreach ($templates as $key => $template)
            <div class="col-xxs-12 col-xs-6 col-sm-3 col-md-2">
                <a href="javascript:;" class="select-template-layout" data-template="{{ $template->uid }}">
                    <div class="panel panel-flat panel-template-style">
                        <div class="panel-body">
                            <a 
                                href="{{ action('CampaignController@templateTheme', ['uid' => $campaign->uid, 'template' => $template->uid]) }}"
                                class="choose-theme"
                            >
                                <img src="{{ $template->getThumbUrl() }}?v={{ rand(0,10) }}" />
                                <label class="mb-20 text-center">{{ $template->name }}</label>
                            </a>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
        
    <hr style="clear: both">
        
    @include('elements/_per_page_select', ["items" => $templates])
    {{ $templates->links() }}

    <script>
        var builderSelectPopup = new Popup(null, undefined, {onclose: function() {
            window.location = '{{ action('CampaignController@template', $campaign->uid) }}';
        }});

        $('.choose-theme').click(function(e) {
            e.preventDefault();        
            var url = $(this).attr('href');

            addMaskLoading();

            // 
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
                    removeMaskLoading();

                    // notify
                    notify('success', '{{ trans('messages.notify.success') }}', response.message);

                    builderSelectPopup.load(response.url);
                    templatePopup.hide();
                }
            });
        });
    </script>
@elseif (!empty(request()->keyword))
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.no_search_result') }}
        </span>
    </div>
@else
    <div class="empty-list">
        <i class="icon-magazine"></i>
        <span class="line-1">
            {{ trans('messages.template_empty_line_1') }}
        </span>
    </div>
@endif
