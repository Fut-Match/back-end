<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailPtBr extends VerifyEmailBase
{
    /**
     * Get the verification email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Verifique seu endereço de e-mail')
            ->greeting('Olá!')
            ->line('Por favor, clique no botão abaixo para verificar seu endereço de e-mail.')
            ->action('Verificar e-mail', $url)
            ->line('Se você não criou uma conta, nenhuma ação é necessária.');
    }
}
