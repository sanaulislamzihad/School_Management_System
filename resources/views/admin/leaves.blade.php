@extends('layouts.dashboard')
@section('title', $title)
@section('content')
<x-page-head :title="$title" :subtitle="$subtitle" />
<div class="card overflow-hidden">
    @if ($leaves->isEmpty())
        <x-empty title="No leave requests" icon="calendar-off" />
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th scope="col">Name</th><th scope="col">Type</th><th scope="col">Dates</th><th scope="col">Reason</th><th scope="col">Status</th><th scope="col" class="text-right">Decision</th></tr></thead>
                <tbody>
                    @foreach ($leaves as $l)
                        <tr>
                            <td><span class="block font-semibold text-ink">{{ $l->person }}</span><span class="block text-xs text-ink-3">{{ $l->detail }}</span></td>
                            <td>{{ $l->type }}</td>
                            <td class="whitespace-nowrap">{{ nice_date($l->from_date) }} to {{ nice_date($l->to_date) }}</td>
                            <td>{{ $l->reason }}</td>
                            <td><span class="badge {{ status_badge($l->status) }}">{{ $l->status }}</span></td>
                            <td class="whitespace-nowrap text-right">
                                @if ($l->status === 'Pending')
                                    <form method="POST" action="{{ route($decideRoute, [$l->id, 'approve']) }}" class="inline">@csrf<button class="btn btn-sm btn-primary">Approve</button></form>
                                    <form method="POST" action="{{ route($decideRoute, [$l->id, 'reject']) }}" class="inline">@csrf<button class="btn btn-sm">Reject</button></form>
                                @else
                                    <span class="text-ink-3">Done</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($leaves->hasPages())<div class="border-t border-line px-5 py-3">{{ $leaves->links() }}</div>@endif
    @endif
</div>
@endsection
