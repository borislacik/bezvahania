@props (['name', 'label', 'type' => 'text', 'placeholder' => null, 'required' => false])

<div>
    <label for="field-{{ $name }}" class="field-label"
        >{{ $label }}
        @if ($required) *@endif
    </label>
    @if ($type === 'textarea')
        <textarea
            id="field-{{ $name }}"
            name="{{ $name }}"
            rows="3"
            placeholder="{{ $placeholder }}"
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="error-{{ $name }}" @endif
            {{ $attributes->class(['field-input', 'border-error' => $errors->has($name)]) }}
            >{{ old($name) }}</textarea
        >
    @else
        <input
            id="field-{{ $name }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ old($name) }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="error-{{ $name }}" @endif
            {{ $attributes->class(['field-input', 'border-error' => $errors->has($name)]) }}
        />
    @endif
    {{ $slot }}
    @error ($name)
        <p id="error-{{ $name }}" class="field-error">{{ $message }}</p>
    @enderror
</div>
