<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private const DAYS = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

    private const PERIODS = [['09:00', '09:40'], ['09:45', '10:25'], ['10:30', '11:10'], ['11:30', '12:10'], ['12:15', '12:55']];

    private int $sessionId;

    private array $classes = [];

    private array $sections = [];

    private array $subjects = [];

    private array $teachers = [];

    private array $students = [];

    private array $links = [];

    public function run(): void
    {
        mt_srand(2026);

        DB::transaction(function () {
            $this->call(WebsiteSeeder::class);
            $this->academics();
            $this->people();
            $this->timetable();
            $this->attendance();
            $this->exams();
            $this->dailyWork();
            $this->finance();
            $this->office();
        });
    }

    private function academics(): void
    {
        foreach ([2025, 2026, 2027] as $year) {
            $id = DB::table('academic_sessions')->insertGetId([
                'name' => (string) $year, 'start_date' => "$year-01-01", 'end_date' => "$year-12-31", 'is_current' => $year === 2026,
            ]);
            if ($year === 2026) {
                $this->sessionId = $id;
            }
        }

        DB::table('shifts')->insert([['name' => 'Morning'], ['name' => 'Day']]);

        $names = ['Play', 'Nursery', 'KG', 'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'];
        foreach ($names as $i => $name) {
            $classId = DB::table('classes')->insertGetId(['name' => $name, 'position' => $i + 1]);
            $this->classes[$name] = $classId;

            foreach ($i >= 8 ? ['A', 'B'] : ['A'] as $section) {
                $this->sections[] = ['id' => DB::table('sections')->insertGetId(['class_id' => $classId, 'name' => $section]), 'class_id' => $classId, 'name' => $section, 'class' => $name];
            }

            $list = match (true) {
                $i <= 2 => ['Bangla', 'English', 'Mathematics', 'Drawing'],
                $i <= 7 => ['Bangla', 'English', 'Mathematics', 'Science', 'Religion', 'Social Science'],
                default => ['Bangla', 'English', 'Mathematics', 'Science', 'ICT', 'Religion', 'Social Science'],
            };
            foreach ($list as $n => $subject) {
                $this->subjects[$classId][] = [
                    'id' => DB::table('subjects')->insertGetId(['class_id' => $classId, 'name' => $subject, 'code' => strtoupper(substr($subject, 0, 3)).'-'.(101 + $n)]),
                    'name' => $subject,
                ];
            }
        }

        DB::table('grades')->insert([
            ['letter' => 'A+', 'point' => 5.00, 'min_mark' => 80, 'max_mark' => 100],
            ['letter' => 'A', 'point' => 4.00, 'min_mark' => 70, 'max_mark' => 79.99],
            ['letter' => 'A-', 'point' => 3.50, 'min_mark' => 60, 'max_mark' => 69.99],
            ['letter' => 'B', 'point' => 3.00, 'min_mark' => 50, 'max_mark' => 59.99],
            ['letter' => 'C', 'point' => 2.00, 'min_mark' => 40, 'max_mark' => 49.99],
            ['letter' => 'D', 'point' => 1.00, 'min_mark' => 33, 'max_mark' => 39.99],
            ['letter' => 'F', 'point' => 0.00, 'min_mark' => 0, 'max_mark' => 32.99],
        ]);
    }

    private function people(): void
    {
        $now = now();
        DB::table('users')->insert([
            'name' => 'Sadia Rahman', 'login' => 'admin', 'email' => 'admin@abcschool.example', 'password' => Hash::make('admin123'),
            'role' => 'admin', 'active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $teacherHash = Hash::make('teacher123');
        $staff = [
            ['Rezaul Karim', 'Head Teacher', 'Administration', 'Male', 'm83', null, 62000],
            ['Farhana Yasmin', 'Senior Teacher', 'Bangla', 'Female', 'f12', 'Bangla', 38000],
            ['Tanvir Hasan', 'Assistant Teacher', 'Bangla', 'Male', 'm32', 'Bangla', 30000],
            ['Nusrat Jahan', 'Senior Teacher', 'English', 'Female', 'f26', 'English', 38000],
            ['Imran Hossain', 'Assistant Teacher', 'English', 'Male', 'm45', 'English', 30000],
            ['Kamrul Islam', 'Senior Teacher', 'Mathematics', 'Male', 'm52', 'Mathematics', 40000],
            ['Sharmin Akter', 'Assistant Teacher', 'Mathematics', 'Female', 'f44', 'Mathematics', 31000],
            ['Mahmudul Hasan', 'Senior Teacher', 'Science', 'Male', 'm75', 'Science', 39000],
            ['Ayesha Siddika', 'Assistant Teacher', 'Science', 'Female', 'f57', 'Science', 31000],
            ['Rakibul Islam', 'ICT Instructor', 'ICT', 'Male', 'm60', 'ICT', 33000],
            ['Abdul Malek', 'Assistant Teacher', 'Religious Studies', 'Male', 'm11', 'Religion', 29000],
            ['Rumana Parvin', 'Assistant Teacher', 'Social Science', 'Female', 'f68', 'Social Science', 30000],
            ['Sabina Khatun', 'Arts Teacher', 'Fine Arts', 'Female', 'f79', 'Drawing', 27000],
        ];

        $bySubject = [];
        foreach ($staff as $i => [$name, $designation, $department, $gender, $photo, $subject, $salary]) {
            $no = 'T'.(1001 + $i);
            $userId = DB::table('users')->insertGetId([
                'name' => $name, 'login' => $no, 'password' => $teacherHash, 'role' => 'teacher', 'active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $id = DB::table('employees')->insertGetId([
                'user_id' => $userId, 'employee_no' => $no, 'name' => $name, 'designation' => $designation, 'department' => $department, 'kind' => 'Teacher',
                'gender' => $gender, 'photo' => "images/people/$photo.jpg", 'phone' => '0171'.str_pad((string) (2000100 + $i), 7, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace(' ', '.', $name)).'@abcschool.example', 'joining_date' => (2012 + $i % 11).'-01-15', 'basic_salary' => $salary,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->teachers[$id] = $name;
            if ($subject) {
                $bySubject[$subject][] = $id;
            }
        }

        foreach ([['Jahangir Alam', 'Accountant', 'Accounts', 24000], ['Morium Begum', 'Librarian', 'Library', 21000], ['Liton Mia', 'Office Assistant', 'Office', 16000]] as $i => [$name, $designation, $department, $salary]) {
            DB::table('employees')->insert([
                'employee_no' => 'T'.(1014 + $i), 'name' => $name, 'designation' => $designation, 'department' => $department, 'kind' => 'Staff',
                'gender' => $i === 1 ? 'Female' : 'Male', 'phone' => '0181'.(3000100 + $i), 'joining_date' => '2018-03-01', 'basic_salary' => $salary,
                'show_public' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $cursor = [];
        foreach ($this->sections as $section) {
            foreach ($this->subjects[$section['class_id']] as $n => $subject) {
                $pool = $bySubject[$subject['name']];
                $cursor[$subject['name']] = ($cursor[$subject['name']] ?? -1) + 1;
                $teacher = $pool[$cursor[$subject['name']] % count($pool)];
                $link = ['employee_id' => $teacher, 'class_id' => $section['class_id'], 'section_id' => $section['id'], 'subject_id' => $subject['id'], 'is_class_teacher' => $n === 0];
                DB::table('teacher_subjects')->insert($link);
                $this->links[$section['id']][] = $link;
            }
        }

        $boys = ['Arif', 'Sabbir', 'Rafi', 'Tahmid', 'Nayeem', 'Mehedi', 'Sakib', 'Fahim', 'Ashik', 'Zubair', 'Shahriar', 'Ratul', 'Adnan', 'Rayhan', 'Tamim', 'Yasin'];
        $girls = ['Sumaiya', 'Nadia', 'Tasnim', 'Maliha', 'Rifa', 'Anika', 'Jannat', 'Mim', 'Sadia', 'Lamia', 'Farzana', 'Tania', 'Nafisa', 'Sanjida', 'Israt', 'Mahi'];
        $family = ['Ahmed', 'Hossain', 'Rahman', 'Islam', 'Chowdhury', 'Khan', 'Sarker', 'Miah', 'Uddin', 'Talukder'];
        $fathers = ['Abdul', 'Shafiq', 'Motaleb', 'Nurul', 'Harun', 'Jalal', 'Mosharraf', 'Delwar'];
        $mothers = ['Rina', 'Shahana', 'Kohinoor', 'Parveen', 'Salma', 'Rehana', 'Nasrin', 'Rokeya'];
        $areas = ['Mirpur, Dhaka', 'Uttara, Dhaka', 'Dhanmondi, Dhaka', 'Mohammadpur, Dhaka', 'Rampura, Dhaka', 'Banasree, Dhaka'];
        $blood = ['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'];
        $studentHash = Hash::make('student123');
        $number = 1;

        foreach ($this->sections as $section) {
            $position = array_search($section['class'], array_keys($this->classes), true);
            for ($roll = 1; $roll <= 8; $roll++, $number++) {
                $isGirl = $roll % 2 === 0;
                $last = $family[array_rand($family)];
                $name = ($isGirl ? $girls[array_rand($girls)] : $boys[array_rand($boys)]).' '.$last;
                $no = '26'.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
                $userId = DB::table('users')->insertGetId([
                    'name' => $name, 'login' => $no, 'password' => $studentHash, 'role' => 'student', 'active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $father = $fathers[array_rand($fathers)].' '.$last;
                $phone = '0171'.str_pad((string) (5000000 + $number), 7, '0', STR_PAD_LEFT);
                $this->students[$section['id']][] = DB::table('students')->insertGetId([
                    'user_id' => $userId, 'student_no' => $no, 'name' => $name, 'gender' => $isGirl ? 'Female' : 'Male',
                    'dob' => (2022 - $position).'-'.str_pad((string) mt_rand(1, 12), 2, '0', STR_PAD_LEFT).'-'.str_pad((string) mt_rand(1, 28), 2, '0', STR_PAD_LEFT),
                    'blood_group' => $blood[array_rand($blood)], 'religion' => 'Islam', 'phone' => $phone,
                    'present_address' => $areas[array_rand($areas)], 'session_id' => $this->sessionId, 'class_id' => $section['class_id'], 'section_id' => $section['id'],
                    'shift_id' => 1, 'roll' => $roll, 'admission_date' => '2026-01-05', 'status' => 'Active',
                    'father_name' => $father, 'father_phone' => $phone, 'father_occupation' => 'Business', 'mother_name' => $mothers[array_rand($mothers)].' '.$last,
                    'guardian_name' => $father, 'guardian_relation' => 'Father', 'guardian_phone' => $phone, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    private function timetable(): void
    {
        $busy = [];
        $rows = [];
        foreach (self::DAYS as $d => $day) {
            foreach ($this->sections as $s => $section) {
                $links = $this->links[$section['id']];
                $used = [];
                foreach (self::PERIODS as $p => [$start, $end]) {
                    $count = count($links);
                    for ($k = 0; $k < $count; $k++) {
                        $link = $links[($d + $s + $p + $k) % $count];
                        if (! isset($busy[$day][$p][$link['employee_id']]) && ! isset($used[$link['subject_id']])) {
                            $busy[$day][$p][$link['employee_id']] = true;
                            $used[$link['subject_id']] = true;
                            $rows[] = [
                                'class_id' => $section['class_id'], 'section_id' => $section['id'], 'day' => $day, 'start_time' => $start, 'end_time' => $end,
                                'subject_id' => $link['subject_id'], 'employee_id' => $link['employee_id'], 'room' => 'R-'.(101 + $s),
                            ];
                            break;
                        }
                    }
                }
            }
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('routines')->insert($chunk);
        }
    }

    private function schoolDays(int $count): array
    {
        $days = [];
        for ($d = Carbon::today(); count($days) < $count; $d = $d->copy()->subDay()) {
            if (! $d->isFriday()) {
                $days[] = $d->toDateString();
            }
        }

        return array_reverse($days);
    }

    private function attendance(): void
    {
        $days = $this->schoolDays(30);
        $rows = [];
        foreach ($this->students as $list) {
            foreach ($list as $student) {
                foreach ($days as $date) {
                    $r = mt_rand(1, 100);
                    $rows[] = ['student_id' => $student, 'date' => $date, 'status' => $r <= 88 ? 'Present' : ($r <= 94 ? 'Absent' : ($r <= 98 ? 'Late' : 'Leave')), 'marked_by' => 1];
                }
            }
        }
        foreach (array_chunk($rows, 400) as $chunk) {
            DB::table('attendance')->insert($chunk);
        }

        $rows = [];
        foreach (DB::table('employees')->pluck('id') as $employee) {
            foreach ($days as $date) {
                $rows[] = ['employee_id' => $employee, 'date' => $date, 'status' => mt_rand(1, 100) <= 94 ? 'Present' : 'Leave'];
            }
        }
        DB::table('staff_attendance')->insert($rows);
    }

    private function exams(): void
    {
        $first = DB::table('exams')->insertGetId(['session_id' => $this->sessionId, 'name' => 'First Term Exam', 'start_date' => '2026-04-05', 'end_date' => '2026-04-16', 'is_published' => true]);
        $half = DB::table('exams')->insertGetId(['session_id' => $this->sessionId, 'name' => 'Half Yearly Exam', 'start_date' => '2026-07-05', 'end_date' => '2026-07-16', 'is_published' => true]);
        $annual = DB::table('exams')->insertGetId(['session_id' => $this->sessionId, 'name' => 'Annual Exam', 'start_date' => '2026-12-01', 'end_date' => '2026-12-14', 'is_published' => false]);

        $rows = [];
        foreach ([$first, $half] as $exam) {
            foreach ($this->sections as $section) {
                foreach ($this->students[$section['id']] as $student) {
                    $ability = mt_rand(52, 92);
                    foreach ($this->subjects[$section['class_id']] as $subject) {
                        $rows[] = ['exam_id' => $exam, 'student_id' => $student, 'subject_id' => $subject['id'], 'marks' => max(28, min(99, $ability + mt_rand(-12, 10))), 'full_marks' => 100, 'entered_by' => 1];
                    }
                }
            }
        }
        foreach (array_chunk($rows, 400) as $chunk) {
            DB::table('marks')->insert($chunk);
        }

        foreach ($this->classes as $classId) {
            $date = Carbon::parse('2026-12-01');
            foreach ($this->subjects[$classId] as $subject) {
                while ($date->isFriday()) {
                    $date->addDay();
                }
                DB::table('exam_routines')->insert(['exam_id' => $annual, 'class_id' => $classId, 'subject_id' => $subject['id'], 'date' => $date->toDateString(), 'start_time' => '10:00', 'end_time' => '13:00', 'room' => 'Hall 1']);
                $date->addDay();
            }
        }
    }

    private function dailyWork(): void
    {
        $tasks = ['Chapter review questions', 'Practice worksheet', 'Short paragraph writing', 'Vocabulary exercise', 'Problem set'];
        $topics = ['Introduced the new chapter and key terms.', 'Solved examples together on the board.', 'Group reading followed by a short discussion.', 'Revision of last week\'s lesson.'];

        foreach ($this->sections as $section) {
            foreach ($this->links[$section['id']] as $n => $link) {
                $subjectName = collect($this->subjects[$section['class_id']])->firstWhere('id', $link['subject_id'])['name'];
                $shared = ['class_id' => $link['class_id'], 'section_id' => $link['section_id'], 'subject_id' => $link['subject_id'], 'employee_id' => $link['employee_id']];

                if ($n < 4) {
                    DB::table('homework')->insert($shared + [
                        'title' => "$subjectName: ".$tasks[$n % 5], 'body' => 'Complete in your exercise book and bring it to the next class.',
                        'assigned_date' => today()->subDays($n + 1)->toDateString(), 'due_date' => today()->addDays(3 - $n)->toDateString(),
                    ]);
                }
                if ($n < 3) {
                    DB::table('diaries')->insert($shared + ['title' => "$subjectName class note", 'body' => $topics[$n % 4], 'date' => today()->subDays($n)->toDateString()]);
                }
                if ($n < 2) {
                    $test = DB::table('class_tests')->insertGetId($shared + ['title' => "$subjectName Class Test ".($n + 1), 'date' => today()->subDays(6 + $n * 4)->toDateString(), 'full_marks' => 20]);
                    foreach ($this->students[$section['id']] as $student) {
                        DB::table('class_test_marks')->insert(['class_test_id' => $test, 'student_id' => $student, 'marks' => mt_rand(9, 20)]);
                    }
                }
            }
        }

        $first = $this->students[$this->sections[0]['id']];
        DB::table('student_leaves')->insert([
            ['student_id' => $first[0], 'type' => 'Sick', 'from_date' => today()->addDay()->toDateString(), 'to_date' => today()->addDays(2)->toDateString(), 'reason' => 'Fever', 'details' => 'Doctor advised two days of rest.', 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
            ['student_id' => $first[1], 'type' => 'Family', 'from_date' => today()->subDays(12)->toDateString(), 'to_date' => today()->subDays(10)->toDateString(), 'reason' => 'Family programme', 'details' => null, 'status' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function finance(): void
    {
        $tuition = DB::table('fee_heads')->insertGetId(['name' => 'Monthly Tuition', 'amount' => 1500, 'frequency' => 'Monthly']);
        $examFee = DB::table('fee_heads')->insertGetId(['name' => 'Exam Fee', 'amount' => 800, 'frequency' => 'One time']);
        DB::table('fee_heads')->insert(['name' => 'Admission Fee', 'amount' => 5000, 'frequency' => 'One time']);

        $serial = 1;
        $methods = ['Cash', 'Cash', 'bKash', 'Nagad', 'Bank'];
        $make = function (int $student, int $head, string $title, string $period, float $amount, Carbon $due, float $chance) use (&$serial, $methods) {
            $roll = mt_rand(1, 100) / 100;
            $paid = $roll <= $chance ? $amount : ($roll <= $chance + 0.1 ? round($amount / 2, -2) : 0);
            $id = DB::table('invoices')->insertGetId([
                'invoice_no' => sprintf('INV-2026-%05d', $serial++), 'student_id' => $student, 'fee_head_id' => $head, 'title' => $title, 'period' => $period,
                'amount' => $amount, 'paid' => $paid, 'due_date' => $due->toDateString(), 'status' => $paid >= $amount ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            ]);
            if ($paid > 0) {
                $on = $due->copy()->subDays(mt_rand(0, 7));
                DB::table('payments')->insert(['invoice_id' => $id, 'amount' => $paid, 'method' => $methods[array_rand($methods)], 'paid_on' => min($on, today())->toDateString(), 'received_by' => 1]);
            }
        };

        foreach ($this->students as $list) {
            foreach ($list as $student) {
                for ($back = 5; $back >= 0; $back--) {
                    $month = today()->startOfMonth()->subMonths($back);
                    $make($student, $tuition, 'Monthly Tuition', $month->format('Y-m'), 1500, $month->copy()->day(10), $back === 0 ? 0.25 : 0.82);
                }
                $make($student, $examFee, 'Half Yearly Exam Fee', '2026-half-yearly', 800, Carbon::parse('2026-06-25'), 0.9);
            }
        }

        foreach ([['Tuition income', 'Income'], ['Donations', 'Income'], ['Utilities', 'Expense'], ['Stationery', 'Expense'], ['Maintenance', 'Expense']] as [$name, $kind]) {
            DB::table('account_heads')->insert(['name' => $name, 'kind' => $kind]);
        }
        DB::table('transactions')->insert([
            ['account_head_id' => 3, 'amount' => 18500, 'date' => today()->subDays(4)->toDateString(), 'note' => 'Electricity and water bill'],
            ['account_head_id' => 4, 'amount' => 6200, 'date' => today()->subDays(9)->toDateString(), 'note' => 'Exam papers and markers'],
            ['account_head_id' => 5, 'amount' => 12000, 'date' => today()->subDays(15)->toDateString(), 'note' => 'Classroom fan repair'],
            ['account_head_id' => 2, 'amount' => 50000, 'date' => today()->subDays(20)->toDateString(), 'note' => 'Alumni association gift'],
        ]);

        foreach (DB::table('employees')->get() as $employee) {
            foreach ([1 => 'Paid', 0 => 'Unpaid'] as $back => $status) {
                $month = today()->startOfMonth()->subMonths($back);
                $allowance = round($employee->basic_salary * 0.1);
                DB::table('payroll')->insert([
                    'employee_id' => $employee->id, 'month' => $month->format('Y-m'), 'basic' => $employee->basic_salary, 'allowance' => $allowance, 'deduction' => 0,
                    'net' => $employee->basic_salary + $allowance, 'status' => $status, 'paid_on' => $status === 'Paid' ? $month->copy()->endOfMonth()->toDateString() : null,
                ]);
            }
        }
    }

    private function office(): void
    {
        $classes = array_values($this->classes);
        $apps = [['Ayaan Rahman', 'Male', 'Motiur Rahman'], ['Zarin Tasnim', 'Female', 'Kabir Hossain'], ['Rohan Ahmed', 'Male', 'Selim Ahmed'], ['Nuha Islam', 'Female', 'Tariqul Islam'], ['Samin Khan', 'Male', 'Bashir Khan']];
        foreach ($apps as $i => [$name, $gender, $guardian]) {
            DB::table('admissions')->insert([
                'reference' => 'ABC-26-'.(1001 + $i), 'name' => $name, 'gender' => $gender, 'dob' => (2018 - $i).'-05-14', 'class_id' => $classes[$i + 1],
                'guardian_name' => $guardian, 'phone' => '0191'.(4000100 + $i), 'address' => 'Dhaka', 'status' => $i === 4 ? 'Rejected' : 'Pending', 'created_at' => now()->subDays($i), 'updated_at' => now()->subDays($i),
            ]);
        }

        DB::table('staff_leaves')->insert([
            ['employee_id' => 3, 'type' => 'Casual', 'from_date' => today()->addDays(3)->toDateString(), 'to_date' => today()->addDays(4)->toDateString(), 'reason' => 'Family matter', 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('holidays')->insert([
            ['title' => 'Victory Day', 'from_date' => '2026-12-16', 'to_date' => '2026-12-16'],
            ['title' => 'Winter Vacation', 'from_date' => '2026-12-20', 'to_date' => '2026-12-31'],
            ['title' => 'Language Martyrs\' Day', 'from_date' => '2027-02-21', 'to_date' => '2027-02-21'],
        ]);

        $books = [
            ['Sea of Stories', 'Anwar Kabir', 'Fiction', 4], ['Young Scientists Handbook', 'Lina Haque', 'Science', 3], ['Everyday Mathematics', 'R. Sen', 'Mathematics', 6],
            ['A Short History of Bengal', 'M. Rahim', 'History', 3], ['Grammar in Practice', 'S. Dutta', 'Language', 8], ['World Atlas for Schools', 'Atlas House', 'Reference', 2],
            ['Computers for Beginners', 'T. Islam', 'ICT', 5], ['Rivers of Our Land', 'Nasir Uddin', 'Geography', 3],
        ];
        foreach ($books as $i => [$title, $author, $category, $copies]) {
            DB::table('books')->insert(['title' => $title, 'author' => $author, 'category' => $category, 'copies' => $copies, 'isbn' => '978-984-'.(10000 + $i * 37), 'shelf' => 'S-'.($i % 4 + 1)]);
        }
        $issued = collect($this->students)->flatten()->take(6)->values();
        foreach ($issued as $i => $student) {
            DB::table('book_issues')->insert(['book_id' => $i % 8 + 1, 'student_id' => $student, 'issue_date' => today()->subDays(5 + $i)->toDateString(), 'due_date' => today()->addDays(9 - $i * 3)->toDateString(), 'return_date' => null]);
        }

        DB::table('transport_routes')->insert([
            ['name' => 'Mirpur to Campus', 'vehicle' => 'Bus 01', 'driver' => 'Shamim Mia', 'phone' => '01612000101', 'fare' => 1200],
            ['name' => 'Uttara to Campus', 'vehicle' => 'Bus 02', 'driver' => 'Babul Sheikh', 'phone' => '01612000102', 'fare' => 1500],
        ]);
        DB::table('hostels')->insert([
            ['name' => 'Shapla House', 'kind' => 'Boys', 'capacity' => 60, 'warden' => 'Abdul Malek', 'phone' => '01612000201'],
            ['name' => 'Bokul House', 'kind' => 'Girls', 'capacity' => 50, 'warden' => 'Rumana Parvin', 'phone' => '01612000202'],
        ]);
    }
}
