<?php
require_once('Auth.php');
$db = new PDO(
    dsn: "mysql:host=127.0.0.1:3306;dbname=referralsystem",
    username: "root",
    password: ""
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

session_start();

$auth = new Auth($db);

$shopId = $auth->loggedShopIn();
if (!$shopId) {
    if (isset($_POST["domain"]) || isset($_POST["accountName"]) || isset($_POST["password"])) {
        $shopId = $auth->authenticate($_POST["domain"], $_POST["accountName"], $_POST["password"]);
        if ($shopId) {
            $auth->logShopIn($shopId);
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
    $auth->logShopOut();
    header("Location: /");
    exit;
}
echo ("You go to secret place!");
?>
<form method="post">
    <input type="hidden" name="logout">
    <button>Logout</button>
</form>