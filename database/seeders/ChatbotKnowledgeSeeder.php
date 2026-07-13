<?php

namespace Database\Seeders;

use App\Models\ChatbotKnowledge;
use Illuminate\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->knowledge() as $item) {
            foreach ($item['questions'] as $question) {
                ChatbotKnowledge::query()->updateOrCreate(
                    ['normalized_question' => $this->normalize($question)],
                    [
                        'question' => $question,
                        'answer' => $item['answer'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function knowledge(): array
    {
        return [
            [
                'questions' => [
                    'How do I add a student?',
                    'Student add',
                    'Create new student',
                    'How to create student record?',
                ],
                'answer' => 'Go to People > Students, click + Add Student, fill personal details, contact details, college, programme, category, and upload a photo if available. Click Create Student to save. Students are not created from Institution > Users. The system creates the linked Student login automatically using enrollment number as username and DOB in ddmmyyyy format as the first password.',
            ],
            [
                'questions' => [
                    'How do I edit a student?',
                    'Student edit',
                    'Update student details',
                ],
                'answer' => 'Go to People > Students, find the student, click Edit, change the required details, optionally upload a new photo to replace the old one, then click Update Student.',
            ],
            [
                'questions' => [
                    'How do I delete a student?',
                    'Student delete',
                    'Remove student record',
                ],
                'answer' => 'Go to People > Students and click Delete beside the student. Confirm the prompt. Use this carefully because deleting a student can affect related records.',
            ],
            [
                'questions' => [
                    'How do I activate or deactivate a student?',
                    'Student active inactive',
                    'Deactivate student',
                ],
                'answer' => 'Go to People > Students. Use Deactivate for an active student or Activate for an inactive student. You can also change the Active checkbox from the student edit form.',
            ],
            [
                'questions' => [
                    'How do I add faculty?',
                    'Faculty add',
                    'How do I add staff?',
                    'Create staff member',
                ],
                'answer' => 'Go to People > Staff, click + Add Staff, enter college, department, employee code, name, email, staff type, staff role, employment type, and profile details. Upload a photo if needed, then click Create. Staff are not created from Institution > Users. The system creates the linked Staff login automatically using employee code as username and DOB in ddmmyyyy format as the first password. If DOB is blank, employee code is used as the first password.',
            ],
            [
                'questions' => [
                    'How do I edit faculty?',
                    'Faculty edit',
                    'Update staff',
                ],
                'answer' => 'Go to People > Staff, click Edit beside the staff member, update the details or upload a new photo, then click Save.',
            ],
            [
                'questions' => [
                    'How do I delete faculty?',
                    'Delete staff',
                    'Remove faculty',
                ],
                'answer' => 'Go to People > Staff and click Delete beside the staff member. Confirm only if you are sure the staff record should be removed.',
            ],
            [
                'questions' => [
                    'How do I enter marks?',
                    'Marks entry',
                    'How to add exam marks?',
                ],
                'answer' => 'Go to Exams > Marks Entry, select the exam subject, click Load, enter theory, practical, internal marks, and result status for each student, then click Save Marks.',
            ],
            [
                'questions' => [
                    'How do I see results?',
                    'Result generation',
                    'View exam results',
                ],
                'answer' => 'Go to Exams > Results to view generated result summaries such as student, exam, SGPA, CGPA, backlog count, and overall status. Enter marks first if no results are visible.',
            ],
            [
                'questions' => [
                    'How do I add attendance?',
                    'Mark attendance',
                    'Attendance entry',
                    'Take attendance',
                ],
                'answer' => 'Use the Attendance module. First configure staff assignments, timetable slots, and lectures if needed. Then go to Attendance > Lectures and click Mark for the lecture. On Mark Attendance you can mark each student as Present, Absent, Late, or Excused, use Mark all shortcuts, add remarks, and click Save Attendance. Cancelled lectures cannot be marked.',
            ],
            [
                'questions' => [
                    'How do I assign subject to staff?',
                    'Staff subject assignment',
                    'Assign faculty subject',
                ],
                'answer' => 'Go to Attendance > Teaching Staff Subject Assignments, select the teaching staff member, subject, semester, and related academic details, then save the assignment. These assignments help organize lectures and attendance.',
            ],
            [
                'questions' => [
                    'How do I create timetable slot?',
                    'Timetable slots',
                    'Add lecture slot',
                ],
                'answer' => 'Go to Attendance > Timetable Slots, enter the day, time, semester, subject, staff, and room information, then save the slot.',
            ],
            [
                'questions' => [
                    'How do I check attendance summary?',
                    'Attendance summary',
                    'Attendance report',
                ],
                'answer' => 'Go to Attendance > Attendance Summary to view attended lectures, total lectures, and attendance percentage by student, subject, and semester.',
            ],
            [
                'questions' => [
                    'How do I create notice?',
                    'Add notice',
                    'Create announcement',
                    'Publish notice',
                ],
                'answer' => 'Go to Notices > Notices, select college and category, enter title and content, choose priority and audience type, set dates if needed, choose publish options, then save.',
            ],
            [
                'questions' => [
                    'How do I set notice audience?',
                    'Notice audience',
                    'Who can see notice?',
                ],
                'answer' => 'Go to Notices > Audience, select the notice, choose the target type such as Department, Programme, Semester, Role, or Individual, enter the target, and save.',
            ],
            [
                'questions' => [
                    'How do I upload notice attachment?',
                    'Notice attachments',
                    'Attach file to notice',
                ],
                'answer' => 'Go to Notices > Attachments, select the notice, choose a PDF, DOC, DOCX, JPG, PNG, or WEBP file, then click Upload Attachment. The file path is saved automatically.',
            ],
            [
                'questions' => [
                    'How do I add fee category?',
                    'Fee categories',
                    'Create fee category',
                ],
                'answer' => 'Go to Fees > Fee Categories, enter the category details, and save. Fee categories are used while creating fee structures.',
            ],
            [
                'questions' => [
                    'How do I create fee structure?',
                    'Fee structure',
                    'Add fee structure',
                ],
                'answer' => 'Go to Fees > Fee Structures, select the fee category and related academic details, enter the amount and applicable information, then save.',
            ],
            [
                'questions' => [
                    'How do I collect fees?',
                    'Fee collection',
                    'Student fee payment',
                ],
                'answer' => 'Go to Fees > Fee Collection, select the student ledger or fee record, enter payment details such as amount, mode, and status, then save the collection.',
            ],
            [
                'questions' => [
                    'How do I print fee receipt?',
                    'Fee receipt',
                    'Print receipt',
                ],
                'answer' => 'Go to Reports > Fee Receipts or Fees > Receipts, find the receipt, and use the Print option where available.',
            ],
            [
                'questions' => [
                    'How do I generate hall ticket?',
                    'Hall ticket',
                    'Print hall ticket',
                ],
                'answer' => 'Go to Exams > Hall Ticket Config to set configuration if needed, then use Exams > Hall Tickets or Reports > Hall Ticket PDF to view or print hall tickets.',
            ],
            [
                'questions' => [
                    'How do I create exam?',
                    'Add exam',
                    'Exam setup',
                ],
                'answer' => 'Go to Exams > Exams, create the exam with name, type, semester, academic year, and date details. After that, configure exam subjects and marks entry.',
            ],
            [
                'questions' => [
                    'How do I add exam subject?',
                    'Exam subjects',
                    'Configure exam subject',
                ],
                'answer' => 'Go to Exams > Exam Subjects, select the exam and subject, then add theory, practical, or internal settings as required.',
            ],
            [
                'questions' => [
                    'How do I add programme?',
                    'Create programme',
                    'Programme add',
                ],
                'answer' => 'Go to Academic > Programmes, click the create/add option, enter programme code, name, level, duration, department, and status, then save.',
            ],
            [
                'questions' => [
                    'How do I add subject?',
                    'Subject add',
                    'Create subject',
                ],
                'answer' => 'Go to Academic > Subjects, click create/add, enter subject code, name, semester or curriculum details as required, credits, and status, then save.',
            ],
            [
                'questions' => [
                    'How do I add user?',
                    'Create user account',
                    'User management',
                ],
                'answer' => 'Go to Institution > Users, click + Add User, and create administration accounts only. Staff logins are created from People > Staff, and student logins are created from People > Students. Use Institution > Users later to adjust permissions, status, or scope.',
            ],
            [
                'questions' => [
                    'What can you help with?',
                    'Help menu',
                    'Show chatbot options',
                    'What questions can I ask?',
                ],
                'answer' => 'You can ask about students, staff/faculty, attendance, marks entry, results, exams, hall tickets, fees, receipts, notices, notice attachments, academic setup, users, roles, and password changes.',
            ],
            [
                'questions' => [
                    'Where is student module?',
                    'Open student module',
                    'Student menu',
                ],
                'answer' => 'Open the left sidebar and go to People > Students. From there you can add, edit, view, activate, deactivate, or delete student records.',
            ],
            [
                'questions' => [
                    'Where is faculty module?',
                    'Where is staff module?',
                    'Staff menu',
                ],
                'answer' => 'Open the left sidebar and go to People > Staff. From there you can add, edit, filter, or delete staff and faculty records.',
            ],
            [
                'questions' => [
                    'How do I change password?',
                    'Change my password',
                    'Password change',
                ],
                'answer' => 'Open Profile or the Change Password page, enter your current password, enter and confirm the new password, then save.',
            ],
            [
                'questions' => [
                    'How do I use this application?',
                    'Application help',
                    'How to use GTU ITR?',
                ],
                'answer' => 'Use the left sidebar to open modules. Institution manages colleges, departments, users, and roles. People manages staff and students. Academic manages programmes, semesters, subjects, curriculum, and electives. Attendance, Exams, Fees, Leave, Notices, and Reports handle daily academic operations.',
            ],
        ];
    }

    private function normalize(string $question): string
    {
        $question = mb_strtolower($question);
        $question = preg_replace('/[^a-z0-9\s]/u', ' ', $question) ?? $question;
        $question = preg_replace('/\s+/', ' ', $question) ?? $question;

        return trim($question);
    }
}
