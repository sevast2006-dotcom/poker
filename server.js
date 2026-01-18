const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { Pool } = require('pg'); // Для PostgreSQL

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Подключение к базе данных (PostgreSQL)
const pool = new Pool({
    connectionString: process.env.DATABASE_URL || 'postgresql://user:password@localhost:5432/lebroom_poker',
    ssl: process.env.NODE_ENV === 'production' ? { rejectUnauthorized: false } : false
});

// База данных: создание таблиц
async function initializeDatabase() {
    try {
        await pool.query(`
            CREATE TABLE IF NOT EXISTS users (
                telegram_id BIGINT PRIMARY KEY,
                username VARCHAR(100),
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                phone VARCHAR(20),
                rating INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS tournaments (
                id SERIAL PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                date DATE NOT NULL,
                time TIME NOT NULL,
                total_seats INTEGER NOT NULL,
                buy_in INTEGER NOT NULL,
                prize_pool INTEGER NOT NULL,
                is_active BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS registrations (
                id SERIAL PRIMARY KEY,
                user_id BIGINT REFERENCES users(telegram_id),
                tournament_id INTEGER REFERENCES tournaments(id),
                registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'active',
                UNIQUE(user_id, tournament_id)
            );

            CREATE TABLE IF NOT EXISTS rating_history (
                id SERIAL PRIMARY KEY,
                user_id BIGINT REFERENCES users(telegram_id),
                points INTEGER NOT NULL,
                reason VARCHAR(200),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        `);
        console.log('База данных инициализирована');
    } catch (error) {
        console.error('Ошибка инициализации БД:', error);
    }
}

// API: Получить текущий турнир
app.get('/api/tournament/current', async (req, res) => {
    try {
        const result = await pool.query(`
            SELECT t.*, 
                   COUNT(r.id) as registered_count
            FROM tournaments t
            LEFT JOIN registrations r ON t.id = r.tournament_id AND r.status = 'active'
            WHERE t.is_active = true
            ORDER BY t.date, t.time
            LIMIT 1
        `);
        
        if (result.rows.length === 0) {
            return res.json({
                title: 'RED LUXE TOURNAMENT',
                date: '22.01',
                time: '19:00',
                totalSeats: 100,
                registeredCount: 65,
                buyIn: 5000,
                prizePool: 500000
            });
        }
        
        const tournament = result.rows[0];
        res.json({
            title: tournament.title,
            date: new Date(tournament.date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' }),
            time: tournament.time.substring(0, 5),
            totalSeats: tournament.total_seats,
            registeredCount: parseInt(tournament.registered_count),
            buyIn: tournament.buy_in,
            prizePool: tournament.prize_pool
        });
        
    } catch (error) {
        console.error('Ошибка получения турнира:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// API: Запись на турнир
app.post('/api/tournament/register', async (req, res) => {
    const { userId, username, tournamentId = 'current' } = req.body;
    
    if (!userId) {
        return res.status(400).json({ success: false, message: 'User ID required' });
    }
    
    try {
        // Создать или обновить пользователя
        await pool.query(`
            INSERT INTO users (telegram_id, username, first_name)
            VALUES ($1, $2, $3)
            ON CONFLICT (telegram_id) DO UPDATE 
            SET username = EXCLUDED.username
            RETURNING *
        `, [userId, username, username?.split(' ')[0]]);
        
        // Получить ID текущего турнира
        const tournamentResult = await pool.query(`
            SELECT id FROM tournaments 
            WHERE is_active = true 
            ORDER BY date, time 
            LIMIT 1
        `);
        
        if (tournamentResult.rows.length === 0) {
            return res.status(404).json({ success: false, message: 'Турнир не найден' });
        }
        
        const tournament_id = tournamentResult.rows[0].id;
        
        // Проверить, не записан ли уже
        const existingRegistration = await pool.query(`
            SELECT * FROM registrations 
            WHERE user_id = $1 AND tournament_id = $2 AND status = 'active'
        `, [userId, tournament_id]);
        
        if (existingRegistration.rows.length > 0) {
            return res.json({ success: false, message: 'Вы уже записаны на этот турнир' });
        }
        
        // Проверить свободные места
        const seatsResult = await pool.query(`
            SELECT t.total_seats, COUNT(r.id) as registered_count
            FROM tournaments t
            LEFT JOIN registrations r ON t.id = r.tournament_id AND r.status = 'active'
            WHERE t.id = $1
            GROUP BY t.id
        `, [tournament_id]);
        
        if (seatsResult.rows[0].registered_count >= seatsResult.rows[0].total_seats) {
            return res.json({ success: false, message: 'К сожалению, все места заняты' });
        }
        
        // Записать на турнир
        await pool.query(`
            INSERT INTO registrations (user_id, tournament_id)
            VALUES ($1, $2)
        `, [userId, tournament_id]);
        
        // Добавить рейтинговые очки за регистрацию
        await pool.query(`
            INSERT INTO rating_history (user_id, points, reason)
            VALUES ($1, 10, 'Регистрация на турнир')
        `, [userId]);
        
        // Обновить общий рейтинг пользователя
        await pool.query(`
            UPDATE users 
            SET rating = rating + 10
            WHERE telegram_id = $1
        `, [userId]);
        
        res.json({ success: true, message: 'Вы успешно записались на турнир!' });
        
    } catch (error) {
        console.error('Ошибка записи на турнир:', error);
        res.status(500).json({ success: false, message: 'Ошибка сервера' });
    }
});

// API: Получить топ рейтинга
app.get('/api/rating/top', async (req, res) => {
    try {
        const result = await pool.query(`
            SELECT 
                u.telegram_id,
                COALESCE(u.username, u.first_name || ' ' || COALESCE(u.last_name, '')) as name,
                u.rating as points,
                COUNT(DISTINCT r.tournament_id) as tournaments
            FROM users u
            LEFT JOIN registrations r ON u.telegram_id = r.user_id
            GROUP BY u.telegram_id, u.username, u.first_name, u.last_name, u.rating
            ORDER BY u.rating DESC
            LIMIT 10
        `);
        
        res.json({
            players: result.rows.map(row => ({
                id: row.telegram_id,
                name: row.name,
                points: row.points,
                tournaments: parseInt(row.tournaments) || 0
            }))
        });
        
    } catch (error) {
        console.error('Ошибка получения рейтинга:', error);
        // Возвращаем тестовые данные для разработки
        res.json({
            players: [
                { id: 1, name: 'Иван Петров', points: 2540, tournaments: 15 },
                { id: 2, name: 'Алексей Смирнов', points: 2120, tournaments: 12 },
                { id: 3, name: 'Мария Иванова', points: 1980, tournaments: 10 },
                { id: 4, name: 'Дмитрий Козлов', points: 1850, tournaments: 8 },
                { id: 5, name: 'Анна Сидорова', points: 1720, tournaments: 7 }
            ]
        });
    }
});

// API: Получить данные пользователя
app.get('/api/user/:userId', async (req, res) => {
    const { userId } = req.params;
    
    try {
        const result = await pool.query(`
            SELECT 
                u.*,
                EXISTS(
                    SELECT 1 FROM registrations r
                    JOIN tournaments t ON r.tournament_id = t.id
                    WHERE r.user_id = u.telegram_id 
                    AND t.is_active = true
                    AND r.status = 'active'
                ) as registered_for_current_tournament
            FROM users u
            WHERE u.telegram_id = $1
        `, [userId]);
        
        if (result.rows.length === 0) {
            return res.json({
                exists: false,
                registeredForCurrentTournament: false
            });
        }
        
        res.json({
            exists: true,
            ...result.rows[0],
            registeredForCurrentTournament: result.rows[0].registered_for_current_tournament
        });
        
    } catch (error) {
        console.error('Ошибка получения пользователя:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Интеграция с Bothelp (вебхук)
app.post('/api/webhook/bothelp', async (req, res) => {
    const event = req.body;
    
    console.log('Webhook от Bothelp:', event);
    
    // Пример обработки событий от Bothelp
    if (event.type === 'contact_added' || event.type === 'contact_updated') {
        // Синхронизация контакта с вашей БД
        try {
            await pool.query(`
                INSERT INTO users (telegram_id, username, first_name, phone)
                VALUES ($1, $2, $3, $4)
                ON CONFLICT (telegram_id) DO UPDATE 
                SET username = EXCLUDED.username,
                    first_name = EXCLUDED.first_name,
                    phone = EXCLUDED.phone
            `, [event.contact.external_id, event.contact.username, event.contact.name, event.contact.phone]);
        } catch (error) {
            console.error('Ошибка синхронизации с Bothelp:', error);
        }
    }
    
    res.json({ received: true });
});

// Запуск сервера
app.listen(PORT, async () => {
    console.log(`Сервер запущен на порту ${PORT}`);
    await initializeDatabase();
});