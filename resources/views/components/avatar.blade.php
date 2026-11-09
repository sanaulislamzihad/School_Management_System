@props(['name', 'photo' => null])
@if ($photo)
    <img src="{{ asset($photo) }}" alt="" {{ $attributes->merge(['class' => 'rounded-full object-cover shrink-0']) }}>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-primary-soft text-primary-ink font-bold text-xs shrink-0']) }}>{{ initials($name) }}</span>
@endif
