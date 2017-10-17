<?php
/**
 * Класс для добавления Hooks с установленных плагинов.
 */
class APlugins_Autoload_Hooks
{
    public static function ApplicationInit()
    {
        echo 'init Plugins';
    }
    
    public static function PluginsInit()
    {
        echo 'APlugins_Autoload_Hooks';
    }
}
?>

