@props(['video'])
@php
    preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]{6,})~', $video->src, $youtube);
    $poster = $video->poster ? asset($video->poster) : null;
@endphp
<div class="overflow-hidden rounded-2xl bg-white/10">
    @if ($youtube)
        <iframe class="aspect-video w-full" src="https://www.youtube-nocookie.com/embed/{{ $youtube[1] }}" title="{{ $video->title }}" loading="lazy" allowfullscreen></iframe>
    @else
        <video class="aspect-video w-full bg-black object-cover" controls preload="none" @if($poster) poster="{{ $poster }}" @endif aria-label="{{ $video->title }}">
            <source src="{{ $video->src }}">
            Your browser cannot play this video.
        </video>
    @endif
    <p class="px-3 py-2.5 text-sm font-semibold text-white">{{ $video->title }}</p>
</div>
