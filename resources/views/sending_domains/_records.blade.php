<table class="table table-trans tbody-white" class="table-layout:fixed">
    <thead>
        <tr>
            <th class="trans-upcase text-semibold">{{ trans('messages.type') }}</th>
            <th class="trans-upcase text-semibold">{{ trans('messages.host') }}</th>
            <th class="trans-upcase text-semibold">{{ trans('messages.value') }}</th>
            <th></th>
        </tr>
    </thead>
    <tbody class="bg-white">
        <tr>
            <td width="1%">
                <span class="text-muted2 list-status pull-left">
                    <span class="label label-flat bg-pending square-tag">TXT</span>
                </span>
            </td>
            <td width="20%">
                {{ $server->getFullVerificationHostName() }}
            </td>
            <td>{{ doublequote($server->verification_token) }}</td>
            <td class="text-right" width="1%">
                @if ($server->domainVerified())
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-active">{{ trans('messages.sending_domain.verified') }}</span>
                    </span>
                @else
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-inactive">{{ trans('messages.sending_domain.pending') }}</span>
                    </span>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <span class="text-muted2 list-status pull-left">
                    <span class="label label-flat bg-pending square-tag">TXT</span>
                </span>
            </td>
            <td>
                {{ $server->getFullDkimHostName() }}
            </td>
            <td><textarea style="width:100%;border:0;height:100px;resize:none;">{{ \Acelle\Model\Setting::isYes('escape_dkim_dns_value') ? $server->getEscapedDnsDkimConfig() : $server->getDnsDkimConfig() }}</textarea></td>
            <td class="text-right">
                @if ($server->dkimVerified())
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-active">{{ trans('messages.sending_domain.verified') }}</span>
                    </span>
                @else
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-inactive">{{ trans('messages.sending_domain.pending') }}</span>
                    </span>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <span class="text-muted2 list-status pull-left">
                    <span class="label label-flat bg-pending square-tag">TXT</span>
                </span>
            </td>
            <td>{{ $server->getDnsHostName() }}</td>
            <td>{{ $server->getQuotedSpf() }}</td>
            <td class="text-right">
                @if ($server->spfVerified())
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-active">{{ trans('messages.sending_domain.verified') }}</span>
                    </span>
                @else
                    <span class="text-muted2 list-status pull-left">
                        <span class="label label-flat bg-inactive">{{ trans('messages.sending_domain.pending') }}</span>
                    </span>
                @endif
            </td>
        </tr>
    </tbody>
</table>
