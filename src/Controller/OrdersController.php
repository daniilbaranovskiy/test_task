<?php

namespace App\Controller;

use App\Form\OrdersType;
use App\Repository\OrdersRepository;
use App\Security\OrdersVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders')]
class OrdersController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param OrdersRepository $ordersRepository
     * @return Response
     */
    #[Route('/', name: 'app_orders_index', methods: ['GET'])]
    public function index(OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $orders = $ordersRepository->findAll();
        } else {
            $orders = $ordersRepository->findBy(['user' => $user]);
        }

        return $this->render('orders/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @param OrdersRepository $ordersRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_orders_show', methods: ['GET'])]
    public function show(OrdersRepository $ordersRepository, $id): Response
    {
        $order = $ordersRepository->find($id);

        if (!$order) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isGranted(OrdersVoter::SHOW, $order)) {
            return $this->renderNotFoundPage();
        }

        return $this->render('orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @param Request $request
     * @param OrdersRepository $ordersRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_orders_edit', methods: [
        'GET',
        'POST'
    ])]
    public function edit(Request $request, OrdersRepository $ordersRepository, $id): Response
    {
        $order = $ordersRepository->find($id);

        if (!$order) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isGranted(OrdersVoter::EDIT, $order)) {
            return $this->renderNotFoundPage();
        }

        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form'  => $form,
        ]);
    }

    /**
     * @param Request $request
     * @param OrdersRepository $ordersRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_orders_delete', methods: ['POST'])]
    public function delete(Request $request, OrdersRepository $ordersRepository, $id): Response
    {
        $order = $ordersRepository->find($id);

        if (!$order) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isGranted(OrdersVoter::DELETE, $order)) {
            return $this->renderNotFoundPage();
        }

        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($order);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @return Response
     */
    private function renderNotFoundPage(): Response
    {
        return $this->render('404.html.twig');
    }

}
