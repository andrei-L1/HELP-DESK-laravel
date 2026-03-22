<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CannedResponse;
use App\Models\User;

class CannedResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try to find the first admin to own these public responses
        $admin = User::where('username', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        $responses = [
            [
                'title'      => 'General Greeting',
                'content'    => "Hi there,\n\nThanks for reaching out! We have received your request and our team is currently looking into it. We'll get back to you with an update as soon as possible.\n\nBest regards,\nThe Support Team",
                'shortcut'   => '/hello',
                'category'   => 'General',
                'created_by' => $adminId,
                'is_shared'  => true,
            ],
            [
                'title'      => 'Password Reset Instructions',
                'content'    => "Hello,\n\nTo reset your password, please go to the login page and click on 'Forgot Password'. You will receive an email with a secure link to create a new password.\n\nLet us know if you have any issues doing this!\n\nBest,\nSupport",
                'shortcut'   => '/pw-reset',
                'category'   => 'Technical',
                'created_by' => $adminId,
                'is_shared'  => true,
            ],
            [
                'title'      => 'Billing Inquiry - Refund Processed',
                'content'    => "Hi,\n\nI'm writing to let you know that we have successfully processed your refund. Please allow 3-5 business days for the amount to reflect in your account depending on your bank's processing times.\n\nThanks,\nBilling Team",
                'shortcut'   => '/refund-done',
                'category'   => 'Billing',
                'created_by' => $adminId,
                'is_shared'  => true,
            ],
            [
                'title'      => 'Issue Resolved / Closing',
                'content'    => "Hello,\n\nI'm glad we were able to get this sorted out for you! I will go ahead and close this ticket now. If you need any further assistance, feel free to reply to this email or open a new ticket.\n\nHave a great day!",
                'shortcut'   => '/close',
                'category'   => 'General',
                'created_by' => $adminId,
                'is_shared'  => true,
            ],
            [
                'title'      => 'Need More Information',
                'content'    => "Hi there,\n\nCould you please provide a bit more information so we can investigate this further? Specifically, any error messages, screenshots, or steps to reproduce the issue would be incredibly helpful.\n\nLooking forward to your reply.",
                'shortcut'   => '/more-info',
                'category'   => 'Technical',
                'created_by' => $adminId,
                'is_shared'  => true,
            ],
            [
                'title'      => 'Feature Request Acknowledgment',
                'content'    => "Hello,\n\nThank you for sharing your feedback! We're always looking for ways to improve our platform. I've passed your suggestion along to our product team for consideration in future updates.\n\nThanks for helping us grow!",
                'shortcut'   => '/feature-req',
                'category'   => 'Feedback',
                'created_by' => $adminId,
                'is_shared'  => true,
            ],
        ];

        foreach ($responses as $response) {
            CannedResponse::updateOrCreate(
                ['shortcut' => $response['shortcut']],
                $response
            );
        }
    }
}
