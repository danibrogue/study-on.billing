<?php

namespace App\Controller;

use App\DTO\Convertor\CourseConvertor;
use App\DTO\PaymentDTO;
use App\Entity\BillingUser;
use App\Repository\CourseRepository;
use App\Service\PaymentService;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/api/v1/courses", name="app_course", methods={GET})
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/", name="app_course", methods={GET})
     */
    public function index(
        CourseRepository $courseRepository,
        SerializerInterface $serializer
    ): Response
    {
        $courses = $courseRepository->findAll();
        $coursesDTO = CourseConvertor::toDTO($courses);
        return new JsonResponse($serializer->serialize($coursesDTO, 'json'));
    }

    /**
     * @Route("/{code}", name="app_course_show", methods={GET})
     */
    public function show(
        string $code,
        CourseRepository $courseRepository,
        SerializerInterface $serializer
    ): Response
    {
        $course = $courseRepository->findOneBy(['code' => $code]);
        $courseDTO = CourseConvertor::toDTO($course);
        return new JsonResponse($serializer->serialize($courseDTO[0], 'json'));
    }

    /**
     * @Route("/{code}/pay", name="app_course_show", methods={GET})
     */
    public function pay(
        string $code,
        CourseRepository $courseRepository,
        SerializerInterface $serializer,
        PaymentService $paymentService
    ): Response
    {
        $course = $courseRepository->findOneBy(['code' => $code]);

        if ($course === null) {
            throw new \Exception('Курс не найден', Response::HTTP_NOT_FOUND);
        }

        /** @var BillingUser $user */
        $user = $this->getUser();

        $transaction = $paymentService->payment($user, $course);

        $payment = new PaymentDTO();
        $payment->success = true;
        $payment->courseType = $course->getType();
        $payment->expiresAt = $transaction->setExpireTime();

        return new JsonResponse($serializer->serialize($payment, 'json'));
    }
}
