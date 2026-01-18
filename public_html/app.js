// Инициализация Telegram Web App
const tg = window.Telegram.WebApp;
let userData = null;

// Инициализация приложения
function initApp() {
    // Развернуть приложение на весь экран
    tg.expand();
    
    // Получить данные пользователя
    userData = tg.initDataUnsafe?.user;
    
    if (userData) {
        updateUserBadge();
        loadUserData();
    }
    
    // Установить тему
    setTheme();
    
    // Загрузить данные
    loadTournamentData();
    loadRatingData();
    
    // Настроить обработчики событий
    setupEventListeners();
    
    // Инициализировать FAQ
    initFAQ();
}

// Обновить бейдж пользователя
function updateUserBadge() {
    const userBadge = document.getElementById('userBadge');
    if (userData?.first_name) {
        const initials = userData.first_name.charAt(0).toUpperCase();
        userBadge.innerHTML = `<span style="font-weight: 700;">${initials}</span>`;
    }
}

// Установить тему
function setTheme() {
    const theme = tg.colorScheme;
    if (theme === 'dark') {
        document.body.style.background = 'linear-gradient(135deg, #0f172a 0%, #1a202c 100%)';
    } else {
        document.body.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
        document.body.style.color = '#1e293b';
    }
}

// Загрузить данные турнира
async function loadTournamentData() {
    try {
        // Здесь будет запрос к вашему бэкенду
        const response = await fetch('http://localhost:3000/api/tournament/current');
        const data = await response.json();
        
        // Обновить интерфейс
        document.getElementById('tournamentTitle').textContent = data.title;
        document.getElementById('tournamentDate').textContent = `${data.date} / ${data.time}`;
        document.getElementById('tournamentSeats').textContent = data.totalSeats;
        document.getElementById('registeredCount').textContent = data.registeredCount;
        document.getElementById('totalSeats').textContent = data.totalSeats;
        
        // Обновить прогресс
        const progress = (data.registeredCount / data.totalSeats) * 100;
        document.getElementById('registrationProgress').style.width = `${progress}%`;
        
        // Обновить модальное окно
        document.getElementById('modalTournamentName').textContent = data.title;
        document.getElementById('modalFreeSeats').textContent = data.totalSeats - data.registeredCount;
        
    } catch (error) {
        console.error('Ошибка загрузки турнира:', error);
    }
}

// Загрузить рейтинг
async function loadRatingData() {
    try {
        const response = await fetch('http://localhost:3000/api/rating/top');
        const data = await response.json();
        
        const ratingList = document.getElementById('ratingList');
        ratingList.innerHTML = '';
        
        data.players.forEach((player, index) => {
            const ratingItem = document.createElement('div');
            ratingItem.className = 'rating-item';
            ratingItem.innerHTML = `
                <div class="rank">${index + 1}</div>
                <div class="player-info">
                    <div class="player-name">${player.name}</div>
                    <div class="player-stats">
                        <span class="points">${player.points.toLocaleString()} очков</span>
                        <span class="tournaments">${player.tournaments} турниров</span>
                    </div>
                </div>
                <div class="medal">${getMedalEmoji(index + 1)}</div>
            `;
            ratingList.appendChild(ratingItem);
        });
        
    } catch (error) {
        console.error('Ошибка загрузки рейтинга:', error);
    }
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

// Загрузить данные пользователя
async function loadUserData() {
    if (!userData?.id) return;
    
    try {
        const response = await fetch(`http://localhost:3000/api/user/${userData.id}`);
        const data = await response.json();
        
        // Проверить, записан ли пользователь на текущий турнир
        if (data.registeredForCurrentTournament) {
            updateRegisterButton(true);
        }
        
    } catch (error) {
        console.error('Ошибка загрузки данных пользователя:', error);
    }
}

// Обновить кнопку записи
function updateRegisterButton(isRegistered) {
    const registerBtn = document.getElementById('registerBtn');
    if (isRegistered) {
        registerBtn.innerHTML = '<i class="fas fa-check"></i> ВЫ ЗАПИСАНЫ';
        registerBtn.style.background = 'linear-gradient(90deg, #10b981, #34d399)';
        registerBtn.disabled = true;
    } else {
        registerBtn.innerHTML = '<i class="fas fa-user-plus"></i> ЗАПИСАТЬСЯ';
        registerBtn.style.background = 'linear-gradient(90deg, #dc2626, #ef4444)';
        registerBtn.disabled = false;
    }
}

// Записаться на турнир
async function registerForTournament() {
    if (!userData?.id) {
        alert('Пожалуйста, авторизуйтесь в Telegram');
        return;
    }
    
    try {
        const response = await fetch('http://localhost:3000/api/tournament/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                userId: userData.id,
                username: userData.username || `${userData.first_name} ${userData.last_name || ''}`,
                tournamentId: 'current'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Обновить интерфейс
            updateRegisterButton(true);
            loadTournamentData(); // Обновить счетчик
            
            // Показать уведомление
            tg.showAlert('Вы успешно записались на турнир!');
            
            // Закрыть модальное окно
            closeModal('registerModal');
            
            // Отправить данные в бот (для Bothelp)
            tg.sendData(JSON.stringify({
                action: 'tournament_registered',
                userId: userData.id,
                tournament: 'RED LUXE TOURNAMENT'
            }));
            
        } else {
            tg.showAlert(result.message || 'Ошибка при записи');
        }
        
    } catch (error) {
        console.error('Ошибка записи:', error);
        tg.showAlert('Произошла ошибка при записи на турнир');
    }
}

// Настроить обработчики событий
function setupEventListeners() {
    // Кнопка записи
    document.getElementById('registerBtn').addEventListener('click', () => {
        openModal('registerModal');
    });
    
    // Подтверждение записи
    document.getElementById('confirmRegisterBtn').addEventListener('click', registerForTournament);
    
    // Кнопка поддержки
    document.getElementById('supportBtn').addEventListener('click', () => {
        tg.openTelegramLink('https://t.me/lebroomsupport');
    });
    
    // Кнопка информации о клубе
    document.getElementById('clubInfoBtn').addEventListener('click', () => {
        openModal('clubInfoModal');
    });
    
    // Кнопка Q&A
    document.getElementById('qaBtn').addEventListener('click', () => {
        openModal('qaModal');
    });
    
    // Кнопка профиля
    document.getElementById('myProfileBtn').addEventListener('click', () => {
        // Переключить на вкладку профиля
        document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
        document.querySelector('.nav-item[data-page="profile"]').classList.add('active');
        
        // Здесь можно загрузить страницу профиля
        tg.showAlert('Раздел профиля в разработке');
    });
    
    // Кнопка просмотра всего рейтинга
    document.getElementById('viewAllRating').addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
        document.querySelector('.nav-item[data-page="rating"]').classList.add('active');
        
        // Здесь можно загрузить полный рейтинг
        tg.showAlert('Полный рейтинг в разработке');
    });
    
    // Кнопка подробнее о турнире
    document.getElementById('detailsBtn').addEventListener('click', () => {
        tg.showAlert('Подробная информация о турнире в разработке');
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
}

// Инициализация FAQ
function initFAQ() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const faqItem = question.parentElement;
            faqItem.classList.toggle('active');
            
            const icon = question.querySelector('i');
            if (faqItem.classList.contains('active')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
}

// Управление модальными окнами
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
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
            tg.showAlert('Личный кабинет в разработке');
            break;
    }
}

// Инициализировать приложение при загрузке
document.addEventListener('DOMContentLoaded', initApp);