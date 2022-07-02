<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;


class UserDTO
{

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Имя пользователя не должно быть пустым")
     * @Assert\Email(message="Некорректный email")
     * @Serializer\Type("string")
     */
    private $username;


    /**
     * @var string
     *
     * @Assert\NotBlank(message="Поле пароля не должно быть пустым")
     * @Assert\Length(min=6, minMessage="Пароль должен быть длиннее 6 символов")
     * @Serializer\Type("string")
     */
    private $password;


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): UserDTO
    {
        $this->username = $username;
        return $this;
    }


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): UserDTO
    {
        $this->password = $password;
        return $this;
    }
}