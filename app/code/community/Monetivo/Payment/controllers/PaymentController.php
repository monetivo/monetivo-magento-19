<?php

/**
 * Payment model for Monetivo Payment plugin
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      monetivo <hello@monetivo.com>
 */

/**
 * Class Monetivo_Payment_PaymentController
 */
class Monetivo_Payment_PaymentController
    extends Mage_Core_Controller_Front_Action
{

    /**
     * Order model
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session|Mage_Core_Model_Abstract
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get Payment Model
     *
     * @return Mage_Core_Model_Abstract|Monetivo_Payment_Model_Payment
     */
    public function getPayment()
    {
        return Mage::getModel('monetivo_payment/payment');
    }

    /**
     * Get Order Model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId(
                $this->_getCheckout()->getLastRealOrderId()
            );
        }

        return $this->_order;
    }

    /**
     * Create new order
     */
    public function newAction()
    {
        try {

            if ( ! $this->getOrder()->getId()) {
                Mage::throwException($this->__('Nie znaleziono zamówienia'));
            }

            $redirectData = $this->getPayment()->orderCreateRequest(
                $this->getOrder()
            );

            $this->_redirectUrl($redirectData['redirectUri']);

            return;
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->_errorAction();
    }

    /**
     * Processes Monetivo OrderNotifyRequest
     *
     * @throws Exception
     * @return void
     */
    public function orderNotifyRequestAction()
    {
        try {
            $this->getPayment()->orderNotifyRequest();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Redirect to next step in checkout
     *
     * @throws Exception
     * @return void
     */
    public function continuePaymentAction()
    {
        try {
            $this->_getCheckout()->getQuote()->setIsActive(false)->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if (isset($_GET['error'])) {
            $this->_redirect(
                'checkout/onepage/failure', array('_secure' => true)
            );
        } else {
            $this->_redirect(
                'checkout/onepage/success', array('_secure' => true)
            );
        }
    }

    /**
     * Redirect to error page
     */
    public function _errorAction()
    {
        $this->_redirect('checkout/onepage/failure', array('_secure' => true));
    }
}