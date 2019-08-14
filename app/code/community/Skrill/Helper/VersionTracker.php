<?php
/**
* 2015 Skrill
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    Skrill <contact@skrill.com>
*  @copyright 2015 Skrill
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Skrill
*/

class Skrill_Helper_VersionTracker extends Mage_Core_Helper_Abstract
{
    protected $versionTrackerUrl = 'http://api.dbserver.payreto.eu/v1/tracker';

    protected function getVersionTrackerUrl()
    {
        return $this->versionTrackerUrl;
    }

    protected function getVersionTrackerParameter($versionData)
    {
        $versionData['hash'] = md5($versionData['shop_version'].$versionData['plugin_version'].$versionData['client']);

        return http_build_query(array_filter($versionData), '', '&');
    }

    public function sendVersionTracker($versionData)
    {
        Mage::log('send version tracker', null, 'skrill_log_file.log');

        $url = $this->getVersionTrackerUrl();

        $request = $this->getVersionTrackerParameter($versionData);
        Mage::log('send version tracker request', null, 'skrill_log_file.log');
        Mage::log($versionData, null, 'skrill_log_file.log');

        $response = Mage::helper('skrill/curl')->sendRequest($url, $request, true);
        Mage::log('send version tracker response', null, 'skrill_log_file.log');
        Mage::log($response, null, 'skrill_log_file.log');

        return $response;
    }
}
