<?php
require_once '../config.php';

$db = getDB();

// Разрешаем POST запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    jsonResponse(['success' => true]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Получаем данные из тела запроса
$input = json_decode(file_get_contents('php://input'), true);

// Валидация входных данных
$userId = $input['userId'] ?? null;
$username = $input['username'] ?? 'Анонимный игрок';
$firstName = $input['firstName'] ?? 'Игрок';

if (!$userId) {
    jsonResponse(['success' => false, 'message' => 'User ID is required'], 400);
}

// Логируем попытку записи
logAction('REGISTER_ATTEMPT', $userId, json_encode($input));

try {
    if (!$db) {
        // Если БД недоступна, имитируем успешную запись
        logAction('REGISTER_SUCCESS_DEMO', $userId, 'Database not available, using demo mode');
        
        jsonResponse([
            'success' => true,
            'message' => 'Вы успешно записались на турнир! (демо-режим)',
            'demo' => true,
            'position' => rand(70, 100)
        ]);
    }
    
    // Начинаем транзакцию
    $db->beginTransaction();
    
    // 1. Получаем текущий активный турнир
    $stmt = $db->query("SELECT id, total_seats FROM tournaments WHERE is_active = 1 ORDER BY tournament_date, tournament_time LIMIT 1");
    $tournament = $stmt->fetch();
    
    if (!$tournament) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Активных турниров нет'], 404);
    }
    
    $tournamentId = $tournament['id'];
    $totalSeats = $tournament['total_seats'];
    
    // 2. Проверяем, не записан ли пользователь уже
    $stmt = $db->prepare("SELECT id FROM registrations WHERE user_id = ? AND tournament_id = ? AND status = 'active'");
    $stmt->execute([$userId, $tournamentId]);
    
    if ($stmt->fetch()) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Вы уже записаны на этот турнир'], 409);
    }
    
    // 3. Проверяем свободные места
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM registrations WHERE tournament_id = ? AND status = 'active'");
    $stmt->execute([$tournamentId]);
    $registered = $stmt->fetch()['count'];
    
    if ($registered >= $totalSeats) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'К сожалению, все места заняты'], 409);
    }
    
    // 4. Создаем/обновляем пользователя
    $stmt = $db->prepare("
        INSERT INTO users (telegram_id, username, first_name, last_login) 
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            username = VALUES(username),
            first_name = VALUES(first_name),
            last_login = NOW()
    ");
    $stmt->execute([$userId, $username, $firstName]);
    
    // 5. Записываем на турнир
    $stmt = $db->prepare("
        INSERT INTO registrations (user_id, tournament_id, registered_at) 
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$userId, $tournamentId]);
    $registrationId = $db->lastInsertId();
    
    // 6. Добавляем рейтинговые очки
    $stmt = $db->prepare("
        INSERT INTO rating_history (user_id, points, reason) 
        VALUES (?, 10, 'Регистрация на турнир через приложение')
    ");
    $stmt->execute([$userId]);
    
    // 7. Обновляем общий рейтинг пользователя
    $stmt = $db->prepare("
        UPDATE users 
        SET rating = rating + 10,
            total_tournaments = total_tournaments + 1
        WHERE telegram_id = ?
    ");
    $stmt->execute([$userId]);
    
    // 8. Фиксируем транзакцию
    $db->commit();
    
    // Получаем номер в списке
    $position = $registered + 1;
    
    // Логируем успешную запись
    logAction('REGISTER_SUCCESS', $userId, "Tournament: $tournamentId, Position: $position");
    
    // Возвращаем успешный ответ
    jsonResponse([
        'success' => true,
        'message' => 'Вы успешно записались на турнир!',
        'position' => $position,
        'registeredCount' => $position,
        'freeSeats' => $totalSeats - $position,
        'tournamentId' => $tournamentId
    ]);
    
} catch (PDOException $e) {
    if ($db) {
        $db->rollBack();
    }
    
    error_log("Registration error: " . $e->getMessage());
    logAction('REGISTER_ERROR', $userId, $e->getMessage());
    
    jsonResponse([
        'success' => false, 
        'message' => 'Произошла ошибка при записи на турнир. Пожалуйста, попробуйте позже.'
    ], 500);
}
?>