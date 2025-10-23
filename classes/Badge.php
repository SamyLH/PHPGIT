<?php
require_once 'Database.php';

class Badge {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnexion();
    }

    // 🔍 Vérifie et attribue les badges
    public function verifierBadges($user_id) {
        $this->badgePremierPas($user_id);
        $this->badgeExplorateur($user_id);
        $this->badgePerfectionniste($user_id);
        $this->badgeMarathon($user_id);
    }

    // 🏅 Premier pas : jouer un premier quiz
    private function badgePremierPas($user_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        if ($stmt->fetchColumn() >= 1) {
            $this->attribuerBadge($user_id, 'Premier pas');
        }
    }

    // 🌍 Explorateur : jouer tous les thèmes
    private function badgeExplorateur($user_id) {
        $totalThemes = $this->pdo->query("SELECT COUNT(*) FROM questionnaires")->fetchColumn();
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $themesJoues = $stmt->fetchColumn();

        if ($themesJoues >= $totalThemes && $totalThemes > 0) {
            $this->attribuerBadge($user_id, 'Explorateur');
        }
    }

    // 💯 Perfectionniste : score parfait
    private function badgePerfectionniste($user_id) {
    // Vérifie si l'utilisateur a obtenu un score parfait (10/10)
    $stmt = $this->pdo->prepare("
        SELECT COUNT(*) 
        FROM scores
        WHERE user_id = ? 
        AND score = 10

    ");
    $stmt->execute([$user_id]);
    
    if ($stmt->fetchColumn() >= 1) {
        $this->attribuerBadge($user_id, 'Perfectionniste');
    }
    }


    // 🕓 Marathon : 10 parties en une journée
    private function badgeMarathon($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT DATE(created_at) AS jour, COUNT(*) AS nb
            FROM users
            WHERE id = ?
            GROUP BY jour
            HAVING nb >= 10
        ");
        $stmt->execute([$user_id]);
        if ($stmt->rowCount() > 0) {
            $this->attribuerBadge($user_id, 'Marathon');
        }
    }

    // 🧩 Attribution du badge si pas déjà obtenu
    private function attribuerBadge($user_id, $nomBadge) {
        $stmt = $this->pdo->prepare("SELECT id FROM badges WHERE nom = ?");
        $stmt->execute([$nomBadge]);
        $badge = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$badge) return;

        // Vérifier si déjà obtenu
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM user_badges WHERE user_id = ? AND badge_id = ?");
        $check->execute([$user_id, $badge['id']]);

        if ($check->fetchColumn() == 0) {
            $insert = $this->pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $insert->execute([$user_id, $badge['id']]);
        }
    }

    // 🔎 Récupère tous les badges d’un utilisateur
    // Fichier : Badge.php
public function getBadgesUtilisateur($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.nom, b.description, ub.date_obtenu
            FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
