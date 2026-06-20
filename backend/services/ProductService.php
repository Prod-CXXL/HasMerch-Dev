<?php

require_once __DIR__ . '/../core/Database.php';

class ProductService {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM products 
            WHERE user_id = ? AND status = 'active'
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}