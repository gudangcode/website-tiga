<script>
    @foreach (['success'] as $msg)
        @if(Session::has('alert-' . $msg))
            $(document).ready(function() {
                notify("{{ $msg }}", '{{ trans('messages.notify.' . $msg)}}', '{!! preg_replace('/[\r\n]+/', ' ', Session::get('alert-' . $msg)) !!}');
            });

        @endif
    @endforeach

    @if (request()->session()->get('user-activated'))
        $(document).ready(function() {
            // Success alert
            swal({
                title: "{!! request()->session()->get('user-activated') !!}",
                text: "",
                confirmButtonColor: "#00695C",
                type: "success",
                allowOutsideClick: true,
                confirmButtonText: "{{ trans('messages.ok') }}",
                customClass: "swl-success",
                html:true
            });

        });
        <?php request()->session()->forget('user-activated'); ?>
    @endif
</script>
