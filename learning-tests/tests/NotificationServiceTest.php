<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase {
  public function testNotificationIsSent() {
//    $mailer = new Mailer;
    // Instead of creating a dependency class instance, we can create a double class.
    // This class will behave as an original dependency
    // createStub creates a class with the same methods as passed class.
    $mailer = $this->createStub(Mailer::class);
    // We can configure method of stub class.
    $mailer->method('sendMail')->willReturn(true);
    $service = new NotificationsService($mailer);
    $to = 'test@mail.com';
    $message = 'Notification test message';
    $this->assertTrue($service->sendNotification($to, $message));
  }

  public function testNotificationSendThrowsException() {
    $mailer = $this->createStub(Mailer::class);
    $mailer->method('sendMail')->willThrowException(new RuntimeException('SMTP is not responding'));
    $service = new NotificationsService($mailer);
    $to = 'test@mail.com';
    $message = 'Notification test message';
    $this->expectException(NotificationException::class);
    $this->expectExceptionMessage('Could not send message');
    $this->assertTrue($service->sendNotification($to, $message));
  }

  public function testMailerIsCalledCorrectly(): void {
    $to = 'test@mail.com';
    $message = 'Notification test message';
    $mailer = $this->createMock(Mailer::class);
    $mailer
      // Method called only once
      ->expects($this->once())
      // Specify method.
      ->method('sendMail')
      // Provide arguments for method sendMail.
      ->with($to, 'New notification', $message)
      // Method will return true
      ->willReturn(true);
    $service = new NotificationsService($mailer);

    $this->assertTrue($service->sendNotification($to, $message));
  }
}
