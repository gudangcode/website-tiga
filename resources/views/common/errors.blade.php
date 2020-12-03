@if (count($errors) > 0)
    <!-- Form Error List -->
    <div class="alert alert-danger alert-noborder">
        <button data-dismiss="alert" class="close" type="button"><span>×</span><span class="sr-only">Close</span></button>
        <strong>{{ trans('messages.check_entry_try_again') }}</strong>

        <br><br>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@foreach (['danger', 'warning', 'info', 'error'] as $msg)
    @php
        $class = $msg;
        if ($msg == 'error') {
            $class = 'danger';
        }
    @endphp
    @if(Session::has('alert-' . $msg))
        <!-- Form Error List -->
        <div class="alert alert-{{ $class }} alert-noborder">
            <button data-dismiss="alert" class="close" type="button"><span>×</span><span class="sr-only">Close</span></button>
            <strong>{{ trans('messages.' . $msg) }}</strong>

            <br>

            <p>{!! preg_replace('/[\r\n]+/', ' ', Session::get('alert-' . $msg)) !!}</p>
        </div>
    @endif    
@endforeach
