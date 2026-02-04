<?php
class Auth
{
    public function __construct(protected PDO $db) {}
    public function addShop(
        string $name,
        string $domain,
        string $subdomain,
        string $currency,
        string $country,
        $accountName,
        $password
    ): bool | int {
        $name = trim($name);
        $domain = trim($domain);
        $subdomain = trim($subdomain);
        $currency = trim($currency);
        $country = trim($country);
        $accountName = trim($accountName);
        $password = trim($password);

        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($hash === false) {
            return false;
        }
        if ($hash === null) {
            throw new \ErrorException('Invalid hashing algorithm');
        }
        //Check if shop already exitst 
        try {
            $stmt = $this->db->prepare("INSERT INTO  shops (name, domain, subdomain, currency, country, account_name, password)
             VALUES(:name, :domain, :subdomain, :currency, :country, :accountName, :password)");
            $stmt->execute([
                ":name" => $name,
                ":domain" => $domain,
                ":subdomain" => $subdomain,
                ":currency" => $currency,
                ":country" => $country,
                ":accountName" => $accountName,
                ":password" => $hash,
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
        $id = $this->db->lastInsertId();

        if ($id === false) {
            return false;
        }
        return intval($id);
    }

    public function authenticate(string $domain, string $accountName, string $password): int | false
    {
        $domain = trim($domain);
        $accountName = trim($accountName);
        $password = trim($password);
        try {
            $stmt = $this->db->prepare("SELECT id, domain, account_name, password FROM shops WHERE domain=:domain AND account_name=:accountName");
            $stmt->execute([
                ":domain" => $domain,
                ":accountName" => $accountName,
            ]);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
        }
        $shop = $stmt->fetch(PDO::FETCH_ASSOC); // lấy bản ghi duy nhất dc trả  từ câu truy vấn;
        if ($shop === false) {
            return false;
        }
        if (!isset($shop["password"])) {
            throw new \Exception("Password collumn is not found in database!");
        }
        if (!isset($shop["id"])) {
            throw new \Exception("ID collumn is not found in database!");
        }
        $verify =  password_verify($password, $shop["password"]);
        if ($verify === true) {
            return $shop["id"];
        }
        return false;
    }
    public function logShopIn(string $shopId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            throw new \Exception("Session has not been started!");
        }
        session_regenerate_id(true);
        $_SESSION["logged_shop_in"] = $shopId;
    }
    public function logShopOut(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            throw new \Exception("Session has not been started!");
        }
        session_regenerate_id(true);
        session_destroy();
    }
    public function loggedShopIn(): bool|int
    {
        if (session_status() === PHP_SESSION_NONE) {
            throw new \Exception("Session has not been started!");
        }
        if (!isset($_SESSION["logged_shop_in"])) {
            return false;
        }
        if (!$_SESSION['logged_shop_in']) {
            return false;
        }
        return intval($_SESSION['logged_shop_in']);
    }
}
