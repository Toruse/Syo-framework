<?php
/**
 * Класс хелпер, выполняет операции над строками.
 * 
 * Create 03.09.2014
 * Update 09.09.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.4.1
 *  
 * @package module
 * @subpackage functions
 */
class Module_Functions_String
{
    /**
     * Хранит асоциации для транслита RU->EN. 
     * @var array 
     */
    private static $converter=array(
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e',
            'ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m',
            'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
            'ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ь'=>'',
            'ы'=>'y','ъ'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
            'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E',
            'Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M',
            'Н'=>'N','О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U',
            'Ф'=>'F','Х'=>'H','Ц'=>'C','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch','Ь'=>'',
            'Ы'=>'Y','Ъ'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya'
        );

    /**
     * Выполняет транслит с русских букв на английские.
     * @param string $str - строка с русскими символами
     * @return string - строка с английским символами
     */
    public static function seoStrToUrl($str)
    {   
        $str=trim($str);
        $str=strtr($str,self::$converter);
        $str=strtolower($str);
        $str=preg_replace('~[^-a-z0-9_]+~u','-',$str);
        return $str;
    }
    
    /**
     * Выполняет декодирование спец-символов HTML.
     * @param string $str - закодированная строка.
     * @return string - раскодированная строка.
     */
    public static function syoHtmlspecialcharsDecode($str)
    {
        return htmlspecialchars_decode($str,ENT_QUOTES);
    }
    
    /**
     * Выполняет склонение чисел.
     * @param number $number - значение количества
     * @param array $titles - список окончаний (array('','а','ов'))
     * @return string - окончание слова
     */
    public static function numberEnd($number,$titles)
    {
        $cases=array(2,0,1,1,1,2);
        return $titles[($number%100>4 && $number%100<20)?2:$cases[min($number%10,5)]];
    }
    
    /**
     * Выполняет транслит с русского на английский.
     * @param string $str - русский текст
     * @return string
     */
    public static function translitRUtoEN($str)
    {
        $str=strtr($str,self::$converter);
        return $str;        
    }
}
?>