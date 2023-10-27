<?php

namespace App\Controller;

use App\Entity\OrderProducts;
use App\Entity\Orders;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
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
     * @param SessionInterface $session
     * @return Response
     */
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];

        foreach ($cart as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if ($product) {
                $cartItems[] = [
                    'product'  => $product,
                    'quantity' => $quantity,
                ];
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    #[Route("/cart/add/{productId}", name: "app_cart_add")]
    public function addToCart(SessionInterface $session, Request $request, $productId): Response
    {
        $cart = $session->get('cart', []);

        $quantity = (int)$request->request->get('quantity', 1);

        $cart[$productId] = isset($cart[$productId]) ? $cart[$productId] + $quantity : $quantity;

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }


    #[Route("/cart/remove/{productId}", name: "app_cart_remove")]
    public function removeFromCart(SessionInterface $session, $productId): Response
    {
        $cart = $session->get('cart', []);

        unset($cart[$productId]);

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route("/cart/checkout", name: "app_cart_checkout")]
    public function checkout(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('app_login');
        }

        /** @var User $user */
        $user = $this->getUser();
        $userBalance = $user->getBalance();
        $totalAmount = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if ($product) {
                $productPrice = $product->getPrice();
                $totalAmount += $productPrice * $quantity;
            }
        }

        $user->setBalance($userBalance - $totalAmount);

        $order = new Orders();
        $order->setUser($user);

        foreach ($cart as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if ($product) {
                $orderProduct = new OrderProducts();
                $orderProduct->setProduct($product);
                $orderProduct->setQuantity($quantity);
                $orderProduct->setPricePerUnit($product->getPrice());

                $order->addOrderProduct($orderProduct);
            }
        }

        if ($totalAmount > $userBalance) {
            return $this->redirectToRoute('app_user_balance', ['id' => $user->getId()]);
        }

        $order->setTotalAmount($totalAmount);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $session->set('cart', []);

        return $this->redirectToRoute('app_orders_index');
    }

}
