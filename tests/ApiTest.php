<?php

namespace App\Tests;

use Symfony\Component\Panther\PantherTestCase;
use App\Tests\AbstractTest;
use App\DataFixtures\UserFixture;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends AbstractTest
{
    private $serializer;

    protected function getFixtures(): array
    {
        return [UserFixture::class];
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
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);
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
        $this->assertResponseCode(Response::HTTP_CREATED);
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
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['error']);
        $this->assertEquals('???????????????????????? user@study-on.local ?????? ????????????????????', $jsonResponse['error']);
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
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['username']);
        $this->assertContains('?????? ???????????????????????? ???? ???????????? ???????? ????????????', $jsonResponse['errors']['username']);

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
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['username']);
        $this->assertContains('???????????????????????? email', $jsonResponse['errors']['username']);
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
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['password']);
        $this->assertContains('???????? ???????????? ???? ???????????? ???????? ????????????', $jsonResponse['errors']['password']);

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
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['password']);
        $this->assertContains('???????????? ???????????? ???????? ?????????????? 6 ????????????????', $jsonResponse['errors']['password']);
    }
}
