<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\BillingUser;
use App\Repository\BillingUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/v1/auth", name="app_api")
     */
    public function auth(): void
    {

    }

    /**
     * @Route ("/api/v1/register", name="api_register", methods={"POST"})
     */
    public function register(
        JWTTokenManagerInterface $JWTTokenManager,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        BillingUserRepository $userRepository): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $userDTO = $serializer->deserialize($request->getContent(), UserDTO::class, 'json');
        $errors = $validator->validate($userDTO);

        $jsonErrors = [];
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $jsonErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return $this->json(['errors' => $jsonErrors], Response::HTTP_BAD_REQUEST);
        }
        if ($userRepository->findOneBy(['email' => $userDTO->getUsername()])) {
            return $this->json(['error' => 'Пользователь ' . $userDTO->getUsername() . ' уже существует'],
                Response::HTTP_BAD_REQUEST);
        }
        $user = BillingUser::fromDTO($userDTO, $passwordHasher);
        $manager->persist($user);
        $manager->flush();
        $token = $JWTTokenManager->create($user);
        return $this->json(['token' => $token], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/v1/current", name="api_current", methods={"GET"})
     */
    public function current(Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->json([
                'status_code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Пользователь не авторизован'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(
            ['username' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'balance' => $user->getBalance(),
            ],
            Response::HTTP_OK
        );
    }
}
