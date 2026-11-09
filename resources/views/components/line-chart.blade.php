@props(['points', 'title', 'max' => null])
@php
    [$w, $h, $l, $r, $t, $b] = [640, 230, 40, 14, 14, 28];
    $count = count($points);
    $ceil = $max ?? max(1, ...array_column($points, 'value'));
    $x = fn ($i) => $l + ($count > 1 ? $i * (($w - $l - $r) / ($count - 1)) : ($w - $l - $r) / 2);
    $y = fn ($v) => $t + ($h - $t - $b) * (1 - $v / $ceil);
    $line = collect($points)->map(fn ($p, $i) => round($x($i), 1).','.round($y($p['value']), 1))->implode(' ');
    $area = $count ? 'M'.round($x(0), 1).','.($h - $b).' L'.$line.' L'.round($x($count - 1), 1).','.($h - $b).' Z' : '';
    $step = $count > 1 ? ($w - $l - $r) / ($count - 1) : $w;
@endphp
@if (! $count)
    <x-empty title="No data yet" icon="chart-line" />
@else
    <div x-data="{ i: null, points: @js($points), n: {{ $count }} }" class="relative">
        <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full text-primary" role="img" aria-label="{{ $title }}" @mouseleave="i = null">
            @foreach ([0, 0.5, 1] as $tick)
                <line x1="{{ $l }}" x2="{{ $w - $r }}" y1="{{ $y($ceil * $tick) }}" y2="{{ $y($ceil * $tick) }}" stroke="var(--line)" stroke-width="1" />
                <text x="{{ $l - 8 }}" y="{{ $y($ceil * $tick) + 4 }}" text-anchor="end" font-size="11" fill="var(--ink-3)">{{ round($ceil * $tick) }}</text>
            @endforeach
            <path d="{{ $area }}" fill="currentColor" opacity=".08" />
            <polyline points="{{ $line }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
            @foreach ($points as $i => $point)
                @if ($i % max(1, intdiv($count, 6)) === 0 || $i === $count - 1)
                    <text x="{{ $x($i) }}" y="{{ $h - 8 }}" text-anchor="middle" font-size="11" fill="var(--ink-3)">{{ $point['label'] }}</text>
                @endif
                <circle cx="{{ $x($i) }}" cy="{{ $y($point['value']) }}" r="4.5" fill="currentColor" stroke="var(--surface)" stroke-width="2" x-show="i === {{ $i }}" />
                <rect x="{{ $x($i) - $step / 2 }}" y="0" width="{{ $step }}" height="{{ $h }}" fill="transparent" @mouseenter="i = {{ $i }}" @click="i = {{ $i }}" />
            @endforeach
        </svg>
        <div x-show="i !== null" x-cloak class="card pointer-events-none absolute top-1 -translate-x-1/2 px-3 py-1.5 text-xs shadow-lg" :style="i !== null && `left:${Math.min(88, Math.max(12, (({{ $l }} + i * {{ $step }}) / {{ $w }}) * 100))}%`">
            <span class="block text-ink-2" x-text="i !== null ? points[i].label : ''"></span>
            <span class="block font-bold text-ink" x-text="i !== null ? points[i].display : ''"></span>
        </div>
        <details class="no-print mt-2 text-xs text-ink-2">
            <summary class="cursor-pointer">View as table</summary>
            <table class="mt-2 w-full"><tbody>@foreach ($points as $p)<tr><td class="py-0.5">{{ $p['label'] }}</td><td class="py-0.5 text-right font-semibold">{{ $p['display'] }}</td></tr>@endforeach</tbody></table>
        </details>
    </div>
@endif
