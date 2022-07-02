<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\BillingUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\PantherTestCase;

class UserTest extends AbstractTest
{
    private $serializer;

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    private function getToken($user)
    {
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);

        return json_decode($client->getResponse()->getContent(), true)['token'];
    }

    public function testCurrentUser(): void
    {
        $user = $this->serializer->serialize([
            'username' => 'user@study-on.local',
            'password' => 'super_pass'
        ], 'json');
        $token = $this->getToken($user);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        $client = self::getClient();
        $client->request(
            'GET',
            '/api/v1/current',
            [],
            [],
            $headers);
        $this->assertResponseOk();
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));

        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $userRepository = self::getEntityManager()->getRepository(BillingUser::class);
        $expected = $userRepository->findOneBy(['email' => $jsonResponse['username']]);
        $this->assertNotEmpty($expected);
        $this->assertEquals($expected->getRoles(), $jsonResponse['roles']);
        $this->assertEquals($expected->getBalance(), $jsonResponse['balance']);
    }

    public function testInvalidToken(): void
    {
        $token = 'invalid_token';

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];

        $client = self::getClient();
        $client->request(
            'GET',
            '/api/v1/current',
            [],
            [],
            $headers);
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid JWT Token', $jsonResponse['message']);

        $headers = [
            'CONTENT_TYPE' => 'application/json'
        ];

        $client = self::getClient();
        $client->request(
            'GET',
            '/api/v1/current',
            [],
            [],
            $headers);
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('JWT Token not found', $jsonResponse['message']);
    }
}
