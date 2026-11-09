@props(['title', 'subtitle' => null])
<div class="no-print mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ $title }}</h1>
        @if ($subtitle) <p class="mt-1 text-sm text-ink-2">{{ $subtitle }}</p> @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
