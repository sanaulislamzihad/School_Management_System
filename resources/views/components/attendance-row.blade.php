@props(['id', 'value' => null])
@php($current = $value ?? 'Present')
<div class="inline-flex overflow-hidden rounded-lg border border-line" role="radiogroup" aria-label="Attendance">
    @foreach (['Present' => 'P', 'Absent' => 'A', 'Late' => 'L', 'Leave' => 'V'] as $status => $letter)
        <label class="cursor-pointer" title="{{ $status }}">
            <input type="radio" class="peer sr-only" name="status[{{ $id }}]" value="{{ $status }}" @checked($current === $status) data-status="{{ $status }}">
            <span class="block px-3 py-1.5 text-[13px] font-semibold text-ink-2 peer-checked:bg-primary peer-checked:text-white peer-focus-visible:outline-2"><span aria-hidden="true">{{ $letter }}</span><span class="sr-only">{{ $status }}</span></span>
        </label>
    @endforeach
</div>
