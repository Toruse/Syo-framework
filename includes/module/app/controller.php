<?php
/**
 * Общий класс контроллера для пользовательской части сайта.
 * 
 * Create 21.04.2014
 * Update 26.08.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.1.0
 * 
 * @package module
 * @subpackage app
 */
class Module_App_Controller extends Module_App_Native
{
    /**
     * Инициализирует контроллер.
     */
    public function init() 
    {
    }

    /**
     * Метод выполняется после вывода шаблона.
     */
    public function afterTemplate()
    {
        
    }

    /**
     * Метод выполняется перед выводом шаблона.
     */
    public function beforeTemplate()
    {
        parent::beforeTemplate();
        //Подгружаем основной шаблон сайта
        $this->setLayout('body','index','layout');
    }
}
?>