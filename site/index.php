<?php
include "header.php";
include "db.php";
?>

<main>
    <div class="slider-container">
        <div class="slider">
            <img src="images/slide1.jpg" class="slide active" alt="Собаки на площадке">
            <img src="images/slide2.jpg" class="slide" alt="Выгул собак">
            <img src="images/slide3.jpg" class="slide" alt="Площадка для собак">
        </div>
        <div class="slider-text">
            <h2>Мониторинг площадок для выгула собак в Москве</h2>
            <p>
                Наш сайт помогает владельцам собак найти подходящие площадки для выгула питомцев, 
                узнать о загруженности, чистоте и удобстве каждой площадки. 
                Вы можете оставлять отзывы о посещениях и видеть статистику активности других пользователей.
            </p>
        </div>
    </div>

    <section class="map-section" id="map-section">
        <h2>Выберите площадку на карте</h2>
        
        <div class="search-container">
            <input 
                type="text" 
                id="park-search" 
                class="search-input" 
                placeholder="Введите название площадки или адрес..."
                autocomplete="off"
            >
            <div id="search-suggestions" class="search-suggestions"></div>
        </div>

        <div class="map-container">
            <div id="map"></div>
        </div>
    </section>

    <section>
        <h2>О сайте</h2>
        <p>
            Этот сайт создан для удобства владельцев собак в Москве. Мы собираем информацию о площадках 
            для выгула собак из открытых данных города Москвы и позволяем пользователям делиться 
            своим опытом посещения этих площадок.
        </p>
        <p>
            Вы можете узнать о загруженности площадок в разное время, чистоте, наличии агрессивных собак 
            и других важных факторах, которые помогут выбрать лучшее место для прогулки с вашим питомцем.
        </p>
    </section>

    <section>
        <h2>Контакты</h2>
        <p>
            Если у вас есть вопросы или предложения, свяжитесь с нами по email: 
            <a href="mailto:info@dogparks.ru" style="color: #c9a882;">info@dogparks.ru</a>
        </p>
    </section>
</main>

<?php include "footer.php"; ?>

