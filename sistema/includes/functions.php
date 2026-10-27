<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf() {
    $sent = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!is_string($sent) || !hash_equals(isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '', $sent)) {
        http_response_code(403);
        exit('Token de segurança inválido. Volte e tente novamente.');
    }
}
function cpf_encrypt($plain) {
    $iv = random_bytes(16);
    $cipher = openssl_encrypt((string)$plain, 'aes-256-cbc', APP_ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
    if ($cipher === false) return '';
    return base64_encode($iv . $cipher);
}
function cpf_decrypt($encoded) {
    $raw = base64_decode((string)$encoded, true);
    if ($raw === false || strlen($raw) < 17) return 'Protegido';
    $plain = openssl_decrypt(substr($raw, 16), 'aes-256-cbc', APP_ENCRYPTION_KEY, OPENSSL_RAW_DATA, substr($raw, 0, 16));
    return $plain === false ? 'Protegido' : $plain;
}
function require_login() {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
    $timeout = 900;
    if (isset($_SESSION['ultima_atividade']) && time() - (int)$_SESSION['ultima_atividade'] > $timeout) {
        session_unset();
        session_destroy();
        header('Location: login.php?expirada=1');
        exit;
    }
    $_SESSION['ultima_atividade'] = time();
}
function current_user() {
    return isset($_SESSION['usuario_nome']) ? (string)$_SESSION['usuario_nome'] : 'Usuário';
}
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
