<?php
/**
 * CREATE TABLE IF NOT EXISTS `menus` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(150) NOT NULL,
    `left_key` int(10) NOT NULL DEFAULT '0',
    `right_key` int(10) NOT NULL DEFAULT '0',
    `level` int(10) NOT NULL DEFAULT '0',
    `user_id` int(10) unsigned NOT NULL,
    `type` enum('shop','link','material','catalog') NOT NULL,
    `link` tinytext NOT NULL,
    `datacreate` datetime NOT NULL,
    `view` tinyint(1) unsigned NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `left_key` (`left_key`,`right_key`,`level`),
    KEY `name` (`name`)
  );
  INSERT INTO `menus` (`id`, `name`, `left_key`, `right_key`, `level`, `user_id`, `type`, `link`, `datacreate`, `view`) VALUES
    (1, 'Меню', 1, 4, 0, 0, 'catalog', '', '0000-00-00 00:00:00', 1),
    (2, 'Главное меню', 2, 3, 1, 1, 'catalog', '', '2013-11-11 06:01:00', 1);
 */
/**
 * Класс для работы с меню, через базу данных типа «дерева категорий (NESTED SETS)».
 */
class Module_Menu_Native extends Syo_Db_Tree
{
    /**
     * Список меню.
     * @var array 
     */
    protected $data;
    /**
     * Список атрибутов HTML для корня меню.
     * @var array 
     */
    protected $attributes=array();

    /**
     * Возвращает список меню из базы данных.
     * @return array
     */
    public function getMenuAll()
    {
        //Помешаем список меню в переменную для дальнейшего использования.
        $this->data=$this->getTreeAll();
        return $this->data;
    }
    
    /**
     * Возвращает список меню.
     * @return array
     */
    public function getMenuData()
    {
        return $this->data;        
    }
    
    /**
     * Переносит элементы меню.
     * @param integer $id - ид перетягиваемого элемента
     * @param integer $parentId - ид элемента, к которому переносится элемента
     * @return boolean
     */
    public function moveMenu($id,$parentId)
    {
        //Получаем иды родителей. 
        $upid=$this->getParent($id);
        $upparentId=$this->getParent($parentId);
        //Проверяем, получены ли родители.
        if (is_object($upid) && is_object($upparentId))
        {
            //Определяем способ переноса, на основе сравнения принадлежности к одному или разным родителям.
            if ($upid->id==$upparentId->id)
            {
                //Выполняем перенос в границах одного родителя.
                return $this->changePositionAll($id,$parentId,'before');                
            }
            else
            {
                //Выполняем перенос в границах разных родителей, и переносим в позицию переносимый ид –> ид елемента, к котрому переноситься
                if ($this->move($id,$upparentId->id))
                {
                    return $this->changePositionAll($id,$parentId,'before');
                }
            }
            return FALSE;
        }
        else
        {
            return FALSE;
        }
    }
}
?>