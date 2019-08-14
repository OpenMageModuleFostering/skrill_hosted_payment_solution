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

/**
 * Skrill Curl helper
 *
 */
class Skrill_Helper_Curl extends Mage_Core_Helper_Abstract
{
    /**
     * @var Varien_Http_Adapter_Curl
     */
    protected $http;

    /**
     * Constructor. Set http.
     */
    public function __construct()
    {
        $this->http = new Varien_Http_Adapter_Curl();
    }

    /**
     * get response body from http
     *
     * @param boolean $isJsonDecoded
     * @return string|boolean|array
     */
    protected function _getResponse($isJsonDecoded = false)
    {
        $response = $this->http->read();
        $responseCode = Zend_Http_Response::extractCode($response);
        $responseBody = Zend_Http_Response::extractBody($response);
        $this->http->close();

        if ($responseCode == 200 || $responseCode == 202 || $responseCode == 400) {
            if ($isJsonDecoded) {
                return json_decode($responseBody, true);
            }
            return $responseBody;
        }
        return false;
    }

    /**
     * get response data from the gateway
     *
     * @param string $data
     * @param string $url
     * @return array|boolean
     */
    public function getResponseData($data, $url)
    {
        $this->http->setConfig(['verifypeer' => false]);
        $this->http->write(Zend_Http_Client::POST, $url, $http_ver = '1.1', $headers = [], $data);

        return $this->_getResponse();
    }

    /**
     * get response from the gateway
     *
     * @param string $url
     * @return array|boolean
     */
    public function getResponse($url, $isJsonDecoded = false)
    {
        $this->http->setConfig(['verifypeer' => false]);
        $this->http->write(Zend_Http_Client::GET, $url);

        return $this->_getResponse($isJsonDecoded);
    }

    /**
     * send request to the gateway
     *
     * @param string $url
     * @param string $request
     * @param boolean $isJsonDecoded
     * @return string|boolean
     */
    public function sendRequest($url, $request, $isJsonDecoded = false)
    {
        $this->http->setConfig(['verifypeer' => false]);
        $headers = ['Content-type: application/x-www-form-urlencoded;charset=UTF-8'];
        $this->http->write(Zend_Http_Client::POST, $url, $http_ver = '1.1', $headers, $request);

        return $this->_getResponse($isJsonDecoded);
    }
}
