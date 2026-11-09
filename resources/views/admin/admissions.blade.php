@extends('layouts.dashboard')
@section('title', 'Admissions')
@section('content')
<div x-data="{ approving: null }">
    <x-page-head title="Admission applications" subtitle="Applications submitted through the public form.">
        <a class="btn" href="{{ route('apply') }}" target="_blank" rel="noopener"><x-lucide-external-link class="size-4" /> Public form</a>
    </x-page-head>

    <nav class="mb-4 flex flex-wrap gap-1.5" aria-label="Application status">
        @foreach (['Pending', 'Approved', 'Rejected', 'All'] as $tab)
            <a href="{{ route('admin.admissions.index', ['status' => $tab]) }}" @class(['btn btn-sm', 'btn-primary' => $status === $tab])>{{ $tab }} @if($tab !== 'All')({{ $counts[$tab] ?? 0 }})@endif</a>
        @endforeach
    </nav>

    <div class="card overflow-hidden">
        @if ($applications->isEmpty())
            <x-empty title="No applications" text="New applications from the public form will show up here." icon="clipboard-list" />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th scope="col">Applicant</th><th scope="col">Class</th><th scope="col">Guardian</th><th scope="col">Applied</th><th scope="col">Status</th><th scope="col" class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($applications as $a)
                            <tr>
                                <td><span class="block font-semibold text-ink">{{ $a->name }}</span><span class="block text-xs text-ink-3">{{ $a->reference }}</span></td>
                                <td>{{ $a->class_name }}</td>
                                <td><span class="block">{{ $a->guardian_name }}</span><span class="block text-xs text-ink-3">{{ $a->phone }}</span></td>
                                <td>{{ nice_date($a->created_at) }}</td>
                                <td><span class="badge {{ status_badge($a->status) }}">{{ $a->status }}</span></td>
                                <td class="whitespace-nowrap text-right">
                                    @if ($a->status === 'Pending')
                                        <button type="button" class="btn btn-sm btn-primary" @click="approving = @js(['id' => $a->id, 'name' => $a->name, 'sections' => $sections->get($a->class_id, collect())->values()])">Approve</button>
                                        <form method="POST" action="{{ route('admin.admissions.reject', $a->id) }}" class="inline">@csrf<button class="btn btn-sm">Reject</button></form>
                                    @elseif ($a->student_id)
                                        <a class="btn btn-sm" href="{{ route('admin.students.show', $a->student_id) }}">View student</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($applications->hasPages())<div class="border-t border-line px-5 py-3">{{ $applications->links() }}</div>@endif
        @endif
    </div>

    <div x-show="approving" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true">
        <form method="POST" :action="approving && `{{ url('admin/admissions') }}/${approving.id}/approve`" class="card w-full max-w-sm p-6">
            @csrf
            <h2 class="text-lg font-bold">Approve <span x-text="approving?.name"></span></h2>
            <p class="mt-1 text-sm text-ink-2">This creates the student record and a sign-in.</p>
            <label class="field-label mt-4" for="approve-section">Section</label>
            <select id="approve-section" name="section_id" class="input" required><template x-for="s in approving?.sections ?? []" :key="s.id"><option :value="s.id" x-text="s.name"></option></template></select>
            <div class="mt-5 flex justify-end gap-2"><button type="button" class="btn" @click="approving = null">Cancel</button><button class="btn btn-primary">Approve</button></div>
        </form>
    </div>
</div>
@endsection
