<?php
/**
 * @author    Itivos Team <info@itivos.com>
 * @license MIT License Copyright (c) 2022 itivos Teams
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}
include_once(dirname(__FILE__) . '/classes/itivos_slider.php');
class ItivosSlider extends Module implements WidgetInterface
{
    protected $config_form = false;
    protected $html = '';
    protected $default_show_text = 1;
    protected $default_mode = 1; //1: SLIDER ; 0: CAROUSEL
    protected $default_speed = 4500; 
    protected $postErrors = array();
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'itivosslider';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Itivos Teams';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Itivos Slider');
        $this->description = $this->l('Show a slider with image in the home.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->templateFile = 'module:itivosslider/views/templates/front/images_list.tpl';
        $this->module_key = "57b56603de07f0dc37d65891ca0fa928";

    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->installDb() &&
            $this->defaultValues() &&
            $this->installSamples() &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        return parent::uninstall() &&
               $this->uninstallDb();
    }
    public function updateConfig()
    {
        $shop_groups_list = array();
        $shops = Shop::getContextListShopID();
        $shop_context = Shop::getContext();

        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }

            $res = Configuration::updateValue('ITIVOS_SLIDER_SPEED', 
                                             (int)Tools::getValue('ITIVOS_SLIDER_SPEED'), 
                                             false, 
                                             $shop_group_id, 
                                             $shop_id);
            $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', 
                                               (int)Tools::getValue('ITIVOS_SLIDER_MODE'), 
                                               false, 
                                               $shop_group_id, 
                                               $shop_id);
            $res &= Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', 
                                               (int)Tools::getValue('ITIVOS_SLIDER_SHOW_TEXT'), 
                                               false, 
                                               $shop_group_id, 
                                               $shop_id);
        }

        /* Update global shop context if needed*/
        switch ($shop_context) {
            case Shop::CONTEXT_ALL:
                $res &= Configuration::updateValue('ITIVOS_SLIDER_SPEED', (int)Tools::getValue('ITIVOS_SLIDER_SPEED'));
                $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', (int)Tools::getValue('ITIVOS_SLIDER_MODE'));
                $res &= Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', (int)Tools::getValue('ITIVOS_SLIDER_SHOW_TEXT'));
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        $res &= Configuration::updateValue('ITIVOS_SLIDER_SPEED', (int)Tools::getValue('ITIVOS_SLIDER_SPEED'), false, $shop_group_id);
                        $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', (int)Tools::getValue('ITIVOS_SLIDER_MODE'), false, $shop_group_id);
                        $res &= Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', (int)Tools::getValue('ITIVOS_SLIDER_SHOW_TEXT'), false, $shop_group_id);
                    }
                }
                break;
            case Shop::CONTEXT_GROUP:
                if (count($shop_groups_list)) {
                    foreach ($shop_groups_list as $shop_group_id) {
                        $res &= Configuration::updateValue('ITIVOS_SLIDER_SPEED', (int)Tools::getValue('ITIVOS_SLIDER_SPEED'), false, $shop_group_id);
                        $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', (int)Tools::getValue('ITIVOS_SLIDER_MODE'), false, $shop_group_id);
                        $res &= Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', (int)Tools::getValue('ITIVOS_SLIDER_SHOW_TEXT'), false, $shop_group_id);
                    }
                }
                break;
        }
        $this->clearCache();
    }
    public function getContent()
    {
        $this->headerHTML();
        $this->html .= $this->headerHTML();
        $mode = null;
        if (Tools::isSubmit('btnSaveConfig')) {
            if ($this->postValidationConfig()) {
                $this->updateConfig();
                $this->html .= $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
            }else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError($err);
                }
            }
        }
        if (Tools::isSubmit('btnSubmitAdd')) {
            if ($this->postValidation()) {
                $this->postProcess();
                if (count($this->postErrors)) {
                    foreach ($this->postErrors as $err) {
                        $this->html .= $this->displayError($err);
                    }
                }else {
                    $this->html .= $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
                }
                $this->html .= $this->renderFormConfig();
                return $this->html .= $this->renderList();
            } else {
                if (count($this->postErrors)) {
                    foreach ($this->postErrors as $err) {
                        $this->html .= $this->displayError($err);
                    }
                }
                return $this->html .= $this->renderFormAdd();
            }
        } else {
            if (Tools::isSubmit('addSlider')) {
                $mode = 'add';
            }
            if (Tools::isSubmit('editSlider')) {
                $mode = 'edit';
            }
            if (Tools::isSubmit('btnEditSlider')) {
                $mode = "edit";
            }
            if (Tools::isSubmit('delIdSlider')) {
                if (ItivosSliderC::del(Tools::getValue('delIdSlider'))) {
                    $this->html .= $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
                } else {
                    $this->html .= $this->displayError($this->l('Error deleting the label'));
                }
                $this->html .= $this->renderFormConfig();
                return $this->html .= $this->renderList();
            }
            if (Tools::isSubmit('btnEditSlider')) {
                if ($this->postValidationUpdate()){
                    $this->postProcessUpdate();
                }else {
                    if (count($this->postErrors)) {
                        foreach ($this->postErrors as $err) {
                            $this->html .= $this->displayError($err);
                        }
                    }
                }
            }
            if ($mode == 'add') {
                if (Shop::getContext() != Shop::CONTEXT_GROUP && Shop::getContext() != Shop::CONTEXT_ALL) {
                    return $this->html .= $this->renderFormAdd();
                } else {
                    $this->html .= $this->getShopContextError(null, $mode);
                }
            }
            if ($mode == 'edit') {
                if (Shop::getContext() != Shop::CONTEXT_GROUP && Shop::getContext() != Shop::CONTEXT_ALL) {
                    return $this->html .= $this->renderFormAdd();
                } else {
                    $this->html .= $this->getShopContextError(null, $mode);
                }
            }
            if ($mode == null) {
                $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/document.tpl');
                $this->html .= $this->renderFormConfig();
                return $this->html .= $this->renderList();
            }
        }
    }
    public function headerHTML()
    {
        if (Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name) {
            return;
        }
        $uri = $this->context->link->getModuleLink($this->name, 'ajax', 
                    array('secure_key' => $this->secure_key));
        $this->context->controller->addJqueryUI('ui.sortable');
        $html = '<script type="text/javascript">
            $(function() {
                var $itivosSliderList = $("#images_slider");
                $itivosSliderList.sortable({
                    opacity: 0.6,
                    cursor: "move",
                    update: function() {
                        var order = $(this).sortable("serialize") + "&action=updateSliderlist";
                        $.post("'.$uri.'", order);
                        }
                    });
                $itivosSliderList.hover(function() {
                    $(this).css("cursor","move");
                    },
                    function() {
                    $(this).css("cursor","auto");
                });
            });
        </script>';

        return $html;
    }
    protected function postValidationConfig()
    {
        if (Tools::isSubmit('btnSaveConfig')) {
            if (!Tools::getValue('ITIVOS_SLIDER_SPEED')) {
                $this->postErrors[] = $this->l('Speed number label is required.');
            }else {
                if (!is_numeric(Tools::getValue('ITIVOS_SLIDER_SPEED'))) {
                    $this->postErrors[] = $this->l('Speed label only must be number');
                }else {
                    if (strpos(Tools::getValue('ITIVOS_SLIDER_SPEED'), ",")) {
                        $this->postErrors[] = $this->l('Speed label only must be int number');
                    }
                    if (strpos(Tools::getValue('ITIVOS_SLIDER_SPEED'), ".")) {
                        $this->postErrors[] = $this->l('Speed label only must be int number');
                    }
                }
            }
            if (count($this->postErrors) > 0) {
                return false;
            }else {
                return true;
            }
        }
    }
    protected function postValidation()
    {
      if (Tools::isSubmit('btnSubmitAdd')) {
          $imagesize = @getimagesize($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['tmp_name']);
          $imagesize2 = @getimagesize($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['tmp_name']);
          if (empty($imagesize) || 
             !in_array(
                Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array(
                    'jpg',
                    'gif',
                    'jpeg',
                    'png'
                )
            )) {
            $this->postErrors[] = $this->l('File Image Desktop empty or not valid.');
          }
          if (empty($imagesize) || 
             !in_array(
                Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array(
                    'jpg',
                    'gif',
                    'jpeg',
                    'png'
                )
            )) {
            $this->postErrors[] = $this->l('File Image Mobile empty or not valid.');
          }
          if (!Tools::getValue('ITIVOS_SLIDER_NAME')) {
              $this->postErrors[] = $this->l('Name label is required.');
          }
          if (!Tools::getValue('ITIVOS_SLIDER_URL')) {
              $this->postErrors[] = $this->l('Url label is required.');
          }
      }
      if (count($this->postErrors) > 0) {
        return false;
      }else {
        return true;
      }
    }
    protected function postValidationUpdate()
    {
      if (Tools::isSubmit('btnEditSlider')) {
          if (!Tools::getValue('ITIVOS_SLIDER_NAME')) {
              $this->postErrors[] = $this->l('Label name is required.');
          }
          if (!Tools::getValue('ITIVOS_SLIDER_URL')) {
              $this->postErrors[] = $this->l('Label url is required.');
          }
      }

      if (count($this->postErrors) > 0) {
        return false;
      }else {
        return true;
      }
    }
    protected function renderFormAdd()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        if (Tools::isSubmit('editSlider')) {
            $helper->submit_action = 'btnEditSlider';
        }else {
            $helper->submit_action = 'btnSubmitAdd';
        }
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getFormSliderValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getFormAdd()));
    }
    protected function renderFormConfig()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSaveConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigSliderValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }
    protected function getConfigForm()
    {
        $form = array(
                'form' => array(
                    'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Speed beteween images', array(), 'Modules.ItivosSlider.Admin'),
                        'name' => 'ITIVOS_SLIDER_SPEED',
                        'suffix' => 'milliseconds',
                        'desc' => $this->getTranslator()->trans('Only Numbers', array(), 'Modules.ItivosSlider.Admin')
                        ),
                        array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Show texts', array(), 'Modules.ItivosSlider.Admin'),
                        'name' => 'ITIVOS_SLIDER_SHOW_TEXT',
                        'is_bool' => true,
                        'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global')
                                )
                            ),
                        ),
                        array(
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Slider mode', array(), 'Modules.ItivosSlider.Admin'),
                        'name' => 'ITIVOS_SLIDER_MODE',
                        'is_bool' => true,
                        'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->getTranslator()->trans('Slider', array(), 'Modules.ItivosSlider.Admin'),
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->getTranslator()->trans('Carousel', array(), 'Modules.ItivosSlider.Admin'),
                                )
                            ),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                ),
            );
        return $form;
    }
    protected function getFormAdd()
    {
        $form = array(
                'form' => array(
                    'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => 'file',
                            'label' => $this->getTranslator()->trans('Imagen desktop', array(), 'Modules.ItivosSlider.Admin'),
                            'name' => 'ITIVOS_SLIDER_IMAGE_DESKTOP',
                            'required' => true,
                            'desc' => $this->l('Displayed on devices with resolution > than 600px').".<br> ".$this->getTranslator()->trans('Maximum image size: %s.', array(ini_get('upload_max_filesize')), 'Admin.Global')
                        ),
                        array(
                            'type' => 'file',
                            'label' => $this->getTranslator()->trans('Imagen mobile', array(), 'Modules.ItivosSlider.Admin'),
                            'name' => 'ITIVOS_SLIDER_IMAGE_MOBILE',
                            'required' => true,
                            'desc' => $this->l('Displayed on devices with resolution < than 600px').".<br> ".$this->getTranslator()->trans('Maximum image size: %s.', array(ini_get('upload_max_filesize')), 'Admin.Global')
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->getTranslator()->trans('Name', array(), 'Modules.ItivosSlider.Admin'),
                            'name' => 'ITIVOS_SLIDER_NAME',
                            'required' => true,
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->getTranslator()->trans('Target URL', array(), 'Modules.ItivosSlider.Admin'),
                            'name' => 'ITIVOS_SLIDER_URL',
                            'required' => true,
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->getTranslator()->trans('Caption', array(), 'Modules.ItivosSlider.Admin'),
                            'name' => 'ITIVOS_SLIDER_LEGEND',
                        ),
                        array(
                            'type' => 'textarea',
                            'name' => 'ITIVOS_SLIDER_DESCRIPTION',
                            'label' => $this->l('Description'),
                            'autoload_rte' => true,
                            'desc' => $this->l("This text will appear in the slider"),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                    ),
                ),
            );
        if (Tools::isSubmit('editSlider')) {
            $input = array(
                            'type' => 'hidden',
                            'name' => 'editSlider'
                        );
            array_push($form['form']['input'], $input);
        }
        return $form;
    }
    public function renderList()
    {
        $images_list = ItivosSliderC::getList();
        $this->context->smarty->assign(
            array('images_list' => $images_list,
                  'link' => $this->context->link
            )
        );
        return $this->context->smarty->fetch($this->local_path.'views/templates/hook/list.tpl');
    }
    protected function installSamples()
    {
        $res = true;
        for ($i = 1; $i <= 3; ++$i) {
            $slider_obj = new ItivosSliderC();
            $slider_obj->id_lang = 1;
            $slider_obj->id_shop = 1;
            $slider_obj->image_desktop = "800x440.png";
            $slider_obj->image_mobile = "340x340.png";
            $slider_obj->name = "Test ".$i."";
            $slider_obj->url = "https://itivos.com";
            $slider_obj->description = "<p style='color:#fff'>Lorem ipsum ".$i." dolor sit amet, consectetur adipisicing elit, sed do eiusmod <br>tempor incididunt ut labore et dolore magna aliqua. </p>";
            $res &= $slider_obj->add();
        }
        return $res;
    }
    protected function defaultValues()
    {
        $res = true;
        $shops = Shop::getContextListShopID();
        $shop_groups_list = array();

        /* Setup each shop */
        foreach ($shops as $shop_id) {
            $shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

            if (!in_array($shop_group_id, $shop_groups_list)) {
                $shop_groups_list[] = $shop_group_id;
            }

            /* Sets up configuration */
            $res = Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', $this->default_show_text, false, $shop_group_id, $shop_id);
            $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', $this->default_mode, false, $shop_group_id, $shop_id);
            $res &= Configuration::updateValue('ITIVOS_SLIDER_SPEED', $this->default_speed, false, $shop_group_id, $shop_id);
        }

        /* Sets up Shop Group configuration */
        if (count($shop_groups_list)) {
            foreach ($shop_groups_list as $shop_group_id) {
                $res &= Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', $this->default_show_text, false, $shop_group_id);
                $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', $this->default_mode, false, $shop_group_id);
                $res &= Configuration::updateValue('ITIVOS_SLIDER_SPEED', $this->default_speed, false, $shop_group_id);
            }
        }

        /* Sets up Global configuration */
        $res &= Configuration::updateValue('ITIVOS_SLIDER_SHOW_TEXT', $this->default_show_text);
        $res &= Configuration::updateValue('ITIVOS_SLIDER_MODE', $this->default_mode);
        $res &= Configuration::updateValue('ITIVOS_SLIDER_SPEED', $this->default_speed);
        return $res;
    }
    protected function getConfigSliderValues()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = Shop::getContextShopID();

        return array(
            'ITIVOS_SLIDER_SHOW_TEXT' => Tools::getValue('ITIVOS_SLIDER_SHOW_TEXT', Configuration::get('ITIVOS_SLIDER_SHOW_TEXT', null, $id_shop_group, $id_shop)),
            'ITIVOS_SLIDER_MODE' => Tools::getValue('ITIVOS_SLIDER_MODE', Configuration::get('ITIVOS_SLIDER_MODE', null, $id_shop_group, $id_shop)),
            'ITIVOS_SLIDER_SPEED' => Tools::getValue('ITIVOS_SLIDER_SPEED', Configuration::get('ITIVOS_SLIDER_SPEED', null, $id_shop_group, $id_shop)),
        );
    }
    protected function getFormSliderValues()
    {
        if (Tools::isSubmit('addSlider')) {
            $mode = 'add';
            return array(
                'ITIVOS_SLIDER_NAME' => Configuration::get('ITIVOS_SLIDER_NAME', null),
                'ITIVOS_SLIDER_DESCRIPTION' => Configuration::get('ITIVOS_SLIDER_DESCRIPTION', null),
                'ITIVOS_SLIDER_URL' => Configuration::get('ITIVOS_SLIDER_URL', null),
                'ITIVOS_SLIDER_LEGEND' => Configuration::get('ITIVOS_SLIDER_LEGEND', null)
            );
        }
        if (Tools::isSubmit('editSlider') || Tools::isSubmit('btnEditSlider')) {
            $mode = 'edit';
            $data_slider = new ItivosSliderC((int)Tools::getValue('editSlider'));
            if (!empty($data_slider)) {
                
                $this->context->smarty->assign(array('img_link_desktop' => 
                                                            "modules/itivosslider/views/img/".$data_slider->image_desktop."",
                                                     'img_link_mobile' => 
                                                            "modules/itivosslider/views/img/".$data_slider->image_mobile."",
                                                     'link' => $this->context->link)
                                               );
                $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/image_content.tpl');
                return array(
                    'ITIVOS_SLIDER_NAME' => $data_slider->name,
                    'ITIVOS_SLIDER_DESCRIPTION' => $data_slider->description,
                    'ITIVOS_SLIDER_URL' => $data_slider->url,
                    'ITIVOS_SLIDER_LEGEND' => $data_slider->legend,
                    'ITIVOS_SLIDER_IMAGE_DESKTOP' => $data_slider->image_desktop,
                    'ITIVOS_SLIDER_IMAGE_MOBILE' => $data_slider->image_mobile,
                    'editSlider' => Tools::getValue('editSlider')
                );
            }
        }
        return array(
            'ITIVOS_SLIDER_NAME' => Configuration::get('ITIVOS_SLIDER_NAME', null),
            'ITIVOS_SLIDER_DESCRIPTION' => Configuration::get('ITIVOS_SLIDER_DESCRIPTION', null),
            'ITIVOS_SLIDER_URL' => Configuration::get('ITIVOS_SLIDER_URL', null),
            'ITIVOS_SLIDER_LEGEND' => Configuration::get('ITIVOS_SLIDER_LEGEND', null)
        );
    }
    public function installDb()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'itivos_slider` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_lang` INT NULL DEFAULT NULL,
              `id_shop` INT NOT NULL,
              `name` LONGTEXT NULL DEFAULT NULL,
              `image_desktop` LONGTEXT NOT NULL,
              `image_mobile` LONGTEXT NOT NULL,
              `url` TEXT NOT NULL,
              `description` TEXT NULL DEFAULT NULL,
              `legend` TEXT NULL DEFAULT NULL,
              `order_menu` INT NULL DEFAULT 0,
              `position` set("right","left") DEFAULT "left", 
              PRIMARY KEY (`id`)
              ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
        return $return;
    }
    public function uninstallDB($drop_table = true)
    {   
        $ret = true;
        if ($drop_table) {
            $ret &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'itivos_slider`');
        }
        return $ret;
    }
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getFormSliderValues();
        if (Tools::isSubmit('btnSubmitAdd')) {
            $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
            $salt = sha1(microtime());
            $type = Tools::strtolower(Tools::substr(strrchr($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'], '.'), 1));

            if ($error = ImageManager::validateUpload($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP'])) {
                $this->html .= $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
            }else {
                if (!$temp_name || !move_uploaded_file($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['tmp_name'], $temp_name)) {
                    $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                }elseif (!ImageManager::resize($temp_name, __DIR__.'/views/img/'.$salt.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'], null, null, $type)) {
                     $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                }
            }

            $temp_name2 = tempnam(_PS_TMP_IMG_DIR_, 'PS');
            $salt2 = sha1(microtime());
            $type2 = Tools::strtolower(Tools::substr(strrchr($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['name'], '.'), 1));

            if ($error = ImageManager::validateUpload($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE'])) {
                $this->html .= $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
            }else {
                if (!$temp_name2 || !move_uploaded_file($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['tmp_name'], $temp_name2)) {
                    $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                }elseif (!ImageManager::resize($temp_name2, __DIR__.'/views/img/'.$salt2.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['name'], null, null, $type2)) {
                     $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                }
            }
            if (empty($this->postErrors)) {
                $slider_obj = new ItivosSliderC();
                $slider_obj->id_lang = 1;
                $slider_obj->id_shop = 1;
                $slider_obj->url = Tools::getValue('ITIVOS_SLIDER_URL');
                $slider_obj->name = Tools::getValue('ITIVOS_SLIDER_NAME');
                $slider_obj->description = Tools::getValue('ITIVOS_SLIDER_DESCRIPTION'); 
                $slider_obj->legend = Tools::getValue('ITIVOS_SLIDER_LEGEND');
                $slider_obj->image_desktop = $salt.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'];
                $slider_obj->image_mobile  = $salt2.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['name'];
                $slider_obj->save();
            }
            if (isset($temp_name)) {
                @unlink($temp_name);
            }
            if (isset($temp_name2)) {
                @unlink($temp_name2);
            }
        }
    }
    protected function postProcessUpdate()
    {
        if (Tools::isSubmit('btnEditSlider')) {
            $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
            $salt = "";
            $type = "";

            if (!empty($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'])) {
                $salt = sha1(microtime());
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'], '.'), 1));
                if ($error = ImageManager::validateUpload($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP'])) {
                    $this->html .= $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                }else {
                    if (!$temp_name || !move_uploaded_file($_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['tmp_name'], $temp_name)) {
                        $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                    }elseif (!ImageManager::resize($temp_name, __DIR__.'/views/img/'.$salt.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'], null, null, $type)) {
                         $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                    }

                    $temp_name2 = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                    $salt2 = sha1(microtime());
                    $type = Tools::strtolower(Tools::substr(strrchr($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['name'], '.'), 1));

                    if ($error = ImageManager::validateUpload($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE'])) {
                        $this->html .= $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                    }else {
                        if (!$temp_name2 || !move_uploaded_file($_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['tmp_name'], $temp_name2)) {
                            $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                        }elseif (!ImageManager::resize($temp_name2, __DIR__.'/views/img/'.$salt2.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['name'], null, null, $type)) {
                             $this->postErrors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', array(), 'Admin.Notifications.Error'));
                        }
                    }
                    if (empty($this->postErrors)) {
                        ItivosSliderC::setImage(Tools::getValue('editSlider'),
                                                $salt.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_DESKTOP']['name'],
                                                $salt2.'_'.$_FILES['ITIVOS_SLIDER_IMAGE_MOBILE']['name']
                                            );
                    }
                }
            }
            if (empty($this->postErrors)) {
                $slider_obj = new ItivosSliderC((int)Tools::getValue('editSlider'));
                $slider_obj->url = Tools::getValue('ITIVOS_SLIDER_URL');
                $slider_obj->name = Tools::getValue('ITIVOS_SLIDER_NAME');
                $slider_obj->description = Tools::getValue('ITIVOS_SLIDER_DESCRIPTION'); 
                $slider_obj->legend = Tools::getValue('ITIVOS_SLIDER_LEGEND');
                $slider_obj->update();
            }
            if (isset($temp_name)) {
                @unlink($temp_name);
            }
        }
    }
    public function clearCache()
    {
        $this->_clearCache($this->templateFile);
    }
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }
    public function hookHeader()
    {
        $this->context->controller->addCss($this->_path.'views/css/slick.css');
        $this->context->controller->addCss($this->_path.'views/css/slick-theme.css');
        $this->context->controller->addCSS($this->_path.'views/css/front.css');
        $this->context->controller->addJs($this->_path.'views/js/slick/slick.min.js');
        $this->context->controller->addJS($this->_path.'views/js/front.js');
    }

    public function getDevice()
    {
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
            return "mobile";
        }else {
            return "pc";
        }

    }
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId())) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId());
    }
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $slides = $sliders_list = ItivosSliderC::getByLang($this->context->language->id);
        $config = $this->getConfigSliderValues();

        return [
            'itivos_homeslider' => [
                'speed' => $config['ITIVOS_SLIDER_SPEED'],
                'mode' => $config['ITIVOS_SLIDER_MODE'],
                'show_text' => $config['ITIVOS_SLIDER_SHOW_TEXT'],
                'device' => $this->getDevice(),
                'slides' => $slides,
            ],
        ];
    }
}
