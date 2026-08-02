<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Vendor $vendor, public string $reason) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your VEXPORTER vendor application needs attention')
            ->greeting("Hello {$this->vendor->name},")
            ->line('We could not approve your application yet for the following reason:')
            ->line($this->reason)
            ->action('Update your application', route('vendor.onboarding.status'))
            ->line('Once the details are corrected our team will review it again within two business days.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'reason' => $this->reason,
            'type' => 'vendor.rejected',
        ];
    }
}
