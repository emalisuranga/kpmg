<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $buil = $this->from(env('MAIL_FROM_ADDRESS', ''))->subject($this->data['subject']);
        $buil = $buil->view('vendor.email.' . $this->data['template']);
        if ($this->data['attachdata'] != null) {
            $buil = $buil->attachData($this->data['attachdata'], $this->data['attchName'], [
                'mime' => 'application/pdf',
            ]);
        }
        $buil = $buil->with([
            'activationLink' => $this->data['activationLink'],
            'token' => $this->data['token'],
            'applicantName' => $this->data['applicantName'],
        ]);
        return $buil;
    }
}
