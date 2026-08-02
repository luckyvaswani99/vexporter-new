<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApplicationReceived extends Notification implements ShouldQueue
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
            ->subject("New vendor application: {$this->vendor->name}")
            ->greeting('A vendor is waiting for review')
            ->line("{$this->vendor->name} ({$this->vendor->legal_name}) from {$this->vendor->city} has submitted an application.")
            ->line("IEC: {$this->vendor->iec_code} · GST: {$this->vendor->gst_number}")
            ->line('Documents are attached to the vendor record for verification.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'vendor_id' => $this->vendor->id,
            'vendor_name' => $this->vendor->name,
            'type' => 'vendor.application_received',
        ];
    }
}
