<?php
require_once '../config.php';

$db = getDB();

try {
    if (!$db) {
        // Fallback данные если БД не доступна
        jsonResponse([
            'title' => 'LEBROOM HIGH ROLLER',
            'date' => '22.01',
            'time' => '19:00',
            'totalSeats' => 100,
            'registeredCount' => rand(65, 85), // Случайное число для демо
            'buyIn' => '5 000 ₽',
            'prizePool' => '500 000 ₽',
            'description' => 'Еженедельный турнир с гарантированным призовым фондом'
        ]);
    }
    
    // Получаем текущий активный турнир
    $stmt = $db->query("
        SELECT 
            id,
            title,
            DATE_FORMAT(tournament_date, '%d.%m') as date,
            DATE_FORMAT(tournament_time, '%H:%i') as time,
            total_seats as totalSeats,
            buy_in as buyIn,
            prize_pool as prizePool
        FROM tournaments 
        WHERE is_active = 1 
        ORDER BY tournament_date, tournament_time 
        LIMIT 1
    ");
    
    $tournament = $stmt->fetch();
    
    if (!$tournament) {
        // Если нет активных турниров, создаем демо-данные
        $tournament = [
            'title' => 'LEBROOM HIGH ROLLER',
            'date' => date('d.m', strtotime('+1 day')),
            'time' => '19:00',
            'totalSeats' => 100,
            'buyIn' => '5 000 ₽',
            'prizePool' => '500 000 ₽'
        ];
    }
    
    // Получаем количество записавшихся
    if (isset($tournament['id'])) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as registeredCount 
            FROM registrations 
            WHERE tournament_id = ? AND status = 'active'
        ");
        $stmt->execute([$tournament['id']]);
        $registration = $stmt->fetch();
        $tournament['registeredCount'] = $registration['registeredCount'] ?? 0;
    } else {
        $tournament['registeredCount'] = rand(65, 85);
    }
    
    jsonResponse($tournament);
    
} catch (PDOException $e) {
    error_log("Tournament query error: " . $e->getMessage());
    
    // Fallback данные при ошибке
    jsonResponse([
        'title' => 'LEBROOM HIGH ROLLER',
        'date' => '22.01',
        'time' => '19:00',
        'totalSeats' => 100,
        'registeredCount' => 72,
        'buyIn' => '5 000 ₽',
        'prizePool' => '500 000 ₽',
        'error' => 'DEMO_DATA'
    ]);
}
?>