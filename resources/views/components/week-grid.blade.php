@props(['periods', 'showTeacher' => true, 'showClass' => false])
@php($today = now()->format('l'))
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach (\App\Http\Controllers\TimetableController::DAYS as $day)
        <section @class(['card overflow-hidden', 'ring-2 ring-primary' => $day === $today])>
            <h2 class="card-head card-title">{{ $day }} @if ($day === $today)<span class="badge badge-blue">Today</span>@endif</h2>
            @if (empty($periods[$day]) || $periods[$day]->isEmpty())
                <p class="px-5 py-5 text-sm text-ink-3">{{ $day === 'Friday' ? 'Weekly holiday' : 'No periods' }}</p>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($periods[$day] as $period)
                        <li class="flex items-center justify-between gap-3 px-5 py-3">
                            <span class="min-w-0">
                                <span class="block text-sm font-bold text-ink">{{ $period->subject_name }}</span>
                                <span class="block text-xs text-ink-2">
                                    @if ($showClass) {{ $period->class_name }} {{ $period->section_name }} · @endif
                                    @if ($showTeacher) {{ $period->teacher_name }} · @endif
                                    Room {{ $period->room }}
                                </span>
                            </span>
                            <span class="whitespace-nowrap text-xs font-semibold text-ink-2">{{ nice_time($period->start_time) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
</div>
