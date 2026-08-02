<?php

namespace App\Notifications;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RfqInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Rfq $rfq) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New quote request: {$this->rfq->title}")
            ->greeting('A buyer is asking for a quote')
            ->line("{$this->rfq->qty} {$this->rfq->unit} · {$this->rfq->incoterm} · delivery to {$this->rfq->destination_country}")
            ->line($this->rfq->description)
            ->line('Respond quickly — buyers usually pick from the first quotes they receive.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'rfq_id' => $this->rfq->id,
            'reference' => $this->rfq->reference,
            'title' => $this->rfq->title,
            'type' => 'rfq.invitation',
        ];
    }
}
