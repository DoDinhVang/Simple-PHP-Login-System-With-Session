<?php
require_once('Auth.php');
require_once('vendor/autoload.php');

$config = require('config.php');
$authMethod = $config['auth_method'];

// Configuration for session storage: 'database' or 'native'
$sessionStorage = 'native'; // Change to 'native' to use default PHP session handling
$sessionName = 'id';

if ($sessionStorage === 'database') {
    require_once('DatabaseSessionHandler.php');
}

$db = new PDO(
    dsn: "mysql:host=127.0.0.1:3306;dbname=referralsystem",
    username: "root",
    password: ""
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($sessionStorage === 'database') {
    $sessionHandler = new DatabaseSessionHandler($db);
    session_set_save_handler($sessionHandler, true);
}

$auth = new Auth($db);

$shopId = false;
if ($authMethod === "jwt") {
    $token = $_COOKIE['auth_token'] ?? "";
    $shopId = $auth->verifyToken($token);
} else {
    session_name($sessionName);
    session_start(['cookie_httponly' => true]);
    $shopId = $auth->loggedShopIn();
}

if (!$shopId) {
    if (isset($_POST["domain"]) || isset($_POST["accountName"]) || isset($_POST["password"])) {
        $shopId = $auth->authenticate($_POST["domain"], $_POST["accountName"], $_POST["password"]);
        if ($shopId) {
            if ($authMethod === "jwt") {
                $token = $auth->genertateToken($shopId);
                setcookie("auth_token", $token, [
                    'expires' => time() + $config['jwt_expire'],
                    'path' => '/',
                    'httponly' => true
                ]);
            } else {
                $auth->logShopIn($shopId);
            }
            header("Location: /");
            exit;
        } else {
            $_SESSION['error'] = "Wrong domain or account name or password";
        }
    }
    require_once("login.php");
    exit;
}

if (isset($_POST["logout"])) {
    if ($authMethod === 'jwt') {
        setcookie("auth_token", '', time() - 3600, '/');
    } else {
        $auth->logShopOut();
    }
    header("Location: /");
    exit;
}
echo ("You go to secret place!");
?>
<form method="post">
    <input type="hidden" name="logout">
    <button>Logout</button>
</form>