<script>
	var DATATABLE_TRANSLATE_URL = '{{ action('Controller@datatable_locale') }}';
	var JVALIDATE_TRANSLATE_URL = '{{ action('Controller@jquery_validate_locale') }}';
	var APP_URL = '{{ url('/') }}';
	var LANG_OK = '{{ trans('messages.ok') }}';
	var LANG_YES = '{{ trans('messages.yes') }}';
	var LANG_NO = '{{ trans('messages.no') }}';
	var LANG_ARE_YOU_SURE = '{{ trans('messages.are_you_sure') }}';
	var LANG_CANCEL = '{{ trans('messages.cancel') }}';
	var LANG_DELETE_VALIDATE = '{{ trans('messages.delete_validate') }}';
	var LANG_DATE_FORMAT = '{{ trans('messages.j_date_format') }}';
	var LANG_ANY_DATETIME_FORMAT = '{{ trans('messages.any_datetime_format') }}';
	var LANG_J_DATE_FORMAT = '{{ trans('messages.j_date_format') }}';
	var CSRF_TOKEN = "{{ csrf_token() }}";
	var LANG_NOTIFY_SUCCESS = '{{ trans('messages.notify.success') }}';
	var LANG_ALERT = '{{ trans('messages.notify.alert') }}';
	var LANG_CONFIRMATION = '{{ trans('messages.notify.confirmation') }}';
	var LANG_NOTIFY = {
		'success': '{{ trans('messages.notify.success') }}',
		'error': '{{ trans('messages.notify.error') }}',
		'notice': '{{ trans('messages.notify.notice') }}'
	};
	
	$(document).ready(function() {
		$('.xtooltip').tooltipster({
			theme: 'tooltipster-light'
		});
	});
</script>