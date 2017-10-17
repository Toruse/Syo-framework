<?php
    //Указываем шаблоны для роутера.
    $router=$application->getRouter();

    //Шаблон роутера к пользовательской части.
    $router->addRoute('default','(<controller>(/<action>(/<id>)))',array('addons'=>HOMEPATH));
?>