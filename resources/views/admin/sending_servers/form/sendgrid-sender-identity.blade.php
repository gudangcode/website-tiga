<div class="mc_section boxing">
    <div class="row">
        <div class="col-md-6">
            <h3 class="mt-0">{{ trans('messages.sending_servers.sending_identity') }}</h3>
            <p>
                {!! trans('messages.sending_servers.sending_identity.sendgrid.intro', ['link' => '']) !!}
            </p>
        </div>

        <div class="col-md-8">
            @if (is_null($identities))
                @include('elements._notification', [
                    'level' => 'warning',
                    'title' => 'Error fetching identities list',
                    'message' => 'Please check your connection to AWS',
                ])
            @else
                <table class="table table-box table-box-head field-list">
                    <thead>
                        <tr>
                            <td>{{ trans('messages.domain') }}</td>
                            <td>{{ trans('messages.status') }}</td>
                            <td align="center" class="xtooltip" title="Set whether or not this identity is available for all users">Available for All</td>
                            <td>Added By</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allIdentities as $domain => $attributes)
                            <tr class="odd">
                                <td>
                                    {{ $domain }}
                                </td>
                                <td>
                                    @if ($attributes['VerificationStatus'] == 'Success')
                                        <span class="badge badge-success badge-lg">{{ trans('messages.sending_domain_status_active') }}</span>
                                    @else
                                        <span class="badge badge-default badge-lg">{{ trans('messages.sending_domain_status_inactive') }}</span>
                                    @endif
                                    
                                </td>

                                @if (!is_null($attributes['UserId']))
                                    <td align="center"><span class="xtooltip" title="This domain is private and is available for the owner user only">Private</span></td>
                                @elseif ($attributes['VerificationStatus'] == 'Success')
                                    <td align="center">
                                        @if (checkEmail($domain))
                                            <input type="checkbox" name="options[emails][]" value="{{ $domain }}" class="switchery"
                                                {{ $attributes['Selected'] ? " checked" : "" }}
                                            />
                                        @else
                                            <input type="checkbox" name="options[domains][]" value="{{ $domain }}" class="switchery"
                                                {{ $attributes['Selected'] ? " checked" : "" }}
                                            />
                                        @endif
                                    </td>
                                @else
                                    <td align="center"></td>
                                @endif
                                <td>
                                    <a href="{{ action('Admin\CustomerController@index') }}" target="_blank">{{ $attributes['UserName'] }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="col-md-6">
            <a href="https://app.sendgrid.com" type="button" target="_blank"
              class="btn btn-mc_default mr-10">
                {{ trans('messages.sending_serbers.go_to_sendgrid_dashboard') }}
            </a>

            <p class="mt-40">
                {{ trans('messages.sending_serbers.sendgrid.allow_verify.intro') }}
            </p>

            @include('helpers.form_control', [
                'type' => 'checkbox2',
                'label' => trans('messages.allow_verify_domain_against_acelle'),
                'name' => 'options[allow_verify_domain_against_acelle]',
                'value' => $server->getOption('allow_verify_domain_against_acelle'),
                'help_class' => 'sending_server',
                'options' => ['no', 'yes'],
            ])

            <hr>
            <div class="mt-20">
                <button class="btn btn-mc_primary mr-10">{{ trans('messages.save') }}</button>
                <a href="{{ action('Admin\SendingServerController@index') }}" type="button" class="btn btn-mc_inline">
                    {{ trans('messages.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
