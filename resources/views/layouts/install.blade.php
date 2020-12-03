<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - Acelle Installation</title>
	
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="apple-touch-icon" sizes="57x57" href="{{ URL::asset('favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ URL::asset('favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ URL::asset('favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ URL::asset('favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ URL::asset('favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ URL::asset('favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ URL::asset('favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ URL::asset('favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ URL::asset('favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{ URL::asset('favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ URL::asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ URL::asset('favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ URL::asset('favicon/favicon-16x16.png') }}">

	@include('layouts._css')
	
	@include('layouts._js')

</head>

<body class="bg-slate-800">

	<!-- Page container -->
	<div class="page-container login-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">
				<div class="row">
					<div class="col-sm-1 col-md-1">
						
					</div>
					<div class="col-sm-10 col-md-10">
					
						<div class="text-center login-header">
							<a class="main-logo-big" href="{{ action('HomeController@index') }}">
								<img src="{{ URL::asset('images/logo_big.png') }}" alt="">
							</a>
                            
                            <h3 class="text-center text-muted2" style="color: #ccc">{{ trans('messages.installation') }}</h3>
						</div>
                        
                        @include('install._steps')
                        
                        <div class="panel panel-flat" style="border-radius: 0 0 3px 3px">
                            <div class="panel-body">
								@if (count($errors) > 0)
									<div class="alert alert-danger alert-noborder">
										@foreach ($errors->all() as $key => $error)
											<p class="text-semibold">{{ $error }}</p>
										@endforeach
									</div>
								@endif

                                @yield('content')
                            </div>
						</div>
					</div>
				</div>
			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->


		<!-- Footer -->
		<div class="footer text-white">
			{!! trans('messages.copy_right_light') !!}			
		</div>
		<!-- /footer -->

	</div>
	<!-- /page container -->

</body>
</html>
