<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreatePodcast extends Notification
{
    use Queueable;
    private $podcast_id,$podcast_title,$channel_name,$channel_image;
    /**
     * Create a new notification instance.
     */
    public function __construct($podcast_id,$podcast_title,$channel_name,$channel_image)
    {
        $this->podcast_id=$podcast_id;
        $this->podcast_title=$podcast_title;
        $this->channel_name=$channel_name;
        $this->channel_image=$channel_image;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'podcast_id'=>$this->podcast_id,
            'podcast_title'=>$this->podcast_title,
            'channel_name'=>$this->channel_name,
            'channel_image'=>$this->channel_image,
        ];
    }
}
