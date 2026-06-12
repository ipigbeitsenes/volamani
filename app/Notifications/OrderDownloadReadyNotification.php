<?php

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Models\Order;
use App\Models\ProductFile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

/**
 * Sent to the buyer once a product order is paid: lists every downloadable file
 * in the order with its own time-limited signed download link, so the buyer can
 * grab their digital goods straight from their inbox (links still require the
 * buyer to be signed in — the signature only proves authenticity + expiry).
 */
class OrderDownloadReadyNotification extends VolamaniNotification
{
    public function __construct(private Order $order) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Orders;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Your downloads are ready — order {$this->order->reference}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for your purchase! Payment for order '
                . "**{$this->order->reference}** was successful and your digital files are ready.");

        foreach ($this->downloadableFiles() as $file) {
            $label = $file->label ?: $file->original_name;
            $mail->line("**{$file->product->name} — {$label}** ({$file->file_size_formatted})")
                ->line('[Download this file](' . $this->linkFor($file) . ')');
        }

        return $mail
            ->line('For your security these links expire and require you to be signed in to your account. '
                . 'You can also re-download anytime from your order page.')
            ->action('Go to My Order', route('orders.show', $this->order))
            ->salutation('The Volamani Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon'     => $this->category()->icon(),
            'title'    => 'Your downloads are ready',
            'message'  => "Order {$this->order->reference} is paid — download your files now.",
            'url'      => route('orders.show', $this->order),
        ];
    }

    /** Every downloadable file across the digital products in this order. */
    private function downloadableFiles(): \Illuminate\Support\Collection
    {
        return $this->order->items
            ->filter(fn ($item) => $item->product && $item->product->is_downloadable)
            ->flatMap(fn ($item) => $item->product->files);
    }

    /** A time-limited signed download URL for a single file. */
    private function linkFor(ProductFile $file): string
    {
        $expiryHours = $file->product->download_expiry_hours
            ?? (int) settings('default_download_expiry_hours', 48);

        return URL::temporarySignedRoute(
            'products.download',
            now()->addHours($expiryHours),
            ['order' => $this->order->id, 'productFile' => $file->id],
        );
    }
}
