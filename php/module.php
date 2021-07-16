<?php

    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/satellites/order/php/model/*.php') AS $models) { require($models); }
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/satellites/order/php/view/*.php') AS $views) { require($views); }
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/satellites/order/php/controller/*.php') AS $controllers) { require($controllers); }

?>