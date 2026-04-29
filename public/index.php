<?php
// public/index.php - Main entry point
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// Get the request URI
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string and base path
$requestPath = parse_url($request, PHP_URL_PATH);
$basePath = '/store-projet/public';
if (strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}
if (empty($requestPath)) $requestPath = '/';

// Routing
switch ($requestPath) {
    case '/':
    case '/index.php':
        $controller = new ProductController();
        $controller->home();
        break;
    
    case '/login':
        $auth = new AuthController();
        if ($method === 'POST') {
            $auth->login();
        } else {
            $auth->showLogin();
        }
        break;
    
    case '/register':
        $auth = new AuthController();
        if ($method === 'POST') {
            $auth->register();
        } else {
            $auth->showRegister();
        }
        break;
    
    case '/logout':
        $auth = new AuthController();
        $auth->logout();
        break;
    
    case '/shop':
        $controller = new ProductController();
        $controller->shop();
        break;
    
    case (preg_match('/^\/product\/([a-z0-9-]+)$/', $requestPath, $matches) ? true : false):
        $controller = new ProductController();
        $controller->showProduct($matches[1]);
        break;
    
    case '/cart':
        $cart = new CartController();
        $cart->showCart();
        break;
    
    case '/checkout':
        $order = new OrderController();
        if ($method === 'POST') {
            $order->processCheckout();
        } else {
            $order->showCheckout();
        }
        break;
    
    case '/order-confirmation':
        $order = new OrderController();
        $order->confirmation();
        break;
    
    case '/profile':
        $auth = new AuthController();
        $auth->profile();
        break;
    
    case '/orders':
        $order = new OrderController();
        $order->userOrders();
        break;
    
    case '/wishlist':
        $cart = new CartController();
        $cart->wishlist();
        break;
    
    case '/admin/dashboard':
    case '/admin':
        $admin = new AdminController();
        $admin->dashboard();
        break;
    
    case '/admin/products':
        $admin = new AdminController();
        $admin->products();
        break;
    
    case '/admin/orders':
        $admin = new AdminController();
        $admin->orders();
        break;
    
    case '/admin/users':
        $admin = new AdminController();
        $admin->users();
        break;
    
    case '/admin/categories':
        $admin = new AdminController();
        $admin->categories();
        break;

    case '/admin/products/add':
        $admin = new AdminController();
        $admin->addProduct();
        break;

    case '/admin/products/edit':
        $admin = new AdminController();
        $admin->updateProduct();
        break;

    case '/admin/products/delete':
        $admin = new AdminController();
        $admin->deleteProduct();
        break;
        
    default:
        http_response_code(404);
        require_once __DIR__ . '/../views/404.php';
        break;
}