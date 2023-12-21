<?php

use mmaurice\modx\Core;
use mmaurice\widgets\Widget;

/**
 * Компонент для генерации виджетов из чанков и сниппетов.
 * 
 * Попытка творческого решения проблемы, когда необходимо экспортировать содержание какого-то виджета из чанка или
 *  сниппета в виже готового кода для внедрения на сторонние проекты. Если не настраивать черный и белый списки, то
 *  экспортировать можно любой чанк или сниппет.
 */

// Запрещаем запуск из CLI
if ((php_sapi_name() === 'cli')) {
    echo 'Access denied';
} else {
    // Подключаем вендоры
    require_once realpath(dirname(__FILE__)) . '/vendor/autoload.php';

    // Загружаем конфигурацию из файла настроек
    $config = include_once realpath(dirname(__FILE__)) . '/config.php';

    // Инициируем нужные компоненты
    // Объявляем всё необходимое тут, что потребуется для ядра modx, чтобы работать в CLI
    $modx = (new Core([
        'docRoot' => realpath(dirname(__FILE__) . '/../'),
    ]))->modx();

    // Подключаем класс виджетов
    $widget = new Widget($config);
    $widget->execute();
}

exit;
