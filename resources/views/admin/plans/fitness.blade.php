<form enctype="multipart/form-data" action="{{ action('Admin\PlanController@fitness', $plan->uid) }}" method="POST" class="form-validate-jquery">
    {{ csrf_field() }}
    <div class="modal-header">
        <h5 class="modal-title">{{ trans('messages.plan.fitness') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
      <div class="mc_section">
        <h2>What is fitness</h2>
        <p>This feature allows you to add a sending server which will actually send out your campaign emails. You can configure a standard SMTP connection or connect to a 3rd services like Amazon SES, SendGrid, Mailgun, ElasticEmail, SparkPost... You can also take advantage of the the hosting server's email capability by creating a "PHP Mail" or "Sendmail" sending server</p>
      
        <ul class="mc-progress-list mt-40">
            @foreach ($plan->plansSendingServers as $planSendingServer)
                <li>
                    <div class="row">
                        <div class="col-md-3 text-right pt-10">
                            <label class="">
                                {{ $planSendingServer->sendingServer->name }}                                        
                            </label>
                        </div>
                        <div class="col-md-7">
                            <div class="pull-right text-small">
                                {{ trans('messages.plan.fitness.more') }}
                            </div>
                            <div class="pull-left text-small">
                                {{ trans('messages.plan.fitness.less') }}
                            </div>
                            <div>
                                <input name="sending_servers[{{ $planSendingServer->sendingServer->uid }}]" class="slider"
                                    data-slider-value="{{ $planSendingServer->fitness }}"
                                    data-slider-min="1"
                                    data-slider-max="100"
                                    data-slider-step="1"
                                    data-slider-tooltip="hide"
                                />
                            </div>
                            
                        </div>
                        <div class="col-md-2 pt-10">
                            <span class="mc-text-bold val hide">{{ $planSendingServer->fitness }}</span>
                            <span class="mc-text-bold percent">{{ $planSendingServer->fitness }}</span> (%)
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
      </div>
    </div>
    <hr>
    <div class="modal-footer">
      <button type="submit" class="btn btn-mc_primary">{{ trans('messages.plan.fitness.save') }}</button>
      <button type="button" class="btn btn-mc_inline" data-dismiss="modal">{{ trans('messages.close') }}</button>
    </div>
<form>