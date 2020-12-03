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
                    <span class="label label-flat bg-ended square-tag">{{ $identity['type'] }}</span>
                </span>
            </td>
            <td width="20%">
                {{ $identity['name'] }}
            </td>
            <td>{{ $identity['value'] }}</td>
            <td class="text-right" width="1%">
                @if ($domain->domainVerified())
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

        @foreach ($dkims as $dkim)
        <tr>
            <td>
                <span class="text-muted2 list-status pull-left">
                    <span class="label label-flat bg-ended square-tag">{{ $dkim['type'] }}</span>
                </span>
            </td>
            <td>
                {{ $dkim['name'] }}
            </td>
            <td width="60%" style="word-wrap:break-word;word-break:break-all;">{{ $dkim['value'] }}</td>
            <td class="text-right">
                @if ($domain->dkimVerified())
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
        @endforeach

        @foreach ($spf as $r)
        <tr>
            <td>
                <span class="text-muted2 list-status pull-left">
                    <span class="label label-flat bg-ended square-tag">{{ $r['type'] }}</span>
                </span>
            </td>
            <td>
                {{ $r['name'] }}
            </td>
            <td>{{ $r['value'] }}</td>
            <td class="text-right">
                @if ($domain->spfVerified())
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
        @endforeach
    </tbody>
</table>
