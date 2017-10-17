<?php
/**
 * Класс применяется для вывода виджетов аддона.
 * 
 * Create 22.04.2015
 * Update 04.05.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.1.1
 * 
 * @package module
 * @subpackage widget
 */
class Module_Widget_Load
{
    private static $displayWidget=TRUE;

    /**
     * Выполняет загрузку аддона и вывод его в шаблон.
     * @param string $nameAddon - имя аддона
     * @param string $nameWidget - имя виджета
     * @param array $config - передаваемые параметры
     * @param string $directory - директория аддона
     * @return string | boolean
     */
    public static function Load($nameAddon=HOMEPATH,$nameWidget='index',$config=array(),$directory=NULL)
    {
        try
        {  
            //С роутера берём имя контролера.
            $class=ucfirst($directory).ucfirst($nameAddon).'WidgetController';
            //Берём название действия.
            $action=$nameWidget.'Widget';
            //Сохраняем полученные параметры.
            $args=Syo_Registry::getInstance()->get('args');
            Syo_Registry::getInstance()->set('args',$args+$config);
            //Генерируем путь к файлу контролера, проверяем на наличие.
            $tmpAddons=APPPATH.DIRSEP.$nameAddon.DIRSEP;
            $tmpDirectory=(is_null($directory))?'':$directory.DIRSEP;
            $file=SITEPATH.$tmpAddons.'controllers'.DIRSEP.$tmpDirectory.$nameAddon.'widget.php';
            //Добавляем директорию для поиска классов
            Syo_Application::addPathLib($tmpAddons.'models');
            //Проверяем на существование файла с классом контроллера
            if (is_readable($file)==false)
            {
                return FALSE;
            }
            //Проверяем не был ли класс уже проинициализирован
            if (!class_exists($class))
            {
                //Загружаем файл с классом контроллера
                include($file);
            }
            //Создаём класс контролера.
            $controller=new $class();
            $controller->setAddon($nameAddon);
            $controller->setName('widget');
            $controller->setActionEvent($nameWidget);
            //Проверяем существования события
            if (is_callable(array($controller,$action))==false) 
            {
                return FALSE;
            }

            //Создаём класс просмотра.
            $template=new Syo_Template($nameAddon);
            //Указываем где находиться файл просмотра.
            $template->setTemplate(strtolower($nameWidget));
            if (!empty($tmpDirectory)) $template->addPath(strtolower($tmpDirectory));
            $template->addPath(strtolower($nameAddon.'widget'));
            
            $controller->setView($template);
            //Инициализируем контроллер.
            $controller->init();
            //Выполняем действие
            $controller->$action();
            //В зависимоти от настроек включаем буфер вывода.
            if (self::$displayWidget) ob_start();
            self::$displayWidget=TRUE;
            //Выполняем метод перед выводом данных
            if (method_exists($controller,'beforeTemplate'))
            {
                $controller->beforeTemplate();
            }
            //Подаём данные на вывод.
            $controller->view->render();
            //Выполняем метод после вывода данных
            if (method_exists($controller,'afterTemplate'))
            {
                $controller->afterTemplate();
            }
            //В зависимоти от настроек копируем содержимое буфера в строку и очищаем его.
            return (self::$displayWidget?ob_get_clean():TRUE);
        }
        catch (Syo_Exception $e)
        {
            echo $e;
            exit();
        }
        return TRUE;
    }
    
    /**
     * Указывает, выводить widget как страницу.
     */
    public static function setWidgetOnPage()
    {
        self::$displayWidget=FALSE;
    }
}
?>