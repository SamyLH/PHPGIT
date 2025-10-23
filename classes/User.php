<?php
/**
 * Classe User
 * Gère l'authentification et les données utilisateur
 *
 * 📚 RESPONSABILITÉS :
 * - Connexion (login)
 * - Inscription (register)
 * - Récupération de l'historique des scores
 */

class User {
    // 📚 CONCEPT : Propriétés de l'utilisateur
    private ?int $id = null;         // ? signifie "peut être null"
    private string $pseudo;
    private string $email;
    private ?string $dateInscription; // nouvelle propriété

    /**
     * Constructeur
     */
    public function __construct(?int $id, string $pseudo, string $email, ?string $dateInscription = null) {
        $this->id = $id;
        $this->pseudo = $pseudo;
        $this->email = $email;
        $this->dateInscription = $dateInscription;
    }

    /**
     * Authentification d'un utilisateur
     */
    public static function login(string $email, string $password): ?User {
        $pdo = Database::getConnexion();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $ligne = $stmt->fetch();

        if ($ligne && password_verify($password, $ligne['password_hash'])) {
            return new User(
                $ligne['id'],
                $ligne['pseudo'],
                $ligne['email'],
                $ligne['date_inscription'] ?? null
            );
        }

        return null;
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public static function register(string $pseudo, string $email, string $password): ?User {
        $pdo = Database::getConnexion();

        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Ajout automatique de la date d'inscription
            $sql = "INSERT INTO users (pseudo, email, password_hash, date_inscription) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pseudo, $email, $passwordHash]);

            $id = (int)$pdo->lastInsertId();

            return new User($id, $pseudo, $email, date('Y-m-d H:i:s'));

        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Récupère l'historique des scores de l'utilisateur
     */
    public function getHistorique(): array {
        $pdo = Database::getConnexion();

        $sql = "
            SELECT
                s.*,                          
                q.titre as theme_titre,       
                q.emoji                       
            FROM scores s
            INNER JOIN questionnaires q ON s.questionnaire_id = q.id
            WHERE s.user_id = ?
            ORDER BY s.date_jeu DESC
            LIMIT 20
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$this->id]);

        return $stmt->fetchAll();
    }

    // ====== GETTERS ======

    public function getId(): ?int {
        return $this->id;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getDateInscription(): ?string {
        return $this->dateInscription;
    }
}
