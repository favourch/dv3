<?php 
// app/Helpers/Email.php

namespace App\Helpers;

use App\Mail\CustomEmail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Email
{
    public static function log($userId, $recipient, $subject, $body, $status, $attempts)
    {
        EmailLog::create([
            'user_id' => $userId,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $body,
            'status' => $status,
            'attempts' => $attempts,
        ]);
    }

    public static function sendInvite($template, $recipientEmail, $inviter, $link)
    {
        $smtpQuery = Setting::where('key', 'smtp_email_active')->first();

        if($smtpQuery->value == 1){
            $emailTemplate = EmailTemplate::where('name', $template)->first();
            $subject = self::replacePlaceholders($emailTemplate->subject, $inviter, $link);
            $body = self::replacePlaceholders($emailTemplate->body, $inviter, $link);

            Mail::to($recipientEmail)->queue(new CustomEmail($subject, $body));
        }
    }

    public static function sendPasswordReset($template, $recipient, $link)
    {
        $smtpQuery = Setting::where('key', 'smtp_email_active')->first();

        if($smtpQuery->value == 1){
            $emailTemplate = EmailTemplate::where('name', $template)->first();
            $subject = self::replacePlaceholders($emailTemplate->subject, $recipient, $link);
            $body = self::replacePlaceholders($emailTemplate->body, $recipient, $link);

            Mail::to($recipient->email)->queue(new CustomEmail($subject, $body));
        }
    }

    public static function sendSubscriptionEmail($template, $recipient, $plan)
    {
        $smtpQuery = Setting::where('key', 'smtp_email_active')->first();

        if($smtpQuery->value == 1){
            $emailTemplate = EmailTemplate::where('name', $template)->first();
            $subject = self::replacePlaceholders($emailTemplate->subject, $recipient, NULL, $plan);
            $body = self::replacePlaceholders($emailTemplate->body, $recipient, NULL, $plan);

            try {
                $email = Mail::to($recipient->email)->queue(new CustomEmail($subject, $body));
            } catch (\Exception $e) {
                //dd($e->getMessage());
            }
        }
    }

    public static function send($template, $recipient)
    {
        $smtpQuery = Setting::where('key', 'smtp_email_active')->first();

        if($smtpQuery->value == 1){
            $emailTemplate = EmailTemplate::where('name', $template)->first();
            $subject = self::replacePlaceholders($emailTemplate->subject, $recipient);
            $body = self::replacePlaceholders($emailTemplate->body, $recipient);

            try {
                $email = Mail::to($recipient->email)->queue(new CustomEmail($subject, $body));
            } catch (\Exception $e) {
                //dd($e->getMessage());
            }
        }
    }

    private static function replacePlaceholders($content, $user = NULL, $link = NULL, $plan = NULL)
    {
        // Define the replacements
        $replacements = [
            '{{FirstName}}' => $user->first_name ?? NULL,
            '{{LastName}}' => $user->last_name ?? NULL,
            '{{Email}}' => $user->email ?? NULL,
            '{{FullName}}' => $user->full_name ?? NULL,
            '{{Link}}' => $link,
            '{{plan}}' => $plan->name ?? NULL,
            '{{CompanyName}}' => Setting::where('key', 'company_name')->value('value'),
        ];

        // Replace the placeholders
        $replacedContent = Str::replace(array_keys($replacements), array_values($replacements), $content);

        return $replacedContent;
    }
}

