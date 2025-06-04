document.addEventListener('DOMContentLoaded', function() {
    var modalHeaderTextElement = document.getElementById('modal-top_geo');
    var modalHeaderText = modalHeaderTextElement ? modalHeaderTextElement.getAttribute('data-header') : 'Выберите Ваш город';
    
    // Используйте текст в HTML модального окна
    var modalHTML = `
        <div id="selectcitylistModal" class="modal-selectcitylist">
            <div class="modal-content">
                <div>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-header">
                    <h2>${modalHeaderText}</h2>
                    <input type="text" id="citySearch" placeholder="Найти...">
                </div>
                <div class="modal-body">
                    <div id="modal-text-selectcitylist" class="city-list">
                        <!-- Сюда будет загружен список городов -->
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    
    
    var link = document.getElementById('link_my_selectcitylist');
    var modal = document.getElementById('selectcitylistModal');
    var modalText = document.getElementById('modal-text-selectcitylist');
    var citySearch = document.getElementById('citySearch');


    
    function openModal() {
        
        // Очистка поля поиска при каждом открытии модального окна
        citySearch.value = '';
        
        // Получение текущего URL
        var Url = window.location.href.replace(/^https?:\/\//, ''); // Убираем 'http://' или 'https://'
        var fullUrl = Url.replace(document.getElementById('link_my_selectcitylist').getAttribute('data-url'), '');
            fullUrl = fullUrl.replace('//', '/');
            
        var encodedFullUrl = encodeURIComponent(fullUrl); // Кодируем для безопасной передачи в URL
        
        var path = window.location.pathname.replace(document.getElementById('link_my_selectcitylist').getAttribute('data-url'), '');
            path = path.replace('//', '/');
            
        var encodedPath = encodeURIComponent(path); // Кодируем путь
    
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/index.php?option=com_ajax&module=selectcitylist&method=getContent&format=json&fullUrl=' + fullUrl + '&path=' + path, true);
        xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    modalText.innerHTML = response.data;
                    modal.style.display = 'flex';
                    //console.log(response.data);
                    } catch (e) {
                        console.error('Ошибка при разборе ответа:', e);
                        }
                    } else {
                        console.error('Ошибка запроса: Статус', xhr.status);
                    }
            }
        };
        xhr.send();
        
        
        // Обработчик события клика на динамически добавленные ссылки
        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('city-one')) {
                var cityId = event.target.getAttribute('data-id');
                var domain = window.location.hostname;
    
                if (domain.indexOf('.') < 0 || domain === 'localhost') {
                    domain = '';
                } else {
                    var domainParts = domain.split('.').slice(-2);
                    domain = '.' + domainParts.join('.');
                }
    
                // Установка куки
                document.cookie = "city_id=" + cityId + ";path=/;domain=" + domain + ";max-age=" + (30 * 24 * 60 * 60) + ";Secure";
            }
        });
        
    }
    
    
    
    if (link && modal && modalText) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
    } else {
        console.error('Один из элементов не найден');
    }
    
    // Закрытие модального окна при клике на кнопку закрытия
    var closeButton = document.querySelector('.modal-selectcitylist .close');
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Закрытие модального окна при клике вне его содержимого
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    
    //поиск города
    var citySearch = document.getElementById('citySearch');
    var cityGroups = document.getElementsByClassName('city-group'); 

    citySearch.addEventListener('input', function() {
        var searchValue = citySearch.value.toLowerCase();
        
        // Проход по всем группам городов
        Array.from(cityGroups).forEach(function(group) {
            var cities = group.getElementsByTagName('li');
            var groupFound = false; // Флаг нахождения совпадения в группе
            
            // Проход по всем городам внутри группы
            Array.from(cities).forEach(function(city) {
                var cityName = city.textContent || city.innerText;
                if (cityName.toLowerCase().indexOf(searchValue) > -1) {
                    city.style.display = ""; // Показать город
                    groupFound = true; // Совпадение найдено
                } else {
                    city.style.display = "none"; // Скрыть город
                }
            });

            // Скрываем или показываем группу в зависимости от наличия совпадений
            group.style.display = groupFound ? "" : "none";
        });
    });
    
    
    
    
    
});




