@props(['title' => 'Nothing here yet', 'text' => null, 'icon' => 'inbox'])
<div class="px-6 py-14 text-center">
    <span class="mx-auto inline-flex size-12 items-center justify-center rounded-full bg-primary-soft text-primary-ink">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-6" />
    </span>
    <p class="mt-3 font-bold text-ink">{{ $title }}</p>
    @if ($text) <p class="mx-auto mt-1 max-w-md text-sm text-ink-2">{{ $text }}</p> @endif
    {{ $slot }}
</div>
