<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Quote $quote, public Order $order) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Quote {$this->quote->reference} accepted — order {$this->order->reference}")
            ->greeting('Your quote was accepted')
            ->line("The buyer accepted {$this->quote->reference} for {$this->quote->total_label}.")
            ->line("Order {$this->order->reference} is now in your panel — confirm production and shipping details.")
            ->line('Payment is collected into escrow and released after delivery is confirmed.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'quote_id' => $this->quote->id,
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
            'type' => 'quote.accepted',
        ];
    }
}
