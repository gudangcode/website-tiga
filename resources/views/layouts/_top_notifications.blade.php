<li class="dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="display: flex;
    align-items: center;
    height: 52px;">
		<i class="lnr lnr-alarm top-notification-icon"></i> 
		<span class="visible-xs-inline-block position-right">{{ trans('messages.activity_log') }}</span>
		@if (Acelle\Model\Notification::count())
			{{-- <span class="badge badge-danger top-notification-alert">!</span> --}}
			<i class="material-icons-outlined tabs-warning-icon text-danger top-notification-alert">info</i>
		@endif
	</a>
	
	<div class="dropdown-menu dropdown-content width-350">
		<div class="dropdown-content-heading">
			{{ trans('messages.activity_log') }}						
		</div>

		<ul class="media-list dropdown-content-body top-history top-notifications">
			@if (Auth::user()->admin->notifications()->count() == 0)
				<li class="text-center text-muted2">
					<span href="#">
						<i class="lnr lnr-bubble"></i> {{ trans('messages.no_notifications') }}
					</span>
				</li>
			@endif
			@foreach (Auth::user()->admin->notifications()->take(20)->get() as $notification)
				<li class="media">
					<div class="media-left">
						@if ($notification->level == \Acelle\Model\Notification::LEVEL_WARNING)
							<i class="lnr lnr-warning bg-warning"></i>
						@elseif ( false &&$notification->level == \Acelle\Model\Notification::LEVEL_ERROR)
							<i class="lnr lnr-cross bg-danger"></i>
						@else
							<i class="lnr lnr-menu bg-info"></i>
						@endif
					</div>

					<div class="media-body">
						<a href="#" class="media-heading">
							<span class="text-semibold">{{ $notification->title }}</span>
							<span class="media-annotation pull-right">{{ $notification->created_at->diffForHumans() }}</span>
						</a>

						<span class="text-muted desc text-muted" title='{!! $notification->message !!}'>{{ $notification->message }}</span>
					</div>
				</li>
			@endforeach
			
		</ul>
		
		<div class="dropdown-content-footer">
			<a href="{{ action("Admin\NotificationController@index") }}" data-popup="tooltip" title="{{ trans('messages.all_notifications') }}"><i class="icon-menu display-block"></i></a>
		</div>
	</div>
</li>