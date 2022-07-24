<?php

namespace App\Service;

use App\Entity\BillingUser;
use App\Entity\Course;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{
    private const OPERATION_TYPE = [
        'deposit' => 1,
        'payment' => 2
    ];

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function deposit(billingUser $user, float $amount): Transaction
    {
        $this->em->getConnection()->beginTransaction();

        try{
            $transaction = new Transaction();
            $transaction->setCustomer($user);
            $transaction->setAmount($amount);
            $transaction->setType(self::OPERATION_TYPE['deposit']);

            $user->setBalance($user->getBalance() + $amount);

            $this->em->persist($transaction);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $exception) {
            $this->em->getConnection()->rollBack();
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        return $transaction;
    }

    public function payment(billingUser $user, Course $course): Transaction
    {
        $this->em->getConnection()->beginTransaction();

        try {
            if ($user->getBalance() < $course->getPrice()) {
                throw new \Exception("На вашем счете недостаточно средств", Response::HTTP_NOT_ACCEPTABLE);
            }

            $transaction = new Transaction();
            $transaction->setCustomer($user);
            $transaction->setCourse($course);
            $transaction->setAmount($course->getPrice());
            $transaction->setType(self::OPERATION_TYPE['payment']);

            if ($course->getType() === 'rent') {
                $transaction->setExpireTime((new \DateTimeImmutable())->add(new \DateInterval('P30D')));
            }

            $user->setBalance($user->getBalance() - $course->getPrice());

            $this->em->persist($transaction);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $exception) {
            $this->em->getConnection()->rollBack();
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }

        return $transaction;
    }
}