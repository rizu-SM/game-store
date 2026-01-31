<?php
/**
 * API Authentification - Games Store
 * Gestion sécurisée des sessions et cookies (remember me)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';


// Handle both JSON and FormData input
$jsonInput = json_decode(file_get_contents('php://input'), true) ?? [];
$input = array_merge($_POST, $jsonInput);
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($input);
        break;
    case 'register':
        handleRegister($input);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        checkAuth();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}

/* ==============================
   LOGIN
================================ */
function handleLogin($input) {
    global $pdo;

    $email    = $input['email'] ?? $_POST['email'] ?? '';
    $password = $input['password'] ?? $_POST['password'] ?? '';
    $remember = $input['remember'] ?? isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
        return;
    }

    $stmt = $pdo->prepare(
        "SELECT id, username, email, password, role 
         FROM users 
         WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
        return;
    }

    // Protection session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['user_role'] = $user['role'];

    /* ===== Remember me ===== */
    if ($remember) {
        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $stmt = $pdo->prepare(
            "UPDATE users SET remember_token = ? WHERE id = ?"
        );
        $stmt->execute([$tokenHash, $user['id']]);

        setcookie(
            'remember_token',
            $token,
            time() + 60*60, // 1 heure
            '/',
            '',
            false,
            true // HttpOnly
        );
    }

    echo json_encode([
        'success'  => true,
        'message'  => 'Connexion réussie',
        'redirect' => $user['role'] === 'admin' ? '/admin/' : '/index.php'
    ]);
}

/* ==============================
   REGISTER
================================ */
function handleRegister($input) {
    global $pdo;

    $username = trim($input['username'] ?? '');
    $email    = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirm  = $input['confirm_password'] ?? '';

    // Validate required fields are not empty
    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
        return;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
        return;
    }

    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas']);
        return;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE email = ? OR username = ?"
    );
    $stmt->execute([$email, $username]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur déjà existant']);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password, role) 
         VALUES (?, ?, ?, 'user')"
    );
    $stmt->execute([$username, $email, $hash]);

    session_regenerate_id(true);

    $_SESSION['user_id']   = $pdo->lastInsertId();
    $_SESSION['username']  = $username;
    $_SESSION['user_role'] = 'user';

    echo json_encode(['success' => true, 'message' => 'Inscription réussie']);
}

/* ==============================
   LOGOUT
================================ */
function handleLogout() {
    global $pdo;

    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare(
            "UPDATE users SET remember_token = NULL WHERE id = ?"
        );
        $stmt->execute([$_SESSION['user_id']]);
    }

    $_SESSION = [];
    session_destroy();

    setcookie('remember_token', '', time() - 3600, '/');

    // Redirection si appel direct (non AJAX)
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Location: /index.php');
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
}

/* ==============================
   CHECK AUTH
================================ */
function checkAuth() {
    if (!empty($_SESSION['user_id'])) {
        refreshRememberToken(); // ← Rafraîchit le cookie à chaque check
        echo json_encode([
            'authenticated' => true,
            'user' => [
                'id'       => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role'     => $_SESSION['user_role']
            ]
        ]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}