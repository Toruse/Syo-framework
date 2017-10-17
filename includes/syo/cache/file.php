<?php
/**
* Класс для работы с кэш.
*/
class Syo_Cache_File
{

    /**
    * Путь к папке с файлами кэша.
    * @var string
    */
    private $path = 'cache/';
    
    /**
     * Хранит название группы файлов кэша.
     * @var string
     */
    private $group='';

    /**
    * Имя файла кэша по умолчанию.
    * @var string
    */
    private $name = 'default';

    /**
    * Расширение файла кэша.
    * @var string
    */
    private $extension = '.cache';

    /**
    * Конструктор.
    * @param string|array $config - конфигурация кэша
    * @return void
    */
    public function __construct($config=NULL) 
    {
        if (is_null($config))
        {
            //Получаем конфигурацию приложения
            $config=Syo_Registry::getInstance()->get('config');
            //Если существуют настройки для Cookie, передаём их классу
            if (isset($config['application']['cache']['name'])) $this->setName($config['application']['cache']['name']);
            if (isset($config['application']['cache']['path'])) $this->setPath($config['application']['cache']['path']);
            if (isset($config['application']['cache']['extension'])) $this->setExtension($config['application']['cache']['extension']);
        }
        else
        {
            if (is_string($config))
            {
                //Указываем имя файла.
                $this->setName($config);
            }
            elseif (is_array($config))
            {
                //Получили параметры, выполняем их установку.
                if (isset($config['name'])) $this->setName($config['name']);
                if (isset($config['path'])) $this->setPath($config['path']);
                if (isset($config['extension'])) $this->setExtension($config['extension']);
            }
        }
    }

    /**
    * Проверяет наличие данных по ключу.
    * @param string $key - ключ значения в кэше
    * @return boolean
    */
    public function isCached($key)
    {
        //Загружаем кэшированные данные
        $cachedData=$this->loadCache();
        //Передаём значение на основе ключа
        if (isset($cachedData[$key]['data']) && ($cachedData!=FALSE) && ($this->checkExpired($cachedData[$key]['time'],$cachedData[$key]['expire'])==FALSE))
        {
            return $cachedData[$key]['data'];
        }
        return NULL;
    }

    /**
    * Сохраняет данные в кэш.
    * @param string $key - ключ значения в кэше
    * @param variant $data - данные (значение) для кэширования
    * @param integer $expiration - время жизни значения в кэше
    * @return Syo_Cache_File
    */
    public function save($key,$data,$expiration=0)
    {
        //Устанавливаем параметры кэшируемой переменной
        $storeData=array(
            'time' => time(),
            'expire' => $expiration,
            'data' => $data
        );
        //Загружаем кэшированные данные
        $dataArray=$this->loadCache();
        //Если есть в наличие - обновляем, иначе добавляем в кэш
        if (is_array($dataArray))
        {
            $dataArray[$key]=$storeData;
        }
        else
        {
            $dataArray=array($key=>$storeData);
        }
        //Сохраняем кэш.
        $cacheData=json_encode($dataArray);
        file_put_contents($this->getCacheDir(),$cacheData);
        return $this;
    }

    /**
    * Получить данные из кэша на основе ключа.
    * @param string $key - ключ значения в кэше
    * @param boolean $timestamp - вывести значение или время жизни кэша
    * @return string
    */
    public function load($key,$timestamp=FALSE)
    {
        //Загружаем кэшированные данные
        $cachedData=$this->loadCache();
        //Устанавливаем, выводить значение кэша или время создания
        if ($timestamp==FALSE) $type='data'; else $type='time';
        //Передаём данные на вывод
        if (!isset($cachedData[$key][$type])) return NULL;
        return $cachedData[$key][$type];
    }

    /**
    * Получить все кэшированные данные.
    * @param boolean $meta - вывести дополнительные параметры кэша
    * @return array
    */
    public function loadAll($meta=FALSE)
    {
        //Выводим массив кэша значений или выводим все данные с кэша
        if ($meta==FALSE)
        {
            $result=array();
            //Загружаем кэшированные данные
            $cachedData=$this->loadCache();
            //Группируем данные
            if ($cachedData)
            {
                foreach ($cachedData as $key=>$value)
                {
                    $result[$key]=$value['data'];
                }
            }
            return $result;
        }
        else
        {
            //Загружаем кэшированные данные
            return $this->loadCache();
        }
    }

    /**
    * Стереть кэшированные данные по ключу.
    * @param string $key - ключ значения в кэше
    * @return Syo_Cache_File
    */
    public function delete($key)
    {
        //Загружаем кэшированные данные
        $cacheData=$this->loadCache();
        //Перебиваем массив
        if (is_array($cacheData))
        {
            if (isset($cacheData[$key]))
            {
                //Значение есть в кэше, и удаляем его
                unset($cacheData[$key]);
                if (count($cacheData)==0)
                {
                    //Кэш пустой, удаляем его файл
                    unlink($this->getCacheDir());
                }
                else
                {
                    //Обновляем данные в файле кэша
                    $cacheData=json_encode($cacheData);
                    file_put_contents($this->getCacheDir(),$cacheData);
                }
            }
            else
            {
                //Значение не было найдено, вызываем ошибку
                throw new Syo_Exception("Error: erase() - Key '{$key}' not found.");
            }
        }
        return $this;
    }

    /**
    * Стирает вес устаревшие записи.
    * @return integer
    */
    public function deleteExpired()
    {
        //Загружаем кэшированные данные
        $cacheData=$this->loadCache();
        if (is_array($cacheData))
        {
            $counter=0;
            //Перебираем значения в кэше
            foreach ($cacheData as $key=>$entry)
            {
                //Найдено устаревшее значение
                if ($this->checkExpired($entry['time'],$entry['expire']))
                {
                    //Удаляем значение
                    unset($cacheData[$key]);
                    $counter++;
                }
            }
            if ((count($cacheData)!=0) && ($counter>0))
            {
                //Обновляем данные в файле кэша
                $cacheData=json_encode($cacheData);
                file_put_contents($this->getCacheDir(),$cacheData);
            }
            else
            {
                //Кэш пустой, удаляем его файл
                unlink($this->getCacheDir());
            }
            return $counter;
        }
    }

    /**
    * Удаляет все кэшированные записи.
    * @return Syo_Cache_File
    */
    public function deleteAll()
    {
        //Загружаем кэшированные данные
        $cacheDir=$this->getCacheDir();
        //Удаляем файл кэша
        if (file_exists($cacheDir))
        {
            unlink($cacheDir);
        }
        return $this;
    }
    
    /**
    * Удаляет группу кэшируемых записей.
    * @return Syo_Cache_File
    */
    public function deleteGroup()
    {
        //Получаем путь к директории с кэш файлами
        $cacheDir=$this->getPath();
        //Удаляем директорию
        if (file_exists($cacheDir))
        {
            $this->deleteDirectory($cacheDir);
        }
        return $this;
    }
    
    /**
     * Удаляете директорию с кэш файлами.
     * @param string $directory - путь к директории
     */
    private function deleteDirectory($directory)
    {
        if ($list=glob($directory.DIRSEP."*"))
            foreach ($list as $file) 
                is_dir($file)?$this->deleteDirectory($file):unlink($file);
        rmdir($directory);        
    }

    /**
    * Загружает файл кэша.
    * @return array
    */
    private function loadCache()
    {
        //Проверяем наличие файла кэша
        if (file_exists($this->getCacheDir())) 
        {
            //Загружаем кэшированные данные
            $file=file_get_contents($this->getCacheDir());
            //Выполняем декодирование
            return json_decode($file,true);
        }
        else 
        {
            return FALSE;
        }
    }

    /**
    * Возвращает путь к каталогу кэша.
    * @return string
    */
    public function getCacheDir() 
    {
        //Проверяет на существование каталога кэша, если нет, то создаёт его
        if ($this->checkCacheDir()) 
        {
            //Фильтруем имя файла от ошибок
            $filename=$this->getName();
            $filename=preg_replace('/[^0-9a-z\.\_\-]/i','',strtolower($filename));
            //Генерируем путь
            return $this->getPath().$this->getHash($filename).$this->getExtension();
        }
    }

    /**
    * Возвращает имя файла кэша.
    * @return string
    */
    private function getHash($filename)
    {
        return sha1($filename);
    }

    /**
    * Проверят время в указанном диапазоне.
    * @param integer $timestamp - дата создания
    * @param integer $expiration - время жизни
    * @return boolean
    */
    private function checkExpired($timestamp, $expiration)
    {
        $result=FALSE;
        if ($expiration!==0) 
        {
            $timeDiff=time()-$timestamp;
            if ($timeDiff>$expiration) $result=TRUE; else $result=FALSE;
        }
        return $result;
    }

    /**
    * Проверяет на существование каталога кэша, если нет, то создаёт его.
    * @return boolean
    */
    private function checkCacheDir()
    {
        //Проверяем, возможно ли создать каталог и создаём его
        if (!is_dir($this->getPath()) && !mkdir($this->getPath(),0775,true)) 
        {
            throw new Syo_Exception('Unable to create cache directory '.$this->getCachePath());
        } 
        //Проверяем, возможно записать и считать данные из папки
        elseif (!is_readable($this->getPath()) || !is_writable($this->getPath())) 
        {
            //Изменяет режим доступа к каталогу
            if (!chmod($this->getCachePath(),0775))
            {
                throw new Syo_Exception($this->getCachePath() . ' must be readable and writeable');
            }
        }
        return TRUE;
    }

    /**
    * Устанавливает путь к кэшу.
    * @param string $path
    * @return Syo_Cache_File
    */
    public function setPath($path) 
    {
        $this->path=$path;
        return $this;
    }

    /**
    * Возвращает путь к кэшу.
    * @return string
    */
    public function getPath()
    {
        return $this->path.$this->getGroup();
    }
    
    /**
    * Устанавливает название группы к кэшу.
    * @param string $path
    * @return Syo_Cache_File
    */
    public function setGroup($group='') 
    {
        $this->group=$group==''?'':$group.DIRSEP;
        return $this;
    }

    /**
    * Возвращает название группы к кэшу.
    * @return string
    */
    public function getGroup()
    {
        return $this->group;
    }

    /**
    * Устанавливает имя кэша.
    * @param string $name
    * @return Syo_Cache_File
    */
    public function setName($name)
    {
        $this->name=$name;
        return $this;
    }

    /**
    * Возвращает имя кэша.
    * @return void
    */
    public function getName()
    {
        return $this->name;
    }

    /**
    * Устанавливает имя расширения кэша.
    * @param string $ext
    * @return Syo_Cache_File
    */
    public function setExtension($ext)
    {
        $this->extension=$ext;
        return $this;
    }

    /**
    * Возвращает имя расширения кэша.
    * @return string
    */
    public function getExtension()
    {
        return $this->extension;
    }

}