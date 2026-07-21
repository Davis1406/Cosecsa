<?php

namespace Database\Seeders;

use App\Models\ProgressReportSetting;
use App\Models\ProgressReportTaskTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgressReportSeeder extends Seeder
{
    // section label => recurring task titles, sourced from the actual
    // March/May 2026 Secretariat Reports so every staff member starts with
    // their real recurring workload already on file instead of a blank sheet.
    protected const TASKS_BY_LABEL = [
        'CEO' => [],
        'FINANCE OFFICER (JONATHAN OMONGOLE)' => [
            'Reconciliation of promotion materials',
            'Operation Smile programmatic report under the second agreement.',
            'Report on status of funds received following the Presidential pledge ahead of 25 years celebration',
            'Opening of a scheme account for investment of funds under a Unit Trust',
            'Annual workplan',
            'Salary breakdown for COSECSA Managing Editor',
            'Preparation of monthly Bank reconciliation',
            'Payroll preparation',
            'Weekly expenditure report',
            'Processing payments',
        ],
        'RESEARCH AND PATIENT OUTCOMES COORDINATION – Dr. Godfrey Sama Philipo' => [
            'Fellow and Trainee small research grants',
            'Rural Surgery',
            'CPD Program',
            'Surgical Audits and Standards',
            'Intuitive Foundation Research Grant',
            'The East and Central African Journal of Surgery (ECAJS)',
            'COSECSA Institutional Review Board',
        ],
        'EXAMINATION OFFICER (AMANI PASCAL)' => [
            'Admission Assistant Orientation',
            'Examination Preparations',
            'MCS & FCS Programme Entry',
            'MCS & FCS Programme Entry Payments',
            'New trainees and trainers SFS and e-Logbook logins',
            'Admission letters and programme details to the new trainees',
            'Transcripts and verifications',
            'Support other Secretariat activities',
        ],
        'EDUCATION OFFICER (NIRAJ BACHHETA)' => [
            'Enrolment of Trainees',
            'Admissions System',
            'MCS Modules',
            'Granting Trainer Access',
            'DeckerMed GS Weekly Curriculum',
            'Scholarships',
            'Support',
            'Workshops',
            'Accreditation',
        ],
        'ADMINISTRATIVE OFFICER (DIANA KAIZA)' => [
            'Operation Smile and Smile Train Post Fellowship Programme',
            'Annual workplan',
            'Research Methodology workshop',
            "President's travel",
            'Travel Fellow arrangements',
            'Newsletter',
        ],
        'ACADEMIC AND RECORDS ASSISTANT (EDNA HERMAN)' => [
            'Hospital Accreditation certificates',
            'FCS and MCS Certificates',
            'Data Management and Records Policy',
            'Certification of Certificate',
            'Post a list of all Fellows (honorary, Foundation, By Election and by Examination) Members, on COSECSA website',
            'Attendance Certificates for Annual Scientific Conference',
            'Emails correspondence',
        ],
        'IT ASSISTANT (LAURENCE KISANGA)' => [
            'COSECSA Journal system restoration',
            'Website and social media posts',
            'Software installation and licensing support',
            'Office equipment troubleshooting',
            'COSECSA Alumni WebApp',
            'Online payment gateway support',
        ],
        'ADMISSION ASSISTANT (DAVIS KONDAMWALI)' => [
            'MCS & FCS Programme Entry',
            'MIS System Updates and Backup Database',
            'SFS and Logbook Support',
            'Voting System',
            'Development of the college Research Training system.',
        ],
    ];

    public function run(): void
    {
        ProgressReportSetting::firstOrCreate([], [
            'due_day' => 24,
            'reminder_days_before' => 3,
            'reminder_enabled' => true,
        ]);

        foreach (config('progress_report_sections') as $section) {
            $userId = $section['user_id'];
            if (! User::where('id', $userId)->exists()) {
                continue;
            }

            $tasks = self::TASKS_BY_LABEL[$section['label']] ?? [];
            foreach ($tasks as $i => $title) {
                ProgressReportTaskTemplate::updateOrCreate(
                    ['user_id' => $userId, 'activity_description' => $title],
                    ['is_active' => true, 'sort_order' => $i]
                );
            }
        }
    }
}
