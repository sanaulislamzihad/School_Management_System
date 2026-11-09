@props(['label', 'value', 'sub' => null, 'icon', 'tone' => 'indigo', 'href' => null])
@php($tag = $href ? 'a' : 'div')
<{{ $tag }} @if($href) href="{{ $href }}" @endif class="card flex items-start gap-4 p-5 transition-shadow {{ $href ? 'hover:shadow-md' : '' }}">
    <span class="tone-{{ $tone }} inline-flex size-11 shrink-0 items-center justify-center rounded-xl">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-5" />
    </span>
    <span class="min-w-0">
        <span class="block text-[13px] font-medium text-ink-2">{{ $label }}</span>
        <span class="mt-0.5 block text-2xl font-extrabold tracking-tight text-ink">{{ $value }}</span>
        @if ($sub) <span class="mt-0.5 block text-xs text-ink-3">{{ $sub }}</span> @endif
    </span>
</{{ $tag }}>
