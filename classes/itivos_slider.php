<?php
/**
 * @author  Itivos Team <info@itivos.com>
 * @copyright Since 2022 Itivos
 * @license MIT License
 */

class ItivosSliderC extends ObjectModel
{

    public $image_desktop;
    public $image_mobile;
    public $name;
    public $legend;
    public $description;
    public $id_shop;
    public $id_lang;
    public $url;
    public $order_menu;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'itivos_slider',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'order_menu' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => false),
            'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
            'description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 4000),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
            'legend' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
            'url' => array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'image_desktop' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 355),
            'image_mobile' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 355),
        )
    );

    public function __construct($id_slide = null, $id_lang = 1, $id_shop = 1)
    {
        parent::__construct($id_slide, $id_lang, $id_shop);
    }
    public function add($autodate = false, $null_values = false)
    {
        $this->order_menu = self::nextOrder();
        $res = parent::add($autodate, $null_values);
        return $res;
    }
    public static function setImage($id, $image_desktop, $imagen_mobile)
    {
        $res = true;
        $query = 'UPDATE `'._DB_PREFIX_.'itivos_slider` 
                    SET image_desktop = "'.$image_desktop.'",
                        image_mobile = "'.$imagen_mobile.'" 
                  WHERE id = '.(int)$id.'';
        $res &= Db::getInstance()->execute($query);
        return $res;
    }
    public static function delImage($imgpath)
    {
        $res = true;
        $res &=@unlink(dirname(__FILE__).'/images/'.$imgpath.'');
        return $res;
    }
    public static function nextOrder()
    {
        $data_return = "";
        $total = Db::getInstance()->executeS('SELECT COUNT(id) as total FROM `'._DB_PREFIX_.'itivos_slider`');
        if (!empty($total)) {
            $data_return = (int) $total[0]['total'];
            $data_return = $data_return+1;
        } else {
            $data_return = 1;
        }
        return $data_return;
    }
    public static function updateOrder($id, $order)
    {
        $res = true;
        $query = 'UPDATE `'._DB_PREFIX_.'itivos_slider` 
                    SET order_menu = '.(int)$order.' WHERE id = '.(int)$id.'';
        $res &= Db::getInstance()->execute($query);
    }
    public static function del($id)
    {
        $res = true;
        $res &= Db::getInstance()->execute('DELETE FROM`'._DB_PREFIX_.'itivos_slider` WHERE id = '.$id.'');
        return $res;
    }
    public static function getByLang($id_lang = 1)
    {
        $query = 'SELECT * FROM `'._DB_PREFIX_.'itivos_slider` WHERE id_lang= '.$id_lang.' order by order_menu ASC';
        return Db::getInstance()->executeS($query);
    }
    public static function getList()
    {
        return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'itivos_slider` order by order_menu ASC');
    }
}
