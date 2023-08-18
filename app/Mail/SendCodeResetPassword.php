<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendCodeResetPassword extends Mailable
{
   public $code;
   public $type;
   public $url;
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($code,$type)
    {
        $this->code=$code;
        $this->type=$type;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Code de Reinitialisation | Heimdall',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content() {  
       
         if ($this->type ='Customer') { 
                $this->url="https://heimdall-store.com/reinitialisation";
            } else {
                $this->url="https://heimdall-store.com/admin/reinitialisation";
            }
        return (new Content)
           ->view('emails/SendCodeResetPassword',['code' =>$this->code, 'url' => $this->url]);
        
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
