<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @package     Skrill
 * @copyright   Copyright (c) 2014 Skrill
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System config observer
 *
 */
class Skrill_Model_Config_Observer
{
    /**
     * Update api password and secret word in md5 when save the config.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function saveConfig(Varien_Event_Observer $observer)
    {
        $section = $observer->getEvent()->getSection();
        $website = $observer->getEvent()->getWebsite();
    	$store = $observer->getEvent()->getStore();

        $currentgroups = Mage::getSingleton('adminhtml/config_data')->getGroups();
        $apiPassword = $currentgroups['skrill_settings']['fields']['api_passwd']['value'];
        $secretWord = $currentgroups['skrill_settings']['fields']['secret_word']['value'];

        if (!$this->isValidMd5($apiPassword)) {
            $groups['skrill_settings']['fields']['api_passwd']['value'] = md5($apiPassword);
        }
        if (!$this->isValidMd5($secretWord)) {
            $groups['skrill_settings']['fields']['secret_word']['value'] = md5($secretWord);
        }

        Mage::getSingleton('adminhtml/config_data')
            ->setSection($section)
            ->setWebsite($website)
            ->setStore($store)
            ->setGroups($groups)
            ->save();
    }

    /**
     * Determine if supplied string is a valid GUID
     *
     * @param string $md5 String to validate
     * @return boolean
     */
    protected function isValidMd5($md5 = '')
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }

}

