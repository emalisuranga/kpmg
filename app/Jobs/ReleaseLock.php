<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use App\Mail\MailTemplate;
class ReleaseLock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    Protected $email;
    protected $data;
    protected $bcc;
    protected $bccEmail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email,array $data, $bcc, $bccEmail)
    {
        $this->email = $email;
        $this->data = $data;
        $this->bcc = $bcc;
        $this->bccEmail = $bccEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->bcc){
            if(is_null($this->bccEmail)){
                $this->bccEmail = env('BCC_MAIL', '');
            }
            Mail::to($this->email)->bcc($this->bccEmail)->send(new MailTemplate($this->data));
        }
        Mail::to($this->email)->send(new MailTemplate($this->data));
    }
}
