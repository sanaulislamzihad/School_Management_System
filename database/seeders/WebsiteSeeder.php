<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Public website content: settings, pages, people, notices, gallery and videos. */
class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'school_name' => 'ABC School',
            'tagline' => 'Learning today, leading tomorrow',
            'address' => '12 Lake Road, Mirpur, Dhaka 1216',
            'phone' => '01700-000000',
            'email' => 'info@abcschool.example',
            'eiin' => '100200',
            'school_code' => '4521',
            'established' => '1998',
            'ticker' => 'Admission for the 2027 session is now open. Apply online or visit the school office between 9 AM and 2 PM.',
        ] as $key => $value) {
            DB::table('settings')->insert(compact('key', 'value'));
        }

        $this->pages();
        $this->people();
        $this->news();
        $this->media();

        DB::table('links')->insert([
            ['title' => 'Ministry of Education', 'url' => 'https://www.moedu.gov.bd/'],
            ['title' => 'Dhaka Education Board', 'url' => 'https://www.dhakaeducationboard.gov.bd/'],
            ['title' => 'Directorate of Secondary and Higher Education', 'url' => 'https://www.dshe.gov.bd/'],
            ['title' => 'National University', 'url' => 'https://www.nu.ac.bd/'],
            ['title' => 'Bangladesh National Portal', 'url' => 'https://bangladesh.gov.bd/'],
        ]);
    }

    private function pages(): void
    {
        $pages = [
            ['about', 'history', 'History of ABC School', 'academic-building', "ABC School opened in 1998 with three classrooms and a simple promise: every child deserves a teacher who knows their name.\n\nToday the school runs from Play to Class 10 on a single campus. Laboratories, a library and a large playground have grown around that first building, but the promise has stayed the same."],
            ['about', 'achievements', 'Our Achievements', 'hall-assembly', "Our students consistently perform well in public examinations, and many go on to leading colleges.\n\nThe school has also earned regional recognition in debate, science fairs and inter-school athletics."],
            ['about', 'facilities', 'Campus Facilities', 'main-building', "Students learn in bright classrooms, a fully equipped science laboratory, a computer lab and a well-stocked library.\n\nThe campus also has a playground, a prayer room, safe drinking water and first-aid support during school hours."],
            ['about', 'future-plans', 'Future Plans', 'campus', "Over the next five years we plan to add a new science block, expand digital classrooms and introduce a scholarship fund for students who need support."],
            ['activities', 'scouts', 'Scouts', 'campus-assembly', "Our scout troop builds leadership, teamwork and service through weekly meetings, camps and community projects."],
            ['activities', 'debate-club', 'Debate Club', 'group-study', "The debate club meets every Thursday. Members practise public speaking and represent the school at inter-school competitions."],
            ['activities', 'sports', 'Sports', 'relay-race', "Football, cricket, badminton and athletics are part of school life. Annual Sports Week is a highlight of the calendar."],
            ['activities', 'computer-lab', 'Computer Lab', 'computer-lab', "Students from Class 3 upward build practical digital skills, from typing and spreadsheets to basic programming."],
            ['activities', 'science-club', 'Science Club', 'chemistry-lab', "Science Club members run experiments, build projects and present their work at the annual science fair."],
            ['activities', 'magazine', 'Annual Magazine', 'library', "Our yearly magazine publishes stories, poems, artwork and essays written by students and teachers."],
            ['rules', 'rules', 'School Rules', null, "Students must arrive before the morning assembly, wear the correct uniform and carry their ID card every day.\n\nMobile phones are not allowed in classrooms."],
            ['rules', 'discipline', 'Code of Conduct', null, "We expect respect for teachers, classmates and school property. Bullying of any kind is treated seriously and reported to guardians."],
            ['rules', 'exam-system', 'Examination System', null, "The academic year has three terminal examinations, supported by regular class tests. Results use a five-point GPA scale."],
            ['rules', 'uniform', 'Uniform', null, "Boys wear a white shirt and navy trousers. Girls wear a white kameez with a navy salwar and belt. Black shoes are required on all school days."],
            ['admission', 'admission-process', 'Admission Process', null, "Submit the online application or collect a form from the office. Shortlisted applicants attend a short assessment, and results are shared by SMS."],
            ['admission', 'admission-fees', 'Admission and Other Fees', null, "Admission fee is 5,000 taka. Monthly tuition is 1,500 taka. A separate fee applies for each terminal examination."],
            ['admission', 'fee-payment', 'Fee Payment', null, "Fees can be paid at the school office in cash, by bank deposit, or through bKash and Nagad. Please keep your payment receipt."],
        ];

        foreach ($pages as [$group, $slug, $title, $image, $body]) {
            DB::table('web_pages')->insert(['slug' => $slug, 'menu_group' => $group, 'title' => $title, 'body' => $body, 'image' => $image ? "images/school/$image.jpg" : null]);
        }
    }

    private function people(): void
    {
        DB::table('web_messages')->insert([
            ['slug' => 'head-teacher', 'heading' => 'Message from the Head Teacher', 'name' => 'Rezaul Karim', 'designation' => 'Head Teacher', 'photo' => 'images/people/m60.jpg',
                'body' => "At ABC School, we believe that good education shapes character as much as it builds knowledge. Our teachers work closely with every student so that no child is left behind.\n\nWe encourage curiosity, honesty and hard work, and we are proud to partner with families on this journey. Thank you for placing your trust in us."],
            ['slug' => 'chairperson', 'heading' => 'Message from the Chairperson', 'name' => 'Nasima Rahman', 'designation' => 'Chairperson, Governing Body', 'photo' => 'images/people/f90.jpg',
                'body' => "For more than two decades, ABC School has served our community with dedication. Our goal is a safe, modern and caring school where every student can discover their strengths.\n\nThe governing body remains committed to better facilities, skilled teachers and a brighter future for all our children."],
        ]);

        $rows = [
            ['governing-body', 'Nasima Rahman', 'Chairperson', 'images/people/f90.jpg'], ['governing-body', 'Rezaul Karim', 'Member Secretary', 'images/people/m60.jpg'],
            ['governing-body', 'Golam Mostafa', 'Guardian Representative', 'images/people/m22.jpg'], ['governing-body', 'Dilruba Ahmed', 'Guardian Representative', 'images/people/f33.jpg'],
            ['management-committee', 'Golam Mostafa', 'Convener', 'images/people/m22.jpg'], ['management-committee', 'Dilruba Ahmed', 'Member', 'images/people/f33.jpg'],
            ['former-headmasters', 'Abul Kashem', 'Head Teacher, 1998 to 2010', null], ['former-headmasters', 'Mominul Haque', 'Head Teacher, 2010 to 2019', null],
            ['former-presidents', 'Shamsul Alam', 'Chairperson, 1998 to 2012', null], ['former-presidents', 'Rokeya Sultana', 'Chairperson, 2012 to 2021', null],
            ['donors', 'ABC Alumni Association', 'Library renovation', null], ['donors', 'Rahman Family Trust', 'Annual scholarships', null],
            ['harassment-committee', 'Farhana Yasmin', 'Convener', 'images/people/f12.jpg'], ['harassment-committee', 'Nusrat Jahan', 'Member', 'images/people/f26.jpg'],
            ['student-cabinet', 'Tasnim Ahmed', 'Student President, Class 10', null], ['student-cabinet', 'Fahim Hossain', 'General Secretary, Class 9', null], ['student-cabinet', 'Maliha Rahman', 'Cultural Secretary, Class 9', null],
        ];
        foreach ($rows as $i => [$group, $name, $designation, $photo]) {
            DB::table('web_people')->insert(['group_name' => $group, 'name' => $name, 'designation' => $designation, 'photo' => $photo, 'position' => $i]);
        }

        DB::table('achievers')->insert([
            ['name' => 'Arif Chowdhury', 'exam' => 'SSC', 'gpa' => '5.00', 'year' => '2025', 'photo' => null], ['name' => 'Sumaiya Hossain', 'exam' => 'SSC', 'gpa' => '5.00', 'year' => '2025', 'photo' => null],
            ['name' => 'Tamim Sarker', 'exam' => 'SSC', 'gpa' => '4.89', 'year' => '2024', 'photo' => null], ['name' => 'Anika Rahman', 'exam' => 'JSC', 'gpa' => '5.00', 'year' => '2024', 'photo' => null],
            ['name' => 'Rayhan Uddin', 'exam' => 'SSC', 'gpa' => '5.00', 'year' => '2023', 'photo' => null], ['name' => 'Jannat Khan', 'exam' => 'SSC', 'gpa' => '4.94', 'year' => '2023', 'photo' => null],
        ]);
    }

    private function news(): void
    {
        $items = [
            ['Admission open for the 2027 session', 'Admission', 'Everyone', 1, 'Applications for Play to Class 9 are now open. Please complete the online form or visit the office with the required documents.'],
            ['Annual Exam schedule published', 'Exam', 'Everyone', 1, 'The Annual Exam starts on 1 December. Students can view the full routine in the student portal.'],
            ['Guardian meeting on Saturday', 'Meeting', 'Students', 0, 'Guardians of Classes 6 to 10 are invited to a meeting with class teachers at 10 AM in the main hall.'],
            ['Winter uniform reminder', 'General', 'Students', 0, 'Students may wear the navy school sweater from next week. Plain jackets are not allowed.'],
            ['Staff meeting on exam preparation', 'Meeting', 'Staff', 0, 'All teachers will meet in the conference room on Thursday at 2 PM.'],
            ['Science fair registration', 'Event', 'Everyone', 0, 'Students who wish to present a project should register with their science teacher by the end of this month.'],
            ['School closed for Victory Day', 'Holiday', 'Everyone', 0, 'The school will remain closed on 16 December in observance of Victory Day.'],
        ];
        foreach ($items as $i => [$title, $category, $audience, $pinned, $body]) {
            DB::table('notices')->insert(compact('title', 'category', 'audience', 'pinned', 'body') + ['date' => today()->subDays($i * 2)->toDateString()]);
        }

        DB::table('events')->insert([
            ['title' => 'Annual Science Fair', 'date' => today()->addDays(12)->toDateString(), 'location' => 'Main hall', 'body' => 'Student projects, live experiments and awards.'],
            ['title' => 'Victory Day Programme', 'date' => '2026-12-16', 'location' => 'School ground', 'body' => 'Flag hoisting, songs and a cultural programme.'],
            ['title' => 'Annual Sports Week', 'date' => '2027-01-18', 'location' => 'School ground', 'body' => 'Track events, team games and the closing ceremony.'],
        ]);

        DB::table('downloads')->insert([
            ['title' => 'Academic Calendar 2026', 'category' => 'Calendar', 'url' => '#', 'date' => '2026-01-02'],
            ['title' => 'Syllabus: Annual Exam', 'category' => 'Syllabus', 'url' => '#', 'date' => today()->subDays(10)->toDateString()],
        ]);
    }

    private function media(): void
    {
        $media = json_decode(file_get_contents(resource_path('data/media.json')), true);

        foreach ($media['photos'] as $photo) {
            DB::table('gallery')->insert(['title' => $photo['caption'], 'category' => 'Campus life', 'image_url' => $photo['file']]);
        }
        foreach ($media['videos'] as $video) {
            DB::table('videos')->insert(['title' => $video['title'], 'src' => $video['src'], 'poster' => $video['poster']]);
        }
    }
}
