<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Vendor $vendor) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your VEXPORTER vendor account is approved')
            ->greeting("Welcome aboard, {$this->vendor->name}!")
            ->line('Your account has been verified. You can now list products and start receiving orders from global buyers.')
            ->action('Go to your dashboard', route('account.dashboard'))
            ->line('Every listing is reviewed before it goes live, so keep certificates and datasheets handy.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'type' => 'vendor.approved',
        ];
    }
}
