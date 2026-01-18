<?php
// Конфигурация для LEBROOM Poker API

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Настройки базы данных Beget (замените на свои)
define('DB_HOST', 'localhost');
define('DB_NAME', 'ваш_логин_lebroom'); // Замените на имя вашей БД
define('DB_USER', 'ваш_логин_lebroom'); // Замените на пользователя БД
define('DB_PASS', 'ваш_пароль_бд');     // Замените на пароль БД

// Проверка соединения с БД
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("DB Connection Error: " . $e->getMessage());
            return null;
        }
    }
    return $db;
}

// Проверка Telegram WebApp данных (упрощенная для MVP)
function validateTelegramData($initData) {
    // В реальном проекте нужно реализовать проверку подписи
    // https://core.telegram.org/bots/webapps#validating-data-received-via-the-web-app
    return true; // Для MVP пропускаем проверку
}

// Безопасный вывод JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Логирование действий
function logAction($action, $userId = null, $details = '') {
    $logFile = __DIR__ . '/../logs/actions.log';
    $message = date('Y-m-d H:i:s') . " | Action: $action | User: $userId | Details: $details\n";
    file_put_contents($logFile, $message, FILE_APPEND);
}
?>