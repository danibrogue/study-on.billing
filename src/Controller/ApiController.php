<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\BillingUser;
use App\Repository\BillingUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use JMS\Serializer\SerializerBuilder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as NelmioSecurity;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

class ApiController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth",
     *     description="Аутентификация пользователя"
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="username",
     *          type="string",
     *          example="user@study-on.local"
     *          ),
     *          @OA\Property(
     *          property="password",
     *          type="string",
     *          example="super_pass"
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="200",
     *     description="Авторизация прошла успешно",
     *     @OA\JsonContent(
     *        @OA\Property(property="token", type="string"),
     *        @OA\Property(property="refreshToken", type="string"),
     *      )
     * )
     * @OA\Response(
     *     response="401",
     *     description="Ошибка авторизации",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Непредвиденная ошибка",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @Route("/api/v1/auth", name="api_auth", methods={"POST"})
     */
    public function auth(): void
    {

    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     description="Регистрация нового пользователя"
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="username",
     *          type="string",
     *          example="ceasar@study-on.local"
     *          ),
     *          @OA\Property(
     *          property="password",
     *          type="string",
     *          example="ChangeMe"
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="201",
     *     description="Регистрация прошла успешно",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="token",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="roles",
     *          type="array",
     *          @OA\Items(type="string")
     *          ),
     *          @OA\Property(
     *          property="refreshToken",
     *          type="string")
     *      )
     * )
     * @OA\Response(
     *     response="400",
     *     description="Ошибка валидации",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="errors",
     *          type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      type="string",
     *                      property="property_name"
     *                  )
     *              )
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="401",
     *     description="Ошибка аутентификации пользователя",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Непредвиденная ошибка",
     *     @OA\JsonContent(
     *          @OA\Property(
     *          property="code",
     *          type="string",
     *          ),
     *          @OA\Property(
     *          property="message",
     *          type="string",
     *          )
     *      )
     * )
     * @Route ("/api/v1/register", name="api_register", methods={"POST"})
     */
    public function register(
        JWTTokenManagerInterface $JWTTokenManager,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        BillingUserRepository $userRepository,
        RefreshTokenManagerInterface $refreshTokenManager,
        RefreshTokenGeneratorInterface $refreshTokenGenerator): Response
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

//        $refreshToken = $refreshTokenManager->create();
//        $refreshToken->setUsername($user->getEmail());
//        $refreshToken->setRefreshToken();
//        $refreshToken->setValid((new \DateTime())->modify('+1 month'));
//        $refreshTokenManager->save($refreshToken); //в $refreshToken->getRefreshToken() строка токена


        $refreshToken = $refreshTokenGenerator->createForUserWithTtl($user, (new \DateTime())->modify('+1 month')->getTimestamp());
        $refreshTokenManager->save($refreshToken);

        $token = $JWTTokenManager->create($user);
//        return $this->json(['token' => $token, 'roles' => $user->getRoles()], Response::HTTP_CREATED);
        return $this->json([
            'token' => $token,
            'roles' => $user->getRoles(),
            'refresh_token' => $refreshToken->getRefreshToken()
            ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="api/v1/current",
     *     description="Получение текущего пользователя",
     * )
     * @OA\Response(
     *     response=200,
     *     description="Возвращение информации о текущем пользователе",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="username",
     *          type="string",
     *        ),
     *        @OA\Property(
     *          property="roles",
     *          type="array",
     *          @OA\Items(type="string")
     *        ),
     *        @OA\Property(
     *          property="balance",
     *          type="float",
     *        )
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Пользователь не был авторизирован",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="code",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string"
     *        ),
     *     )
     * )
     * @OA\Response(
     *     response="default",
     *     description="Непредвиденная ошибка",
     *     @OA\JsonContent(
     *        @OA\Property(
     *          property="code",
     *          type="string"
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string"
     *        ),
     *     )
     * )
     * @\Nelmio\ApiDocBundle\Annotation\Security(name="Bearer")
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

    /**
     * @Route("/api/v1/token/refresh", name="refresh", methods={"POST"})
     */
    public function refresh(Request $request, RefreshToken $refreshService)
    {
        return $refreshService->refresh($request);
    }
}
