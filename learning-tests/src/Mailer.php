<?php

declare(ticks=1);

use Illuminate\Contracts\Mail\Mailable;

class Mailer {

  public function sendMail(string $to, string $subject, string $message) {
    echo "Sending $to: $subject: $message\n";
    sleep(2);
    return true;
  }


}
