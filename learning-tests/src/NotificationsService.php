<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversMethod;

class NotificationsService {

  public function __construct(private Mailer $mailer) {}

  public function sendNotification(string $to, string $message) {
    $subject = 'New notification';

    try {
      return $this->mailer->sendMail($to, $subject, $message);
    } catch (\RuntimeException $e) {
      throw new NotificationException('Could not send message', 0, $e);
    }
  }

}
