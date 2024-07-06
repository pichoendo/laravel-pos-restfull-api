<?php

namespace App\Notifications;

use App\Models\EmployeeSalary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SalaryReport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public EmployeeSalary $employeeSalary)
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
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;">Description</th>';
        $table .= '<th style="border: 1px solid #ddd; padding: 8px;">Amount</th>';
        $table .= '</tr></thead>';
        $table .= '<tbody>';

        $table .= '<tr>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">Basic Salary</td>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $this->employeeSalary->basic_salary . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">Commission Sales</td>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $this->employeeSalary->sales_commission . '</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">Total Salary</td>';
        $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $this->employeeSalary->total_salary . '</td>';
        $table .= '</tr>';



        return (new MailMessage)
            ->subject('Salary Report')
            ->line('Here are the details of your recent purchase:')
            ->line(new HtmlString($table))
            ->line('Thank you for your dedication!');
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
