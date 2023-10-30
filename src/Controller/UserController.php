<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BalanceType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class UserController extends AbstractController
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
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->renderNotFoundPage();
        }
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @param UserRepository $userRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(UserRepository $userRepository, $id): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isGranted(UserVoter::SHOW, $user)) {
            return $this->renderNotFoundPage();
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_user_edit', methods: [
        'GET',
        'POST'
    ])]
    public function edit(Request $request, UserRepository $userRepository, $id): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isGranted(UserVoter::EDIT, $user)) {
            return $this->renderNotFoundPage();
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, UserRepository $userRepository, $id): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isGranted(UserVoter::DELETE, $user)) {
            return $this->renderNotFoundPage();
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}/balance', name: 'app_user_balance', methods: [
        'GET',
        'POST'
    ])]
    public function topUpBalance(Request $request, UserRepository $userRepository, $id): Response
    {
        /** @var User $user */
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted(UserVoter::BALANCE, $user)) {
            return $this->renderNotFoundPage();
        }

        $form = $this->createForm(BalanceType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();

            if ($amount <= 0) {
                $form->get('amount')->addError(new FormError('Amount must be greater than zero.'));
            } else {
                $user->setBalance($user->getBalance() + $amount);
                $this->entityManager->flush();
                $this->addFlash('success', 'Balance topped up successfully');

                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
            }
        }

        return $this->render('user/balance.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     */
    private function renderNotFoundPage(): Response
    {
        return $this->render('404.html.twig');
    }

}
