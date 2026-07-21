<?php

namespace Database\Seeders;

use App\Models\CollegeLetterheadSetting;
use App\Models\LetterTemplate;
use App\Models\TranscriptTemplate;
use Illuminate\Database\Seeder;

class CollegeLettersSeeder extends Seeder
{
    public function run(): void
    {
        // Reuse the already-uploaded COSECSA crest/address/footer from the
        // default Transcript Template rather than asking someone to
        // re-upload the same assets for the letterhead.
        $transcript = TranscriptTemplate::where('is_default', true)->first();

        $settings = CollegeLetterheadSetting::current();
        if (empty($settings->logo_path) && $transcript) {
            $settings->institution_name = 'College of Surgeons of East, Central and Southern Africa (COSECSA)';
            $settings->address_text = $transcript->address_text;
            $settings->footer_text = $transcript->footer_text;
            $settings->logo_path = $transcript->logo_path;
            $settings->watermark_path = $transcript->watermark_path;
            $settings->save();
        }

        $adminUserId = \App\Models\User::where('email', 'admission@cosecsa.org')->value('id');

        LetterTemplate::updateOrCreate(
            ['name' => 'Admission Letter'],
            [
                'subject'             => 'COSECSA Admission Letter — {{name}}',
                'recipient_source'    => 'trainees',
                'legacy_status_field' => 'admission_letter_status',
                'is_active'           => true,
                'created_by'          => $adminUserId,
                'email_body'          => "Dear {{first_name}},\n\nPlease find attached your COSECSA admission letter.\n\nCongratulations, and welcome to COSECSA.",
                'pdf_body'            => "REF: ADMISSION TO COSECSA TRAINING PROGRAMME\n\n"
                    . "Following your Application for COSECSA {{programme}} programme, I am pleased to inform you that you have been accepted to the training programme at {{hospital}}. Kindly be advised that you are required to be at your training Centre early before the start of the training.\n\n"
                    . "The successful completion of your training leads to the qualification of Membership of College of Surgeons of East, Central and Southern Africa, MCS (ECSA).\n\n"
                    . "Important information for you to note:\n"
                    . "Your COSECSA programme entry number: {{entry_number}}\n"
                    . "Accredited Hospital: {{hospital}}\n"
                    . "Expected Exam Year: {{exam_year}}\n\n"
                    . "I wish you success in your entire period of training.",
            ]
        );

        LetterTemplate::updateOrCreate(
            ['name' => 'Invitation Letter (Login Credentials)'],
            [
                'subject'             => 'COSECSA Login Credentials — {{name}}',
                'recipient_source'    => 'trainees',
                'legacy_status_field' => 'invitation_letter_status',
                'is_active'           => true,
                'created_by'          => $adminUserId,
                'email_body'          => "Dear {{first_name}},\n\nPlease find attached your COSECSA login credentials and orientation letter.",
                'pdf_body'            => "This is to confirm that you have been registered as a COSECSA {{programme}} trainee.\n\n"
                    . "Please take note of the following important details.\n"
                    . "1. Your COSECSA Program Entry Number: {{entry_number}}\n"
                    . "2. COSECSA Accredited Hospital: {{hospital}}\n"
                    . "3. Examination year: {{exam_year}}\n"
                    . "4. COSECSA Training Programme: {{programme}}\n\n"
                    . "The regulations for your training programme along with further information can be accessed through https://www.cosecsa.org/mcs-ecsa-information/\n\n"
                    . "COSECSA Login Credentials\n\n"
                    . "Your credentials will be used for both the online learning platform (School for Surgeons) and the Bespoke Electronic Logbook:\n\n"
                    . "SFS/Logbook Username: {{sfs_username}}\n"
                    . "SFS/Logbook Password: {{sfs_password}}\n\n"
                    . "You are required to record all operations which you perform or assisted in during the period of your COSECSA training in the COSECSA electronic logbook, accessible at https://logbook.cosecsa.org\n\n"
                    . "You can access School for Surgeons, COSECSA's e-learning platform, at https://www.schoolforsurgeons.net\n\n"
                    . "If you are having any difficulties or any questions, please don't hesitate to write back to the Secretariat Office.",
            ]
        );
    }
}
