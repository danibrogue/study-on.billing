<?php

namespace App\Controller;

use App\DTO\Convertor\TransactionConvertor;
use App\Repository\TransactionRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    private $us;

    public function __construct(UserService $us)
    {
        $this->us = $us;
    }

    private const OPERATION_TYPE = [
        1 => 'deposit',
        2 => 'payment'
    ];

    /**
     * @Route("/api/v1/transactions", name="app_transaction", methods={"GET"})
     */
    public function index(
        EntityManagerInterface $em,
        TransactionRepository $transactionRepository,
        SerializerInterface $serializer,
        Request $request
    ): Response
    {
        $token = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)['token'];
        $user = $this->us->getUserByToken($token);


        $filters = [
            'type' => $request->query->get('type') ? self::OPERATION_TYPE[$request->query->get('type')] : null,
            'course_code' => $request->query->get('course_code'),
            'skip_expired' => $request->query->get('skip_expired')
        ];

        $transactions = $transactionRepository->findSomeBy($filters, $user, $em);
        $transactionsDTO = TransactionConvertor::toDTO($transactions);
        return JsonResponse::fromJsonString($serializer->serialize($transactionsDTO, 'json'));
    }
}
