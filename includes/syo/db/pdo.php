<?php
/**
 * Класс для работы с базой данных через расширение PDO.
 */
class Syo_Db_Pdo
{
    /**
     * Свойство содержавшее  PDO-расширения.
     * @var PDO 
     */
    private $db=null;
    
    /**
     * Хранит настройки базы данных.
     * @var array 
     */
    protected $configs=array();
    
    /**
     * Указывает выводить Sql-запрос для просмотра или нет.
     * @var boolean 
     */
    protected $viewSql=false;
    
    /**
     * Хранит поступившие запросы для дальнейшего просмотра.
     * @var array 
     */
    protected $sqlBuffer=array();
    
    /**
     * Одиночка.
     * @var Syo_Db_Pdo 
     */
    protected static $instance;

    /**
     * Возвращает единственный экземпляр класса. @return Singleton
     * @return Syo_Db_Pdo
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance=new Syo_Db_Pdo();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор.
     * @param array $config - настройки базы данных
     */
    private function __construct()
    {
        $this->initConnection();
    }

    /**
     * Защищаем от создания через клонирование
     */
    private function __clone()
    {
        
    }
    
    /**
     * Защищаем от создания через unserialize
     */
    private function __wakeup()
    {
        
    }
    
    /**
     * Устанавливает настройки для базы данных.
     * @param array $config - массив с настройками
     * @return \Syo_Db_Pdo
     */
    private function setConfig($config=NULL)
    {
        if (is_null($config))
        {
            $this->configs=Syo_Config::Load('db');
        }
        else 
        {
            $this->configs=$config;
        }
        return $this;
    }

    /**
     * Возвращает настройки базы данных.
     * @return array
     */
    private function getConfig()
    {
        return $this->configs;
    }

    /**
     * Подключение базы данных.
     * @param array $config - настройки базы данных
     * @return PDO - расширение PDO
     * @throws Syo_Exception
     */
    public function initConnection($config=NULL)
    {
        try
        {
            //Устанавливаем настройки базы данных.
            $this->setConfig($config);

            //Настройки установлены, иначе ошибка.
            if (!is_null($this->getConfig()))
            {
                //Формируем параметр подключения к базе данных. 
                $dsn=sprintf('%s:host=%s;dbname=%s', $this->configs['db']['adapter'], $this->configs['db']['params']['host'],$this->configs['db']['params']['dbname']);
                try
                { 
                    //Устанавливаем соединение, и сохраняем его.
                    $this->setConnection(new PDO($dsn,$this->configs['db']['params']['username'],$this->configs['db']['params']['password']));
                    //Выполняем настроечные запросы к базе данных.
                    if (isset($this->configs['db']['sql']['names']))
                    {
                        $this->getConnection()->query('SET NAMES '.$this->configs['db']['sql']['names']);
                        $this->getConnection()->query('SET CHARACTER SET '.$this->configs['db']['sql']['names']);
                    }
                    if (isset($this->configs['db']['sql']['encoding']))
                    {
                        $this->getConnection()->query('SET collation_connection='.$this->configs['db']['sql']['encoding']);
                    }
                    if (isset($this->configs['db']['sql']['character']))
                    {     
                        $this->getConnection()->query('SET character_set_client='.$this->configs['db']['sql']['character']);
                        $this->getConnection()->query('SET character_set_results='.$this->configs['db']['sql']['character']);
                    }
                }
                //Нет соединения.
                catch (PDOException $e) 
                {
                    $Err=new Syo_Exception('Connection failed: ' . $e->getMessage());
                    echo $Err; 
                    exit();
                }
            }
            else
            {
                throw new Syo_Exception('Connection Database failed!');
            }
            return $this->db;
        }
        //При выполнении произошла ошибка.
        catch (Syo_Exception $e)
        {
            echo $e;
        }
    }
    
    /**
     * Возвращаем соединение.
     */
    public function getConnection()
    {
        return $this->db;
    }
    
    /**
     * Заменяет установленное соединение к базе данных.
     * @param PDO $base
     */
    public function setConnection($base)
    {
        $this->db=$base;
    }

    /**
     * Возвращает ID последней вставленной строки или последовательное значение.
     * @return integer
     */
    public function getLastId()
    {
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * Инициализация транзакции.
     */
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }
    
    /**
     * Откат транзакции.
     */
    public function failTransaction()
    {
        $this->db->rollBack();        
    }
    
    /**
     * Фиксирует транзакцию.
     */
    public function commit()
    {
        $this->db->commit();
    }

    /**
     * Определяет версию базы данных.
     * @return string
     */
    public function getVersionMySql()
    {
        return $this->getConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
    
    /**
     * Устанавливает возможность вывода запроса для просмотра.
     */
    public function setViewSql($bool)
    {
        $this->viewSql=$bool;
    }
    
    /**
     * Возвращает список поступивших запросов.
     * @return array
     */
    public function getViewSql()
    {
        return $this->sqlBuffer;
    }

    /**
     * Заключает строку в кавычки (если требуется) и экранирует специальные символы.
     * @param string $query - SQL запрос
     * @return string
     */
    public function quote($query)
    {
        return $this->db->quote($query); 
    }
    
    /**
     * Выполняет SQL запрос и возвращает результирующий набор в виде объекта PDOStatement
     * @param string $query - SQL запрос
     * @return PDOStatement
     * @throws Syo_Exception
     */
    public function query($query)
    {     
        try
        {
            //Если нужно сохраняем поступивший запрос.
            if ($this->viewSql) $this->sqlBuffer[]=$query;
            //Выполняем запрос.
            $result=$this->getConnection()->query($query);
            //Если возникла ошибка.
            if ($this->getConnection()->errorCode()!=0000)
            {
                //Передаём информацию об ошибке.
                $info=$this->getConnection()->errorInfo();
                throw new Syo_Exception('Error SQL: ['.$query.'] ('.$info[1].') '.$info[2]);
            }
            return $result;
        }
        catch (Syo_Exception $e)
        {
            echo $e;
            exit();
        }
    }
    
    /**
     * Возвращает массив, содержащий все строки с запроса.
     * @param sting $query
     * @return array
     */
    public function fetchAll($query)
    {
        return $this->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Возвращает сгруппированный массив, содержащий все строки с запроса.
     * @param sting $query
     * @return array
     */
    public function fetchGroup($query)
    {
        return $this->query($query)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
    }

    /**
     * Возвращает ассоциативный массив, содержащий все строки с запроса.
     * @param sting $query
     * @return array
     */
    public function fetchAssoc($query)
    {
        $result=$this->query($query)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
        return array_map(array($this,'delArrayKeyZero'),$result);
    }
    
    /**
     * Обрабатывает массив для метода fetchAssoc.
     * @param array $element - входной элемент
     * @return array
     */
    private function delArrayKeyZero($element)
    {
        return array_pop($element);
    }

    /**
     * Извлечение следующей строки из результирующего запроса.
     * @param string $query
     * @return array
     */
    public function fetchOne($query)
    {
        return $this->query($query)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Возвращает данные одного столбца следующей строки результирующего запроса.
     * @param string $query
     * @return array
     */
    public function fetchColumn($query)
    {
        return $this->query($query)->fetchColumn();
    }

    /**
     * Извлечение следующей строки из результирующего запроса виде объекта. 
     * @param string $query
     * @return object
     */
    public function fetchObj($query)
    {
        return $this->query($query)->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Конвертирует дату базы данных на русский вид (d.m.y).
     * @param date $date
     * @return date
     */
    static public function SQLDateToRUDate($date)
    {
        $date=strtotime($date);
        $date=strftime("%d.%m.%Y",$date);
        return $date;
    }

    /**
     * Конвертирует дату русского вида на дату базы данных (y-m-d).
     * @param date $date
     * @return date
     */
    static public function RUDateToSQLDate($date)
    {
        $today=explode(".",$date);
        return $today[2].'-'.$today[1].'-'.$today[0];;
    }
}
?>
