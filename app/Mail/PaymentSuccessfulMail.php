<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userId;
    public $userName;
    public $invoiceNumber;
    public $itemName;
    public $tanggalPinjam;
    public $tanggalSelesai;
    public $status;
    public $paymentMethod;

    public function __construct($userId, $userName, $invoiceNumber, $itemName, $tanggalPinjam, $tanggalSelesai, $status, $paymentMethod)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->invoiceNumber = $invoiceNumber;
        $this->itemName = $itemName;
        $this->tanggalPinjam = $tanggalPinjam;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->status = $status;
        $this->paymentMethod = $paymentMethod;
    }

    public function build()
    {
        return $this->subject('Pembayaran Berhasil - ' . $this->invoiceNumber)
                    ->view('emails.payment_success');
    }
}
