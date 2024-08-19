<?php

namespace App\Notifications;

use App\Models\Sales;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SalesReport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Sales $sales)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $table = '<table style="width:100%; border-collapse: collapse;">';
        $table .= '<thead><tr>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;">Item</th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;">Quantity</th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;">Price</th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;">Total</th>';
        $table .= '</tr></thead>';
        $table .= '<tbody>';
        $this->sales->items->each(function ($item) use ($table) {
            $table .= '<tr>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item->sales->name . '</td>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item->qty . '</td>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item->price . '</td>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item->sub_total . '</td>';
            $table .= '</tr>';
        });
        $table .= '<tr>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;"></th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;"></th>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">Sub total</td>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $this->sales->sub_total . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;"></th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;"></th>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">Tax</td>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $this->sales->tax . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;"></th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;"></th>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">Total</td>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $this->sales->total . '</td>';
        $table .= '</tr>';
        return (new MailMessage)
            ->subject('Sales Report')
            ->line('Here are the details of your recent purchase:')
            ->line(new HtmlString($table))
            ->line('Thank you, we wait for your next coming !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
