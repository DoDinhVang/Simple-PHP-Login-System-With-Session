<?php
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['data'] : '';
    }

    public function write($id, $data): bool
    {
        $stmt = $this->db->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (:id, :data, :ts)");
        return $stmt->execute(['id' => $id, 'data' => $data, 'ts' => time()]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function gc($maxlifetime): int|false
    {
        $old = time() - $maxlifetime;
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE timestamp < :old");
        return $stmt->execute(['old' => $old]) ? 1 : false;
    }
}
