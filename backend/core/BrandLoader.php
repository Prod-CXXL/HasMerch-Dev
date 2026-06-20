<?php

require_once __DIR__ . '/Database.php';

class BrandLoader {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getBranding($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM branding WHERE user_id = ?
        ");
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSocialsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT platform, url FROM social_links WHERE user_id = ?
        ");
        $stmt->execute([$userId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $socials = [];

        foreach ($rows as $row) {
            $socials[$row['platform']] = $row['url'];
        }

        return $socials;
    }

    public function getBrandingBySlug($slug){
    $stmt = $this->db->prepare("
        SELECT b.*, u.id as user_id
        FROM branding b
        JOIN users u ON b.user_id = u.id
        WHERE u.subdomain = ?
        LIMIT 1
    ");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}