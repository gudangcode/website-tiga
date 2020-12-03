@if (!isset($disabled) || $disabled === false)
    <input type="hidden" name="{{ $name }}" value="{{ $options[0] }}" />
@endif

<div class="checkbox inline unlimited-check text-semibold {{ isset($class) ? $class : "" }} {{ isset($disabled) && $disabled == true ? ' disabled' : "" }}">
    <label>
        <input {{ $value == $options[1] ? " checked" : "" }}
            {{ !isset($value) && isset($default_value) && $default_value == $options[1] ? " checked" : "" }}
            {{ isset($disabled) && $disabled == true ? ' disabled="disabled"' : "" }}
            name="{{ $name }}" value="{{ $options[1] }}"
            class="styled {{ $classes }}  {{ isset($class) ? $class : "" }}"
            type="checkbox" class="styled">
        {!! $label !!}
    </label>
</div>
