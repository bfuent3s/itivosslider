<?php
/**
 * @author    Itivos Team <info@itivos.com>
 * @license MIT License Copyright (c) 2022 itivos Teams
 */

class ItivosSliderAjaxModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function initContent()
    {   
        parent::initContent();
        $itivosSlider = new ItivosSlider();
        $order = array();
        if(!Tools::isSubmit('secure_key') || 
            Tools::getValue('secure_key') != $itivosSlider->secure_key || 
            !Tools::getValue('action')){
        }
        $order = array();
        if (Tools::getValue('action') == 'updateSliderlist' && Tools::getValue('order')){
            $order = Tools::getValue('order');
            foreach ($order as $position => $id_category)
            {
                $position = (int)$position +1;
                itivosSliderC::updateOrder($id_category, $position);
            }
            $itivosSlider->clearCache();
        }
    }
}

