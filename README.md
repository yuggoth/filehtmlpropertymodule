# filehtmlpropertymodule

Кастомное свойство для элементов инфоблоков "Файл + визуальный редактор + строка".

## Установка

1. Распаковать в папку /local/modules/

2. Установить модуль в админке: Настройки -> Настройки продукта -> Модули -> Управление модулями

<img src="https://i.ibb.co/yQmth4C/2024-09-23-050343.png" alt="админка">   

3. Подключить в /local/php_interface/init.php

```
CModule::IncludeModule('filehtmlpropertymodule');
```
