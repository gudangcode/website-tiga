<div class="sub-section">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <h2 class="text-semibold">{{ trans('messages.subscription.logs_transactions') }}</h2>
            <p>{{ trans('messages.subscription.logs.intro') }}</p>

            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#logs">{{ trans('messages.subscription.logs') }}</a></li>
                <li><a data-toggle="tab" href="#transactions">{{ trans('messages.subscription.transactions') }}</a></li>
            </ul>

            <div class="tab-content">
                <div id="logs" class="tab-pane fade in active">
                    <table class="table table-box pml-table table-log mt-10">
                        <tr>
                            <th width="200px">{{ trans('messages.subscription.log.created_at') }}</th>
                            <th>{{ trans('messages.subscription.log.message') }}</th>
                        </tr>
                        @forelse ($subscription->getLogs() as $key => $log)
                            <tr>
                                <td>
                                    <span class="no-margin kq_search">
                                        {{ Acelle\Library\Tool::formatDateTime($log->created_at) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="no-margin kq_search">
                                        {!! trans('cashier::messages.subscription.log.' . $log->type, $log->getData()) !!}
                                    </span>
                                </td>                                
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="5">
                                    {{ trans('messages.subscription.logs.empty') }}
                                </td>
                            </te>
                        @endforelse
                    </table>
                </div>
                <div id="transactions" class="tab-pane fade">
                    <table class="table table-box pml-table table-log mt-10">
                        <tr>
                            <th width="130px">{{ trans('messages.invoice.created_at') }}</th>
                            <th>{{ trans('messages.invoice.title') }}</th>
                            <th>{{ trans('messages.invoice.amount') }}</th>
                            <th>{{ trans('messages.invoice.status') }}</th>
                            <th>{{ trans('messages.invoice.action') }}</th>
                        </tr>
                        @forelse ($subscription->getTransactions() as $key => $invoice)
                            <tr>
                                <td>
                                    <span class="no-margin kq_search">
                                        {{ Acelle\Library\Tool::formatDate($invoice->created_at) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="no-margin kq_search">
                                        {!! $invoice->title !!}
                                    </span>
                                    @if ($invoice->description)
                                        <div class="small text-muted">{!! $invoice->description !!}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="no-margin kq_search">
                                        {{ $invoice->amount }}
                                    </span>
                                </td>
                                <td>
                                    <span class="no-margin kq_search">
                                        <span class="label label-success bg-{{ $invoice->status }}" style="white-space: nowrap;">
                                            {{ str_replace('_', ' ', $invoice->status) }}
                                        </span>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-mc_default btn-disabled" disabled>
                                        {{ trans('messages.edit') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="5">
                                    {{ trans('messages.subscription.logs.empty') }}
                                </td>
                            </te>
                        @endforelse
                    </table>
                </div>
            </div>


            
        </div>
    </div>
</div>