<?php
    $icons = [
        'warning' =>  'lnr lnr-warning',
        'info' =>  'lnr lnr-checkmark-circle',
        'danger' =>  'lnr lnr-question-circle',
        'error' =>  'lnr lnr-question-circle',
    ];

    if ($level == 'error') {
        $level = 'danger';
    }

    if (isset($params)) {
        $paramsString = implode(' ', collect($params)
            ->map(function($value, $key) { return "$key=\"$value\""; })
            ->values()->all()
        );
    }
?>
<div {!! $paramsString ?? '' !!} title="Click to suppress notification messages" class="alert alert-{{ $level }}" style="display: flex; flex-direction: row; align-items: center; cursor: pointer">
    <div style="margin-right:15px">
        <i class="{{ $icons[$level] }}"></i>
    </div>
    <div>
        @isset($title)
            <h4>{{ $title }}</h4>
        @endisset
        <p>{!! $message !!}</p>
    </div>
</div>
