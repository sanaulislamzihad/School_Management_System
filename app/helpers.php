<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

function setting(string $key, ?string $default = null): ?string
{
    static $all = null;
    $all ??= DB::table('settings')->pluck('value', 'key');

    return $all[$key] ?? $default;
}

function money(float|int|string|null $value): string
{
    return '৳'.number_format((float) $value, 2);
}

function nice_date(mixed $date): string
{
    return $date ? Carbon::parse($date)->format('M j, Y') : '-';
}

function nice_time(?string $time): string
{
    return $time ? Carbon::createFromFormat('H:i', $time)->format('g:i A') : '-';
}

function status_badge(?string $status): string
{
    return match ($status) {
        'Paid', 'Approved', 'Active', 'Present', 'Published', 'Income', 'Returned' => 'badge-green',
        'Partial', 'Pending', 'Late', 'Leave', 'Issued' => 'badge-amber',
        'Unpaid', 'Rejected', 'Absent', 'Inactive', 'Expense', 'Overdue' => 'badge-red',
        'Students', 'Staff' => 'badge-blue',
        default => 'badge-gray',
    };
}

function initials(string $name): string
{
    return collect(explode(' ', trim($name)))->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
}

/** Link to the same-named page for the signed-in staff role (admin.* or teacher.*). */
function staff_route(string $name, mixed $params = []): string
{
    return route(auth()->user()->role.'.'.$name, $params);
}
