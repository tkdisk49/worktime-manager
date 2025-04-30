<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerificationIfNotVerified
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;

        // 一般ユーザーでメール未認証の場合かつ、新規登録直後ではない場合に送信する
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail() && !$user->wasRecentlyCreated) {
            $user->sendEmailVerificationNotification();
        }
    }
}
