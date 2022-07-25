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
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as NelmioSecurity;

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
     * @OA\Get(
     *     path="/api/v1/transactions/",
     *     description="Список транзакций"
     * )
     * @OA\Parameter(
     *     name="token",
     *     in="header",
     *     description="The field used to order rewards"
     * )
     * @OA\Parameter(
     *     name="Content-Type",
     *     in="header",
     *     example="application/json",
     *     description="The field used to order rewards"
     * )
     *  @OA\Parameter(
     *     name="filters[type]",
     *     in="query",
     *     required=false,
     *     description="The field used to order rewards"
     * )
     *  @OA\Parameter(
     *     name="filters[course_code]",
     *     in="query",
     *     required=false,
     *     description="The field used to order rewards"
     * )
     *  @OA\Parameter(
     *     name="filters[skip_expired]",
     *     in="query",
     *     required=false,
     *     description="The field used to order rewards"
     * )
     * @Route("/api/v1/transactions", name="app_transaction", methods={"GET"})
     */
    public function index(
        EntityManagerInterface $em,
        TransactionRepository $transactionRepository,
        SerializerInterface $serializer,
        Request $request
    ): Response
    {
        $token = $request->headers->get('token');
        $user = $this->us->getUserByToken($token);


        $filters = $request->query->get('filters');
        $transactions = $transactionRepository->findSomeBy($filters, $user, $em);
        $transactionsDTO = TransactionConvertor::toDTO($transactions);
        return JsonResponse::fromJsonString($serializer->serialize($transactionsDTO, 'json'));
    }
}
