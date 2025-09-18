<?php
declare(strict_types=1);

namespace App\Catalog\NewProductNotification;

use App\Catalog\Repository\UserRepository;
use App\Catalog\Value\Product;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class is incomplete and uses arrays for the sake of brief examples.
 */
final class NewProductNotification
{
    private MailerInterface $mailer;
    private UserRepository $userRepository;

    public function __construct(MailerInterface $mailer, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    public function notifyUsers(Product $product): void
    {
        $this->notifyUsersInArea($product);
        $this->notifyUsersInterestedInArtist($product);
    }

    private function notifyUsersInArea(Product $product): void
    {
        $users = $this->userRepository->findUsersInArea('area1');
        if (count($users) === 0) {
            return;
        }

        $email = new Email();
        $email->subject('A new concert is available in your area');
        $envelope = new Envelope(
            Address::create('me+pluralsight@afilina.com'),
            $this->usersToRecipients($users)
        );
        $this->mailer->send($email, $envelope);
    }

    private function notifyUsersInterestedInArtist(Product $product): void
    {
        $users = $this->userRepository->findUsersInterestedInArtist('artist1');
        if (count($users) === 0) {
            return;
        }

        $email = new Email();
        $email->subject('A new concert is available for an artist you follow');
        $envelope = new Envelope(
            Address::create('me+pluralsight@afilina.com'),
            $this->usersToRecipients($users)
        );
        $this->mailer->send($email, $envelope);
    }

    /**
     * @param array $users
     * @return Address[]
     */
    private function usersToRecipients(array $users): array
    {
        return array_map(static function (array $user) {
            return Address::create(($user['email']));
        }, $users);
    }
}
