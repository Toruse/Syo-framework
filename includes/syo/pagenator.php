<?php
/**
 * Класс для генерации параметров нумерации страниц.
 */
class Syo_Pagenator
{
    /**
     * Список параметров для генерации страниц
     * @var array
     */
    private $param=array();
    /**
     * Количество страниц для показа в блоке
     * @var integer
     */
    private $maxblock=100000;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        //Устанавливаем параметры по умолчанию
        //Количество страниц слева
        $this->param['block']['left']=0;
        //Количество страниц в центре, так же данная переменная может выступать общим количеством страниц
        $this->param['block']['center']=$this->maxblock;
        //Количество страниц справа
        $this->param['block']['right']=0;
        //Активная страница
        $this->param['current']=1;
        //Массив - содержащий страницы слева, по центру и справа.
        $this->param['pages']=array();
    }
    
    /**
     * Устанавливает параметры пагинатора
     * @param array $param - cписок параметров, сгенерированный пользователем
     * @return \Syo_Pagenator
     */
    public function setParam($param)
    {
        $this->param=array_merge($this->param,$param);
        return $this;
    }
    
    /**
     * Устанавливает общее количество строк в «списке»
     * @param number $total
     * @return \Syo_Pagenator
     */
    public function setTotal($total)
    {
        $this->param['total_rows']=$total;
        return $this; 
    }

    /**
     * Устанавливает количество строк на одной странице
     * @param number $list
     * @return \Syo_Pagenator
     */
    public function setCountListed($list)
    {
        $this->param['count_rows']=$list;
        return $this; 
    }

    /**
     * Указывает активную страницу
     * @param number $page
     * @return \Syo_Pagenator
     */
    public function setPage($page)
    {
        $this->param['current']=$page;
        return $this; 
    }

    /**
     * Устанавливает количество страниц с лева и справа относительно активной странице
     * @param type $block
     * @return \Syo_Pagenator
     */
    public function setBlock($block=null)
    {
        if (is_null($block))
        {
            $this->param['block']['center']=$this->maxblock;            
        }
        else
        {
            $this->param['block']['center']=$block;
        }
        return $this; 
    }

    /**
     * Устанавливает количество страниц с лева
     * @param number $block
     * @return \Syo_Pagenator
     */
    public function setBlockLeft($block=null)
    {
        if (is_null($block))
        {
            $this->param['block']['left']=$this->maxblock;            
        }
        else
        {
            $this->param['block']['left']=$block;
        }
        return $this; 
    }

    /**
     * Устанавливает количество страниц справа
     * @param number $block
     * @return \Syo_Pagenator
     */
    public function setBlockRight($block=null)
    {
        if (is_null($block))
        {
            $this->param['block']['right']=$this->maxblock;            
        }
        else
        {
            $this->param['block']['right']=$block;
        }
        return $this; 
    }

    /**
     * Одновременно устанавливает количество страниц с лева и справа
     * @param number $block
     * @return \Syo_Pagenator
     */
    public function setBlockLeftRight($block=null)
    {
        if (is_null($block))
        {
            $this->param['block']['left']=$this->maxblock;            
            $this->param['block']['right']=$this->maxblock;            
        }
        else
        {
            $this->param['block']['left']=$block;            
            $this->param['block']['right']=$block;
        }
        return $this; 
    }

    /**
     * Возвращает сгенерированные параметры для нумерации страниц
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Возвращает номер следующей страницы
     * @return number
     */
    public function getNext()
    {
        return $this->param['next'];
    }

    /**
     * Возвращает номер предыдущей страницы
     * @return number
     */
    public function getPrevious()
    {
        return $this->param['previous'];
    }

    /**
     * Возвращает значение последней страницы
     * @return number
     */
    public function getLast()
    {
        return $this->param['last'];
    }

    /**
     * Возвращает номер текущей  страницы
     * @return number
     */
    public function getPage()
    {
        return $this->param['current'];
    }

    /**
     * Возвращает список страниц или список страниц находящийся в центре блока
     * @return array
     */
    public function getListPages()
    {
        return $this->param['pages']['center'];
    }

    /**
     * Возвращает полный список страниц с учётом левого, центрально и правого списка страниц
     * @param string $separator
     * @return array
     */
    public function getListPagesAll($separator=null)
    {
        //Инициализируем массив в котором будет хранить полный список страниц
        $result=array();
        //Определяем нужно ли добавлять разделитель в список
        if (is_null($separator))
        {
            //Объединяем три массива со списком страниц в один
            $result=array_merge($this->param['pages']['left'],$this->param['pages']['center'],$this->param['pages']['right']);
        }
        else
        {
            //Объединяем три массива со списком страниц в один с учётом разделителя, и так же удаляем одинаковые страницы из списка.
            $result=$this->param['pages']['left'];
            $count=count($result);
            if (($count) && ($result[$count-1]!=($this->param['pages']['center'][0]-1))) $result[]=$separator;
            $result=array_merge($result,$this->param['pages']['center']);
            $count=count($result);
            if ((count($this->param['pages']['right'])) && ($result[$count-1]!=($this->param['pages']['right'][0]-1))) $result[]=$separator;
            $result=array_merge($result,$this->param['pages']['right']);
        }
        //Возвращаем результат
        return $result; 
    }

    /**
     * На основе указанных параметров, вычисляет основные параметры для отображения списка страниц
     * @return \Syo_Pagenator
     */
    public function Calculate()
    {
        //Определяем количество страниц
        $last_page=ceil($this->param['total_rows']/$this->param['count_rows']);
        //Определяем корректность указанной страницы
        $page_num=$this->param['current'];
        if ($page_num<1)
        {
           $page_num=1;
        } 
        elseif ($page_num>$last_page)
        {
           $page_num=$last_page;
        }
        //Определяем после, какой записи в базе берём данные
        $upto=($page_num-1)*$this->param['count_rows'];
        $this->param['limit']['count']=$upto;
        //Указываем сколько нужно взять записей из базы данных
        $this->param['limit']['offset']=$this->param['count_rows'];
        //Указываем активную страницу
        $this->param['current']=$page_num;
        //Указываем предыдущую и следующую страницу
        if ($page_num==1) $this->param['previous']=$page_num; else $this->param['previous']=$page_num-1;
        if ($page_num==$last_page) $this->param['next']=$last_page; else $this->param['next']=$page_num+1;
        //Указываем последнюю страницу
        $this->param['last']=$last_page;
        //Генерируем списки страниц
        $this->param['pages']['center']=$this->calcListPages($this->param['current'],$this->param['block']['center']);
        $this->param['pages']['left']=$this->calcListPages(1,$this->param['block']['left']);
        $this->param['pages']['left']=array_diff($this->param['pages']['left'],$this->param['pages']['center']);
        $this->param['pages']['right']=$this->calcListPages($this->param['last'],$this->param['block']['right']);
        //Сбрасываем ключи массива
        $tmparr=array_diff($this->param['pages']['right'],$this->param['pages']['center']);
        $tmparr=array_values($tmparr);
        $this->param['pages']['right']=$tmparr;
        return $this;
    }

    /**
     * Генерирует список страниц
     * @param number $page - активная страница
     * @param number $block - количество страниц с лева и справа
     * @return array
     */
    private function calcListPages($page,$block)
    {
        //Инициализируем массив, в который поместим список страниц
        $pagelist=array();
        //Указываем количество страниц слева и справа
        $show=$block;
        //Активная страница в начале списка страниц, генерируем страницы только слева
        if ($page==1)
        {
                if ($this->param['next']==$page) return array(1);
                for ($i=0;$i<$show;$i++)
                {
                        if ($i==$this->param['last']) break;
                        array_push($pagelist,$i+1);
                }
                return $pagelist;
        }
        //Активная страница в конце списка страниц, генерируем страницы только справа
        if ($page==$this->param['last'])
        {
                $start=$this->param['last']-$show;
                if ($start<1) $start=0;
                for ($i=$start;$i<$this->param['last'];$i++)
                {
                        array_push($pagelist,$i+1);
                }
                return $pagelist;
        }
        //Страница находиться в средине списка, генерируем страницы слева и справа
        $start=$page-$show;
        if ($start<1) $start=0;
        for ($i=$start;$i<$page;$i++)
        {
                array_push($pagelist,$i+1);
        }
        for ($i=($page+1);$i<($page+$show);$i++)
        {
                if ($i==($this->param['last']+1)) break;
                array_push($pagelist,$i);
        }
        return $pagelist;
    }
}
?>