<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/User.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Enregistrer l’utilisateur avec la date d’inscription
            $stmt = $conn->prepare("INSERT INTO users (pseudo, email, password, date_inscription) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$pseudo, $email, $hashed]);

            $succes = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - QuizMusic 🎵</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🎵 QuizMusic</h1>
            <p class="text-gray-600">Créez un compte pour jouer</p>
        </div>

        <?php if ($erreur): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php elseif ($succes): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4">
                <?= htmlspecialchars($succes) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Pseudo</label>
                <input type="text" name="pseudo" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Mot de passe</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Confirmer le mot de passe</label>
                <input type="password" name="confirm" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all duration-200">
                S’inscrire
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
            Déjà un compte ?
            <a href="login.php" class="text-purple-600 hover:text-purple-700 font-medium">Connectez-vous</a>
        </p>
    </div>
</body>
</html>
