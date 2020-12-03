@if (isset($unlimited_check))
    <div class="row">
        <div class="col-md-6">
@endif
            <div {{ isset($id) ? "id=" . $id : 'class="autofill"' }}>
                <input
                    header="{{ isset($header) ? $header : "" }}"
                    empty-message="{{ isset($empty) ? $empty : "" }}"
                    error-message='{{ isset($error) ? $error : "" }}'
                    autocomplete="new-password"
                    {{ isset($disabled) && $disabled == true ? ' disabled="disabled"' : "" }}
                    id="{{ $name }}" placeholder="{{ isset($placeholder) ? $placeholder : "" }}"
                    value="{{ isset($value) ? $value : "" }}"
                    data-url="{{ isset($url) ? $url : "" }}"
                    type="text"
                    name="{{ $name }}"
                    class="form-control{{ $classes }}  {{ isset($class) ? $class : "" }} autofill-input"
                    {!! isset($default_value) ? 'default-value="'.$default_value.'"' : '' !!}
                    {{ isset($readonly) && $readonly ? "readonly=readonly" : "" }}
                >
            </div>
@if (isset($unlimited_check))
        </div>
        <div class="col-md-6">
            <div class="checkbox inline unlimited-check text-semibold">
                <label>
                    <input{{ $value  == -1 ? " checked=checked" : "" }} type="checkbox" class="styled">
                    {{ trans('messages.unlimited') }}
                </label>
            </div>
        </div>
    </div>
@endif
