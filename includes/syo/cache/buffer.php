<?php
/**
* Класс для работы с кэшем буфера.
*/
class Syo_Cache_Buffer extends Syo_Cache_File
{
    /**
     * Включаем буферизацию вывода.
     */
    public function start()
    {
        ob_start();
    }
    
    /**
     * Отключает буферизацию вывода, и сохраняет данные в кэш.
     * @param string $key  - ключ значения в кэше
     * @param integer $expiration - время жизни
     */
    public function end($key,$expiration=0)
    {
        //Возвращаем содержание буфера вывода
        $data=ob_get_contents();
        if ($data!=FALSE)
        {
            //Сохраняем данные в кэш
            parent::save($key,ob_get_contents(),$expiration);
            //Очищаем буфер, и отключаем его
            ob_end_flush();
            return TRUE;
        }
        return FALSE;
    }
    
    /**
    * Сохраняет данные в кэш.
    * @param string $key
    * @param variant $data
    * @param integer $expiration
    * @return object
    */
    public function save($key,$data,$expiration=0)
    {
        //Заглушка
        return NULL;
    }
}