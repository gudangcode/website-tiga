<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') - {{ \Acelle\Model\Setting::get("site_name") }}</title>

	@include('layouts._favicon')

	@include('layouts._head')

	@include('layouts._css')

	@include('layouts._js')
	
	<!-- Custom langue -->
	<script>
		var LANG_CODE = 'en-US';
	</script>
	@if (Auth::user()->customer->getLanguageCodeFull())
		<script type="text/javascript" src="{{ URL::asset('assets/datepicker/i18n/datepicker.' . Auth::user()->customer->getLanguageCodeFull() . '.js') }}"></script>
		<script>
			LANG_CODE = '{{ Auth::user()->customer->getLanguageCodeFull() }}';
		</script>
	@endif

	<script>
		$.cookie('last_language_code', '{{ Auth::user()->customer->getLanguageCode() }}');
	</script>

</head>

<body class="navbar-top color-scheme-{{ Auth::user()->customer->getColorScheme() }}">

	<!-- Main navbar -->
	<div class="navbar navbar-{{ Auth::user()->customer->getColorScheme() == "white" ? "default" : "inverse" }} navbar-fixed-top">
		<div class="navbar-header">
			<a class="navbar-brand" href="{{ action('HomeController@index') }}">
				@if (\Acelle\Model\Setting::get('site_logo_small'))
                    <img src="{{ action('SettingController@file', \Acelle\Model\Setting::get('site_logo_small')) }}" alt="">
                @else
                    <img src="{{ URL::asset('images/default_site_logo_small_' . (Auth::user()->customer->getColorScheme() == "white" ? "dark" : "light") . '.png') }}" alt="">
                @endif
			</a>

			<ul class="nav navbar-nav pull-right visible-xs-block">
				<li><a class="mobile-menu-button" data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-menu7"></i></a></li>
			</ul>
		</div>

		<div class="navbar-collapse collapse" id="navbar-mobile">
			<ul class="nav navbar-nav">
				<li rel0="HomeController">
					<a href="{{ action('HomeController@index') }}">
						<i class="icon-home"></i> {{ trans('messages.dashboard') }}
					</a>
				</li>
				<li rel0="CampaignController">
					<a href="{{ action('CampaignController@index') }}">
						<i class="icon-paperplane"></i> {{ trans('messages.campaigns') }}
					</a>
				</li>
				<li rel0="Automation2Controller">
					<a href="{{ action('Automation2Controller@index') }}">
						<i class="icon-alarm-check"></i> {{ trans('messages.Automations') }}
					</a>
				</li>
				<li
					rel0="MailListController"
					rel1="FieldController"
					rel2="SubscriberController"
					rel3="SegmentController"
				>
					<a href="{{ action('MailListController@index') }}"><i class="icon-address-book2"></i> {{ trans('messages.lists') }}</a>
				</li>
                <li rel0="TemplateController">
					<a href="{{ action('TemplateController@index') }}">
						<i class="icon-magazine"></i> {{ trans('messages.templates') }}
					</a>
				</li>
				@if (
					Auth::user()->customer->can("read", new Acelle\Model\SendingServer()) ||					
                    Auth::user()->customer->can("read", new Acelle\Model\EmailVerificationServer()) ||
					Auth::user()->customer->can("read", new Acelle\Model\Blacklist()) ||
					true
				)
					<li class="dropdown language-switch"
						rel0="SendingServerController"
						rel1="SendingDomainController"
						rel2="SenderController"
                        rel3="EmailVerificationServerController"
						rel4="BlacklistController"
					>
						<a class="dropdown-toggle" data-toggle="dropdown">
							<i class="glyphicon glyphicon-transfer"></i> {{ trans('messages.sending') }}
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							@if (Auth::user()->customer->can("read", new Acelle\Model\SendingServer()))
								<li rel0="SendingServerController">
									<a href="{{ action('SendingServerController@index') }}">
										<i class="icon-server"></i> {{ trans('messages.sending_servers') }}
									</a>
								</li>
							@endif
							<li rel0="SenderController" rel1="SendingDomainController">
								<a href="{{ action('SenderController@index') }}">
									<i class="icon-user-check"></i> {{ trans('messages.verified_senders') }}
								</a>
							</li>
							<li rel0="TrackingDomainController">
								<a href="{{ action('TrackingDomainController@index') }}">
									<i class="icon-earth"></i> {{ trans('messages.tracking_domains') }}
								</a>
							</li>
                            @if (Auth::user()->customer->can("read", new Acelle\Model\EmailVerificationServer()))
								<li rel0="EmailVerificationServerController">
									<a href="{{ action('EmailVerificationServerController@index') }}">
										<i class="icon-database-check"></i> {{ trans('messages.email_verification_servers') }}
									</a>
								</li>
							@endif
							@if (Auth::user()->customer->can("read", new Acelle\Model\Blacklist()))
								<li rel0="BlacklistController">
									<a href="{{ action('BlacklistController@index') }}">
										<i class="glyphicon glyphicon-minus-sign"></i> {{ trans('messages.blacklist') }}
									</a>
								</li>
							@endif
						</ul>
					</li>
				@endif
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<!--<li class="dropdown language-switch">
					<a class="dropdown-toggle" data-toggle="dropdown">
						{{ Acelle\Model\Language::getByCode(Config::get('app.locale'))->name }}
						<span class="caret"></span>
					</a>

					<ul class="dropdown-menu">
						@foreach(Acelle\Model\Language::getAll() as $language)
							<li class="{{ Acelle\Model\Language::getByCode(Config::get('app.locale'))->code == $language->code ? "active" : "" }}">
								<a>{{ $language->name }}</a>
							</li>
						@endforeach
					</ul>
                </li>-->

				<!--<li class="dropdown">
					<a href="#" class="dropdown-toggle top-quota-button" data-toggle="dropdown" data-url="{{ action("AccountController@quotaLog") }}">
						<i class="icon-stats-bars4"></i>
						<span class="visible-xs-inline-block position-right">{{ trans('messages.used_quota') }}</span>
					</a>
				</li>-->

				@include('layouts._top_activity_log')

				<li class="dropdown dropdown-user">
					<a class="dropdown-toggle" data-toggle="dropdown">
						<img src="{{ action('CustomerController@avatar', Auth::user()->customer->uid) }}" alt="">
						<span>{{ Auth::user()->customer->displayName() }}</span>
						<i class="caret"></i>

						@if (Auth::user()->customer->hasSubscriptionNotice())
							<i class="material-icons customer-warning-icon text-danger">info</i>
						@endif
					</a>

					<ul class="dropdown-menu dropdown-menu-right">
						@can("admin_access", Auth::user())
							<li><a href="{{ action("Admin\HomeController@index") }}"><i class="icon-enter2"></i> {{ trans('messages.admin_view') }}</a></li>
							<li class="divider"></li>
						@endif
						@if (request()->user()->customer->activeSubscription())
							<li class="dropdown">
								<a href="#" class="top-quota-button" data-url="{{ action("AccountController@quotaLog") }}">
									<i class="icon-stats-bars4"></i>
									<span class="">{{ trans('messages.used_quota') }}</span>
								</a>
							</li>
						@endif
						<li rel0="AccountSubscriptionController\index">
							<a href="{{ action('AccountSubscriptionController@index') }}">
								<i class="icon-quill4"></i> {{ trans('messages.subscriptions') }}
								@if (Auth::user()->customer->hasSubscriptionNotice())
									<i class="material-icons-outlined subscription-warning-icon text-danger">info</i>
								@endif
							</a>
						</li>
						<li><a href="{{ action("AccountController@profile") }}"><i class="icon-profile"></i> {{ trans('messages.account') }}</a></li>
						@if (Auth::user()->customer->canUseApi())
							<li rel0="AccountController/api">
								<a href="{{ action("AccountController@api") }}" class="level-1">
									<i class="icon-key position-left"></i> {{ trans('messages.api') }}
								</a>
							</li>
						@endif
						<li><a href="{{ url("/logout") }}"><i class="icon-switch2"></i> {{ trans('messages.logout') }}</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	<!-- /main navbar -->

	<!-- Page header -->
	<div class="page-header">
		<div class="page-header-content">

			@yield('page_header')

		</div>
	</div>
	<!-- /page header -->

	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- display flash message -->
				@include('common.errors')

				<!-- main inner content -->
				@yield('content')

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->


		<!-- Footer -->
		<div class="footer text-muted">
			{!! trans('messages.copy_right') !!}
		</div>
		<!-- /footer -->

	</div>
	<!-- /page container -->

	@include("layouts._modals")

        {!! \Acelle\Model\Setting::get('custom_script') !!}

</body>
</html>
