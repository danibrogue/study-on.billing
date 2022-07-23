<?php

namespace App\Service;

use App\Entity\BillingUser;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function getUserByToken($apiToken)
    {
        $parts = explode('.', $apiToken);
        $payload = json_decode(base64_decode($parts[1]), true, 512, JSON_THROW_ON_ERROR);
        return $this->em->getRepository(BillingUser::class)->findOneBy(['email' => $payload['email']]);
    }
}