<?php

namespace Database\Seeders;

use App\Models\KbCategory;
use App\Models\KbArticle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KbArticle::query()->delete();
        KbCategory::query()->delete();

        // Get an admin or agent user to be the author
        $author = User::whereHas('role', function ($query) {
            $query->where('name', 'admin')->orWhere('name', 'agent');
        })->first() ?? User::factory()->create();

        // 1. Getting Started Category
        $gettingStarted = KbCategory::create([
            'name' => 'Getting Started',
            'slug' => 'getting-started',
            'description' => 'Everything you need to know to get up and running.',
            'icon' => '🚀',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        KbArticle::create([
            'category_id' => $gettingStarted->id,
            'author_id' => $author->id,
            'title' => 'How to create your first ticket',
            'slug' => 'how-to-create-your-first-ticket',
            'excerpt' => 'A quick guide on submitting a support request to our team.',
            'content' => "<h3>Welcome to the Help Desk!</h3><p>Creating a ticket is the best way to get help from our support team. Here is how you do it:</p><ol><li>Log into your customer portal.</li><li>Click the <b>Create Ticket</b> button located on your dashboard or sidebar.</li><li>Fill out the subject and describe your issue in detail.</li><li>Select the appropriate priority (Low, Medium, High, Urgent).</li><li>Submit!</li></ol><p>Our team will get back to you as soon as possible.</p>",
            'is_published' => true,
            'is_internal' => false,
            'views' => 1250,
            'sort_order' => 10,
        ]);

        KbArticle::create([
            'category_id' => $gettingStarted->id,
            'author_id' => $author->id,
            'title' => 'Understanding Ticket Priorities',
            'slug' => 'understanding-ticket-priorities',
            'excerpt' => 'Learn when to use Low, Medium, High, or Urgent priority.',
            'content' => "<p>To help us serve you better, please choose the correct priority for your ticket:</p><ul><li><b>Low:</b> General questions, feature requests, or issues that do not affect your workflow.</li><li><b>Medium:</b> Standard issues, minor bugs, or workarounds exist.</li><li><b>High:</b> Important features are failing, severely impacting your ability to work.</li><li><b>Urgent:</b> System outage, critical security issue, or complete halt of business operations.</li></ul>",
            'is_published' => true,
            'is_internal' => false,
            'views' => 840,
            'sort_order' => 20,
        ]);

        // 2. Account & Billing Category
        $accountBilling = KbCategory::create([
            'name' => 'Account & Billing',
            'slug' => 'account-billing',
            'description' => 'Manage your account settings, password, and billing information.',
            'icon' => '💳',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        KbArticle::create([
            'category_id' => $accountBilling->id,
            'author_id' => $author->id,
            'title' => 'How to reset your password',
            'slug' => 'how-to-reset-your-password',
            'excerpt' => 'Forgot your password? Here are the steps to recover your account.',
            'content' => "<p>If you've forgotten your password, follow these steps to reset it:</p><ol><li>Go to the Login page.</li><li>Click on the <b>Forgot Password?</b> link below the login form.</li><li>Enter your registered email address.</li><li>Check your inbox for a password reset email.</li><li>Click the link in the email and enter your new password.</li></ol>",
            'is_published' => true,
            'is_internal' => false,
            'views' => 3200,
            'sort_order' => 10,
        ]);

        // 3. internal Staff Procedures Category
        $internalStaff = KbCategory::create([
            'name' => 'Staff Procedures (Internal)',
            'slug' => 'staff-procedures-internal',
            'description' => 'Internal standard operating procedures for agents and managers.',
            'icon' => '🛡️',
            'sort_order' => 90,
            'is_active' => true,
        ]);

        KbArticle::create([
            'category_id' => $internalStaff->id,
            'author_id' => $author->id,
            'title' => 'Handling VIP Customer Escalations',
            'slug' => 'handling-vip-customer-escalations',
            'excerpt' => 'Internal SOP for managing urgent requests from VIP tier customers.',
            'content' => "<h3>SOP: VIP Escalation Protocol</h3><p><b>CONFIDENTIAL - INTERNAL ONLY</b></p><p>When a VIP customer submits a ticket rated High or Urgent, the following protocol must be followed:</p><ol><li>Acknowledge the ticket within 15 minutes.</li><li>Assign a Tier 2 agent immediately.</li><li>If unresolved within 2 hours, notify the Department Manager.</li><li>Log all phone calls in the ticket thread using private notes.</li></ol>",
            'is_published' => true,
            'is_internal' => true, // This makes it invisible to the public
            'views' => 45,
            'sort_order' => 10,
        ]);
        
        // Example Draft article
        KbArticle::create([
            'category_id' => $gettingStarted->id,
            'author_id' => $author->id,
            'title' => 'Setting up SSO Integration',
            'slug' => 'setting-up-sso-integration',
            'excerpt' => 'Draft guide on SSO integration.',
            'content' => "<p>This article is a work in progress and should not be published yet.</p>",
            'is_published' => false,
            'is_internal' => false, 
            'views' => 0,
            'sort_order' => 100,
        ]);
    }
}
