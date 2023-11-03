<?php
/**
 * 2007-2020 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('itivosslider.php');
$itivosSlider = new ItivosSlider();
$order = array();

if (!Tools::isSubmit('secure_key') || Tools::getValue('secure_key') != $itivosSlider->secure_key || !Tools::getValue('action'))
	die(1);

if (Tools::getValue('action') == 'updateSliderlist' && Tools::getValue('order'))
{
    $order = Tools::getValue('order');
	foreach ($order as $position => $id_category)
    {
        $position = (int)$position +1;
		itivosSliderC::updateOrder($id_category, $position);
    }
	$itivosSlider->clearCache();
}
