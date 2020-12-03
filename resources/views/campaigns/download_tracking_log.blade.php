@extends('layouts.empty')

@section('title', $campaign->name)

@section('page_header')

    @include("campaigns._header")

@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col" style="padding-top:20px">
                <p align="center" id='inprogress'>{{ trans('messages.campaign.log.download.inprogress') }}<span id="progress">0%</span></p>
                <p align="center" id='done' style="display:none">{{ trans('messages.campaign.log.download.complete') }}<br><a id="download" href="#">{{ trans('messages.tracking_log.download') }}</a></p>
            </div>
        </div>
    </div>

    <script>
        var interval;
        var check = function() {
            $.ajax({
                url: "{{ action('CampaignController@trackingLogExportProgress', $job->id) }}"
            }).done(function( data, textStatus, jqXHR ) {
                console.log(data);
                if (data.progress != 1) {
                    $("#progress").html(data.progress * 100 + "%");
                } else {
                    clearInterval(interval);
                    $("#done").show();
                    $("#inprogress").hide();

                    // tell the parent: ready to close
                    $("#download").click(function(){
                        window.opener.downloadAndCloseDownloadWindow(data.download_url);
                    });
                }
            }).fail(function( jqXHR, textStatus, errorThrown ) {
                alert(errorThrown);
            });
        };

        interval = setInterval(check, 2500);
    </script>
@endsection