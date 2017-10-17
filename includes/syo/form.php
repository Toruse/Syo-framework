<?php
/**
 * Класс предназначен для обработки данных формы полученных от пользователя.
 * 
 * Create 28.08.2014
 * Update 28.08.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 */
class Syo_Form
{
    /**
     * Хранит полученные данные
     * @var stdClass 
     */
    protected $list;
    
    /**
     * Переменная содержит список возникших ошибок.
     * @var array 
     */
    private $errorList=NULL;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        //Инициализируем класс для хранения полученных данных
        $this->list=new stdClass();
    }
    
    /**
     * Возвращает данные формы
     * @return stdClass
     */
    public function getList()
    {
        return $this->list;
    }
    
    /**
     * Получает данные с формы и сохраняет в list.
     * @param variant $list
     */
    public function setList($list)
    {
        //Получен массив
        if (is_array($list))
        {
            foreach ($list as $key=>$value)
            {
                $this->list->$key=$value;
            }
            return TRUE;
        }
        //Получен объект
        elseif (is_object($list))
        {
            $this->list=$list;
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Возвращает данные формы
     * @return stdClass
     */
    public function getError()
    {
        return $this->errorList;
    }
    
    /**
     * Перезагружаем метод установки переменных
     * @param string $key
     * @param variant $var
     * @return boolean
     */
    function __set($key,$var)
    {
        $this->list->$key=$var;
        return true;
    }
    
    /**
     * Перезагружаем метод возврата значения переменной
     * @param string $key
     * @return variant
     */
    function __get($key)
    {
        if (isset($this->list->$key)==false)
        {
                return null;
        }
        return $this->list->$key;
    }
    
    /**
     * Генерирует новый token
     */
    public function generateToken()
    {
        $this->list->token=Syo_Token::getInstance()->getToken();
    }
    
    /**
     * Очищает данные формы.
     */
    public function cleanerData()
    {
        $this->list=new stdClass();
    }
    
    /**
     * Проверяет правильность ведённых данных в форму.
     * @return boolean
     */
    public function isValidate()
    {

    }
    
    /**
     * Проверяет форму на повторный запрос.
     * @return boolean
     */
    public function verifyToken()
    {
        if (isset($this->list->token))
            return Syo_Token::getInstance()->isVerify($this->list->token);
        else
            return FALSE;
    }
    
    /**
     * Убираем из полученных данных теги и спецсимволы или экранируем их.
     */
    public function isFilter()
    {
        $filters=new Syo_Filters();
        $filters->addFilter(new Syo_Filter_StripTags())->addFilter(new Syo_Filter_Htmlspecialchars());
        foreach ($this->getList() as $key=>$value)
        {
            $this->list->$key=$filters->isFilter($this->list->$key);
        }
    }
    
    /**
     * Устанавливает значения формы по умолчанию.
     */
    public function setDefault()
    {
        foreach ($this->getList() as $key=>$value)
        {
            $this->list->$key='';
        }
    }
}
?>