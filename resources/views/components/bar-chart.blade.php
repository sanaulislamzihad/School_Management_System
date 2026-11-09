@props(['points', 'title'])
@php
    [$w, $h, $l, $r, $t, $b] = [640, 230, 64, 14, 14, 28];
    $count = count($points);
    $ceil = max(1, ...array_column($points, 'value'));
    $magnitude = 10 ** floor(log10($ceil));
    $ceil = ceil($ceil / $magnitude) * $magnitude;
    $band = ($w - $l - $r) / max(1, $count);
    $barWidth = min(34, $band * 0.5);
    $y = fn ($v) => $t + ($h - $t - $b) * (1 - $v / $ceil);
    $base = $h - $b;
@endphp
<div x-data="{ i: null, points: @js($points) }" class="relative">
    <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full text-primary" role="img" aria-label="{{ $title }}" @mouseleave="i = null">
        @foreach ([0, 0.5, 1] as $tick)
            <line x1="{{ $l }}" x2="{{ $w - $r }}" y1="{{ $y($ceil * $tick) }}" y2="{{ $y($ceil * $tick) }}" stroke="var(--line)" stroke-width="1" />
            <text x="{{ $l - 8 }}" y="{{ $y($ceil * $tick) + 4 }}" text-anchor="end" font-size="11" fill="var(--ink-3)">{{ number_format($ceil * $tick) }}</text>
        @endforeach
        @foreach ($points as $i => $point)
            @php
                $cx = $l + $band * ($i + 0.5);
                $top = $y($point['value']);
                $radius = min(4, $base - $top);
                $left = $cx - $barWidth / 2;
                $right = $cx + $barWidth / 2;
            @endphp
            <path d="M{{ $left }},{{ $base }} V{{ $top + $radius }} Q{{ $left }},{{ $top }} {{ $left + $radius }},{{ $top }} H{{ $right - $radius }} Q{{ $right }},{{ $top }} {{ $right }},{{ $top + $radius }} V{{ $base }} Z" fill="currentColor" :opacity="i === null || i === {{ $i }} ? 1 : .45" />
            <text x="{{ $cx }}" y="{{ $h - 8 }}" text-anchor="middle" font-size="11" fill="var(--ink-3)">{{ $point['label'] }}</text>
            <rect x="{{ $l + $band * $i }}" y="0" width="{{ $band }}" height="{{ $h }}" fill="transparent" @mouseenter="i = {{ $i }}" @click="i = {{ $i }}" />
        @endforeach
    </svg>
    <div x-show="i !== null" x-cloak class="card pointer-events-none absolute top-1 -translate-x-1/2 px-3 py-1.5 text-xs shadow-lg" :style="i !== null && `left:${Math.min(88, Math.max(12, (({{ $l }} + {{ $band }} * (i + .5)) / {{ $w }}) * 100))}%`">
        <span class="block text-ink-2" x-text="i !== null ? points[i].label : ''"></span>
        <span class="block font-bold text-ink" x-text="i !== null ? points[i].display : ''"></span>
    </div>
    <details class="no-print mt-2 text-xs text-ink-2">
        <summary class="cursor-pointer">View as table</summary>
        <table class="mt-2 w-full"><tbody>@foreach ($points as $p)<tr><td class="py-0.5">{{ $p['label'] }}</td><td class="py-0.5 text-right font-semibold">{{ $p['display'] }}</td></tr>@endforeach</tbody></table>
    </details>
</div>
