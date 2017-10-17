<?php
/**
 * Класс для работы с таблицей типа «дерева категорий (NESTED SETS)» в базе данных.
CREATE TABLE IF NOT EXISTS `tree` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `left_key` int(10) NOT NULL DEFAULT '0',
  `right_key` int(10) NOT NULL DEFAULT '0',
  `level` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `left_key` (`left_key`,`right_key`,`level`)
) 
 */
class Syo_Db_Tree
{
    /**
     * Имя таблицы с деревом.
     * @var string 
     */
    protected $table=NULL;

    /**
     * Конструктор. Устанавливаем имя таблицы.
     * @param string $tablename - имя таблицы
     * @return boolean
     */
    public function __construct($tablename=NULL)
    {
        if (is_null($tablename))
        {
            $this->table='tree';
        }
        else
        {
            $this->table=$tablename;
        }
	return true;
    }
    
    /**
     * Инициализирует дерево.
     * @param array $extrafields - массив с перечисленными полями для добавления в таблицу
     * @return boolean
     */
    public function createRootNode($extrafields)
    {
        Syo_Db_Pdo::getInstance()->beginTransaction();
        //Определяем наличие записей.
	$sql="SELECT id FROM ".$this->table." ORDER BY right_key DESC LIMIT 1";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        //Таблица пуста, добавляем базовый элемент дерева.
	if ($result->rowCount()==0)
        {
            //Формируем начальные данные.
            $data=array(
                'left_key'=>1,
                'right_key'=>2,
                'level'=>0
            );
            //Подготавливаем данные к запросу.
            $data=array_merge($data,$extrafields);
            $data_keys=array_keys($data);
            $data_values=array_values($data);
            //Формируем запрос на добавление корня дерева.
            $sql="INSERT INTO ".$this->table." (".implode(",",$data_keys).") VALUES ('".implode("','",$data_values)."');";
            //Выполняем добавление.
            $result=Syo_Db_Pdo::getInstance()->query($sql);
            if ($result->rowCount()==0)
            {
                Syo_Db_Pdo::getInstance()->failTransaction();
                return FALSE;
            }
	}
        else
        {
            return FALSE;
	}
        Syo_Db_Pdo::getInstance()->commit();
        return TRUE;           
    }

    /**
     * Генерирует строку со списком полей, которые нужно получить из таблицы.
     * @param array $extrafields - список полей виде массива
     * @return string - строка со списком полей
     */
    private function specifyFields($extrafields)
    {
         if (is_null($extrafields) || count($extrafields)==0)
            return '*';
        else
            return implode(',',$extrafields);
    }
    
    /**
     * Добавляет запись о добавляемом узле дерева в базу данных.
     * @param array $data - массив с перечисленными полями для добавления в таблицу
     * @return PDOStatement - результат запроса о добавлении узла в дерево.
     */
    protected function insertNode($data)
    {
        $data_keys=array_keys($data);
        $data_values=array_values($data);
	$sql="INSERT INTO ".$this->table." (".implode(",",$data_keys).") VALUES ('".implode("','",$data_values)."');";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        return $result;
    }
    
    /**
     * Возвращает информацию о текущем узле с номером $id
     * @param number $id - id узла
     * @param array $extrafields - список полей виде массива
     * @param string $where - дополнительное условие для выборки из таблицы
     * @return strClass | boolean - информация о узле
     */
    public function getNode($id,$extrafields=array(),$where=NULL)
    {
        //Формируем, какие вернуть поля с таблицы
        if (is_null($extrafields))
            $extrafields='*';
        else
            $extrafields='id,left_key,right_key,level'.implode(',',$extrafields);            
        $sql="SELECT ".$extrafields." FROM ".$this->table." WHERE id='".$id."'";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            return FALSE;
        }
        $node=$result->fetchObject();
        return $node;
    }
    
    /**
     * Возвращает информацию о родителе текущего узла с номером $id
     * @param number $id - id узла
     * @param array $extrafields - список полей виде массива
     * @param string $where - дополнительное условие для выборки из таблицы
     * @return strClass | boolean - информация о узле
     */
    public function getParent($id,$extrafields=NULL,$where=NULL)
    {
        //проверяем наличие узла
        $node=$this->getNode($id);
        if ($node===FALSE)
        {
            return FALSE;
        }
        //увеличиваем уровень
        $node->level--;
        //генерируем список выводимых полей таблицы
        $extrafields=$this->specifyFields($extrafields);
        //Выполняем запрос
        $sql="SELECT ".$extrafields." FROM ".$this->table." WHERE left_key<".$node->left_key." AND right_key>".$node->right_key." AND level=".$node->level." ORDER BY left_key";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            return FALSE;
        }
        $node=$result->fetchObject();
        return $node;
    }
    
    /**
     * Вставляет в дерево новый дочерний узел по отношению к элементу с номером $id
     * @param number $id - номер родительского узла
     * @param array $extrafields - содержит массив с информацией для дополнительных полей таблицы
     * @return boolean
     */
    public function insert($id,$extrafields)
    {
        Syo_Db_Pdo::getInstance()->beginTransaction();
        //Получаем данные об узле, к которому добавляется новый узел.
        $parent=$this->getNode($id);
	if (!$parent)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        //Выполняем обновление элементов в таблице, чтобы добавить новый узел
        $sql="UPDATE ".$this->table." SET right_key=right_key+2,left_key=IF(left_key >".$parent->right_key.",left_key+2,left_key) WHERE right_key>=".$parent->right_key;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        //Подготавливаем данные и добавляем в базу данных
        $data=array(
            'left_key'=>$parent->right_key,
            'right_key'=>$parent->right_key+1,
            'level'=>$parent->level+1
	);
        $data=array_merge($data,$extrafields);
	$result=$this->insertNode($data);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        Syo_Db_Pdo::getInstance()->commit();
        return true;
    }
    
    /**
     * Обновляет данные об узле.
     * @param number $id - id узла
     * @param array $updatefields - список полей виде массива
     * @param string $where - дополнительное условие для выборки из таблицы
     * @return boolean
     */
    public function update($id,$updatefields,$where=NULL)
    {
        //Убираем из массива поля, запрещённые к редактированию
        if (isset($updatefields['id'])) unset($array['id']);
        if (isset($updatefields['left_key'])) unset($array['left_key']);
        if (isset($updatefields['right_key'])) unset($array['right_key']);
        if (isset($updatefields['level'])) unset($array['level']);
        //Генерируем строку со списком полей для изменения данных в таблице
        $set=null;
        foreach ($updatefields as $key=>$value)
        {
            $set.=((is_null($set))?'':',').$key."='".$value."'";
        }
        //Обновляем данные в таблице
        if (!is_null($set))
            $sql="UPDATE ".$this->table." SET ".$set." WHERE id=".$id." ".$where;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        return TRUE;
    }

    /**
     * Делаем выборку всего дерева.
     * @param array $extrafields - массив с перечислением требуемых вам полей таблицы
     * @return array
     */
    public function getTreeAll($extrafields=NULL)
    {
        //Формируем, какие вернуть поля с таблицы
        $extrafields=$this->specifyFields($extrafields);
        //Выполняем выборку
        $sql="SELECT ".$extrafields." FROM ".$this->table." ORDER BY left_key";            
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Выводит ветку узла с номером $id.
     * @param number $id - номер узла 
     * @param array $extrafields - массив с перечислением требуемых вам полей таблицы 
     * @return array (or boolean)
     */
    public function getTreeId($id,$extrafields=NULL)
    {
        //Получаем информацию об узле для выборки
        $data=$this->getNode($id);
        if (is_object($data))
        {
            //Формируем, какие вернуть поля с таблицы
            if (is_null($extrafields) || count($extrafields)==0)
            {
                $extrafields='*';
            }
            else
            {
                $extrafields=implode(',',$extrafields);
            }
            //Выполняем выборку
            $sql="SELECT ".$extrafields." FROM ".$this->table." WHERE left_key>=".$data->left_key." AND right_key<=".$data->right_key." ORDER BY left_key";
            $result=Syo_Db_Pdo::getInstance()->query($sql);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Выводит всю ветку узла с номером $id.
     * @param number $id - номер узла 
     * @param array $extrafields - массив с перечислением требуемых вам полей таблицы 
     * @return array (or boolean)
     */
    public function getTreeIdAll($id,$extrafields=NULL)
    {
        //Получаем информацию об узле для выборки
        $data=$this->getNode($id);
        if (is_object($data))
        {
            //Формируем, какие вернуть поля с таблицы
            if (is_null($extrafields) || count($extrafields)==0)
            {
                $extrafields='*';
            }
            else
            {
                $extrafields=implode(',',$extrafields);
            }
            //Выполняем выборку
            $sql="SELECT ".$extrafields." FROM ".$this->table." WHERE right_key>".$data->left_key." AND left_key<".$data->right_key." ORDER BY left_key";
            $result=Syo_Db_Pdo::getInstance()->query($sql);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Выводит список узлов узла с номером $id.
     * @param number $id - номер узла 
     * @param array $extrafields - массив с перечислением требуемых вам полей таблицы 
     * @param string $where - дополнительно условие при выборке из таблицы 
     * @return array (or boolean)
     */
    public function getBranch($id,$extrafields=NULL,$where=NULL)
    {
        //Получаем информацию об узле для выборки
        $data=$this->getNode($id);
        if (is_object($data))
        {
            //Формируем, какие вернуть поля с таблицы
            if (is_null($extrafields) || count($extrafields)==0)
            {
                $extrafields='*';
            }
            else
            {
                $extrafields=implode(',',$extrafields);
            }
            //Выполняем выборку
            $sql="SELECT ".$extrafields." FROM ".$this->table." WHERE left_key>=".$data->left_key." AND right_key<=".$data->right_key." AND level=".$data->level."+1 ".$where." ORDER BY left_key";
            $result=Syo_Db_Pdo::getInstance()->query($sql);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Выводит приоткрытое дерево, где условием приоткрытости выступает элемент с номером $id.
     * @param number $id - номер узла 
     * @param array $extrafields - массив с перечислением требуемых вам полей таблицы 
     * @return array (or boolean)
     */
    public function getReveal($id,$extrafields=NULL)
    {
        //Формируем, какие вернуть поля с таблицы
        if (is_null($extrafields) || count($extrafields)==0)
        {
            $extrafields='*';
        }
        else
        {
            $extrafields=implode(',',$extrafields);
        }
        //Получаем дерево с узлом $id
        $sql="SELECT A.left_key,A.right_key, A.level FROM ".$this->table." A, ".$this->table." B "
        ."WHERE B.id=".(int)$id." AND B.left_key BETWEEN A.left_key AND A.right_key ORDER BY A.left_key";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        $alen=$result->rowCount();
        if ($alen==0)
        {
            return FALSE;
        }
        //Формируем запрос с учётом полученного дерева и дополнительных узлов,
        //чтобы получить приоткрытое дерево из базы данных.
        $i=0;
        $sql= "SELECT ".$extrafields." FROM ".$this->table." WHERE (level=0";
        while ($row = $result->fetch())
        {
            if ((++$i==$alen) && (($row['left_key']+1)==$row['right_key']))
            {
                break;
            }
            $sql .=" OR (level=".($row['level']+1)." AND left_key>".$row['left_key']." AND right_key<".$row['right_key'].")";
        }
        $sql.=") ORDER BY left_key";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            return FALSE;
        }
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Выводит всех родителей узла.
     * @param number $id - номер узла 
     * @param array $extrafields - массив с перечислением требуемых вам полей таблицы 
     * @return array (or boolean)
     */
    public function getPath($id,$extrafields=NULL,$where=NULL)
    {
        //Получаем информацию об узле для выборки
	$data=$this->getNode($id);
        if (is_object($data))
        {
            //Формируем, какие вернуть поля с таблицы
            if (is_null($extrafields) || count($extrafields)==0)
            {
                $extrafields='*';
            }
            else
            {
                $extrafields=implode(',',$extrafields);
            }
            $sql="SELECT ".$extrafields." FROM ".$this->table." WHERE right_key>".$data->left_key." AND left_key<".$data->right_key." ".$where." ORDER BY left_key";
            //Выполняем выборку
            $result=Syo_Db_Pdo::getInstance()->query($sql);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Меняет элементы местами. $id1 и $id2 уникальные номера первого и второго элемента соответственно.
     * @param number $id1 - уникальный номер первого узла
     * @param number $id2 - уникальный номер второго узла
     * @return boolean
     */
    public function changePosition($id1,$id2)
    {
        //Получаем информацию о первом узле
        $node1=$this->getNode($id1);
        if ($node1===FALSE)
        {
            return FALSE;
        }
        //Получаем информацию о втором узле
        $node2=$this->getNode($id2);
        if ($node2===FALSE)
        {
            return FALSE;
        }
        //Обновляем данные первого узла
        $sql="UPDATE ".$this->table." SET left_key=".$node2->left_key.",right_key=".$node2->right_key.",level=".$node2->level." WHERE id=".(int)$id1;
        Syo_Db_Pdo::getInstance()->beginTransaction();
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        //Обновляем данные второго узла
        $sql="UPDATE ".$this->table." SET left_key=".$node1->left_key.",right_key=".$node1->right_key.",level=".$node1->level." WHERE id=".(int)$id2;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        Syo_Db_Pdo::getInstance()->commit();
        return TRUE;
    }
    
    /**
     * Меняет порядок детей у одного родителя в рамках одного уровня. 
     * Все дети переносимого элемента также перемещаются вместе с ним, сохраняя иерархию
     * $position:
     *  'after' - переносимый узел ($id1) будет поставлен после указанного узла ($id2),
     *  'before' - переносимый узел ($id1) будет поставлен перед указанным ($id2).
     * @param number $id1 - уникальный номер первого узла
     * @param number $id2 - уникальный номер второго узла
     * @param string $position - позиция, в которую будет помещен переносимый узел
     * @return boolean
     */
    public function changePositionAll($id1,$id2,$position='after')
    {
        //Получаем информацию о первом узле
        $node1=$this->getNode($id1);
        if ($node1===FALSE)
        {
            return FALSE;
        }
        //Получаем информацию о втором узле
        $node2=$this->getNode($id2);
        if ($node2===FALSE)
        {
            return FALSE;
        }
        //Проверяем, что узлы находятся на одном уровне
        if ($node1->level<>$node2->level)
        {
            return FALSE;
        }
        //В зависимости от входных данных формируем запрос
        if ("before"==$position)
        {
            if ($node1->left_key>$node2->left_key)
            {
                $sql="UPDATE ".$this->table." SET "
                ."right_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN right_key-".($node1->left_key-$node2->left_key)." "
                ."WHEN left_key BETWEEN ".$node2->left_key." AND ".($node1->left_key-1)." THEN right_key+".($node1->right_key-$node1->left_key+1)
                ." ELSE right_key END, left_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN left_key-"
                .($node1->left_key - $node2->left_key)." WHEN left_key BETWEEN ".$node2->left_key." AND ".($node1->left_key-1)." THEN left_key+"
                .($node1->right_key - $node1->left_key+1)." ELSE left_key END WHERE left_key BETWEEN ".$node2->left_key." AND ".$node1->right_key;
            }
            else
            {
                $sql="UPDATE ".$this->table." SET right_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN right_key+"
                .(($node2->left_key-$node1->left_key)-($node1->right_key-$node1->left_key+1))." WHEN left_key BETWEEN ".($node1->right_key+1)." AND "
                .($node2->left_key-1)." THEN right_key-".(($node1->right_key-$node1->left_key+1))." ELSE right_key END, left_key=CASE WHEN left_key BETWEEN "
                .$node1->left_key." AND ".$node1->right_key." THEN left_key+".(($node2->left_key-$node1->left_key)-($node1->right_key-$node1->left_key+1))." "
                ."WHEN left_key BETWEEN ".($node1->right_key + 1)." AND ".($node2->left_key-1)." THEN left_key-".($node1->right_key-$node1->left_key+1)
                ." ELSE left_key END WHERE left_key BETWEEN ".$node1->left_key." AND ".($node2->left_key-1);
            }
        }
        if ("after"==$position)
        {
            if ($node1->left_key>$node2->left_key)
            {
                $sql="UPDATE ".$this->table." SET "
               ."right_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN right_key-".($node1->left_key-$node2->left_key-($node2->right_key-$node2->left_key+1))." "
               ."WHEN left_key BETWEEN ".($node2->right_key+1)." AND ".($node1->left_key-1)." THEN right_key+".($node1->right_key-$node1->left_key+1)." ELSE right_key END, "
               ."left_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN left_key-".($node1->left_key-$node2->left_key-($node2->right_key-$node2->left_key+1))." "
               ."WHEN left_key BETWEEN ".($node2->right_key+1)." AND ".($node1->left_key-1)." THEN left_key+".($node1->right_key-$node1->left_key+1)." ELSE left_key END "
               ."WHERE left_key BETWEEN ".($node2->right_key+1)." AND ".$node1->right_key;
            }
            else
            {
                $sql="UPDATE ".$this->table." SET "
               ."right_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN right_key+".($node2->right_key-$node1->right_key)." "
               ."WHEN left_key BETWEEN ".($node1->right_key+1)." AND ".$node2->right_key." THEN right_key-".(($node1->right_key-$node1->left_key+1))." ELSE right_key END, "
               ."left_key=CASE WHEN left_key BETWEEN ".$node1->left_key." AND ".$node1->right_key." THEN left_key+".($node2->right_key-$node1->right_key)." "
               ."WHEN left_key BETWEEN ".($node1->right_key+1)." AND ".$node2->right_key." THEN left_key-".($node1->right_key-$node1->left_key+1)." ELSE left_key END "
               ."WHERE left_key BETWEEN ".$node1->left_key." AND ".$node2->right_key;
            }
        }
        //Выполняем сформированный запрос
        Syo_Db_Pdo::getInstance()->beginTransaction();
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        Syo_Db_Pdo::getInstance()->commit();
        return TRUE;
    }
    
    /**
     * Удаляет узел с номером $id. Все его дети перемещаются на уровень выше.
     * @param number $id - номер узла 
     * @return boolean
     */
    public function delete($id)
    {
        //Получаем информацию об узле
        $node=$this->getNode($id);
        if ($node===FALSE)
        {
            return FALSE;
        }
        //Удаляем узел из базы данных
        $sql="DELETE FROM ".$this->table." WHERE id=".$id;
        Syo_Db_Pdo::getInstance()->beginTransaction();
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        //Обновляем узлы в базе данных
        $sql="UPDATE ".$this->table." SET "
        ."level=CASE WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN level-1 ELSE level END, "
        ."right_key=CASE WHEN right_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN right_key-1 "
        ."WHEN right_key>".$node->right_key." THEN right_key-2 ELSE right_key END, "
        ."left_key=CASE WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN left_key-1 "
        ."WHEN left_key>".$node->right_key." THEN left_key-2 ELSE left_key END "
        ."WHERE right_key>".$node->left_key;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        Syo_Db_Pdo::getInstance()->commit();
        return TRUE;
    }
    
    /**
     * Удаляет узел с номером $id и всех его детей
     * @param number $id - номер узла 
     * @return boolean
     */
    public function deleteAll($id)
    {
        //Получаем информацию об узле
        $node=$this->getNode($id);
        if ($node===FALSE)
        {
            return FALSE;
        }
        //Удаляем узел и его детей из базы данных
        $sql="DELETE FROM ".$this->table." WHERE left_key BETWEEN ".$node->left_key." AND ".$node->right_key;
        Syo_Db_Pdo::getInstance()->beginTransaction();
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        //Обновляем узлы в базе данных
        $delta=(($node->right_key-$node->left_key)+1);
        $sql="UPDATE ".$this->table." SET left_key= CASE WHEN left_key>".$node->left_key." THEN left_key - ".$delta." ELSE left_key END, "
        ."right_key= CASE WHEN right_key>".$node->left_key." THEN right_key-".$delta." ELSE right_key END WHERE right_key>".$node->right_key;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        Syo_Db_Pdo::getInstance()->commit();
        return TRUE;
    }
    
    /**
     * Перемещает узел с номером $id и всех его детей к новому разделу с номером $parentId.
     * @param number $id - номер узла
     * @param number $parentId - номер узла, к которому нужно перенести
     * @return boolean
     */
    public function move($id,$parentId)
    {
        //Получаем информацию об узле
        $node=$this->getNode($id);
        if ($node===FALSE)
        {
            return FALSE;
        }
        //Получаем информацию об узле, к которому нужно перенести узел $id
        $parent_node=$this->getNode($parentId);
        if ($parent_node===FALSE)
        {
            return FALSE;
        }
        //Проверяем на совпадение двух узлов и соответствие переноса
        if (($id==$parentId) ||
            ($node->left_key==$parent_node->left_key) ||
            (($parent_node->left_key>=$node->left_key) && ($parent_node->left_key<=$node->right_key)) ||
            (($node->level==$parent_node->level+1) && ($node->left_key>$parent_node->left_key) && ($node->right_key<$parent_node->right_key))
           )
        {
            return FALSE;
        }
        //В зависимости от входных данных формируем запрос для переноса узлов
        if (($parent_node->left_key<$node->left_key) && ($parent_node->right_key>$node->right_key) && ($parent_node->level<$node->level-1))
        {
            $sql="UPDATE ".$this->table." SET "
            ."level=CASE WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN level".sprintf("%+d", -($node->level-1)+$parent_node->level)
            ." ELSE level END, right_key=CASE WHEN right_key BETWEEN ".($node->right_key+1)." AND ".($parent_node->right_key-1)." THEN right_key-"
            .($node->right_key-$node->left_key+1)." WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN right_key+"
            .((($parent_node->right_key-$node->right_key-$node->level+$parent_node->level)/2)*2+$node->level-$parent_node->level-1)." ELSE right_key END, "
            ."left_key=CASE WHEN left_key BETWEEN ".($node->right_key+1)." AND ".($parent_node->right_key-1)." THEN left_key-"
            .($node->right_key-$node->left_key+1)." WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN left_key+"
            .((($parent_node->right_key-$node->right_key-$node->level+$parent_node->level)/2)*2+$node->level-$parent_node->level-1)." ELSE left_key END "
            ."WHERE left_key BETWEEN ".($parent_node->left_key+1)." AND ".($parent_node->right_key-1);
        }
        elseif ($parent_node->left_key<$node->left_key)
        {
            $sql="UPDATE ".$this->table." SET "
            ."level=CASE WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN level".sprintf("%+d", -($node->level-1)+$parent_node->level)
            ." ELSE level END, left_key=CASE WHEN left_key BETWEEN ".$parent_node->right_key." AND ".($node->left_key-1)." THEN left_key+"
            .($node->right_key-$node->left_key+1)." WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN left_key-"
            .($node->left_key-$parent_node->right_key)." ELSE left_key END, right_key=CASE WHEN right_key BETWEEN ".$parent_node->right_key." AND "
            .$node->left_key." THEN right_key+".($node->right_key-$node->left_key+1)." WHEN right_key BETWEEN ".$node->left_key." AND ".$node->right_key
            ." THEN right_key-".($node->left_key-$parent_node->right_key)." ELSE right_key END WHERE (left_key BETWEEN ".$parent_node->left_key." AND "
            .$node->right_key." OR right_key BETWEEN ".$parent_node->left_key." AND ".$node->right_key.")";
        }
        else
        {
            $sql="UPDATE ".$this->table." SET "
            ."level=CASE WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN level".sprintf("%+d", -($node->level-1)+$parent_node->level)
            ." ELSE level END, left_key=CASE WHEN left_key BETWEEN ".$node->right_key." AND ".$parent_node->right_key." THEN left_key-"
            .($node->right_key-$node->left_key+1)." WHEN left_key BETWEEN ".$node->left_key." AND ".$node->right_key." THEN left_key+"
            .($parent_node->right_key-1-$node->right_key)." ELSE left_key END, right_key=CASE WHEN right_key BETWEEN ".($node->right_key+1)
            ." AND ".($parent_node->right_key-1)." THEN right_key-".($node->right_key-$node->left_key+1)." WHEN right_key BETWEEN ".$node->left_key
            ." AND ".$node->right_key." THEN right_key+".($parent_node->right_key-1-$node->right_key)." ELSE right_key END WHERE (left_key BETWEEN "
            .$node->left_key." AND ".$parent_node->right_key." OR right_key BETWEEN ".$node->left_key." AND ".$parent_node->right_key.")";
        }
        //Выполняем сгенерированный запрос
        Syo_Db_Pdo::getInstance()->beginTransaction();
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
        {
            Syo_Db_Pdo::getInstance()->failTransaction();
            return FALSE;
        }
        Syo_Db_Pdo::getInstance()->commit();
        return TRUE;
    }
    
    /**
     * Проверяет дерево на ошибки.
     * @param boolean $thorough - указывает выполнять более глубокую проверку или нет
     * @return array (or boolean) - возвращается список повреждённых узлов 
     */
    public function check($thorough=FALSE)
    {
        //Тест 1
        $sql="SELECT id FROM ".$this->table." WHERE MOD (right_key-left_key,2)=0";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()!=0)
        {
            return $result->fetchAll();
        }
        //Тест 2
        $sql="SELECT id FROM ".$this->table." WHERE MOD(left_key-level+2,2)=0";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()!=0)
        {
            return $result->fetchAll();
        }
        //Тест 3 - более глубокая проверка
        if ($thorough)
	{
            $sql="SELECT t1.id, COUNT(t1.id) AS rep, MAX(t3.right_key) AS max_right FROM ".$this->table." AS t1,".$this->table." AS t2,".$this->table." AS t3 "
            ."WHERE t1.left_key<>t2.left_key AND t1.left_key<>t2.right_key AND t1.right_key<>t2.left_key AND t1.right_key <> t2.right_key "
            ."GROUP BY t1.id HAVING max_right<>SQRT(4*rep+1)+1";
            $result=Syo_Db_Pdo::getInstance()->query($sql);
            if ($result->rowCount()!=0)
            {
                return $result->fetchAll();
            } 
        }
        //Тест 4
        $sql="SELECT node.id AS id, node.level AS level FROM ".$this->table." AS node,".$this->table." AS parent WHERE node.left_key BETWEEN parent.left_key AND parent.right_key "
        ."GROUP BY node.id HAVING COUNT(parent.name)-1!=level ORDER BY node.left_key";
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()!=0)
        {
            return $result->fetchAll();
        }		
        return FALSE;
    }
    
    /**
     * Очищает дерево и инициализирует его.
     * @param array $extrafields - массив с перечисленными полями для добавления в таблицу
     * @return boolean
     */
    public function clear($extrafields)
    {
        //Сбрасываем настройки и очищаем таблицу.
        $sql="TRUNCATE ".$this->table;
        Syo_Db_Pdo::getInstance()->query($sql);
        $sql="DELETE FROM ".$this->table;
        //Инициализируем дерево.
        Syo_Db_Pdo::getInstance()->query($sql);
        if ($this->createRootNode($extrafields))
        {
            return FALSE;            
        }
        return TRUE;
    }
}
?>