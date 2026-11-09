@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
    <label class="field-label" for="{{ $name }}">{{ $label }} @if ($required)<span class="text-rose-600">*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="input" @required($required)>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" class="input" @required($required)>
    @endif
    @error($name) <p class="field-error">{{ $message }}</p> @enderror
</div>
