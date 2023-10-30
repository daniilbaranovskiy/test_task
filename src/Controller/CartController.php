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
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $this->getUserCart($session, $user);
        $cartItems = [];

        foreach ($cart as $productId => $cartItem) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if ($product) {
                $cartItems[] = [
                    'product'  => $product,
                    'quantity' => $cartItem['quantity'],
                ];
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    /**
     * @param SessionInterface $session
     * @param Request $request
     * @param $productId
     * @return Response
     */
    #[Route("/cart/add/{productId}", name: "app_cart_add")]
    public function addToCart(SessionInterface $session, Request $request, $productId): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $this->getUserCart($session, $user);

        $quantity = (int)$request->request->get('quantity', 1);

        if (!isset($cart[$productId])) {
            $cart[$productId] = ['quantity' => 0];
        }

        $cart[$productId]['quantity'] += $quantity;

        $this->saveUserCart($session, $user, $cart);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @param SessionInterface $session
     * @param $productId
     * @return Response
     */
    #[Route("/cart/remove/{productId}", name: "app_cart_remove")]
    public function removeFromCart(SessionInterface $session, $productId): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $this->getUserCart($session, $user);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        $this->saveUserCart($session, $user, $cart);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @param SessionInterface $session
     * @param $productId
     * @return Response
     */
    #[Route("/cart/increase/{productId}", name: "app_cart_increase")]
    public function increaseCartQuantity(SessionInterface $session, $productId): Response
    {
        $this->changeCartQuantity($session, $productId, 1);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @param SessionInterface $session
     * @param $productId
     * @return Response
     */
    #[Route("/cart/decrease/{productId}", name: "app_cart_decrease")]
    public function decreaseCartQuantity(SessionInterface $session, $productId): Response
    {
        $this->changeCartQuantity($session, $productId, -1);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @param SessionInterface $session
     * @return Response
     */
    #[Route("/cart/checkout", name: "app_cart_checkout")]
    public function checkout(SessionInterface $session): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $this->getUserCart($session, $user);

        if (empty($cart)) {
            return $this->redirectToRoute('app_cart');
        }

        $userBalance = $user->getBalance();
        $totalAmount = 0;
        $products = [];
        $repository = $this->entityManager->getRepository(Product::class);

        foreach ($cart as $productId => $cartItem) {
            $product = $repository->find($productId);

            if ($product) {
                $productPrice = $product->getPrice();
                $totalAmount += $productPrice * $cartItem['quantity'];
                $products[$productId] = $product;
            }
        }

        if ($totalAmount > $userBalance) {
            return $this->redirectToRoute('app_user_balance', ['id' => $user->getId()]);
        }

        foreach ($cart as $productId => $cartItem) {
            if (isset($products[$productId])) {
                $product = $products[$productId];
                $newStockQuantity = $product->getStockQuantity() - $cartItem['quantity'];
                $product->setStockQuantity($newStockQuantity);
                $this->entityManager->persist($product);
            }
        }

        $user->setBalance($userBalance - $totalAmount);

        $order = new Orders();
        $order->setUser($user);

        foreach ($cart as $productId => $cartItem) {
            if (isset($products[$productId])) {
                $product = $products[$productId];
                $orderProduct = new OrderProducts();
                $orderProduct->setProduct($product);
                $orderProduct->setQuantity($cartItem['quantity']);
                $orderProduct->setPricePerUnit($product->getPrice());

                $order->addOrderProduct($orderProduct);
            }
        }

        $order->setTotalAmount($totalAmount);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->saveUserCart($session, $user, $cart);

        return $this->redirectToRoute('app_orders_index');
    }

    /**
     * @param SessionInterface $session
     * @param User $user
     * @return array
     */
    private function getUserCart(SessionInterface $session, User $user): array
    {
        $userId = $user->getId();
        $cartKey = 'cart_' . $userId;
        return $session->get($cartKey, []);
    }

    /**
     * @param SessionInterface $session
     * @param User $user
     * @param array $cart
     */
    private function saveUserCart(SessionInterface $session, User $user, array $cart): void
    {
        $userId = $user->getId();
        $cartKey = 'cart_' . $userId;
        $session->set($cartKey, $cart);
    }

    /**
     * @param SessionInterface $session
     * @param $productId
     * @param $change
     * @return void
     */
    private function changeCartQuantity(SessionInterface $session, $productId, $change): void
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            $this->redirectToRoute('app_login');
            return;
        }

        $cart = $this->getUserCart($session, $user);

        if (isset($cart[$productId])) {
            $newQuantity = $cart[$productId]['quantity'] + $change;

            if ($newQuantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $newQuantity;
            }

            $this->saveUserCart($session, $user, $cart);
        }
    }

}
