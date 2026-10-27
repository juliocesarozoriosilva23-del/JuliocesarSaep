<?php
// Conexao simples com MySQL usando mysqli (sem PDO).
$host = '127.0.0.1';
$usuario = 'root';
$senha = '';
$banco = 'saep_db';
$porta = 3307; // Porta mostrada no phpMyAdmin. Altere para 3306 se seu MySQL usar essa porta.

mysqli_report(MYSQLI_REPORT_OFF);
$conexao = mysqli_connect($host, $usuario, $senha, $banco, $porta);
if (!$conexao) {
    http_response_code(500);
    exit('Erro ao conectar ao MySQL: ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8') .
         '. Confira a porta, usuario, senha e se o banco saep_db existe.');
}
mysqli_set_charset($conexao, 'utf8mb4');

// Chave local usada para criptografar CPFs.
$key = getenv('APP_ENCRYPTION_KEY');
if (!$key) {
    $key = '9f3a2b1c4d5e6f708192a3b4c5d6e7f8091a2b3c4d5e6f708192a3b4c5d6e7f8';
}
$keyBin = hex2bin($key);
if ($keyBin === false || strlen($keyBin) !== 32) {
    $keyBin = hex2bin('9f3a2b1c4d5e6f708192a3b4c5d6e7f8091a2b3c4d5e6f708192a3b4c5d6e7f8');
}
if (!defined('APP_ENCRYPTION_KEY')) {
    define('APP_ENCRYPTION_KEY', $keyBin);
}

// Funcoes simples para consultas preparadas com mysqli.
function db_stmt($sql, $types = '', $params = array()) {
    global $conexao;
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        throw new RuntimeException('Erro ao preparar consulta: ' . mysqli_error($conexao));
    }
    if ($types !== '' && count($params) > 0) {
        $refs = array();
        foreach ($params as $i => $value) {
            $refs[$i] = &$params[$i];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$refs);
    }
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Erro ao executar consulta: ' . mysqli_stmt_error($stmt));
    }
    return $stmt;
}
function db_all($sql, $types = '', $params = array()) {
    $stmt = db_stmt($sql, $types, $params);
    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : array();
    mysqli_stmt_close($stmt);
    return $rows;
}
function db_one($sql, $types = '', $params = array()) {
    $rows = db_all($sql, $types, $params);
    return $rows ? $rows[0] : null;
}
function db_value($sql, $types = '', $params = array()) {
    $row = db_one($sql, $types, $params);
    return $row ? reset($row) : 0;
}
function db_exec($sql, $types = '', $params = array()) {
    $stmt = db_stmt($sql, $types, $params);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected;
}
