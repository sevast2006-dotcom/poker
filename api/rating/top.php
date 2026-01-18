<?php
require_once '../config.php';

$db = getDB();

try {
    if (!$db) {
        // Fallback данные для демо
        $players = [
            ['id' => 1, 'name' => 'Иван Петров', 'points' => 2540, 'tournaments' => 15],
            ['id' => 2, 'name' => 'Алексей Смирнов', 'points' => 2120, 'tournaments' => 12],
            ['id' => 3, 'name' => 'Мария Иванова', 'points' => 1980, 'tournaments' => 10],
            ['id' => 4, 'name' => 'Дмитрий Козлов', 'points' => 1850, 'tournaments' => 8],
            ['id' => 5, 'name' => 'Анна Сидорова', 'points' => 1720, 'tournaments' => 7],
            ['id' => 6, 'name' => 'Сергей Волков', 'points' => 1650, 'tournaments' => 9],
            ['id' => 7, 'name' => 'Ольга Морозова', 'points' => 1580, 'tournaments' => 6],
            ['id' => 8, 'name' => 'Павел Новиков', 'points' => 1520, 'tournaments' => 8],
            ['id' => 9, 'name' => 'Екатерина Ковалева', 'points' => 1480, 'tournaments' => 5],
            ['id' => 10, 'name' => 'Максим Лебедев', 'points' => 1420, 'tournaments' => 7]
        ];
        
        jsonResponse(['players' => $players, 'demo' => true]);
    }
    
    // Получаем топ игроков
    $stmt = $db->query("
        SELECT 
            u.telegram_id as id,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as name,
            COALESCE(u.username, '') as username,
            u.rating as points,
            COALESCE(u.total_tournaments, 0) as tournaments,
            u.created_at as member_since
        FROM users u
        WHERE u.rating > 0
        ORDER BY u.rating DESC
        LIMIT 10
    ");
    
    $players = $stmt->fetchAll();
    
    // Форматируем данные
    foreach ($players as &$player) {
        if (empty(trim($player['name']))) {
            $player['name'] = $player['username'] ? '@' . $player['username'] : 'Анонимный игрок';
        }
        
        $player['points'] = (int)$player['points'];
        $player['tournaments'] = (int)$player['tournaments'];
    }
    
    if (empty($players)) {
        // Если нет данных в БД, возвращаем демо-данные
        $players = [
            ['id' => 1, 'name' => 'Иван Петров', 'points' => 2540, 'tournaments' => 15],
            ['id' => 2, 'name' => 'Алексей Смирнов', 'points' => 2120, 'tournaments' => 12],
            ['id' => 3, 'name' => 'Мария Иванова', 'points' => 1980, 'tournaments' => 10]
        ];
    }
    
    jsonResponse(['players' => $players]);
    
} catch (PDOException $e) {
    error_log("Rating query error: " . $e->getMessage());
    
    // Fallback данные при ошибке
    $players = [
        ['id' => 1, 'name' => 'Иван Петров', 'points' => 2540, 'tournaments' => 15],
        ['id' => 2, 'name' => 'Алексей Смирнов', 'points' => 2120, 'tournaments' => 12],
        ['id' => 3, 'name' => 'Мария Иванова', 'points' => 1980, 'tournaments' => 10]
    ];
    
    jsonResponse(['players' => $players, 'error' => 'DEMO_DATA']);
}
?>