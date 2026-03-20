<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Global Business Hours
        Setting::set('business_hours_start', '09:00', 'string', 'sla');
        Setting::set('business_hours_end', '18:00', 'string', 'sla');
        Setting::set('business_days', [1, 2, 3, 4, 5], 'json', 'sla'); // Mon-Fri

        // App Branding
        Setting::set('app_name', 'HelpDesk Premium', 'string', 'general');
        Setting::set('company_name', 'TechSupport Inc.', 'string', 'general');

        // Email SMTP Settings
        Setting::set('mail_mailer', 'smtp', 'string', 'email');
        Setting::set('mail_host', 'smtp.gmail.com', 'string', 'email');
        Setting::set('mail_port', '587', 'string', 'email');
        Setting::set('mail_username', 'powercreeper098@gmail.com', 'string', 'email');
        Setting::set('mail_password', 'udwmduzuypckrqpb', 'string', 'email');
        Setting::set('mail_encryption', 'tls', 'string', 'email');
        Setting::set('mail_from_address', 'noreply@Helpdesk.com', 'string', 'email');
        Setting::set('mail_from_name', 'IT-Helpdesk', 'string', 'email');
        Setting::set('mail_reply_to_address', '', 'string', 'email');
        Setting::set('mail_reply_to_name', '', 'string', 'email');

        // Email IMAP Settings
        Setting::set('imap_enabled', true, 'boolean', 'email');
        Setting::set('imap_host', '', 'string', 'email');
        Setting::set('imap_port', 993, 'integer', 'email');
        Setting::set('imap_encryption', 'ssl', 'string', 'email');
        Setting::set('imap_username', '', 'string', 'email');
        Setting::set('imap_password', '', 'string', 'email');
        Setting::set('imap_protocol', 'imap', 'string', 'email');
        Setting::set('imap_delete_after_import', false, 'boolean', 'email');
    }
}
