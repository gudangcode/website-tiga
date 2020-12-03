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

</head>

<body>
    @yield('content')
</body>
</html>
