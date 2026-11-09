<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Configuration for simple list/add/edit/delete pages.
 *
 * Column types: text, date, money, bool, badge, image. Field types: text, textarea, number, date, time, select, checkbox, image, url.
 * Roles default to admin only. "owned" resources are limited to a teacher's own rows and assigned classes.
 */
class ResourceRegistry
{
    public static function get(string $name): ?array
    {
        return self::all()[$name] ?? null;
    }

    private static function classFields(): array
    {
        return [
            ['name' => 'class_id', 'label' => 'Class', 'type' => 'select', 'lookup' => 'classes', 'required' => true],
            ['name' => 'section_id', 'label' => 'Section', 'type' => 'select', 'lookup' => 'sections', 'depends' => 'class_id', 'required' => true],
            ['name' => 'subject_id', 'label' => 'Subject', 'type' => 'select', 'lookup' => 'subjects', 'depends' => 'class_id', 'required' => true],
        ];
    }

    private static function classJoins(): array
    {
        return [['classes as c', 't.class_id', 'c.id'], ['sections as s', 't.section_id', 's.id'], ['subjects as su', 't.subject_id', 'su.id']];
    }

    private static function classColumns(): array
    {
        return [['key' => 'class_name', 'label' => 'Class'], ['key' => 'section_name', 'label' => 'Section'], ['key' => 'subject_name', 'label' => 'Subject']];
    }

    private const CLASS_SELECT = ['c.name as class_name', 's.name as section_name', 'su.name as subject_name'];

    private const ACADEMIC_TABS = ['sessions' => 'Sessions', 'classes' => 'Classes', 'sections' => 'Sections', 'shifts' => 'Shifts', 'subjects' => 'Subjects'];

    private const DAYS = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    private static function all(): array
    {
        $classFilter = ['name' => 'class_id', 'label' => 'All classes', 'lookup' => 'classes'];
        $academic = ['title' => 'Academic setup', 'subtitle' => 'Sessions, classes, sections, shifts and subjects.', 'tabs' => self::ACADEMIC_TABS];

        return [
            'sessions' => $academic + [
                'table' => 'academic_sessions', 'singular' => 'session', 'order' => [['name', 'desc']], 'search' => ['t.name'],
                'columns' => [['key' => 'name', 'label' => 'Session'], ['key' => 'start_date', 'label' => 'Starts', 'type' => 'date'], ['key' => 'end_date', 'label' => 'Ends', 'type' => 'date'], ['key' => 'is_current', 'label' => 'Current', 'type' => 'bool']],
                'fields' => [['name' => 'name', 'label' => 'Session name', 'type' => 'text', 'required' => true, 'unique' => true], ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date'], ['name' => 'end_date', 'label' => 'End date', 'type' => 'date'], ['name' => 'is_current', 'label' => 'This is the current session', 'type' => 'checkbox']],
                'after' => function (array $data, int $id) {
                    if (! empty($data['is_current'])) {
                        DB::table('academic_sessions')->where('id', '!=', $id)->update(['is_current' => false]);
                    }
                },
            ],
            'classes' => $academic + [
                'table' => 'classes', 'singular' => 'class', 'order' => [['position', 'asc']], 'search' => ['t.name'],
                'columns' => [['key' => 'position', 'label' => 'Order'], ['key' => 'name', 'label' => 'Class']],
                'fields' => [['name' => 'name', 'label' => 'Class name', 'type' => 'text', 'required' => true, 'unique' => true], ['name' => 'position', 'label' => 'Display order', 'type' => 'number', 'required' => true]],
            ],
            'sections' => $academic + [
                'table' => 'sections', 'singular' => 'section', 'order' => [['c.position', 'asc'], ['t.name', 'asc']], 'search' => ['t.name'],
                'joins' => [['classes as c', 't.class_id', 'c.id']], 'select' => ['c.name as class_name'], 'filters' => [$classFilter],
                'columns' => [['key' => 'class_name', 'label' => 'Class'], ['key' => 'name', 'label' => 'Section']],
                'fields' => [['name' => 'class_id', 'label' => 'Class', 'type' => 'select', 'lookup' => 'classes', 'required' => true], ['name' => 'name', 'label' => 'Section name', 'type' => 'text', 'required' => true]],
            ],
            'shifts' => $academic + [
                'table' => 'shifts', 'singular' => 'shift', 'order' => [['name', 'asc']], 'search' => ['t.name'],
                'columns' => [['key' => 'name', 'label' => 'Shift']], 'fields' => [['name' => 'name', 'label' => 'Shift name', 'type' => 'text', 'required' => true, 'unique' => true]],
            ],
            'subjects' => $academic + [
                'table' => 'subjects', 'singular' => 'subject', 'order' => [['c.position', 'asc'], ['t.name', 'asc']], 'search' => ['t.name', 't.code'],
                'joins' => [['classes as c', 't.class_id', 'c.id']], 'select' => ['c.name as class_name'], 'filters' => [$classFilter],
                'columns' => [['key' => 'class_name', 'label' => 'Class'], ['key' => 'name', 'label' => 'Subject'], ['key' => 'code', 'label' => 'Code']],
                'fields' => [['name' => 'class_id', 'label' => 'Class', 'type' => 'select', 'lookup' => 'classes', 'required' => true], ['name' => 'name', 'label' => 'Subject name', 'type' => 'text', 'required' => true], ['name' => 'code', 'label' => 'Subject code', 'type' => 'text']],
            ],
            'teacher-subjects' => [
                'title' => 'Teacher assignment', 'subtitle' => 'Link teachers to a class, section and subject. Teachers only see what is assigned here.', 'singular' => 'assignment',
                'table' => 'teacher_subjects', 'order' => [['c.position', 'asc'], ['s.name', 'asc'], ['su.name', 'asc']], 'search' => ['e.name', 'su.name'],
                'joins' => [...self::classJoins(), ['employees as e', 't.employee_id', 'e.id']], 'select' => [...self::CLASS_SELECT, 'e.name as teacher_name'], 'filters' => [$classFilter],
                'columns' => [['key' => 'teacher_name', 'label' => 'Teacher'], ...self::classColumns(), ['key' => 'is_class_teacher', 'label' => 'Class teacher', 'type' => 'bool']],
                'fields' => [['name' => 'employee_id', 'label' => 'Teacher', 'type' => 'select', 'lookup' => 'teachers', 'required' => true], ...self::classFields(), ['name' => 'is_class_teacher', 'label' => 'Class teacher for this section', 'type' => 'checkbox']],
                'check' => function (array $d, ?int $id) {
                    $taken = DB::table('teacher_subjects')->where(['class_id' => $d['class_id'], 'section_id' => $d['section_id'], 'subject_id' => $d['subject_id']])->when($id, fn ($q) => $q->where('id', '!=', $id))->exists();

                    return $taken ? 'This subject already has a teacher in that section.' : null;
                },
            ],
            'routines' => [
                'title' => 'Timetable periods', 'subtitle' => 'Add or change class periods. Double-booked teachers and overlapping periods are blocked.', 'singular' => 'period',
                'table' => 'routines', 'order' => [['c.position', 'asc'], ['s.name', 'asc'], ['t.day', 'asc'], ['t.start_time', 'asc']], 'search' => ['su.name', 'e.name'],
                'joins' => [...self::classJoins(), ['employees as e', 't.employee_id', 'e.id']], 'select' => [...self::CLASS_SELECT, 'e.name as teacher_name'], 'filters' => [$classFilter, ['name' => 'day', 'label' => 'All days', 'options' => self::DAYS]],
                'columns' => [...self::classColumns(), ['key' => 'day', 'label' => 'Day'], ['key' => 'start_time', 'label' => 'Start'], ['key' => 'end_time', 'label' => 'End'], ['key' => 'teacher_name', 'label' => 'Teacher'], ['key' => 'room', 'label' => 'Room']],
                'fields' => [...self::classFields(), ['name' => 'day', 'label' => 'Day', 'type' => 'select', 'options' => self::DAYS, 'required' => true], ['name' => 'employee_id', 'label' => 'Teacher', 'type' => 'select', 'lookup' => 'teachers', 'required' => true], ['name' => 'start_time', 'label' => 'Start time', 'type' => 'time', 'required' => true], ['name' => 'end_time', 'label' => 'End time', 'type' => 'time', 'required' => true], ['name' => 'room', 'label' => 'Room', 'type' => 'text']],
                'check' => function (array $d, ?int $id) {
                    if ($d['end_time'] <= $d['start_time']) {
                        return 'End time must be after the start time.';
                    }
                    $overlap = fn () => DB::table('routines')->where('day', $d['day'])->where('start_time', '<', $d['end_time'])->where('end_time', '>', $d['start_time'])->when($id, fn ($q) => $q->where('id', '!=', $id));
                    if ($overlap()->where('section_id', $d['section_id'])->exists()) {
                        return 'This section already has a period at that time.';
                    }

                    return $overlap()->where('employee_id', $d['employee_id'])->exists() ? 'This teacher is already teaching at that time.' : null;
                },
            ],
            'homework' => [
                'title' => 'Assignments', 'subtitle' => 'Class assignments appear in the student portal right away.', 'singular' => 'assignment', 'roles' => ['admin', 'teacher'], 'owned' => true,
                'table' => 'homework', 'order' => [['t.due_date', 'desc']], 'search' => ['t.title'], 'joins' => self::classJoins(), 'select' => self::CLASS_SELECT, 'filters' => [$classFilter],
                'columns' => [['key' => 'title', 'label' => 'Title'], ...self::classColumns(), ['key' => 'due_date', 'label' => 'Due', 'type' => 'date']],
                'fields' => [...self::classFields(), ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'body', 'label' => 'Instructions', 'type' => 'textarea', 'wide' => true], ['name' => 'assigned_date', 'label' => 'Assigned on', 'type' => 'date', 'required' => true], ['name' => 'due_date', 'label' => 'Due date', 'type' => 'date', 'required' => true]],
            ],
            'diaries' => [
                'title' => 'Daily diary', 'subtitle' => 'A short note on what each class covered today.', 'singular' => 'diary entry', 'roles' => ['admin', 'teacher'], 'owned' => true,
                'table' => 'diaries', 'order' => [['t.date', 'desc']], 'search' => ['t.title'], 'joins' => self::classJoins(), 'select' => self::CLASS_SELECT, 'filters' => [$classFilter],
                'columns' => [['key' => 'title', 'label' => 'Title'], ...self::classColumns(), ['key' => 'date', 'label' => 'Date', 'type' => 'date']],
                'fields' => [...self::classFields(), ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true], ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'body', 'label' => 'What was covered', 'type' => 'textarea', 'wide' => true]],
            ],
            'class-tests' => [
                'title' => 'Class tests', 'subtitle' => 'Create a test, then enter marks for the class.', 'singular' => 'class test', 'roles' => ['admin', 'teacher'], 'owned' => true,
                'table' => 'class_tests', 'order' => [['t.date', 'desc']], 'search' => ['t.title'], 'joins' => self::classJoins(), 'select' => self::CLASS_SELECT, 'filters' => [$classFilter],
                'rowLink' => ['label' => 'Enter marks', 'route' => 'class-test-marks.edit'],
                'columns' => [['key' => 'title', 'label' => 'Test'], ...self::classColumns(), ['key' => 'date', 'label' => 'Date', 'type' => 'date'], ['key' => 'full_marks', 'label' => 'Full marks']],
                'fields' => [...self::classFields(), ['name' => 'title', 'label' => 'Test title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true], ['name' => 'full_marks', 'label' => 'Full marks', 'type' => 'number', 'required' => true]],
            ],
            'exams' => [
                'title' => 'Exams', 'subtitle' => 'Students can see results only after an exam is published.', 'singular' => 'exam',
                'table' => 'exams', 'order' => [['t.start_date', 'desc']], 'search' => ['t.name'], 'joins' => [['academic_sessions as a', 't.session_id', 'a.id']], 'select' => ['a.name as session_name'],
                'columns' => [['key' => 'name', 'label' => 'Exam'], ['key' => 'session_name', 'label' => 'Session'], ['key' => 'start_date', 'label' => 'Starts', 'type' => 'date'], ['key' => 'end_date', 'label' => 'Ends', 'type' => 'date'], ['key' => 'is_published', 'label' => 'Result published', 'type' => 'bool']],
                'fields' => [['name' => 'name', 'label' => 'Exam name', 'type' => 'text', 'required' => true], ['name' => 'session_id', 'label' => 'Session', 'type' => 'select', 'lookup' => 'sessions', 'required' => true], ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date'], ['name' => 'end_date', 'label' => 'End date', 'type' => 'date'], ['name' => 'is_published', 'label' => 'Publish results', 'type' => 'checkbox']],
            ],
            'exam-routines' => [
                'title' => 'Exam routine', 'subtitle' => 'Exam dates and rooms for every class.', 'singular' => 'exam slot',
                'table' => 'exam_routines', 'order' => [['t.date', 'asc'], ['t.start_time', 'asc']], 'search' => ['su.name', 'e.name'],
                'joins' => [['classes as c', 't.class_id', 'c.id'], ['subjects as su', 't.subject_id', 'su.id'], ['exams as e', 't.exam_id', 'e.id']], 'select' => ['c.name as class_name', 'su.name as subject_name', 'e.name as exam_name'], 'filters' => [['name' => 'exam_id', 'label' => 'All exams', 'lookup' => 'exams'], $classFilter],
                'columns' => [['key' => 'exam_name', 'label' => 'Exam'], ['key' => 'class_name', 'label' => 'Class'], ['key' => 'subject_name', 'label' => 'Subject'], ['key' => 'date', 'label' => 'Date', 'type' => 'date'], ['key' => 'start_time', 'label' => 'Start'], ['key' => 'end_time', 'label' => 'End'], ['key' => 'room', 'label' => 'Room']],
                'fields' => [['name' => 'exam_id', 'label' => 'Exam', 'type' => 'select', 'lookup' => 'exams', 'required' => true], ['name' => 'class_id', 'label' => 'Class', 'type' => 'select', 'lookup' => 'classes', 'required' => true], ['name' => 'subject_id', 'label' => 'Subject', 'type' => 'select', 'lookup' => 'subjects', 'depends' => 'class_id', 'required' => true], ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true], ['name' => 'start_time', 'label' => 'Start time', 'type' => 'time'], ['name' => 'end_time', 'label' => 'End time', 'type' => 'time'], ['name' => 'room', 'label' => 'Room', 'type' => 'text']],
            ],
            'grades' => [
                'title' => 'Grading scale', 'subtitle' => 'Mark ranges used to calculate grades and GPA.', 'singular' => 'grade',
                'table' => 'grades', 'order' => [['t.min_mark', 'desc']],
                'columns' => [['key' => 'letter', 'label' => 'Grade'], ['key' => 'point', 'label' => 'Point'], ['key' => 'min_mark', 'label' => 'From %'], ['key' => 'max_mark', 'label' => 'To %']],
                'fields' => [['name' => 'letter', 'label' => 'Grade letter', 'type' => 'text', 'required' => true, 'unique' => true], ['name' => 'point', 'label' => 'Grade point', 'type' => 'number', 'required' => true], ['name' => 'min_mark', 'label' => 'From %', 'type' => 'number', 'required' => true], ['name' => 'max_mark', 'label' => 'To %', 'type' => 'number', 'required' => true]],
            ],
            'notices' => [
                'title' => 'Notice board', 'subtitle' => 'Announcements for students, staff and the public website.', 'singular' => 'notice',
                'table' => 'notices', 'order' => [['t.pinned', 'desc'], ['t.date', 'desc']], 'search' => ['t.title', 't.category'], 'filters' => [['name' => 'audience', 'label' => 'All audiences', 'options' => ['Everyone', 'Students', 'Staff']]],
                'columns' => [['key' => 'title', 'label' => 'Title'], ['key' => 'category', 'label' => 'Category'], ['key' => 'audience', 'label' => 'For', 'type' => 'badge'], ['key' => 'date', 'label' => 'Date', 'type' => 'date'], ['key' => 'pinned', 'label' => 'Pinned', 'type' => 'bool']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'audience', 'label' => 'Show to', 'type' => 'select', 'options' => ['Everyone', 'Students', 'Staff'], 'required' => true], ['name' => 'category', 'label' => 'Category', 'type' => 'text', 'required' => true], ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true], ['name' => 'pinned', 'label' => 'Pin to the top', 'type' => 'checkbox'], ['name' => 'body', 'label' => 'Details', 'type' => 'textarea', 'wide' => true]],
            ],
            'holidays' => [
                'title' => 'Holidays', 'subtitle' => 'School calendar closures.', 'singular' => 'holiday', 'table' => 'holidays', 'order' => [['t.from_date', 'asc']], 'search' => ['t.title'],
                'columns' => [['key' => 'title', 'label' => 'Holiday'], ['key' => 'from_date', 'label' => 'From', 'type' => 'date'], ['key' => 'to_date', 'label' => 'To', 'type' => 'date']],
                'fields' => [['name' => 'title', 'label' => 'Holiday name', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'from_date', 'label' => 'From', 'type' => 'date', 'required' => true], ['name' => 'to_date', 'label' => 'To', 'type' => 'date', 'required' => true]],
            ],
            'fee-heads' => [
                'title' => 'Fee heads', 'subtitle' => 'Types of fees used on invoices.', 'singular' => 'fee head', 'table' => 'fee_heads', 'order' => [['t.name', 'asc']], 'search' => ['t.name'],
                'columns' => [['key' => 'name', 'label' => 'Name'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'frequency', 'label' => 'Frequency', 'type' => 'badge']],
                'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'unique' => true, 'wide' => true], ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true], ['name' => 'frequency', 'label' => 'Frequency', 'type' => 'select', 'options' => ['Monthly', 'Yearly', 'One time'], 'required' => true]],
            ],
            'account-heads' => [
                'title' => 'Account heads', 'subtitle' => 'Ledger categories for income and expenses.', 'singular' => 'account head', 'table' => 'account_heads', 'order' => [['t.kind', 'asc'], ['t.name', 'asc']], 'search' => ['t.name'],
                'columns' => [['key' => 'name', 'label' => 'Name'], ['key' => 'kind', 'label' => 'Type', 'type' => 'badge']],
                'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'unique' => true], ['name' => 'kind', 'label' => 'Type', 'type' => 'select', 'options' => ['Income', 'Expense'], 'required' => true]],
            ],
            'transactions' => [
                'title' => 'Income & expenses', 'subtitle' => 'Office ledger entries outside student fees.', 'singular' => 'entry', 'table' => 'transactions', 'order' => [['t.date', 'desc']], 'search' => ['t.note', 'h.name'],
                'joins' => [['account_heads as h', 't.account_head_id', 'h.id']], 'select' => ['h.name as head_name', 'h.kind as head_kind'],
                'columns' => [['key' => 'date', 'label' => 'Date', 'type' => 'date'], ['key' => 'head_name', 'label' => 'Head'], ['key' => 'head_kind', 'label' => 'Type', 'type' => 'badge'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'], ['key' => 'note', 'label' => 'Note']],
                'fields' => [['name' => 'account_head_id', 'label' => 'Account head', 'type' => 'select', 'lookup' => 'account_heads', 'required' => true], ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required' => true], ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true], ['name' => 'note', 'label' => 'Note', 'type' => 'text']],
            ],
            'books' => [
                'title' => 'Books', 'subtitle' => 'Library catalogue.', 'singular' => 'book', 'table' => 'books', 'order' => [['t.title', 'asc']], 'search' => ['t.title', 't.author', 't.isbn'],
                'columns' => [['key' => 'title', 'label' => 'Title'], ['key' => 'author', 'label' => 'Author'], ['key' => 'category', 'label' => 'Category'], ['key' => 'copies', 'label' => 'Copies'], ['key' => 'shelf', 'label' => 'Shelf']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'author', 'label' => 'Author', 'type' => 'text'], ['name' => 'isbn', 'label' => 'ISBN', 'type' => 'text'], ['name' => 'category', 'label' => 'Category', 'type' => 'text'], ['name' => 'copies', 'label' => 'Copies', 'type' => 'number', 'required' => true], ['name' => 'shelf', 'label' => 'Shelf', 'type' => 'text']],
            ],
            'transport-routes' => [
                'title' => 'Transport', 'subtitle' => 'School bus routes and drivers.', 'singular' => 'route', 'table' => 'transport_routes', 'order' => [['t.name', 'asc']], 'search' => ['t.name', 't.driver'],
                'columns' => [['key' => 'name', 'label' => 'Route'], ['key' => 'vehicle', 'label' => 'Vehicle'], ['key' => 'driver', 'label' => 'Driver'], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'fare', 'label' => 'Monthly fare', 'type' => 'money']],
                'fields' => [['name' => 'name', 'label' => 'Route name', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'vehicle', 'label' => 'Vehicle', 'type' => 'text'], ['name' => 'driver', 'label' => 'Driver', 'type' => 'text'], ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'], ['name' => 'fare', 'label' => 'Monthly fare', 'type' => 'number', 'required' => true]],
            ],
            'hostels' => [
                'title' => 'Hostels', 'subtitle' => 'Boarding houses.', 'singular' => 'hostel', 'table' => 'hostels', 'order' => [['t.name', 'asc']], 'search' => ['t.name', 't.warden'],
                'columns' => [['key' => 'name', 'label' => 'Hostel'], ['key' => 'kind', 'label' => 'Type'], ['key' => 'capacity', 'label' => 'Capacity'], ['key' => 'warden', 'label' => 'Warden'], ['key' => 'phone', 'label' => 'Phone']],
                'fields' => [['name' => 'name', 'label' => 'Hostel name', 'type' => 'text', 'required' => true], ['name' => 'kind', 'label' => 'Type', 'type' => 'select', 'options' => ['Boys', 'Girls'], 'required' => true], ['name' => 'capacity', 'label' => 'Capacity', 'type' => 'number', 'required' => true], ['name' => 'warden', 'label' => 'Warden', 'type' => 'text'], ['name' => 'phone', 'label' => 'Phone', 'type' => 'text']],
            ],
            'downloads' => [
                'title' => 'Downloads', 'subtitle' => 'Files and links shown in the student portal.', 'singular' => 'download', 'table' => 'downloads', 'order' => [['t.date', 'desc']], 'search' => ['t.title'],
                'columns' => [['key' => 'title', 'label' => 'Title'], ['key' => 'category', 'label' => 'Category'], ['key' => 'date', 'label' => 'Date', 'type' => 'date']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'category', 'label' => 'Category', 'type' => 'text'], ['name' => 'url', 'label' => 'File link', 'type' => 'url'], ['name' => 'date', 'label' => 'Date', 'type' => 'date']],
            ],
            'contact-messages' => [
                'title' => 'Contact messages', 'subtitle' => 'Messages sent from the website contact form.', 'singular' => 'message', 'noCreate' => true, 'table' => 'contact_messages', 'order' => [['t.created_at', 'desc']], 'search' => ['t.name', 't.subject'],
                'columns' => [['key' => 'name', 'label' => 'From'], ['key' => 'phone', 'label' => 'Phone'], ['key' => 'subject', 'label' => 'Subject'], ['key' => 'message', 'label' => 'Message'], ['key' => 'is_read', 'label' => 'Read', 'type' => 'bool']],
                'fields' => [['name' => 'is_read', 'label' => 'Mark as read', 'type' => 'checkbox']],
            ],
            'web-pages' => [
                'title' => 'Website pages', 'subtitle' => 'About, activity, rules and admission pages on the public site.', 'singular' => 'page', 'table' => 'web_pages', 'order' => [['t.menu_group', 'asc'], ['t.title', 'asc']], 'search' => ['t.title'],
                'columns' => [['key' => 'title', 'label' => 'Title'], ['key' => 'menu_group', 'label' => 'Menu', 'type' => 'badge'], ['key' => 'slug', 'label' => 'Link']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true], ['name' => 'slug', 'label' => 'Link name (letters and dashes)', 'type' => 'text', 'required' => true, 'unique' => true], ['name' => 'menu_group', 'label' => 'Menu', 'type' => 'select', 'options' => ['about', 'activities', 'rules', 'admission'], 'required' => true], ['name' => 'image', 'label' => 'Image', 'type' => 'image'], ['name' => 'body', 'label' => 'Content', 'type' => 'textarea', 'wide' => true]],
            ],
            'web-messages' => [
                'title' => 'Leadership messages', 'subtitle' => 'Head Teacher and Chairperson messages on the home page.', 'singular' => 'message', 'noCreate' => true, 'table' => 'web_messages', 'order' => [['t.id', 'asc']],
                'columns' => [['key' => 'photo', 'label' => 'Photo', 'type' => 'image'], ['key' => 'heading', 'label' => 'Heading'], ['key' => 'name', 'label' => 'Name']],
                'fields' => [['name' => 'heading', 'label' => 'Heading', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true], ['name' => 'designation', 'label' => 'Designation', 'type' => 'text'], ['name' => 'photo', 'label' => 'Photo', 'type' => 'image'], ['name' => 'body', 'label' => 'Message', 'type' => 'textarea', 'wide' => true]],
            ],
            'web-people' => [
                'title' => 'Committees & people', 'subtitle' => 'Governing body, committees, former heads, donors and the student cabinet.', 'singular' => 'person', 'table' => 'web_people', 'order' => [['t.group_name', 'asc'], ['t.position', 'asc']], 'search' => ['t.name'],
                'filters' => [['name' => 'group_name', 'label' => 'All groups', 'options' => array_keys(self::peopleGroups())]],
                'columns' => [['key' => 'photo', 'label' => 'Photo', 'type' => 'image'], ['key' => 'name', 'label' => 'Name'], ['key' => 'designation', 'label' => 'Role'], ['key' => 'group_name', 'label' => 'Group', 'type' => 'badge']],
                'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true], ['name' => 'designation', 'label' => 'Role', 'type' => 'text'], ['name' => 'group_name', 'label' => 'Group', 'type' => 'select', 'options' => array_keys(self::peopleGroups()), 'required' => true], ['name' => 'position', 'label' => 'Display order', 'type' => 'number', 'required' => true], ['name' => 'photo', 'label' => 'Photo', 'type' => 'image']],
            ],
            'events' => [
                'title' => 'Events', 'subtitle' => 'Upcoming school events on the website.', 'singular' => 'event', 'table' => 'events', 'order' => [['t.date', 'desc']], 'search' => ['t.title'],
                'columns' => [['key' => 'title', 'label' => 'Event'], ['key' => 'date', 'label' => 'Date', 'type' => 'date'], ['key' => 'location', 'label' => 'Location']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true], ['name' => 'location', 'label' => 'Location', 'type' => 'text'], ['name' => 'body', 'label' => 'Details', 'type' => 'textarea', 'wide' => true]],
            ],
            'gallery' => [
                'title' => 'Photo gallery', 'subtitle' => 'Photos shown on the website.', 'singular' => 'photo', 'table' => 'gallery', 'order' => [['t.id', 'desc']], 'search' => ['t.title'],
                'columns' => [['key' => 'image_url', 'label' => 'Photo', 'type' => 'image'], ['key' => 'title', 'label' => 'Title'], ['key' => 'category', 'label' => 'Category']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true], ['name' => 'category', 'label' => 'Category', 'type' => 'text'], ['name' => 'image_url', 'label' => 'Photo', 'type' => 'image', 'required' => true, 'wide' => true]],
            ],
            'videos' => [
                'title' => 'Video gallery', 'subtitle' => 'Add a direct video file link or a YouTube link.', 'singular' => 'video', 'table' => 'videos', 'order' => [['t.id', 'asc']], 'search' => ['t.title'],
                'columns' => [['key' => 'poster', 'label' => 'Poster', 'type' => 'image'], ['key' => 'title', 'label' => 'Title']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'src', 'label' => 'Video link', 'type' => 'url', 'required' => true, 'wide' => true], ['name' => 'poster', 'label' => 'Poster image', 'type' => 'image']],
            ],
            'achievers' => [
                'title' => 'Top achievers', 'subtitle' => 'Students featured on the home page.', 'singular' => 'achiever', 'table' => 'achievers', 'order' => [['t.year', 'desc']], 'search' => ['t.name'],
                'columns' => [['key' => 'name', 'label' => 'Name'], ['key' => 'exam', 'label' => 'Exam'], ['key' => 'gpa', 'label' => 'GPA'], ['key' => 'year', 'label' => 'Year']],
                'fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'exam', 'label' => 'Exam', 'type' => 'text'], ['name' => 'gpa', 'label' => 'GPA', 'type' => 'text'], ['name' => 'year', 'label' => 'Year', 'type' => 'text'], ['name' => 'photo', 'label' => 'Photo', 'type' => 'image']],
            ],
            'links' => [
                'title' => 'Useful links', 'subtitle' => 'Quick links on the home page.', 'singular' => 'link', 'table' => 'links', 'order' => [['t.title', 'asc']], 'search' => ['t.title'],
                'columns' => [['key' => 'title', 'label' => 'Title'], ['key' => 'url', 'label' => 'Link']],
                'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'wide' => true], ['name' => 'url', 'label' => 'Link', 'type' => 'url', 'required' => true, 'wide' => true]],
            ],
        ];
    }

    public static function peopleGroups(): array
    {
        return [
            'governing-body' => 'Governing Body', 'management-committee' => 'Management Committee', 'former-headmasters' => 'Former Head Teachers',
            'former-presidents' => 'Former Chairpersons', 'donors' => 'Donors', 'harassment-committee' => 'Anti-Harassment Committee', 'student-cabinet' => 'Student Cabinet',
        ];
    }
}
