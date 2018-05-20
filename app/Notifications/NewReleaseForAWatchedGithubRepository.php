<?php

namespace App\Notifications;

use App\Release;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewReleaseForAWatchedGithubRepository extends Notification
{
    use Queueable;

    /**
     * The release instance.
     *
     * @var \App\Release
     */
    public $release;

    /**
     * Create a new notification instance.
     *
     * @param \App\Release $release
     */
    public function __construct(Release $release)
    {
        $this->release = $release->load('repository');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->markdown('mail.new-release', ['release' => $this->release]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
