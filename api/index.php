<?php
// Принудительный переход на HTTPS для безопасности Telegram Mini App
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect_url");
    exit();
}

// Получение данных пользователя из Telegram (если есть)
$initData = $_GET['tgWebAppData'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEBROOM Poker Club</title>
    
    <!-- Telegram Web App SDK -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    
    <!-- Иконки FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>♠️</text></svg>">
    
    <style>
        /* Основные стили */
        :root {
            --primary: #dc2626;
            --secondary: #1e293b;
            --accent: #f59e0b;
            --light: #f8fafc;
            --dark: #0f172a;
            --success: #10b981;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1a202c 100%);
            color: white;
            min-height: 100vh;
            line-height: 1.5;
            overflow-x: hidden;
        }
        
        .app-container {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background: linear-gradient(180deg, rgba(15, 23, 42, 1) 0%, rgba(26, 32, 44, 1) 100%);
            position: relative;
            padding-bottom: 70px;
        }
        
        /* Хедер */
        .app-header {
            background: linear-gradient(90deg, #1e293b 0%, #334155 100%);
            padding: 15px 20px;
            border-bottom: 2px solid rgba(220, 38, 38, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            font-size: 28px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .logo h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(90deg, #dc2626, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }
        
        .logo-subtitle {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-left: 5px;
        }
        
        .user-badge {
            width: 40px;
            height: 40px;
            background: rgba(220, 38, 38, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(220, 38, 38, 0.5);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-badge:hover {
            background: rgba(220, 38, 38, 0.3);
        }
        
        .user-badge i {
            color: #f59e0b;
        }
        
        /* Секции */
        .main-content {
            padding: 20px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-header h2 {
            font-size: 14px;
            font-weight: 600;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-header h2 i {
            color: #f59e0b;
        }
        
        .view-all {
            color: #60a5fa;
            font-size: 12px;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .view-all:hover {
            color: #93c5fd;
        }
        
        /* Карточка турнира */
        .tournament-card {
            background: linear-gradient(145deg, #1e293b, #334155);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(220, 38, 38, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .tournament-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .tournament-badge {
            background: linear-gradient(90deg, #dc2626, #ef4444);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .tournament-date {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #cbd5e1;
            font-size: 14px;
        }
        
        .tournament-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Montserrat', sans-serif;
        }
        
        .tournament-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(30, 41, 59, 0.7);
            border-radius: 10px;
            transition: transform 0.3s;
        }
        
        .info-item:hover {
            transform: translateY(-2px);
            background: rgba(30, 41, 59, 0.9);
        }
        
        .info-item i {
            font-size: 20px;
            color: #f59e0b;
        }
        
        .info-label {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 700;
            color: white;
        }
        
        /* Прогресс бар */
        .registration-info {
            margin-bottom: 20px;
        }
        
        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #cbd5e1;
        }
        
        /* Кнопки */
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .btn-primary, .btn-secondary, .btn-confirm {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #dc2626, #ef4444);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #ef4444, #f87171);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        /* Рейтинг */
        .rating-card {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .rating-list {
            padding: 10px;
        }
        
        .rating-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.3s;
        }
        
        .rating-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .rating-item:last-child {
            border-bottom: none;
        }
        
        .rank {
            width: 32px;
            height: 32px;
            background: rgba(30, 41, 59, 0.9);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            margin-right: 12px;
        }
        
        .rating-item:nth-child(1) .rank {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .rating-item:nth-child(2) .rank {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: white;
        }
        
        .rating-item:nth-child(3) .rank {
            background: linear-gradient(135deg, #92400e, #78350f);
            color: white;
        }
        
        .player-info {
            flex: 1;
        }
        
        .player-name {
            font-weight: 600;
            margin-bottom: 4px;
            color: #e2e8f0;
        }
        
        .player-stats {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #94a3b8;
        }
        
        .points {
            color: #f59e0b;
            font-weight: 500;
        }
        
        .medal {
            font-size: 20px;
        }
        
        /* Быстрые действия */
        .quick-actions {
            margin-bottom: 25px;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .action-item {
            background: rgba(30, 41, 59, 0.7);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .action-item:hover {
            background: rgba(30, 41, 59, 0.9);
            transform: translateY(-3px);
            border-color: rgba(220, 38, 38, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .action-icon {
            font-size: 32px;
            color: #f59e0b;
            margin-bottom: 10px;
        }
        
        .action-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #e2e8f0;
        }
        
        .action-subtitle {
            font-size: 12px;
            color: #94a3b8;
        }
        
        /* Нижняя навигация */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            z-index: 1000;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #94a3b8;
            padding: 8px 15px;
            border-radius: 12px;
            transition: all 0.3s;
            flex: 1;
            max-width: 80px;
        }
        
        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .nav-item span {
            font-size: 11px;
            font-weight: 500;
        }
        
        .nav-item.active {
            color: #f59e0b;
            background: rgba(220, 38, 38, 0.1);
        }
        
        .nav-item:hover:not(.active) {
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.05);
        }
        
        /* Модальные окна */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: linear-gradient(145deg, #1e293b, #334155);
            border-radius: 20px;
            margin: 20px;
            margin-top: 60px;
            max-height: 80vh;
            overflow-y: auto;
            border: 1px solid rgba(220, 38, 38, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: modalSlideUp 0.4s ease;
        }
        
        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #f59e0b;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 28px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .close-modal:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .btn-confirm {
            width: 100%;
            background: linear-gradient(90deg, #10b981, #34d399);
            color: white;
            margin-top: 20px;
        }
        
        .btn-confirm:hover {
            background: linear-gradient(90deg, #34d399, #6ee7b7);
            transform: translateY(-2px);
        }
        
        /* Загрузчик */
        .loader {
            text-align: center;
            padding: 30px;
        }
        
        .loader-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(220, 38, 38, 0.3);
            border-radius: 50%;
            border-top-color: #dc2626;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loader-text {
            color: #94a3b8;
            font-size: 14px;
        }
        
        /* Адаптивность */
        @media (max-width: 380px) {
            .tournament-title {
                font-size: 20px;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-primary, .btn-secondary {
                padding: 12px 16px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Хедер с логотипом -->
        <header class="app-header">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">♠️</div>
                    <h1>LEBROOM</h1>
                    <span class="logo-subtitle">POKER CLUB</span>
                </div>
                <div class="user-badge" id="userBadge">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </header>

        <!-- Основной контент -->
        <main class="main-content">
            <!-- Секция ближайшего турнира -->
            <section class="tournament-section">
                <div class="section-header">
                    <h2><i class="fas fa-trophy"></i> БЛИЖАЙШИЙ ТУРНИР</h2>
                </div>
                
                <div class="tournament-card" id="currentTournament">
                    <div class="tournament-header">
                        <div class="tournament-badge">НОВЫЙ</div>
                        <div class="tournament-date">
                            <i class="far fa-calendar"></i>
                            <span id="tournamentDate">Загрузка...</span>
                        </div>
                    </div>
                    
                    <h3 class="tournament-title" id="tournamentTitle">LEBROOM TOURNAMENT</h3>
                    
                    <div class="tournament-info">
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <div class="info-label">Мест</div>
                                <div class="info-value" id="tournamentSeats">...</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-coins"></i>
                            <div>
                                <div class="info-label">Бай-ин</div>
                                <div class="info-value" id="tournamentBuyIn">...</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-award"></i>
                            <div>
                                <div class="info-label">Призовой</div>
                                <div class="info-value" id="tournamentPrize">...</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="registration-info">
                        <div class="progress-bar">
                            <div class="progress-fill" id="registrationProgress" style="width: 0%;"></div>
                        </div>
                        <div class="progress-text">
                            <span id="registeredCount">0</span> из <span id="totalSeats">0</span> мест занято
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-primary" id="registerBtn">
                            <i class="fas fa-user-plus"></i> ЗАПИСАТЬСЯ
                        </button>
                        <button class="btn-secondary" id="detailsBtn">
                            <i class="fas fa-info-circle"></i> ПОДРОБНЕЕ
                        </button>
                    </div>
                </div>
            </section>

            <!-- Секция рейтинга -->
            <section class="rating-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> РЕЙТИНГ LEBROOM</h2>
                    <a href="#" class="view-all" id="viewAllRating">Все →</a>
                </div>
                
                <div class="rating-card">
                    <div class="rating-list" id="ratingList">
                        <div class="loader">
                            <div class="loader-spinner"></div>
                            <div class="loader-text">Загрузка рейтинга...</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Секция быстрых действий -->
            <section class="quick-actions">
                <div class="action-grid">
                    <div class="action-item" id="qaBtn">
                        <div class="action-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="action-title">Q&A</div>
                        <div class="action-subtitle">Ответы на вопросы</div>
                    </div>
                    
                    <div class="action-item" id="supportBtn">
                        <div class="action-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="action-title">SUPPORT</div>
                        <div class="action-subtitle">Чат поддержки</div>
                    </div>
                    
                    <div class="action-item" id="clubInfoBtn">
                        <div class="action-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="action-title">О КЛУБЕ</div>
                        <div class="action-subtitle">Информация</div>
                    </div>
                    
                    <div class="action-item" id="myProfileBtn">
                        <div class="action-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="action-title">ПРОФИЛЬ</div>
                        <div class="action-subtitle">Личный кабинет</div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Нижняя навигация -->
        <nav class="bottom-nav">
            <a href="#" class="nav-item active" data-page="main">
                <i class="fas fa-home"></i>
                <span>Главная</span>
            </a>
            <a href="#" class="nav-item" data-page="rating">
                <i class="fas fa-chart-bar"></i>
                <span>Рейтинг</span>
            </a>
            <a href="#" class="nav-item" data-page="tournaments">
                <i class="fas fa-trophy"></i>
                <span>Турниры</span>
            </a>
            <a href="#" class="nav-item" data-page="profile">
                <i class="fas fa-user"></i>
                <span>Профиль</span>
            </a>
        </nav>

        <!-- Модальные окна -->
        <div id="modals">
            <!-- Модалка записи на турнир -->
            <div class="modal" id="registerModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Запись на турнир</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Вы действительно хотите записаться на турнир <strong id="modalTournamentName">LEBROOM TOURNAMENT</strong>?</p>
                        <div class="modal-info">
                            <p><i class="far fa-calendar"></i> <strong>Дата:</strong> <span id="modalTournamentDate">Загрузка...</span></p>
                            <p><i class="fas fa-coins"></i> <strong>Бай-ин:</strong> <span id="modalTournamentBuyIn">Загрузка...</span></p>
                            <p><i class="fas fa-users"></i> <strong>Свободных мест:</strong> <span id="modalFreeSeats">Загрузка...</span></p>
                        </div>
                        <button class="btn-confirm" id="confirmRegisterBtn">
                            <i class="fas fa-check"></i> ПОДТВЕРДИТЬ ЗАПИСЬ
                        </button>
                        <p style="margin-top: 15px; font-size: 12px; color: #94a3b8; text-align: center;">
                            После записи с вами свяжется администратор для подтверждения
                        </p>
                    </div>
                </div>
            </div>

            <!-- Модалка информации о клубе -->
            <div class="modal" id="clubInfoModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>LEBROOM POKER CLUB</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="club-logo-modal">
                            <div class="logo-modal-icon">♠️</div>
                            <h2>LEBROOM</h2>
                            <p style="color: #94a3b8; margin-top: 5px;">PREMIUM POKER CLUB</p>
                        </div>
                        
                        <div class="club-info">
                            <h4><i class="fas fa-star"></i> О НАС</h4>
                            <p>Премиальный покерный клуб с комфортной атмосферой и профессиональными дилерами. Мы проводим регулярные турниры и кэш-игры для игроков всех уровней.</p>
                            
                            <h4><i class="fas fa-map-marker-alt"></i> АДРЕС КЛУБА</h4>
                            <p>📍 Москва, Пресненская набережная, 12</p>
                            <p>🚇 Метро: Выставочная, Деловой центр</p>
                            <p>🕐 Ежедневно с 18:00 до 06:00</p>
                            
                            <h4><i class="fas fa-phone-alt"></i> КОНТАКТЫ</h4>
                            <p>📞 Телефон: +7 (999) 123-45-67</p>
                            <p>📧 Email: info@lebroom.ru</p>
                            <p>💬 Telegram поддержка: @lebroomsupport</p>
                            
                            <h4><i class="fas fa-medal"></i> ПРЕИМУЩЕСТВА КЛУБА</h4>
                            <ul style="list-style: none; padding-left: 0;">
                                <li>✅ Профессиональные дилеры с лицензией</li>
                                <li>✅ Современные игровые столы</li>
                                <li>✅ Комфортные VIP зоны</li>
                                <li>✅ Бесплатные напитки и закуски</li>
                                <li>✅ Регулярные турниры с гарантированным призовым фондом</li>
                                <li>✅ Система рейтинга и бонусов</li>
                                <li>✅ Безопасность и конфиденциальность</li>
                            </ul>
                            
                            <h4><i class="fas fa-calendar-alt"></i> РАСПИСАНИЕ ТУРНИРОВ</h4>
                            <p>🔥 Понедельник: Texas Hold'em (20:00)</p>
                            <p>🔥 Среда: Omaha Hi-Lo (20:00)</p>
                            <p>🔥 Пятница: High Roller (21:00)</p>
                            <p>🔥 Суббота: Main Event (19:00)</p>
                            
                            <div style="background: rgba(220, 38, 38, 0.1); padding: 15px; border-radius: 10px; margin-top: 20px;">
                                <h4><i class="fas fa-exclamation-circle"></i> ВАЖНАЯ ИНФОРМАЦИЯ</h4>
                                <p>• Минимальный возраст: 21 год</p>
                                <p>• При себе необходимо иметь паспорт</p>
                                <p>• Дресс-код: smart casual</p>
                                <p>• Бронирование столов обязательно</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модалка Q&A -->
            <div class="modal" id="qaModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Часто задаваемые вопросы</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="faq-list">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Как записаться на турнир через это приложение?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    На главной странице нажмите кнопку "ЗАПИСАТЬСЯ" на карточке турнира. Подтвердите запись в появившемся окне. После записи с вами свяжется администратор для подтверждения участия и уточнения деталей.
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Как работает рейтинговая система LEBROOM?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Рейтинг начисляется за участие в турнирах:
                                    • 1 место: 100 очков
                                    • 2 место: 80 очков  
                                    • 3 место: 60 очков
                                    • 4-10 место: 40 очков
                                    • Участие: 20 очков
                                    • Запись через приложение: +10 очков
                                    Топ-10 игроков месяца получают бонусы и специальные привилегии.
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Какие способы оплаты доступны в клубе?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    • Наличные (рубли)
                                    • Банковские карты (Visa, MasterCard, Мир)
                                    • Перевод на карту Сбербанк/Тинькофф
                                    • USDT (криптовалюта)
                                    • Банковский перевод для юридических лиц
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Что такое бай-ин и как он работает?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Бай-ин — это взнос за участие в турнире. Он распределяется следующим образом:
                                    80% — призовой фонд
                                    15% — организационные расходы
                                    5% — бонусный фонд для игроков
                                    Пример: при бай-ине 5 000 ₽, 4 000 ₽ идет в призовой фонд.
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Можно ли отменить запись на турнир?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Да, отмена возможна не менее чем за 3 часа до начала турнира. Для отмены свяжитесь с поддержкой через кнопку "SUPPORT" в приложении. При отмене менее чем за 3 часа может взиматься штраф в размере 50% от бай-ина.
                                </div>
                            </div>
                            
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span>Есть ли система скидок для постоянных игроков?</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="faq-answer">
                                    Да, у нас есть программа лояльности:
                                    • 5 турниров: скидка 10% на следующий
                                    • 10 турниров: скидка 15%
                                    • 20 турниров: VIP статус (20% скидка + бесплатные напитки)
                                    • Топ-3 рейтинга: специальные условия
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Модалка успешной записи -->
            <div class="modal" id="successModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Успешная запись!</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body" style="text-align: center;">
                        <div style="font-size: 60px; color: #10b981; margin: 20px 0;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 style="color: #10b981; margin-bottom: 15px;">Вы успешно записаны!</h3>
                        <p id="successMessage">Вы записаны на турнир <strong>LEBROOM TOURNAMENT</strong></p>
                        <p style="color: #94a3b8; margin: 15px 0;">
                            С вами свяжется администратор для подтверждения участия и уточнения деталей.
                        </p>
                        <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 10px; margin: 20px 0;">
                            <h4><i class="fas fa-info-circle"></i> ДЕТАЛИ ЗАПИСИ</h4>
                            <p><strong>Дата:</strong> <span id="successDate">Загрузка...</span></p>
                            <p><strong>Время:</strong> <span id="successTime">Загрузка...</span></p>
                            <p><strong>Ваш номер в списке:</strong> <span id="successPosition">Загрузка...</span></p>
                        </div>
                        <button class="btn-confirm" onclick="closeModal('successModal')">
                            <i class="fas fa-check"></i> ПОНЯТНО
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Полный код app.js встроенный прямо в index.php
        // Инициализация Telegram Web App
        const tg = window.Telegram.WebApp;
        let userData = null;
        let currentTournament = null;
        let isUserRegistered = false;

        // Базовый URL для API (для Beget)
        const API_BASE_URL = '/api';

        // Инициализация приложения
        function initApp() {
            console.log('Инициализация LEBROOM Poker App...');
            
            // Развернуть приложение на весь экран
            tg.expand();
            
            // Получить данные пользователя
            userData = tg.initDataUnsafe?.user;
            console.log('Данные пользователя:', userData);
            
            if (userData) {
                updateUserBadge();
                checkUserRegistration();
            } else {
                console.warn('Данные пользователя не получены');
                showNotification('Для полного доступа войдите через Telegram', 'warning');
            }
            
            // Установить тему Telegram
            setTelegramTheme();
            
            // Загрузить данные
            loadTournamentData();
            loadRatingData();
            
            // Настроить обработчики событий
            setupEventListeners();
            
            // Инициализировать FAQ
            initFAQ();
            
            console.log('Приложение инициализировано');
        }

        // Обновить бейдж пользователя
        function updateUserBadge() {
            const userBadge = document.getElementById('userBadge');
            if (!userBadge) return;
            
            if (userData?.first_name) {
                const initials = userData.first_name.charAt(0).toUpperCase();
                userBadge.innerHTML = `<span style="font-weight: 700; font-size: 16px;">${initials}</span>`;
                userBadge.title = `${userData.first_name} ${userData.last_name || ''}`;
            }
        }

        // Установить тему Telegram
        function setTelegramTheme() {
            const theme = tg.colorScheme;
            if (theme === 'dark') {
                document.body.style.background = 'linear-gradient(135deg, #0f172a 0%, #1a202c 100%)';
            } else {
                document.body.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
                document.body.style.color = '#1e293b';
                
                // Обновляем цвета для светлой темы
                document.documentElement.style.setProperty('--light', '#1e293b');
                document.documentElement.style.setProperty('--dark', '#f8fafc');
            }
        }

        // Загрузить данные турнира
        async function loadTournamentData() {
            try {
                showLoader('tournament');
                
                // Для Beget используем PHP API
                const response = await fetch(`${API_BASE_URL}/tournament/current.php`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                currentTournament = data;
                
                // Обновить интерфейс
                updateTournamentUI(data);
                
                hideLoader('tournament');
                
            } catch (error) {
                console.error('Ошибка загрузки турнира:', error);
                hideLoader('tournament');
                
                // Fallback данные для отображения
                const fallbackData = {
                    title: 'LEBROOM HIGH ROLLER',
                    date: '22.01',
                    time: '19:00',
                    totalSeats: 100,
                    registeredCount: 65,
                    buyIn: '5 000 ₽',
                    prizePool: '500 000 ₽',
                    progress: 65
                };
                
                updateTournamentUI(fallbackData);
                
                showNotification('Не удалось загрузить данные турнира', 'error');
            }
        }

        // Обновить UI турнира
        function updateTournamentUI(data) {
            // Основные данные
            document.getElementById('tournamentTitle').textContent = data.title || 'LEBROOM TOURNAMENT';
            document.getElementById('tournamentDate').textContent = `${data.date || '22.01'} / ${data.time || '19:00'}`;
            document.getElementById('tournamentSeats').textContent = data.totalSeats || '100';
            document.getElementById('tournamentBuyIn').textContent = data.buyIn || '5 000 ₽';
            document.getElementById('tournamentPrize').textContent = data.prizePool || '500 000 ₽';
            
            // Прогресс записи
            const registered = data.registeredCount || 65;
            const total = data.totalSeats || 100;
            const progress = Math.min((registered / total) * 100, 100);
            
            document.getElementById('registeredCount').textContent = registered;
            document.getElementById('totalSeats').textContent = total;
            document.getElementById('registrationProgress').style.width = `${progress}%`;
            
            // Обновить модальное окно
            document.getElementById('modalTournamentName').textContent = data.title || 'LEBROOM TOURNAMENT';
            document.getElementById('modalTournamentDate').textContent = `${data.date || '22.01.2024'} в ${data.time || '19:00'}`;
            document.getElementById('modalTournamentBuyIn').textContent = data.buyIn || '5 000 ₽';
            document.getElementById('modalFreeSeats').textContent = total - registered;
            
            // Обновить успешную запись
            document.getElementById('successDate').textContent = data.date || '22.01.2024';
            document.getElementById('successTime').textContent = data.time || '19:00';
        }

        // Проверить запись пользователя
        async function checkUserRegistration() {
            if (!userData?.id || !currentTournament) return;
            
            try {
                // Здесь можно добавить проверку через API
                // Для MVP считаем, что пользователь не записан
                isUserRegistered = false;
                updateRegisterButton();
                
            } catch (error) {
                console.error('Ошибка проверки записи:', error);
            }
        }

        // Обновить кнопку записи
        function updateRegisterButton() {
            const registerBtn = document.getElementById('registerBtn');
            if (!registerBtn) return;
            
            if (isUserRegistered) {
                registerBtn.innerHTML = '<i class="fas fa-check"></i> ВЫ ЗАПИСАНЫ';
                registerBtn.style.background = 'linear-gradient(90deg, #10b981, #34d399)';
                registerBtn.disabled = true;
                registerBtn.onclick = null;
            } else {
                registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> ЗАПИСАТЬСЯ';
                registerBtn.style.background = 'linear-gradient(90deg, #dc2626, #ef4444)';
                registerBtn.disabled = false;
                registerBtn.onclick = () => openModal('registerModal');
            }
        }

        // Записаться на турнир
        async function registerForTournament() {
            if (!userData?.id) {
                showNotification('Пожалуйста, авторизуйтесь в Telegram', 'warning');
                return;
            }
            
            if (!currentTournament) {
                showNotification('Данные турнира не загружены', 'error');
                return;
            }
            
            try {
                // Показать индикатор загрузки
                const confirmBtn = document.getElementById('confirmRegisterBtn');
                const originalText = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ОБРАБОТКА...';
                confirmBtn.disabled = true;
                
                // Отправить запрос на запись
                const response = await fetch(`${API_BASE_URL}/tournament/register.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userId: userData.id,
                        username: userData.username || `${userData.first_name} ${userData.last_name || ''}`,
                        firstName: userData.first_name,
                        tournamentId: 'current'
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // Успешная запись
                    isUserRegistered = true;
                    
                    // Обновить интерфейс
                    updateRegisterButton();
                    
                    // Обновить счетчик
                    const currentRegistered = parseInt(document.getElementById('registeredCount').textContent);
                    document.getElementById('registeredCount').textContent = currentRegistered + 1;
                    
                    const totalSeats = parseInt(document.getElementById('totalSeats').textContent);
                    const newProgress = ((currentRegistered + 1) / totalSeats) * 100;
                    document.getElementById('registrationProgress').style.width = `${newProgress}%`;
                    
                    // Обновить свободные места
                    document.getElementById('modalFreeSeats').textContent = totalSeats - (currentRegistered + 1);
                    
                    // Показать номер в списке
                    document.getElementById('successPosition').textContent = `#${currentRegistered + 1}`;
                    document.getElementById('successMessage').innerHTML = `Вы записаны на турнир <strong>${currentTournament.title || 'LEBROOM TOURNAMENT'}</strong>`;
                    
                    // Закрыть модальное окно и показать успех
                    closeModal('registerModal');
                    setTimeout(() => openModal('successModal'), 300);
                    
                    // Отправить данные в бот (для Bothelp)
                    if (tg.sendData) {
                        try {
                            tg.sendData(JSON.stringify({
                                action: 'tournament_registered',
                                userId: userData.id,
                                tournament: currentTournament.title,
                                date: currentTournament.date,
                                time: currentTournament.time,
                                position: currentRegistered + 1
                            }));
                        } catch (e) {
                            console.warn('Не удалось отправить данные в бота:', e);
                        }
                    }
                    
                    // Показать уведомление
                    showNotification('Вы успешно записались на турнир!', 'success');
                    
                } else {
                    throw new Error(result.message || 'Ошибка при записи');
                }
                
            } catch (error) {
                console.error('Ошибка записи:', error);
                
                // Показать ошибку
                if (error.message.includes('уже записаны')) {
                    isUserRegistered = true;
                    updateRegisterButton();
                    showNotification('Вы уже записаны на этот турнир', 'info');
                } else if (error.message.includes('все места заняты')) {
                    showNotification('К сожалению, все места заняты', 'error');
                } else {
                    showNotification(error.message || 'Ошибка при записи на турнир', 'error');
                }
                
            } finally {
                // Восстановить кнопку
                const confirmBtn = document.getElementById('confirmRegisterBtn');
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> ПОДТВЕРДИТЬ ЗАПИСЬ';
                confirmBtn.disabled = false;
            }
        }

        // Загрузить рейтинг
        async function loadRatingData() {
            try {
                const response = await fetch(`${API_BASE_URL}/rating/top.php`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                updateRatingUI(data.players || []);
                
            } catch (error) {
                console.error('Ошибка загрузки рейтинга:', error);
                
                // Fallback данные
                const fallbackPlayers = [
                    { id: 1, name: 'Иван Петров', points: 2540, tournaments: 15 },
                    { id: 2, name: 'Алексей Смирнов', points: 2120, tournaments: 12 },
                    { id: 3, name: 'Мария Иванова', points: 1980, tournaments: 10 },
                    { id: 4, name: 'Дмитрий Козлов', points: 1850, tournaments: 8 },
                    { id: 5, name: 'Анна Сидорова', points: 1720, tournaments: 7 }
                ];
                
                updateRatingUI(fallbackPlayers);
            }
        }

        // Обновить UI рейтинга
        function updateRatingUI(players) {
            const ratingList = document.getElementById('ratingList');
            if (!ratingList) return;
            
            if (players.length === 0) {
                ratingList.innerHTML = `
                    <div style="text-align: center; padding: 30px; color: #94a3b8;">
                        <i class="fas fa-chart-line" style="font-size: 40px; margin-bottom: 15px;"></i>
                        <p>Рейтинг пока пуст</p>
                        <p style="font-size: 12px;">Станьте первым участником турнира!</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            players.forEach((player, index) => {
                const medal = getMedalEmoji(index + 1);
                html += `
                    <div class="rating-item">
                        <div class="rank">${index + 1}</div>
                        <div class="player-info">
                            <div class="player-name">${player.name || `Игрок ${player.id}`}</div>
                            <div class="player-stats">
                                <span class="points">${player.points?.toLocaleString() || '0'} очков</span>
                                <span class="tournaments">${player.tournaments || 0} турниров</span>
                            </div>
                        </div>
                        <div class="medal">${medal}</div>
                    </div>
                `;
            });
            
            ratingList.innerHTML = html;
        }

        // Получить эмодзи медали
        function getMedalEmoji(rank) {
            switch(rank) {
                case 1: return '🥇';
                case 2: return '🥈';
                case 3: return '🥉';
                default: return '';
            }
        }

        // Настроить обработчики событий
        function setupEventListeners() {
            // Кнопка записи
            document.getElementById('registerBtn')?.addEventListener('click', () => {
                openModal('registerModal');
            });
            
            // Подтверждение записи
            document.getElementById('confirmRegisterBtn')?.addEventListener('click', registerForTournament);
            
            // Кнопка поддержки
            document.getElementById('supportBtn')?.addEventListener('click', () => {
                tg.openTelegramLink('https://t.me/lebroomsupport');
            });
            
            // Кнопка информации о клубе
            document.getElementById('clubInfoBtn')?.addEventListener('click', () => {
                openModal('clubInfoModal');
            });
            
            // Кнопка Q&A
            document.getElementById('qaBtn')?.addEventListener('click', () => {
                openModal('qaModal');
            });
            
            // Кнопка профиля
            document.getElementById('myProfileBtn')?.addEventListener('click', () => {
                if (userData) {
                    // Переключить на вкладку профиля
                    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
                    document.querySelector('.nav-item[data-page="profile"]')?.classList.add('active');
                    
                    // Показать информацию профиля
                    showProfileModal();
                } else {
                    showNotification('Для доступа к профилю войдите через Telegram', 'warning');
                }
            });
            
            // Кнопка просмотра всего рейтинга
            document.getElementById('viewAllRating')?.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
                document.querySelector('.nav-item[data-page="rating"]')?.classList.add('active');
                
                showNotification('Полный рейтинг загружается...', 'info');
                // Здесь можно загрузить полный рейтинг
            });
            
            // Кнопка подробнее о турнире
            document.getElementById('detailsBtn')?.addEventListener('click', () => {
                if (currentTournament) {
                    tg.showAlert(`🎯 ${currentTournament.title}\n📅 Дата: ${currentTournament.date}\n⏰ Время: ${currentTournament.time}\n💰 Бай-ин: ${currentTournament.buyIn}\n🏆 Призовой фонд: ${currentTournament.prizePool}\n👥 Участников: ${currentTournament.registeredCount}/${currentTournament.totalSeats}`);
                } else {
                    tg.showAlert('Подробная информация о турнире в разработке');
                }
            });
            
            // Нижняя навигация
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = item.getAttribute('data-page');
                    
                    // Убрать активный класс у всех
                    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                    // Добавить активный класс текущему
                    item.classList.add('active');
                    
                    // Загрузить соответствующую страницу
                    loadPage(page);
                });
            });
            
            // Закрытие модальных окон
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal.id);
                });
            });
            
            // Закрытие модальных окон по клику на фон
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this.id);
                    }
                });
            });
            
            // Обработка клавиши ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const modals = document.querySelectorAll('.modal[style*="display: block"]');
                    if (modals.length > 0) {
                        closeModal(modals[modals.length - 1].id);
                    }
                }
            });
        }

        // Инициализация FAQ
        function initFAQ() {
            const faqQuestions = document.querySelectorAll('.faq-question');
            
            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const faqItem = question.parentElement;
                    const isActive = faqItem.classList.contains('active');
                    
                    // Закрыть все
                    document.querySelectorAll('.faq-item').forEach(item => {
                        item.classList.remove('active');
                        const icon = item.querySelector('.faq-question i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                        }
                    });
                    
                    // Открыть текущий, если был закрыт
                    if (!isActive) {
                        faqItem.classList.add('active');
                        const icon = question.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                        }
                    }
                });
            });
        }

        // Показать модальное окно профиля
        function showProfileModal() {
            if (!userData) return;
            
            const profileHtml = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Ваш профиль</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc2626, #f59e0b); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; color: white; font-weight: bold;">
                                ${userData.first_name?.charAt(0).toUpperCase() || 'U'}
                            </div>
                            <h3 style="margin-top: 15px;">${userData.first_name || ''} ${userData.last_name || ''}</h3>
                            ${userData.username ? `<p style="color: #94a3b8;">@${userData.username}</p>` : ''}
                        </div>
                        
                        <div style="background: rgba(30, 41, 59, 0.5); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                            <h4><i class="fas fa-chart-line"></i> ВАША СТАТИСТИКА</h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 15px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: #f59e0b;">0</div>
                                    <div style="font-size: 12px; color: #94a3b8;">Турниров</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: #f59e0b;">0</div>
                                    <div style="font-size: 12px; color: #94a3b8;">Очков рейтинга</div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: rgba(30, 41, 59, 0.5); padding: 20px; border-radius: 12px;">
                            <h4><i class="fas fa-history"></i> ПОСЛЕДНИЕ АКТИВНОСТИ</h4>
                            <p style="color: #94a3b8; text-align: center; padding: 20px;">
                                У вас еще нет активности.<br>
                                Запишитесь на турнир, чтобы начать!
                            </p>
                        </div>
                        
                        <button class="btn-primary" style="width: 100%; margin-top: 20px;" onclick="closeCurrentModal()">
                            <i class="fas fa-sign-out-alt"></i> ВЫЙТИ ИЗ ПРОФИЛЯ
                        </button>
                    </div>
                </div>
            `;
            
            // Создаем модальное окно
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = 'profileModal';
            modal.innerHTML = profileHtml;
            document.getElementById('modals').appendChild(modal);
            
            // Настроить закрытие
            modal.querySelector('.close-modal').addEventListener('click', () => closeModal('profileModal'));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal('profileModal');
            });
            
            openModal('profileModal');
        }

        // Управление модальными окнами
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Анимация появления
                setTimeout(() => {
                    modal.style.opacity = '1';
                }, 10);
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        function closeCurrentModal() {
            const modals = document.querySelectorAll('.modal[style*="display: block"]');
            if (modals.length > 0) {
                closeModal(modals[modals.length - 1].id);
            }
        }

        // Загрузка страниц
        function loadPage(page) {
            // Здесь можно реализовать загрузку разных страниц
            // Для MVP пока просто показываем уведомление
            switch(page) {
                case 'main':
                    // Уже на главной
                    break;
                case 'rating':
                    tg.showAlert('Страница полного рейтинга в разработке');
                    break;
                case 'tournaments':
                    tg.showAlert('Страница всех турниров в разработке');
                    break;
                case 'profile':
                    showProfileModal();
                    break;
            }
        }

        // Вспомогательные функции
        function showLoader(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.add('loading');
            }
        }

        function hideLoader(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.classList.remove('loading');
            }
        }

        function showNotification(message, type = 'info') {
            // Используем Telegram уведомления
            try {
                switch(type) {
                    case 'success':
                        tg.HapticFeedback.notificationOccurred('success');
                        tg.showAlert(message);
                        break;
                    case 'error':
                        tg.HapticFeedback.notificationOccurred('error');
                        tg.showAlert(`❌ ${message}`);
                        break;
                    case 'warning':
                        tg.HapticFeedback.notificationOccurred('warning');
                        tg.showAlert(`⚠️ ${message}`);
                        break;
                    default:
                        tg.showAlert(message);
                }
            } catch (e) {
                console.log('Уведомление:', message);
            }
        }

        // Инициализировать приложение при загрузке
        document.addEventListener('DOMContentLoaded', initApp);
        
        // Обработка видимости страницы
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Обновить данные при возвращении на страницу
                loadTournamentData();
                loadRatingData();
            }
        });
        
        // Периодическое обновление данных (каждые 30 секунд)
        setInterval(() => {
            if (!document.hidden) {
                loadTournamentData();
            }
        }, 30000);
    </script>
</body>
</html>