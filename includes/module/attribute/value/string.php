<?php
/**
 * Класс для работы со значениями элемента строкового типа через базу данных.
 * 
 CREATE TABLE IF NOT EXISTS `attribute_value_string` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `value` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
);
 
ALTER TABLE `attribute_value_string`
  ADD CONSTRAINT `attribute_value_string_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE; 
 */
class Module_Attribute_Value_String extends Module_Attribute_Value_Native
{            
    /**
     * Переопределяем конструктор.
     * @param array $param
     */
    public function __construct($param=NULL) 
    {
        if (is_null($param))
        {
            parent::__construct(array('name'=>'string','nameTableValue'=>'attribute_value_string'));
        }
        else
        {
            parent::__construct($param);
        }
    }
}
?>