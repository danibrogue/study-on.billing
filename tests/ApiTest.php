<?php

namespace App\Tests;

use Symfony\Component\Panther\PantherTestCase;
use App\Tests\AbstractTest;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends AbstractTest
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

    public function testAuth(): void
    {
        $user = $this->serializer->serialize([
            'username' => 'user@study-on.local',
            'password' => 'super_pass'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseOk();
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['token']);
    }

    public function testAuthNonExistentUser(): void
    {
        $user = $this->serializer->serialize([
            'username' => 'alex@study-on.local',
            'password' => 'super_pass'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(401);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid credentials.', $jsonResponse['message']);
    }

    public function testRegister(): void
    {
        $user = $this->serializer->serialize([
            'username' => 'rex@study-on.local',
            'password' => 'ChangeMe'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(201);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['token']);
    }

    public function testRegisterExistentUser(): void
    {
        $user = $this->serializer->serialize([
            'username' => 'user@study-on.local',
            'password' => 'super_pass'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(400);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['error']);
        $this->assertEquals('Пользователь user@study-on.local уже существует', $jsonResponse['error']);
    }

    public function testRegisterInvalidEmail(): void
    {
        $user = $this->serializer->serialize([
            'username' => '',
            'password' => 'super_pass'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(400);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['username']);
        $this->assertContains('Имя пользователя не должно быть пустым', $jsonResponse['errors']['username']);

        $user = $this->serializer->serialize([
            'username' => 'valeryzhmyshenko.net',
            'password' => 'super_pass'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(400);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['username']);
        $this->assertContains('Некорректный email', $jsonResponse['errors']['username']);
    }

    public function testRegisterInvalidPassword(): void
    {
        $user = $this->serializer->serialize([
            'username' => 'rex@study-on.local',
            'password' => ''
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(400);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['password']);
        $this->assertContains('Поле пароля не должно быть пустым', $jsonResponse['errors']['password']);

        $user = $this->serializer->serialize([
            'username' => 'rex@study-on.local',
            'password' => 's'
        ], 'json');
        $client = self::getClient();
        $client->request(
            'POST',
            '/api/v1/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $user);
        $this->assertResponseCode(400);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['password']);
        $this->assertContains('Пароль должен быть длиннее 6 символов', $jsonResponse['errors']['password']);
    }
}
