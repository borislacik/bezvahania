@props (['name', 'label', 'type' => 'text', 'placeholder' => null, 'required' => false, 'rows' => 3, 'idPrefix' => 'field'])

@php
    $id = $idPrefix.'-'.$name;
    $errorId = $idPrefix.'-error-'.$name;
@endphp

<div>
    <label for="{{ $id }}" class="field-label"
        >{{ $label }}
        @if ($required) *@endif
    </label>
    @if ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            {{ $attributes->class(['field-input', 'border-error' => $errors->has($name)]) }}
            >{{ old($name) }}</textarea
        >
    @else
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ old($name) }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            {{ $attributes->class(['field-input', 'border-error' => $errors->has($name)]) }}
        />
    @endif
    {{ $slot }}
    @error ($name)
        <p id="{{ $errorId }}" class="field-error">{{ $message }}</p>
    @enderror
</div>
