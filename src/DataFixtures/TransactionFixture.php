<?php

namespace App\DataFixtures;

use App\Entity\BillingUser;
use App\Entity\Course;
use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TransactionFixture extends Fixture implements DependentFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $courseRepository = $manager->getRepository(Course::class);
        $user = $manager->getRepository(BillingUser::class)->findOneBy(['email' => 'user@study-on.local']);

        $courses = [
            'free' => $courseRepository->findOneBy(['type' => 1]),
            'rent' => $courseRepository->findBy(['type' => 2]),
            'full' => $courseRepository->findOneBy(['type' => 3])
        ];

        $transactions = [
            //депозит
            [
                'customer' => $user,
                'type' => 1,
                'amount' => 20000,
                'createTime' => new \DateTimeImmutable('2022-05-29 00:00:00')
            ],
            //бесплатный курс
            [
                'course' => $courses['free'],
                'customer' => $user,
                'type' => 2,
                'amount' => 0,
                'createTime' => new \DateTimeImmutable('2022-06-01 00:00:00')
            ],
            //курс с неистекшей арендой
            [
                'course' => $courses['rent'][0],
                'customer' => $user,
                'type' => 2,
                'amount' => 500,
                'createTime' => new \DateTimeImmutable('2022-07-20 00:00:00'),
                'expireTime' => new \DateTimeImmutable('2022-08-18 00:00:00')
            ],
            //курс с истекшей арендой
            [
                'course' => $courses['rent'][1],
                'customer' => $user,
                'type' => 2,
                'amount' => 800,
                'createTime' => new \DateTimeImmutable('2022-06-20 00:00:00'),
                'expireTime' => new \DateTimeImmutable('2022-07-19 00:00:00')
            ],
            //купленный курс
            [
                'course' => $courses['full'],
                'customer' => $user,
                'type' => 2,
                'amount' => 5000,
                'createTime' => new \DateTimeImmutable('2022-07-20 00:00:00')
            ]
        ];

        foreach ($transactions as $transaction)
        {
            $newTransaction = new Transaction();
            $newTransaction->setType($transaction['type']);
            $newTransaction->setCourse($transaction['course']??null);
            $newTransaction->setCustomer($transaction['customer']);
            $newTransaction->setCreateTime($transaction['createTime']);
            $newTransaction->setAmount($transaction['amount']);
            if (isset($transaction['expiresAt'])) {
                $newTransaction->setExpireTime($transaction['expireTime']);
            }
            $manager->persist($newTransaction);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
            CourseFixture::class
        ];
    }
}