<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class orderMail extends Mailable
{ public $vente; 
 public $produit;
 public $user;
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($vente,$listeProduit)
    {
        $this->vente=$vente;
        $this->produit=$listeProduit;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre achat | Heimdall Store',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {$this->user =app('currentUser');
    
        return (new Content)
        ->view('emails/orderMail',[ 'vente' =>$this->vente, 'produit' => $this->produit, 'user'=>$this->user]);
     
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
