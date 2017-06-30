<?php

/**
 * Config model for Monetivo Payment plugin
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      Adrian 'eKsiK' Pranga <core@magentocommerce.com>
 */

/**
 * Class Monetivo_Payment_Model_Config
 */
class Monetivo_Payment_Model_Config
{
    /**
     * @var string plugin version
     */
    protected $_pluginVersion = '1.0.0';

    /**
     * @var string minimum Magento e-commerce version
     */
    protected $_minimumMageVersion = '1.6.0';

    /**
     * @var int current store id
     */
    protected $_storeId;


    /**
     * Monetivo_Payment_Model_Config constructor.
     *
     * @param array
     */
    public function __construct($params = array())
    {
        $this->setStoreId(Mage::app()->getStore()->getId());
    }

    /**
     * Set store id to context
     *
     * @param $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    /**
     * Monetivo App Token for auth to api
     *
     * @return string
     */
    public function getAppToken()
    {
        return $this->getStoreConfig('payment/monetivo_payment/app_token');
    }

    /**
     * Monetivo merchant login for auth to api
     *
     * @return string
     */
    public function getMerchantLogin()
    {
        return $this->getStoreConfig('payment/monetivo_payment/login');
    }

    /**
     * Monetivo merchant password for auth to api
     *
     * @return string
     */
    public function getMerchantPassword()
    {
        return $this->getStoreConfig('payment/monetivo_payment/password');
    }

    /**
     * Get display name in frontend
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getStoreConfig('payment/monetivo_payment/displayname');
    }

    /**
     * Helper function to getUrl
     *
     * @param $action
     *
     * @return string base module url
     */
    public function getUrl($action)
    {
        return Mage::getUrl(
            "monetivo_payment/payment/$action", array('_secure' => true)
        );
    }

    /**
     * Check if is one step checkout method enabled
     *
     * @return string
     */
    public function getIsOneStepCheckoutEnabled()
    {
        return $this->getStoreConfig(
            'payment/monetivo_payment/onestepcheckoutenabled'
        );
    }

    /**
     * Get current plugin version
     *
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->_pluginVersion;
    }

    /**
     * Get store config variable
     *
     * @param $name
     *
     * @return mixed
     */
    protected function getStoreConfig($name)
    {
        return Mage::getStoreConfig($name, $this->_storeId);
    }
}