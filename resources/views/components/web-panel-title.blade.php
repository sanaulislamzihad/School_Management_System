@props(['icon', 'title', 'tone' => 'indigo'])
@php
    $tones = ['indigo' => 'bg-indigo-100 text-indigo-700', 'teal' => 'bg-teal-100 text-teal-700', 'amber' => 'bg-amber-100 text-amber-700', 'rose' => 'bg-rose-100 text-rose-700', 'violet' => 'bg-violet-100 text-violet-700'];
@endphp
<h2 class="flex items-center gap-2.5 text-lg font-bold text-navy">
    <span class="{{ $tones[$tone] }} inline-flex size-9 items-center justify-center rounded-xl"><x-dynamic-component :component="'lucide-'.$icon" class="size-5" /></span>
    {{ $title }}
</h2>
