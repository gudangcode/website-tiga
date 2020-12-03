@extends('layouts.none')

@section('content')

    @include('layouts._css')
    @include('layouts._js')

    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8 text-center" style="margin-top: 30vh">
            <div class="mb-40">
                <img src="{{ url('images/loading.gif') }}" />
            </div>
            <h1 class="text-semibold">{!! trans('messages.subscription.checkout.processing_payment') !!}</h1>

            <div class="sub-section">
                <div class="row">
                    <div class="col-md-12">


                        <p class="text-muted">{!! trans('messages.subscription.checkout.processing_payment.intro') !!}</p>

                        <a id="pay_now" style="display: none" link-method="POST"
                            class="btn btn-mc_primary"
                            href="{{ action('AccountSubscriptionController@pay') }}">
                            {{ trans('messages.payment.pay_now') }}
                        </a>

                        <script>
                            setTimeout(function() { $('#pay_now').click() }, 2000);
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
