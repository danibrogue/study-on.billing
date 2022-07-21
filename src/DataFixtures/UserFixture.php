<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\BillingUser;

class UserFixture extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new BillingUser();
        $user->setEmail('user@study-on.local');

        $plainTextPassword = 'super_pass';
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plainTextPassword
        );
        $user->setPassword($hashedPassword);
        $user->setBalance(20000);
        $manager->persist($user);

        $superAdmin = new BillingUser();
        $superAdmin->setEmail('admin@study-on.local');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $superAdmin,
            $plainTextPassword
        );
        $superAdmin->setPassword($hashedPassword);
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setBalance(0);
        $manager->persist($superAdmin);

        $manager->flush();
    }
}
