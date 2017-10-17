<?php
/**
 * Класс для загрузки и работы с конфигурационными данными.
 */
class Syo_Config
{
    /**
     * Метод загрузки конфигурационных данных.
     * @param string $name - имя файла конфигурации
     * @param string $directory - путь к конфигурационному файлу
     * @return array (or boolean)
     */
    public static function Load($name,$directory=NULL)
    {
        try 
        {
            //Генерируем путь к файлу.
            if (is_null($directory))
            {
                //Файл находиться в папке с приложением.
                $path=SITEPATH.'configs'.DIRSEP.$name.'.php';
            }
            else
            {
                //Файл находиться в другой папке.
                $path=SITEPATH.$directory.DIRSEP.$name.'.php';
            }
            //Проверяем на существование файла.
            if (is_readable($path)==false)
            {
                throw new Syo_Exception("Not Found \"$name\" file config!");
            }
            else
            {
                //Загружаем конфигурационный файл.
                return include($path);
            }
        }
        catch (Syo_Exception $e)
        {
            echo $e;
            exit();
        }
        return FALSE;
    }
}
?>