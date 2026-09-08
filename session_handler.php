<?php
// Ruajtja e sesioneve në databazë - e nevojshme në Vercel sepse funksionet
// serverless nuk ndajnë disk mes njëra-tjetrës, ndryshe nga MAMP lokalisht.
class PdoSessionHandler implements SessionHandlerInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                data MEDIUMTEXT NOT NULL,
                updated_at INT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
    }

    public function open($path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';
    }

    public function write($id, $data)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO sessions (id, data, updated_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = VALUES(updated_at)"
        );
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc($max_lifetime)
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE updated_at < ?");
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}
