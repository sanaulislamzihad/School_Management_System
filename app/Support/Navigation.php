<?php

namespace App\Support;

use App\Models\User;

/** Sidebar menus for each role. Every entry points at a page that exists. */
class Navigation
{
    public static function for(User $user): array
    {
        return match ($user->role) {
            'admin' => self::admin(),
            'teacher' => self::teacher(),
            default => self::student(),
        };
    }

    private static function item(string $label, string $icon, string $route, array $params = []): array
    {
        return ['label' => $label, 'icon' => $icon, 'url' => route($route, $params)];
    }

    private static function group(string $label, string $icon, array $children): array
    {
        return ['label' => $label, 'icon' => $icon, 'children' => array_map(
            fn ($c) => ['label' => $c[0], 'url' => route($c[1], $c[2] ?? [])],
            $children,
        )];
    }

    private static function admin(): array
    {
        $r = fn (string $label, string $resource) => [$label, 'admin.resource.index', ['resource' => $resource]];

        return [
            self::item('Dashboard', 'layout-dashboard', 'admin.dashboard'),
            self::group('Admission', 'clipboard-list', [['Applications', 'admin.admissions.index'], ['Add student', 'admin.students.create']]),
            self::group('Students', 'graduation-cap', [['Student list', 'admin.students.index'], ['Promotions', 'admin.promotions.index'], ['Student leave', 'admin.student-leaves.index']]),
            self::group('Employees', 'users', [['Employees', 'admin.employees.index'], ['Staff attendance', 'admin.staff-attendance.index'], ['Staff leave', 'admin.staff-leaves.index'], ['Payroll', 'admin.payroll.index'], $r('Holidays', 'holidays')]),
            self::group('Academic', 'book-open', [$r('Academic setup', 'sessions'), $r('Teacher assignment', 'teacher-subjects'), ['Timetable', 'admin.timetable.index'], $r('Assignments', 'homework'), $r('Daily diary', 'diaries')]),
            self::item('Attendance', 'calendar-check', 'admin.attendance.index'),
            self::group('Exams', 'file-pen-line', [$r('Exams', 'exams'), $r('Exam routine', 'exam-routines'), $r('Class tests', 'class-tests'), ['Marks entry', 'admin.marks.index'], ['Results', 'admin.results.index'], $r('Grading scale', 'grades')]),
            self::group('Accounts', 'wallet', [['Fees', 'admin.fees.index'], ['Due list', 'admin.fees.dues'], $r('Fee heads', 'fee-heads'), $r('Account heads', 'account-heads'), $r('Income & expenses', 'transactions')]),
            self::group('Library', 'library', [$r('Books', 'books'), ['Issue & return', 'admin.library.index']]),
            self::group('Facilities', 'bus', [$r('Transport', 'transport-routes'), $r('Hostels', 'hostels')]),
            self::group('Communication', 'megaphone', [$r('Notices', 'notices'), $r('Contact messages', 'contact-messages'), $r('Downloads', 'downloads')]),
            self::group('Website', 'globe', [$r('Pages', 'web-pages'), $r('Leadership messages', 'web-messages'), $r('Committees & people', 'web-people'), $r('Events', 'events'), $r('Photo gallery', 'gallery'), $r('Video gallery', 'videos'), $r('Top achievers', 'achievers'), $r('Useful links', 'links')]),
            self::item('Settings', 'settings', 'admin.settings.edit'),
        ];
    }

    private static function teacher(): array
    {
        $r = fn (string $label, string $icon, string $resource) => self::item($label, $icon, 'teacher.resource.index', ['resource' => $resource]);

        return [
            self::item('Dashboard', 'layout-dashboard', 'teacher.dashboard'),
            self::item('My timetable', 'calendar-days', 'teacher.timetable'),
            self::item('My students', 'graduation-cap', 'teacher.students'),
            self::item('Attendance', 'calendar-check', 'teacher.attendance.index'),
            self::item('Marks entry', 'file-pen-line', 'teacher.marks.index'),
            $r('Class tests', 'clipboard-check', 'class-tests'),
            $r('Assignments', 'book-open', 'homework'),
            $r('Daily diary', 'notebook-pen', 'diaries'),
            self::item('Notices', 'megaphone', 'teacher.notices'),
            self::item('Leave', 'calendar-off', 'teacher.leave.index'),
            self::item('Profile', 'user-round', 'teacher.profile'),
        ];
    }

    private static function student(): array
    {
        return [
            self::item('Dashboard', 'layout-dashboard', 'portal.dashboard'),
            self::item('Class routine', 'calendar-days', 'portal.routine'),
            self::item('Attendance', 'calendar-check', 'portal.attendance'),
            self::item('Assignments', 'book-open', 'portal.assignments'),
            self::item('Daily diary', 'notebook-pen', 'portal.diary'),
            self::item('Class tests', 'clipboard-check', 'portal.class-tests'),
            self::item('Results', 'award', 'portal.results'),
            self::item('Fees', 'wallet', 'portal.accounts'),
            self::item('Notices', 'megaphone', 'portal.notices'),
            self::item('Leave application', 'calendar-off', 'portal.leave.index'),
            self::item('Downloads', 'download', 'portal.downloads'),
            self::item('ID card', 'id-card', 'portal.id-card'),
            self::item('Profile', 'user-round', 'portal.profile'),
        ];
    }
}
