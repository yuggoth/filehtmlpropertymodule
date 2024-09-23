<?php
// Автозагрузка классов и регистрация обработчиков событий
\Bitrix\Main\Loader::registerAutoLoadClasses('filehtmlpropertymodule', array(
    'FileHtmlProperty\\FileHtmlProperty' => 'lib/FileHtmlProperty.php',
));

// Регистрация обработчиков событий
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    array('FileHtmlProperty\\FileHtmlProperty', 'GetIBlockPropertyDescription')
);