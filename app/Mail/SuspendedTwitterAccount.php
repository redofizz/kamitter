<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\TwitterUser;
/**
 * Twitterアカウント凍結時に送信するメールのクラス
 * Class SuspendedTwitterAccount
 */
class SuspendedTwitterAccount extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $twitter_user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, TwitterUser $twitter_user)
    {
        $this->user = $user;
        $this->twitter_user = $twitter_user;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Twitterアカウント凍結のお知らせ')
            ->view('emails.suspended');
    }
}
