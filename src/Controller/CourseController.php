<?php

namespace App\Controller;

use App\DTO\Convertor\CourseConvertor;
use App\DTO\PaymentDTO;
use App\Entity\BillingUser;
use App\Repository\CourseRepository;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use HttpException;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserService;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as NelmioSecurity;


/**
 * @Route("/api/v1/courses", name="app_course")
 */
class CourseController extends AbstractController
{
    private $us;

    public function __construct(UserService $us)
    {
        $this->us = $us;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/",
     *     description="Список курсов"
     * )
     * @Route("/", name="app_course", methods={"GET"})
     */
    public function index(
        CourseRepository $courseRepository,
        SerializerInterface $serializer
    ): Response
    {
        $courses = $courseRepository->findAll();
        $coursesDTO = CourseConvertor::toDTO($courses);
        return JsonResponse::fromJsonString($serializer->serialize($coursesDTO, 'json'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{code}",
     *     description="Список курсов"
     * )
     * @Route("/{code}", name="app_course_show", methods={"GET"})
     */
    public function show(
        string $code,
        CourseRepository $courseRepository,
        SerializerInterface $serializer
    ): Response
    {
        $course = $courseRepository->findOneBy(['code' => $code]);
        $courseDTO = CourseConvertor::toDTO([$course]);
        return JsonResponse::fromJsonString($serializer->serialize($courseDTO[0], 'json'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/courses/{code}/pay",
     *     description="Оплата курса"
     * )
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *        @OA\Property(
     *     property="token",
     *     type="string"),
     *     ),
     * )
     * @OA\Response(
     *     response="201",
     *     description="Курс оплачен",
     *     @OA\JsonContent(
     *          @OA\Property(property="success", type="bool"),
     *          @OA\Property(property="course_type", type="string"),
     *          @OA\Property(property="expires_at", type="datetime")
     *      )
     * )
     *
     * @OA\Response(
     *     response="406",
     *     description="Недостаточно средств",
     *     @OA\JsonContent(
     *          @OA\Property(property="code", type="string"),
     *          @OA\Property(property="message", type="string")
     *      )
     * )
     * @Route("/{code}/pay", name="app_course_pay", methods={"POST"})
     */
    public function pay(
        string $code,
        CourseRepository $courseRepository,
        SerializerInterface $serializer,
        PaymentService $paymentService,
        Request $request
    ): Response
    {
        $course = $courseRepository->findOneBy(['code' => $code]);

        if ($course === null) {
            throw new \Exception('Курс не найден', Response::HTTP_NOT_FOUND);
        }

        $token = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['token'];
        $user = $this->us->getUserByToken($token);

        try {
            $transaction = $paymentService->payment($user, $course);
        } catch(\Exception $e) {
            throw new HttpException($e->getCode(), $e->getMessage());
        }

        $payment = new PaymentDTO();
        $payment->success = true;
        $payment->courseType = $course->getType();
        $payment->expiresAt = $transaction->getExpireTime();

        return JsonResponse::fromJsonString($serializer->serialize($payment, 'json'));
    }
}
