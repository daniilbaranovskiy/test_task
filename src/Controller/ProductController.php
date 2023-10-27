<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/product')]
class ProductController extends AbstractController
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
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/new', name: 'app_product_new', methods: [
        'GET',
        'POST'
    ])]
    public function new(Request $request): Response
    {
        if (!$this->isAdmin()) {
            return $this->renderNotFoundPage();
        }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form'    => $form,
        ]);
    }

    /**
     * @param ProductRepository $productRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(ProductRepository $productRepository, $id): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->renderNotFoundPage();
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_product_edit', methods: [
        'GET',
        'POST'
    ])]
    public function edit(Request $request, ProductRepository $productRepository, $id): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->renderNotFoundPage();
        }

        if (!$this->isAdmin()) {
            return $this->renderNotFoundPage();
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form'    => $form,
        ]);
    }

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param $id
     * @return Response
     */
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, ProductRepository $productRepository, $id): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->renderNotFoundPage();
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @return bool
     */
    private function isAdmin(): bool
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    /**
     * @return Response
     */
    private function renderNotFoundPage(): Response
    {
        return $this->render('404.html.twig');
    }

}
