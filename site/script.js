console.log('=== script.js загружен ===');

// ========== СЛАЙД-ШОУ ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - страница загружена');
    
    //слайд-шоу
    const slides = document.querySelectorAll('.slide');
    if (slides.length > 0) {
        let currentSlide = 0;
        
        function showNextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }
        
        setInterval(showNextSlide, 4000);
    }
    
    //карта на главной
    if (document.getElementById('map')) {
        initMainMap();
    }
    
    //карта на странице площадки
    if (document.getElementById('park-map')) {
        initParkMap();
    }
    
    //поиск
    const searchInput = document.getElementById('park-search');
    if (searchInput) {
        initSearch();
    }
    
    //форма
    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        initReviewForm();
    }
    
    //тепловая карта и статистика
    if (document.getElementById('heatmap-container')) {
        loadHeatmap();
        
        //обработчик изменения параметра
        const paramSelect = document.getElementById('heatmap-parameter');
        if (paramSelect) {
            paramSelect.addEventListener('change', loadHeatmap);
        }
    }
});

// ========== ЗАГРУЗКА ТЕПЛОВОЙ КАРТЫ ==========
function loadHeatmap() {
    const parkId = window.parkData?.id;
    if (!parkId) {
        console.error('Нет данных о парке');
        return;
    }
    
    const container = document.getElementById('heatmap-container');
    if (!container) return;
    
    const parameter = document.getElementById('heatmap-parameter')?.value || 'activity_level';
    
    container.innerHTML = '<p class="msg msg--p20">Загрузка данных...</p>';
    
    fetch(`api/get_heatmap_stats.php?park_id=${parkId}&parameter=${parameter}`)
        .then(response => {
            if (!response.ok) throw new Error('Ошибка сети');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                container.innerHTML = `<div class="error-message">${data.error}</div>`;
                return;
            }
            
            //отображаем таблицу
            renderHeatmap(container, data.heatmap, parameter, data);
        })
        .catch(error => {
            console.error('Ошибка загрузки тепловой карты:', error);
            container.innerHTML = '<div class="error-message">Ошибка загрузки данных</div>';
        });
}

// ========== ОТОБРАЖЕНИЕ ТЕПЛОВОЙ КАРТЫ ==========
function renderHeatmap(container, heatmapData, parameter, data) {
    const days = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
    const times = ['6-8','8-10','10-12','12-14','14-16','16-18','18-20','20-22'];
    
    let html = `
        <div class="heatmap-wrapper">
            <table class="heatmap-table">
                <thead>
                    <tr>
                        <th>День / Время</th>
    `;
    
    //заголовки времени
    times.forEach(time => {
        html += `<th>${time}</th>`;
    });
    
    html += `</tr></thead><tbody>`;
    
    //данные по дням
    let hasData = false;
    days.forEach(day => {
        html += `<tr><td class="heatmap-day">${getDayName(day)}</td>`;
        
        times.forEach(time => {
            const value = heatmapData?.[day]?.[time];
            
            if (value === null || value === undefined) {
                html += `<td class="heat-0">-</td>`;
            } else {
                hasData = true;
                
                //определяем класс цвета в зависимости от параметра
                let heatClass = getHeatClass(value, parameter);
                
                //текст для ячейки
                let cellText = value;
                let cellTitle = `${getDayName(day)} ${time}: ${value}`;
                
                if (parameter === 'aggressive_dogs' || parameter === 'children_present') {
                    cellText = value == 1 ? 'Да' : 'Нет';
                    cellTitle = `${getDayName(day)} ${time}: ${value == 1 ? 'Есть' : 'Нет'}`;
                }
                
                html += `<td class="${heatClass}" title="${cellTitle}">${cellText}</td>`;
            }
        });
        
        html += `</tr>`;
    });
    
    html += `</tbody></table></div>`;
    
    //добавляем легенду
    html += createLegend(parameter);
    
    //добавляем общий счет отзывов
    if (data.total_reviews) {
        html += `<div class="review-count"><p>Всего отзывов: <strong>${data.total_reviews}</strong></p></div>`;
    }
    
    if (!hasData) {
        html = '<p class="msg msg--p40">Нет данных для отображения. Будьте первым, кто оставит отзыв!</p>';
    }
    
    container.innerHTML = html;
}

// ========== ОПРЕДЕЛЕНИЕ ЦВЕТА ДЛЯ ЯЧЕЙКИ ==========
function getHeatClass(value, parameter) {
    //для наличия детей и агрессивных собак
    if (parameter === 'aggressive_dogs' || parameter === 'children_present') {
        return value == 1 ? 'heat-yes' : 'heat-no';
    }
    
    //для активности, количества собак, чистоты, удобства
    if (parameter === 'activity_level') {
        if (value === 'спокойно') return 'heat-1';
        else if (value === 'средне') return 'heat-2';
        else if (value === 'активно') return 'heat-3';
    }
    
    if (parameter === 'dogs_count') {
        if (value === '0-2') return 'heat-1';
        else if (value === '3-5') return 'heat-2';
        else if (value === '6+') return 'heat-3';
    }
    
    if (parameter === 'cleanliness' || parameter === 'convenience') {
        if (value === 'отлично') return 'heat-1';
        else if (value === 'хорошо') return 'heat-2';
        else if (value === 'удовлетворительно') return 'heat-3';
        else if (value === 'плохо') return 'heat-4';
    }
    
    return 'heat-0';
}

// ========== ЛЕГЕНДА ==========
function createLegend(parameter) {
    let legend = '<div class="heatmap-legend">';
    legend += '<span class="legend-text"><strong>Легенда:</strong></span>';
    
    if (parameter === 'aggressive_dogs' || parameter === 'children_present') {
        legend += '<span class="legend-item"><span class="legend-color legend-pink"></span>Да</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-green"></span>Нет</span>';
    } else if (parameter === 'dogs_count') {
        legend += '<span class="legend-item"><span class="legend-color legend-green"></span>0-2</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-yellow"></span>3-5</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-orange"></span>6+</span>';
    } else if (parameter === 'activity_level') {
        legend += '<span class="legend-item"><span class="legend-color legend-green"></span>Спокойно</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-yellow"></span>Средне</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-orange"></span>Активно</span>';
    } else if (parameter === 'cleanliness' || parameter === 'convenience') {
        legend += '<span class="legend-item"><span class="legend-color legend-green"></span>Отлично</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-yellow"></span>Хорошо</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-orange"></span>Удовл.</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-pink"></span>Плохо</span>';
    } else {
        legend += '<span class="legend-item"><span class="legend-color legend-green"></span>Мало</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-yellow"></span>Средне</span>';
        legend += '<span class="legend-item"><span class="legend-color legend-orange"></span>Много</span>';
    }
    
    legend += '</div>';
    return legend;
}

// ========== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ==========
function getDayName(shortDay) {
    const daysMap = {
        'Пн': 'Понедельник',
        'Вт': 'Вторник',
        'Ср': 'Среда',
        'Чт': 'Четверг',
        'Пт': 'Пятница',
        'Сб': 'Суббота',
        'Вс': 'Воскресенье'
    };
    return daysMap[shortDay] || shortDay;
}

function getParameterName(param) {
    const names = {
        'activity_level': 'Уровень активности',
        'dogs_count': 'Количество собак',
        'aggressive_dogs': 'Агрессивные собаки',
        'children_present': 'Наличие детей',
        'cleanliness': 'Чистота площадки',
        'convenience': 'Удобство площадки'
    };
    return names[param] || param;
}

// ========== КАРТА НА ГЛАВНОЙ СТРАНИЦЕ ==========
function initMainMap() {
    ymaps.ready(function() {
        const map = new ymaps.Map('map', {
            center: [55.7558, 37.6173],
            zoom: 11
        });
        
        fetch('api/get_parks.php')
            .then(response => response.json())
            .then(parks => {
                parks.forEach(function(park) {
                    const placemark = new ymaps.Placemark(
                        [park.lat, park.lon],
                        {
                            balloonContentHeader: 'Площадка ' + park.district,
                            balloonContentBody: park.location,
                            balloonContentFooter: '<a href="park.php?id=' + park.id + '">Подробнее</a>'
                        },
                        { preset: 'islands#violetDotIcon' }
                    );
                    
                    placemark.events.add('click', function() {
                        placemark.balloon.open();
                    });

                    map.geoObjects.add(placemark);
                });
                
                window.parksData = parks;
            })
            .catch(error => console.error('Ошибка загрузки площадок:', error));
    });
}

// ========== КАРТА НА СТРАНИЦЕ ПЛОЩАДКИ ==========
function initParkMap() {
    if (!window.parkData || !window.parkData.lat || !window.parkData.lon) {
        document.getElementById('park-map').innerHTML = 
            '<div class="alert-msg">Координаты площадки не указаны</div>';
        return;
    }
    
    if (typeof ymaps === 'undefined') {
        document.getElementById('park-map').innerHTML = 
            '<div class="alert-msg">Карты не загружены</div>';
        return;
    }
    
    ymaps.ready(function() {
        try {
            const map = new ymaps.Map('park-map', {
                center: [window.parkData.lat, window.parkData.lon],
                zoom: 16,
                controls: ['zoomControl', 'fullscreenControl']
            });
            
            const placemark = new ymaps.Placemark(
                [window.parkData.lat, window.parkData.lon],
                {
                    balloonContentHeader: window.parkData.name || 'Площадка',
                    balloonContentBody: 'Площадка для выгула собак'
                },
                {
                    preset: 'islands#violetDotIcon',
                    draggable: false
                }
            );
            
            placemark.events.add('click', function() {
                placemark.balloon.open();
            });

            map.geoObjects.add(placemark);
            
        } catch (error) {
            document.getElementById('park-map').innerHTML = 
                '<div class="alert-msg">Ошибка создания карты</div>';
        }
    });
}

// ========== ПОИСК С АВТОДОПОЛНЕНИЕМ ==========
function initSearch() {
    const searchInput = document.getElementById('park-search');
    const suggestionsDiv = document.getElementById('search-suggestions');
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        suggestionsDiv.innerHTML = '';
        
        if (query.length < 2) {
            suggestionsDiv.classList.remove('active');
            return;
        }
        
        if (!window.parksData) {
            fetch('api/get_parks.php')
                .then(response => response.json())
                .then(parks => {
                    window.parksData = parks;
                    showSuggestions(query, parks);
                })
                .catch(error => console.error('Ошибка загрузки данных:', error));
        } else {
            showSuggestions(query, window.parksData);
        }
    });
    
    function showSuggestions(query, parks) {
        const filtered = parks.filter(park => 
            park.location.toLowerCase().includes(query) ||
            park.district.toLowerCase().includes(query)
        ).slice(0, 10);
        
        if (filtered.length === 0) {
            suggestionsDiv.classList.remove('active');
            return;
        }
        
        filtered.forEach(park => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = park.location;
            
            item.addEventListener('click', function() {
                window.location.href = 'park.php?id=' + park.id;
            });
            
            suggestionsDiv.appendChild(item);
        });
        
        suggestionsDiv.classList.add('active');
    }
    
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.remove('active');
        }
    });
}

// ========== ФОРМА ОТЗЫВА ==========
function initReviewForm() {
    const form = document.getElementById('review-form');
    const messageDiv = document.getElementById('review-message');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('api/submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const parkId = form.querySelector('input[name="park_id"]').value;

                messageDiv.innerHTML = `
                    <div class="success-message">
                        Спасибо за отзыв!
                    </div>
                    <a href="park.php?id=${parkId}" class="btn-submit">
                        Вернуться на площадку
                    </a>
                `;

                form.reset();
                
                if (typeof loadHeatmap === 'function') {
                    setTimeout(loadHeatmap, 1000);
                }
            } else {
                messageDiv.innerHTML = '<div class="error-message">' + data.message + '</div>';
            }
        })
        .catch(error => {
            messageDiv.innerHTML = '<div class="error-message">Ошибка при отправке отзыва</div>';
            console.error('Ошибка:', error);
        });
    });
}


window.loadHeatmap = loadHeatmap;