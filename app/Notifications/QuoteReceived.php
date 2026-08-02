<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Quote $quote) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New quote for {$this->quote->rfq->title}")
            ->greeting('You have a new quote')
            ->line("{$this->quote->vendor->name} quoted {$this->quote->total_label} ({$this->quote->incoterm}).")
            ->line("Lead time: {$this->quote->lead_time_days} days · valid until "
                .($this->quote->validity_until?->format('d M Y') ?? 'further notice'))
            ->action('Compare quotes', route('account.rfqs.show', $this->quote->rfq));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'quote_id' => $this->quote->id,
            'rfq_id' => $this->quote->rfq_id,
            'vendor' => $this->quote->vendor->name,
            'type' => 'quote.received',
        ];
    }
}
