<?php
/**
 * Класс для генерации проверочного кода (captcha).
 */
class Module_Captcha_Text 
{
    /**
     * Константы указывающие, какие использовать символы для генерации 
     * проверочного кода.
     */
    const TYPE_CASE_LOWER=0;
    const TYPE_CASE_UPPER=1;
    const TYPE_CASE_NUMERIC=2;
    const TYPE_CASE_UPPER_LOWER=3;
    const TYPE_CASE_LOWER_NUMERIC=4;
    const TYPE_CASE_UPPER_NUMERIC=5;
    const TYPE_CASE_UPPER_LOWER_NUMERIC=6;
    
    /**
     * Константы указывающие, какие применить графические эффекты к тексту.
     */
    const EFFECT_TEXT_NONE=0;
    const EFFECT_TEXT_COLOR=1;
    const EFFECT_TEXT_ANGLE=2;
    const EFFECT_TEXT_SIZE=4;
    const EFFECT_TEXT_SPACING=8;
    const EFFECT_TEXT_SPACING_NOT=16;
    const EFFECT_TEXT_ALL=31;

    /**
     * Константы указывающие, какие применить графические эффекты 
     * к изображению проверочного кода.
     */
    const EFFECT_IMAGE_NONE=0;
    const EFFECT_IMAGE_CHAR=1;
    const EFFECT_IMAGE_LINE_BEFORE=2;
    const EFFECT_IMAGE_LINE_AFTER=4;
    const EFFECT_IMAGE_NOISE_COLOR=8;
    const EFFECT_IMAGE_NOISE_PLAIN=16;
    const EFFECT_IMAGE_RECTANGLE=32;
    const EFFECT_IMAGE_MULTI_WAVE=64;
    const EFFECT_IMAGE_TWIST=128;
    const EFFECT_IMAGE_ALL=255;
    
    /**
     * Хранит настройки.
     * @var array
     */
    private $config=array(
        //Параметры шрифта
        'font'=>array(
            //Путь к файлу шрифта
            'path'=>'fonts/captcha.ttf',
            //Размер
            'size'=>20,
            //Цвет
            'color'=>'000000',
            //Диапазон колебания цвета текста
            'color_range'=>255,
            //Хранит список эффектов для текста
            'effect'=>0
        ),
        //Параметры кода
        'code'=>array(
            //Какие символы использовать в коде
            'typeCase'=>4,
            //Количество символов в коде
            'length'=>4,
        ),
        //Параметры для генерации изображения
        'image'=>array(
            //Расширение изображения
            'type'=>'png',
            //Фон
            'bgcolor'=>'FFFFFF',
            //Отступ от краёв
            'padding'=>15,
            //Хранит список эффектов для изображения
            'effect'=>0
        ),
        //Имя сессии для хранения кода
        'sessionName'=>'captcha',
    );
    
    /**
     * Хранит сгенерированный код
     * @var string
     */
    private $code=NULL;
    
    /**
     * Хранит ширину изображения
     * @var integer 
     */
    private $width=0;
    
    /**
     * Хранит высоту изображения
     * @var integer
     */
    private $height=0;

    /**
     * Конструктор.
     */
    public function __construct()
    {
        //Переводим цвет шрифта из строки в байты
        $this->config['font']['color_rgb']=array(
            hexdec(substr($this->getFontColor(),0,2)),
            hexdec(substr($this->getFontColor(),2,2)),
            hexdec(substr($this->getFontColor(),4,2))
        );
        //Переводим цвет фона изображения из строки в байты
        $this->config['image']['bgcolor_rgb']=array(
            hexdec(substr($this->getImageBgColor(),0,2)),
            hexdec(substr($this->getImageBgColor(),2,2)),
            hexdec(substr($this->getImageBgColor(),4,2))
        );
    }

    /**
     * Генерирует заголовки.
     */
    private function sendHeader()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT"); 
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0",false);
        switch ($this->getTypeImage())
        {
            case 'jpeg': 
                header('Content-type: image/jpeg'); 
            break;
            case 'png':
                header('Content-type: image/png');
            break;
            case 'gif': 
                header('Content-type: image/gif');
            break;
            default: 
                header('Content-type: image/png');
            break;
        }
    }
    
    /**
     * Генерирует проверочный код.
     * @return string
     */
    private function generateRandomString()
    {
        //Создаём массивы символов
        $uppercase=range('A','Z');
        $lowercase=range('a','z');
        $numeric=range(0,9);
        $chars='';
        
        //В зависимости от параметра, 
        //создаём строку с символами для генерации кода
        switch ($this->getTypeCase())
        {
            case self::TYPE_CASE_LOWER: 
                $chars=implode('',$lowercase); 
            break;
            case self::TYPE_CASE_UPPER: 
                $chars=implode('',$lowercase); 
            break;
            case self::TYPE_CASE_NUMERIC:
                $chars=implode('',$numeric); 
            break;
            case self::TYPE_CASE_UPPER_LOWER: 
                $chars=implode('',$uppercase).implode('',$lowercase); 
            break;
            case self::TYPE_CASE_LOWER_NUMERIC:
                $chars=implode('',$lowercase).implode('',$numeric); 
            break;
            case self::TYPE_CASE_UPPER_NUMERIC:
                $chars=implode('',$uppercase).implode('',$numeric); 
            break;
            case self::TYPE_CASE_UPPER_LOWER_NUMERIC:
                $chars=implode('',$uppercase).implode('',$lowercase).implode('',$numeric); 
            break;
            default:
                $chars=implode('',$lowercase).implode('',$numeric); 
            break;
        }
        
        //Генерируем кода, выбирая из строки случайные символы
        $length=strlen($chars)-1;
        for ($i=0;$i<$this->config['code']['length'];$i++)
        {
            $this->code.=$chars[mt_rand(0,$length)];
        }
        return $this->code;
    }
    
    /**
     * Создает изображение проверочного кода.
     */
    public function createCaptcha()
    {
        //Определяем размеры строки для изображения
        $sizeImageFont=$this->getFontImageSize();
        //Получаем параметры для создания изображения
        $padding=$this->getImagePadding();
        $width=$sizeImageFont['width']+$padding*2;
        $height=$sizeImageFont['height']+$padding*2;
        $this->width=$width;
        $this->height=$height;
        $x=$sizeImageFont['x']+$padding;
        $y=$sizeImageFont['y']+$padding;

        //Создаём изображение
        $image=imagecreatetruecolor($width,$height);
        if ($image)
        {
            //Получаем цвет фона
            $bgcolor=imagecolorallocate(
                $image,
                $this->config['image']['bgcolor_rgb'][0],
                $this->config['image']['bgcolor_rgb'][1],
                $this->config['image']['bgcolor_rgb'][2]
            );
            //Закрашиваем изображение
            imagefill($image,0,0,$bgcolor);

            //Применяем эффекты к изображению перед выводом текста
            $this->BeforeImageEffect($image);
            //Выводим текст
            $this->ImageTtfText($image,$x,$y);
            //Применяем эффекты к изображению после вывода текста
            $this->AfterImageEffect($image);
            
            //Выводим изображение
            switch($this->getTypeImage())
            {
                case 'jpeg': 
                    imagejpeg($image,NULL,100);
                break;
                case 'png':
                    imagepng($image);
                break;
                case 'gif':
                    imagegif($image);
                break;
                default:
                    imagepng($image);
                break;
            }
            imagedestroy($image);
            //Сохраняем код в сессию
            $sessionName=$this->config['sessionName'];
            Syo_Session::getInstance()->$sessionName=sha1($this->code);
        }
    }
    
    /**
     * Размешает текст на изображении
     * @param id $image - изображение
     * @param integer $x - x-координата
     * @param integer $y - y-координата
     */
    private function ImageTtfText($image,$x,$y)
    {
        //Печатаем каждый символ отдельно
        $font=$this->getPathFont();
        for($i=0;$i<$this->getLengthCode();$i++)
        {
            //Получаем размер шрифта
            $size=$this->getFontImageSize($i);
            //Применяем эффект изменения цвета символов
            $stringcolor=$this->applyFontEffectColor($image);
            //Применяем эффект разного отступа между символами
            $spacing=$this->applyFontEffectNotSpacing();
            $spacing=$this->applyFontEffectSpacing();
            //Печатаем шрифт на изображении, применяя 
            //эффект случайного размера символа, и случайного угла поворота
            imagettftext($image,$this->applyFontEffectSize(),$this->applyFontEffectAngle(),$x,$y,$stringcolor,$font,$this->code[$i]);
            $x+=$spacing+$size['width'];
        }
    }
    
    /**
     * Применяет эффекты к рисунку перед выводом текста
     * @param id $image - изображение
     */
    private function BeforeImageEffect($image)
    {
        //Применяем фоновые эффекты
        $this->ImageEffectBackground($image);
        //Эффект квадратов на фоне
        if ($this->getImageEffect() & self::EFFECT_IMAGE_RECTANGLE)
            $this->applyImageEffectRectangle($image);
        //Эффект символы на фоне
        if ($this->getImageEffect() & self::EFFECT_IMAGE_CHAR)
            $this->applyImageEffectChar($image);
        //Эффект линии на фоне
        if ($this->getImageEffect() & self::EFFECT_IMAGE_LINE_BEFORE)
            $this->applyImageEffectLine($image);
    }

    /**
     * Применяет эффекты к рисунку после вывода текста
     * @param id $image - изображение
     */
    private function AfterImageEffect($image)
    {
        //Эффект линии 
        if ($this->getImageEffect() & self::EFFECT_IMAGE_LINE_AFTER)
            $this->applyImageEffectLine($image); 
        //Эффект волны
        if ($this->getImageEffect() & self::EFFECT_IMAGE_MULTI_WAVE)
            $this->applyImageEffectMultiWave($image);    
        //Эффект скручивания
        if ($this->getImageEffect() & self::EFFECT_IMAGE_TWIST)
            $this->applyImageEffectTwist($image);    
    }

    /**
     * Применяет фоновые эффекты
     * @param id $image - изображение
     */
    private function ImageEffectBackground($image)
    {
        //Эффекта шума
        if ($this->getImageEffect() & self::EFFECT_IMAGE_NOISE_COLOR)
            $this->applyImageEffectNoiseColor($image);
        //Эффекта чёрно-белого шума
        if ($this->getImageEffect() & self::EFFECT_IMAGE_NOISE_PLAIN)
            $this->applyImageEffectNoisePlain($image);        
    }        
     
    /**
     * Генерирует случайный цвет для шрифта
     * @param id $image - изображение
     * @return integer
     */
    private function applyFontEffectColor($image)
    {
        if ($this->getFontEffect() & self::EFFECT_TEXT_COLOR)
        {
            //Определяем диапазон колебания цвета и генерируем случайный цвет
            $r_min=$this->config['font']['color_rgb'][0]-$this->config['font']['color_range'];
            $r_max=$this->config['font']['color_rgb'][0]+$this->config['font']['color_range'];
            if ($r_min<0) $r_min=0;
            if ($r_max>255) $r_max=255;
            $g_min=$this->config['font']['color_rgb'][1]-$this->config['font']['color_range'];
            $g_max=$this->config['font']['color_rgb'][1]+$this->config['font']['color_range'];
            if ($g_min<0) $g_min=0;
            if ($g_max>255) $g_max=255;
            $b_min=$this->config['font']['color_rgb'][2]-$this->config['font']['color_range'];
            $b_max=$this->config['font']['color_rgb'][2]+$this->config['font']['color_range'];
            if ($b_min<0) $b_min=0;
            if ($b_max>255) $b_max=255;
            //Выводим полученный цвет
            return imagecolorallocatealpha(
                    $image,
                    mt_rand($r_min,$r_max),
                    mt_rand($g_min,$g_max),
                    mt_rand($b_min,$b_max),
                    mt_rand(0,30)
            ); 
        }
        else
        {
            //Эффект отключен. Выводим установленный цвет для текста.
            return imagecolorallocate(
                $image,
                $this->config['font']['color_rgb'][0],
                $this->config['font']['color_rgb'][1],
                $this->config['font']['color_rgb'][2]
            );
        }
    }
    
    /**
     * Генерирует случайный угол поворота для текста.
     * @return integer
     */
    private function applyFontEffectAngle()
    {
        if ($this->getFontEffect() & self::EFFECT_TEXT_ANGLE)
            return mt_rand(0,30)-15; 
        else
            return 0;
    }
    
    /**
     * Генерирует случайный размер для текста.
     * @return integer
     */
    private function applyFontEffectSize()
    {
        //Проверяет включён ли данный эффект.
        //Эффект не работает если в строке присутствуют символы верхнего региста.
        if (
                ($this->getFontEffect() & self::EFFECT_TEXT_SIZE) && 
                ($this->getTypeCase()!=self::TYPE_CASE_UPPER) &&
                ($this->getTypeCase()!=self::TYPE_CASE_UPPER_LOWER) &&
                ($this->getTypeCase()!=self::TYPE_CASE_UPPER_NUMERIC) &&
                ($this->getTypeCase()!=self::TYPE_CASE_UPPER_LOWER_NUMERIC)
        )
        {
            $size_min=$this->getFontSize()-20;
            if ($size_min<12) $size_min=12;
            return mt_rand($size_min,$this->getFontSize()); 
        }
        else
        {
            return $this->getFontSize();
        }
    }


    /**
     * Генерирует случайный размер отступа между символами.
     * @return integer
     */
    private function applyFontEffectSpacing()
    {
        if (($this->getFontEffect() & self::EFFECT_TEXT_SPACING))
        {
            $sp_max=ceil($this->getFontSize()/4);
            return mt_rand(0,$sp_max)-$sp_max; 
        }
        else
        {
            return 3;
        }
    }

    /**
     * Генерирует отрицательный отступ для склеивания символов кода.
     * @return integer
     */
    private function applyFontEffectNotSpacing()
    {
        if (($this->getFontEffect() & self::EFFECT_TEXT_SPACING_NOT))
        {
            $sp_max=ceil($this->getFontSize()/20);
            return -$sp_max; 
        }
        else
        {
            return 3;
        }
    }
    
    /**
     * Применяет эффект волны к изображению
     * @param id $image - изображение
     */
    private function applyImageEffectMultiWave($image)
    {
        //Создаём дублирующее изображение
        $width=$this->width;
        $height=$this->height;
        $tmp_img=imagecreatetruecolor($width,$height);
        
        if ($tmp_img)
        {   
            //Устанавливаем цвет фона для дублирующего изображения
            $bgcolor=imagecolorallocate(
                $tmp_img,
                $this->config['image']['bgcolor_rgb'][0],
                $this->config['image']['bgcolor_rgb'][1],
                $this->config['image']['bgcolor_rgb'][2]
            );
            //Закрашиваем фон
            imagefill($tmp_img,0,0,$bgcolor);
            //Указываем прозрачный цвет
            imagecolortransparent($tmp_img,$bgcolor);
            
            //Генерируем параметры эффекта
            //частоты
            $rand1=mt_rand(700,1000)/15000;
            $rand2=mt_rand(700,1000)/15000;
            $rand3=mt_rand(700,1000)/15000;
            $rand4=mt_rand(700,1000)/15000;
            //фазы
            $rand5=mt_rand(0,3141592)/100000;
            $rand6=mt_rand(0,3141592)/100000;
            $rand7=mt_rand(0,3141592)/100000;
            $rand8=mt_rand(0,3141592)/100000;
            //амплитуды
            $rand9=mt_rand(400,600)/95;
            $rand10=mt_rand(400,600)/95;

            //Перебираем каждую точку изображения
            for($x=0;$x<$width;$x++)
            {
                for($y=0;$y<$height;$y++)
                {
                    // координаты пикселя-первообраза.
                    $sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9;
                    $sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;
                    //Устанавливаем цвет пикселя
                    if (!(($sx<0) || ($sy<0) || ($sx>=$width-1) || ($sy>=$height-1)))
                    { 
                        $color=imagecolorat($image, $sx, $sy);
                    }
                    else
                    {
                        $color=$bgcolor;
                    }   
                    //Рисуем пиксель
                    imagesetpixel($tmp_img,$x,$y,$color); 
              }
            }
            //Очищаем основной рисунок
            imagefilledrectangle($image,0,0,$width,$height,$bgcolor);
            //Применяем заново фоновые эффекты
            $this->ImageEffectBackground($image);
            //Переносим сгенерированное изображение на основное изображение
            imagecopymerge($image,$tmp_img,0,0,0,0,$width,$height,100);
            imagedestroy($tmp_img);
        }
    }

    /**
     * Применяет эффект закручивания к изображению
     * @param id $image - изображение
     */
    private function applyImageEffectTwist($image)
    {
        //Создаём дублирующее изображение
        $width=$this->width;
        $height=$this->height;
        $tmp_img=imagecreatetruecolor($width,$height);
        
        if ($tmp_img)
        {   
            //Устанавливаем цвет фона для дублирующего изображения
            $bgcolor=imagecolorallocate(
                $tmp_img,
                $this->config['image']['bgcolor_rgb'][0],
                $this->config['image']['bgcolor_rgb'][1],
                $this->config['image']['bgcolor_rgb'][2]
            );
            //Закрашиваем фон
            imagefill($tmp_img,0,0,$bgcolor);
            //Указываем прозрачный цвет
            imagecolortransparent($tmp_img,$bgcolor);
         
            //Генерируем параметры эффекта
            //Указываем центр закручивания
            $cx=$width/2;
            $cy=$height/2;
            $PI=3.1415;
            //Фактор
            $factor=mt_rand(15,25)/1000;
            
            //Перебираем каждую точку изображения
            for($x=0;$x<$width;$x++)
            {
                $rx=$cx-$x;
                for($y=0;$y<$height;$y++)
                {
                    $ry=$cy-$y;
                    $originalAngle=0;
                    //Вычисляем угол закручивания
                    if ($rx!=0)
                    {
                        $originalAngle=atan(abs($ry)/abs($rx));
                        if ($rx<=0 && $ry>=0) 
                            $originalAngle=2*$PI-$originalAngle;
                        elseif ($rx>0 && $ry<0) 
                            $originalAngle=$PI-$originalAngle;
                        elseif ($rx>0 && $ry>=0) 
                            $originalAngle+=$PI; 
                    }
                    else
                    {
                        if ($ry<=0) 
                            $originalAngle=0.5*$PI;
                        else 
                            $originalAngle=1.5*$PI;
                    }
                    $originalAngle=$originalAngle-1;
                    $radius=sqrt($rx*$rx+$ry*$ry);
                    $newAngle=$originalAngle+$factor*$radius;	// a progressive twist
                    //$newAngle=$originalAngle+1/($factor*$radius+(4.0/$PI)); 	// a progressive Swirl
                    
                    //Вычисляем новые координаты точки
                    $sx=floor($radius*cos($newAngle)+0.5);
                    $sy=floor($radius*sin($newAngle)+0.5);
                    $sx+=$cx;
                    $sy+=$cy;
                    
                    //Устанавливаем цвет пикселя
                    if (!(($sx<0) || ($sy<0) || ($sx>=$width-1) || ($sy>=$height-1)))
                    { 
                        $color=imagecolorat($image,$sx,$sy);
                    }
                    else
                    {
                        $color=$bgcolor;
                    }
                    //Рисуем пиксель
                    imagesetpixel($tmp_img,$x,$y,$color); 
              }
            }
            //Очищаем основной рисунок
            imagefilledrectangle($image,0,0,$width,$height,$bgcolor);
            //Применяем заново фоновые эффекты
            $this->ImageEffectBackground($image);
            //Переносим сгенерированное изображение на основное изображение
            imagecopymerge($image,$tmp_img,0,0,0,0,$width,$height,100);
            imagedestroy($tmp_img);
        }
    }
    
    /**
     * Генерирует цветовые помехи на фоне изображения
     * @param id $image - изображение
     */
    private function applyImageEffectNoiseColor($image)
    {
        $width=$this->width;
        $height=$this->height;
        //Проходим по каждой точке изображения
        for($x=0;$x<$width;$x++)
        {
            for($y=0;$y<$height;$y++)
            {
                //Генерируем случайный цвет
                $color=imagecolorallocate(
                    $image,
                    mt_rand(0,255),
                    mt_rand(0,255),
                    mt_rand(0,255)
                );
                //Рисуем пиксель
                imagesetpixel($image,$x,$y,$color); 
          }
        }
    }
    
    /**
     * Генерирует чёрно-белые помехи на фоне изображения
     * @param id $image - изображение
     */
    private function applyImageEffectNoisePlain($image)
    {
        $width=$this->width;
        $height=$this->height;
        //Проходим по каждой точке изображения
        for($x=0;$x<$width;$x++)
        {
            for($y=0;$y<$height;$y++)
            {
                //Генерируем случайный цвет
                $indexColor=mt_rand(100,255);
                $color=imagecolorallocate(
                    $image,
                    $indexColor,
                    $indexColor,
                    $indexColor
                );
                //Рисуем пиксель
                imagesetpixel($image,$x,$y,$color); 
          }
        }
    }
    
    /**
     * Генерирует линии на изображении
     * @param id $image - изображение
     */
    private function applyImageEffectLine($image)
    {
        $width=$this->width;
        $height=$this->height;
        //Генерируем случайное количество линий
        $countLine=mt_rand(3,5);
        //Рисуем линии
        for ($i=0;$i<$countLine;$i++)
        {
            $color=imagecolorallocate($image,mt_rand(100,200),mt_rand(100,200),mt_rand(100,200));
            imageline($image,mt_rand(0,20),mt_rand(1,$height),mt_rand(ceil($width/2),$width),mt_rand(1,$height),$color);
        }        
    }
    
    /**
     * Генерирует прямоугольники на изображении
     * @param id $image - изображение
     */
    private function applyImageEffectRectangle($image)
    {
        $width=$this->width;
        $height=$this->height;
        //Генерируем случайное количество прямоугольников
        $countRectangle=mt_rand(5,20);
        //Рисуем прямоугольники
        for ($i=0;$i<$countRectangle;$i++)
        {
            $color=imagecolorallocate($image,mt_rand(100,200),mt_rand(100,200),mt_rand(100,200));
            $colorLine=imagecolorallocate($image,mt_rand(100,200),mt_rand(100,200),mt_rand(100,200));
            imagefilledrectangle($image,mt_rand(1,$width),mt_rand(1,$height),mt_rand(1,$width),mt_rand(1,$height),$color);
            imagerectangle($image,mt_rand(1,$width),mt_rand(1,$height),mt_rand(1,$width),mt_rand(1,$height),$colorLine);
        }        
    }
    
    /**
     * Генерирует символы на фоне изображения
     * @param id $image - изображение
     */
    private function applyImageEffectChar($image)
    {
        $width=$this->width;
        $height=$this->height;
        //Генерируем количество случайных символов
        $countChar=mt_rand(3,5);
        //Символы для вывода
        $letters=array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','2','3','4','5','6','7','9');
        //Рисуем символы
        for($i=0;$i<$countChar;$i++)
        {
            //Случайный цвет
            $indexColor=mt_rand(0,100);
            $color=imagecolorallocatealpha($image,$indexColor,$indexColor,$indexColor,100); 
            //Случайный символ
            $letter=$letters[mt_rand(0,sizeof($letters)-1)];
            //Случайный размер
            $size=mt_rand(0,ceil($this->getFontSize()/2));
            imagettftext($image,$size,rand(35,80),mt_rand($width*0.1,$width),mt_rand($height*0.2,$height),$color,$this->getPathFont(),$letter);
        }
    }
    
    /**
     * Получает размер строки на изображении
     * @param integer $index - индекс символа в строке
     * @return array
     */
    private function getFontImageSize($index=NULL)
    {
        //Получаем массив параметров
        if (is_null($index))
            $box=imagettfbbox($this->getFontSize(),0,$this->getPathFont(),$this->code);
        else
            $box=imagettfbbox($this->getFontSize(),0,$this->getPathFont(),$this->code[$index]);
        //Переводим в формат данного класса
        $minX=min(array($box[0],$box[2],$box[4],$box[6]));
        $maxX=max(array($box[0],$box[2],$box[4],$box[6]));
        $minY=min(array($box[1],$box[3],$box[5],$box[7]));
        $maxY=max(array($box[1],$box[3],$box[5],$box[7]));
        return array(
            "x"=>abs($minX)-1,
            "y"=>abs($minY)-1,
            "width"=>$maxX-$minX,
            "height"=>$maxY-$minY,
        ); 
    }
    
    /**
     * Выполняет вывод проверочного кода для пользователя
     */
    public function render()
    {
        //Генерируем код
        $this->generateRandomString();
        //Генерируем заголовки
        $this->sendHeader();
        //Генерируем изображение кода
        $this->createCaptcha();
    }
    
    /**
     * Перегрузка метода привидение к строке
     * @return string
     */
    public function __toString()
    {
        $this->render();
        return '';
    }
    
    /**
     * Выполняет проверку кода на соответствие с генерированным кодам.
     * @param string $code - код для проверки
     * @param string $sessionName - имя проверочного кода в сессии
     * @return boolean
     */
    public static function check($code,$sessionName='captcha')
    {
        if (isset(Syo_Session::getInstance()->$sessionName))
        {
            if (Syo_Session::getInstance()->$sessionName==sha1($code))
            {
                unset(Syo_Session::getInstance()->$sessionName);
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Выводит полный путь к файлу шрифта
     * @return string
     */
    private function getPathFont()
    {
        return SITEPATH.PATHPUBLIC.DIRSEP.$this->config['font']['path'];
    }
    
    /**
     * Возвращает размер шрифта
     * @return integer
     */
    public function getFontSize()
    {
        return $this->config['font']['size'];
    }

    /**
     * Устанавливает размер шрифта.
     * @param integer $fontSize - размер шрифта
     * @return Module_Captcha_Text
     */
    public function setFontSize($fontSize=16)
    {
        $this->config['font']['size']=$fontSize;
        return $this;
    }
    
    /**
     * Возвращает имя проверочного кода в сессии.
     * @return string
     */
    public function getSessionName()
    {
        return $this->config['sessionName'];
    }

    /**
     * Устанавливает имя проверочного кода в сессии.
     * @param string $sessionName - имя проверочного кода
     * @return Module_Captcha_Text
     */
    public function setSessionName($sessionName)
    {
        $this->config['sessionName']=$sessionName;
        return $this;
    }
    
    /**
     * Возвращает тип генерации проверочного кода.
     * @return integer
     */
    public function getTypeCase()
    {
        return $this->config['code']['typeCase'];
    }

    /**
     * Устанавливает тип генерации проверочного кода.
     * @param integer $typeCase - тип кода
     * @return Module_Captcha_Text
     */
    public function setTypeCase($typeCase=4)
    {
        $this->config['code']['typeCase']=$typeCase;
        return $this;
    }
    
    /**
     * Возвращает длину кода.
     * @return integer
     */
    public function getLengthCode()
    {
        return $this->config['code']['length'];
    }

    /**
     * Устанавливает длину кода.
     * @param integer $length - длина кода
     * @return Module_Captcha_Text
     */
    public function setLengthCode($length=4)
    {
        $this->config['code']['length']=$length;
        return $this;
    }

    /**
     * Возвращает расширение изображения.
     * @return string
     */
    public function getTypeImage()
    {
        return $this->config['image']['type'];
    }

    /**
     * Устанавливает расширение изображения.
     * @param string $type - расширение изображения
     * @return Module_Captcha_Text
     */
    public function setTypeImage($type='png')
    {
        $this->config['image']['type']=$type;
        return $this;
    }

    /**
     * Возвращает цвет фона.
     * @return string
     */
    public function getImageBgColor()
    {
        return $this->config['image']['bgcolor'];
    }

    /**
     * Устанавливает цвет фона.
     * @param string $bgcolor - цвет фона
     * @return Module_Captcha_Text
     */
    public function setImageBgColor($bgcolor='FFFFFF')
    {
        $this->config['image']['bgcolor']=$bgcolor;
        $this->config['image']['bgcolor_rgb']=array(
            hexdec(substr($this->getImageBgColor(),0,2)),
            hexdec(substr($this->getImageBgColor(),2,2)),
            hexdec(substr($this->getImageBgColor(),4,2))
        );
        return $this;
    }
    
    /**
     * Возвращает отступ для теста.
     * @return integer
     */
    public function getImagePadding()
    {
        return $this->config['image']['padding'];
    }

    /**
     * Устанавливает отступ для теста.
     * @param integer $padding - отступ для теста
     * @return Module_Captcha_Text
     */
    public function setImagePadding($padding=10)
    {
        $this->config['image']['padding']=$padding;
        return $this;
    }
    
    /**
     * Возвращает цвет текста.
     * @return string
     */
    public function getFontColor()
    {
        return $this->config['font']['color'];
    }

    /**
     * Устанавливает цвет текста.
     * @param string $color - цвет текста
     * @return Module_Captcha_Text
     */
    public function setFontColor($color='000000')
    {
        $this->config['font']['color']=$color;
        $this->config['font']['color_rgb']=array(
            hexdec(substr($this->getFontColor(),0,2)),
            hexdec(substr($this->getFontColor(),2,2)),
            hexdec(substr($this->getFontColor(),4,2))
        );
        return $this;
    }

    /**
     * Возвращает значение установленных эффектов для шрифта.
     * @return integer
     */
    private function getFontEffect()
    {
        return $this->config['font']['effect'];
    }

    /**
     * Устанавливает значение применяемых эффектов для шрифта.
     * @param integer $effect - значение эффектов
     * @return Module_Captcha_Text
     */
    public function setFontEffect($effect=0)
    {
        $this->config['font']['effect']=$effect;
        return $this;
    }
    
    /**
     * Возвращает значение установленных эффектов для изображения.
     * @return integer
     */
    private function getImageEffect()
    {
        return $this->config['image']['effect'];
    }

    /**
     * Устанавливает значение применяемых эффектов для изображения.
     * @param type $effect - значение эффектов
     * @return Module_Captcha_Text
     */
    public function setImageEffect($effect=0)
    {
        $this->config['image']['effect']=$effect;
        return $this;
    }
}
?>