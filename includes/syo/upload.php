<?php
/**
 * Класс выполняет загрузку файлов на сервер.
 * 
 * Create 03.09.2014
 * Update 27.01.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.1
 * 
 * @package syo
 */
class Syo_Upload
{
    /**
     * Права доступа к каталогу.
     * @var integer 
     */
    protected $permissions=0755;
    
    /**
     * Каталог назначения
     * @var string 
     */
    protected $path='';
    
    /**
     * Имя input file.
     * @var string 
     */
    protected $fileName='';
    
    /**
     * Содержит информацию о загружаемых файлах.
     * @var array 
     */
    protected $fileInfo=NULL;

    /**
     * Список расширений файла, которые нужно переименовать в txt.
     * @var array 
     */
    protected $ext_rename=array(
                        "#\.php#is",
                        "#\.phtml#is",
                        "#\.php3#is",
                        "#\.html#is",
                        "#\.htm#is",
                        "#\.hta#is",
                        "#\.pl#is",
                        "#\.xml#is",
                        "#\.inc#is",
                        "#\.shtml#is",
                        "#\.xht#is",
                        "#\.xhtml#is"
                    );
    
    /**
     * Хранит список валидаторов.
     * @var Syo_Validates
     */
    protected $validates=NULL;
    
    /**
     * Список с произошедшими при проверке.
     * @var array 
     */
    protected $error=array();
    
    /**
     * Параметр указывает, нужно ли создать уникальное имя файла.
     * Устанавливается если нужно перезаписать файл.
     * @var boolean 
     */
    protected $replaceFile=FALSE;

    /**
     * Конструктор.
     * @param string $name - имя input file
     */
    public function __construct($name)
    {
        $this->fileName=$name;
        $this->setFileInfo();
        $this->validates=new Syo_Validates();
    }
    
    /**
     * Быстрое создание объекта.
     * @param string $name - имя input file
     * @return \Syo_Upload
     */
    public static function factory($name)
    {
        return new Syo_Upload($name);
    }
    
    /**
     * Выполняет загрузку файла.
     * @return boolean
     */
    public function isLoadFiles()
    {
        if (!$this->isVerify($this->fileInfo))
        {
            $countFile=$this->getCountFile();
            if ($countFile>0)
                return $this->loadFilesAll($countFile);
            else 
                return $this->loadFileOne();
        }
        else
            return FALSE;
    }
    
    /**
     * Выполняет загрузку нескольких файлов.
     * @param integer $countFile - количество загружаемых файлов
     * @return boolean
     */
    protected function loadFilesAll($countFile)
    {
        //Указываем абсолютный путь
        $dir=$this->getRootDir().$this->getPath().DIRSEP;
        //Если нет указанного каталога, создаём его
        if (!$this->createDir($dir)) return FALSE;
        //Перебираем загруженные файлы
        $listLoad=array();
        for ($i=0;$i<$countFile;$i++)
        {
            //Проверяем имя на пустоту
            if (empty($this->fileInfo['name'][$i]) || empty($this->fileInfo['tmp_name'][$i])) return FALSE;
            //Выполняем транслит с кириллицы на латиницу
            $filename=Module_Functions_String::translitRUtoEN($this->fileInfo['name'][$i]);
            //Проверяем расширение файла на наличие скрипта
            $part_parts=pathinfo($this->fileInfo['name'][$i]);
            $ext='.'.$part_parts['extension'];
            $file=basename($filename,$ext);
            $addext=$ext;
            foreach ($this->ext_rename as $exten)
            {
                if (preg_match($exten,$ext)) $addext=".txt";
            }
            //Генерируем имя файла
            $file.=$addext;
            $file=$this->fileExists($dir,$file);
            $path=str_replace(DIRSEP.DIRSEP,DIRSEP,$dir.$file);
            //Выполняем загрузку файла
            if (move_uploaded_file($this->fileInfo['tmp_name'][$i],$path))
            {
                @chmod($path,$this->permissions);
                //Добавляем имя файла в список загруженных файлов
                $listLoad[]=$file;
            }
            else
                return FALSE;
        }
        return $listLoad;
    }
    
    /**
     * Выполняет загрузку одного файла.
     * @return boolean
     */
    protected function loadFileOne()
    {
        //Проверяем имя на пустоту
        if (empty($this->fileInfo['name']) || empty($this->fileInfo['tmp_name'])) return FALSE;
        //Выполняем транслит с кириллицы на латиницу
        $filename=Module_Functions_String::translitRUtoEN($this->fileInfo['name']);
        //Проверяем расширение файла на наличие скрипта
        $part_parts=pathinfo($this->fileInfo['name']);
        $ext='.'.$part_parts['extension'];
        $file=basename($filename,$ext);
        $addext=$ext;
        foreach ($this->ext_rename as $exten)
        {
            if (preg_match($exten,$ext)) $addext=".txt";
        }
        //Генерируем имя файла
        $file.=$addext;
        $dir=$this->getRootDir().$this->getPath().DIRSEP;
        //Если нет указанного каталога, создаём его
        if (!$this->createDir($dir)) return FALSE;
        $file=$this->fileExists($dir,$file);
        $path=str_replace(DIRSEP.DIRSEP,DIRSEP,$dir.$file);
        //Выполняем загрузку файла
        if (move_uploaded_file($this->fileInfo['tmp_name'],$path))
        {
            @chmod($path,$this->permissions);
            //Возвращаем имя загруженного файла
            return $file;
        }
        return FALSE;
    }
    
    /**
     * Получаем путь к хранилищу файлов.
     * @return string
     */
    protected function getRootDir()
    {
        return SITEPATH.PATHPUBLIC.DIRSEP.PATHFILE.DIRSEP;
    }
    
    /**
     * Создаёт каталог для загружаемых файлов
     * @param string $path - имя или путь к каталогу для загрузки файлов
     * @return boolean
     */
    protected function createDir($path)
    {
        if (file_exists($path)) return TRUE;
        return mkdir($path,$this->permissions,true); 
    }
    
    /**
     * Получает информацию о файлах из глобального массива $_FILES.
     * @return boolean
     */
    protected function setFileInfo()
    {
        if (isset($_FILES[$this->fileName]))
        {
            $this->fileInfo=$_FILES[$this->fileName];
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Получает информацию о файлах из глобального массива $_FILES обвёрнутого массивом формы.
     * @param string $nameForm - имя формы
     * @return array
     */
    public function setFileInfoForm($nameForm)
    {
        $result=FALSE;
        if (isset($_FILES[$nameForm]))
        {            
            foreach ($_FILES[$nameForm] as $key=>$value)
            {
                if (isset($value[$this->fileName]))
                    foreach ($value as $k=>$v)
                    {
                        $result[$k][$key]=$v;
                    }
            }
        }
        $this->fileInfo=$result[$this->fileName];
        return $result;
    }
    
    /**
     * Возвращает количество загружаемых файлов.
     * @return integer
     */
    protected function getCountFile()
    {
        return (is_array($this->fileInfo['name']))?count($this->fileInfo['name']):FALSE;
    }
    
    /**
     * Устанавливает имя каталога или путь для загрузки файлов.
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path=$path;
    }
    
    /**
     * Возвращает имя каталога или путь для загрузки файлов.
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Добавляет валидацию файла.
     * @param variant $valid - объект валидации
     * @return Syo_Upload
     */
    public function addVerify($valid)
    {
        $this->validates->addVerify($valid);
        return $this;
    }
    
    /**
     * Выполняем валидацию.
     * @param variant $value - значение для проверки
     * @return boolean
     */
    public function isVerify($fileInfo)
    {
        return $this->validates->isVerify($fileInfo);
    }
    
    /**
     * Проверяет, загружен ли файл на сервер.
     * @return boolean
     */
    public function checkUploadFile()
    {
        if (is_array($this->fileInfo['error']))
        {
            foreach ($this->fileInfo['error'] as $key=>$error)
                if ($error==UPLOAD_ERR_NO_FILE)
                    return FALSE;
        }
        else 
        {
            if ($this->fileInfo['error']==UPLOAD_ERR_NO_FILE)
                return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Возвращает список с произошедшими ошибками.
     * @return array - список с произошедшими ошибками
     */
    public function getError()
    {
        return $this->validates->getError();
    }
    
    /**
     * Генерирует уникальное имя для файла.
     * @param string $dir
     * @param string $file
     * @return string
     */
    protected function fileExists($dir,$file)
    {
        if (!$this->replaceFile && file_exists($dir.$file))
            return uniqid().'_'.$file;
        else
            return $file;
    }
    
    /**
     * Устанавливает генерировать или не генерировать уникальное имя файла.
     * @param boolean $bool
     */
    public function setReplaceFile($bool)
    {
        $this->replaceFile=$bool;
    }
}
?>