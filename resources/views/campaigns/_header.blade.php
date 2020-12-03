<div class="page-title">
	<ul class="breadcrumb breadcrumb-caret position-right">
		<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
		<li><a href="{{ action("CampaignController@index") }}">{{ trans('messages.campaigns') }}</a></li>
	</ul>
	<h1>
		<span class="text-semibold">{{ $campaign->name }}</span>
	</h1>
</div>
