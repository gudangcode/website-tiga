<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')

	@include('layouts._css')

	@include('layouts._js')

	<script>
		$.cookie('last_language_code', '{{ Auth::user()->customer->getLanguageCode() }}');
	</script>
        
    <style>
        html {
            overflow: hidden;
        }
    </style>

</head>

<body>
    
    <div class="iframe-modal-header">
        <div class="title">@yield('title')</div>
        <div class="close" onclick="parent.GlobalIframeModal.hide()"><i class="lnr lnr-cross"></i></div>
    </div>
    
    <div class="iframe-modal-body">
        <!-- display flash message -->
        @include('common.errors')
    
        <!-- main inner content -->
        @yield('content')
    </div>

</body>
</html>
