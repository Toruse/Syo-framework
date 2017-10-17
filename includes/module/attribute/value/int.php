<?php
/**
 * Класс для работы со значениями элемента целого типа через базу данных.
 * 
 CREATE TABLE IF NOT EXISTS `attribute_value_int` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
);

 ALTER TABLE `attribute_value_int`
  ADD CONSTRAINT `attribute_value_int_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
 */
class Module_Attribute_Value_Int extends Module_Attribute_Value_Native
{
    /**
     * Переопределяем конструктор.
     * @param array $param
     */
    public function __construct($param=NULL) 
    {
        if (is_null($param))
        {
            parent::__construct(array('name'=>'int','nameTableValue'=>'attribute_value_int'));
        }
        else
        {
            parent::__construct($param);
        }
    }
}
?>