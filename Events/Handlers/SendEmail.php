<?php

namespace Modules\Iquote\Events\Handlers;

use Illuminate\Contracts\Mail\Mailer;
use Modules\Iquote\Emails\Sendmail;
use Modules\Iquote\Events\QuoteIsSending;

class SendEmail
{

    private $mail;
    private $setting;

    public function __construct(Mailer $mail)
    {
        $this->mail = $mail;
        $this->setting = app('Modules\Setting\Contracts\Setting');
    }

    public function handle(QuoteIsSending $event)
    {

        $quote = $event->entity;
        $sender = $this->setting->get('core::site-name');
        $subject = "Cotización #".str_pad($quote->id,5,'0',STR_PAD_LEFT)." - " . $sender;
        $view = ['iquote::frontend.emails.quote','iquote::frontend.emails.textquote'];

        $formEmails = !empty($this->setting->get('iforms::form-emails'))?$this->setting->get('iforms::form-emails'):env('MAIL_FROM_ADDRESS');
        $emails = explode(',', $formEmails);

        if (isset($quote->user->email) && !empty($quote->user->email)) {
            array_push($emails, $quote->user->email);
        }

        $reply = json_decode(json_encode(['to'=>env('MAIL_FROM_ADDRESS'),'toName'=>$sender]));

        $this->mail->to($emails)->send(new Sendmail($quote, $subject, $view),function ($m) use($reply) {
            $m->replyTo($reply->to, $reply->toName);
        });

    }
}