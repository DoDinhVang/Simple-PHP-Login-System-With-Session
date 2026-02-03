<?php
if (isset($_SESSION["error"])) {
    echo '<div style="color:red">' . htmlspecialchars($_SESSION["error"])  . '</div>';
    $_SESSION["error"] = null;
}
?>


<form method="post">
    Domain: <input type="text" name="domain"> <br />
    Account name: <input type="text" name="accountName"> <br />
    Password: <input type="text" name="password"> <br />
    <button type="submit">Login</button>
</form>