<?php

/** Require Monetivo API autoloader */
require_once (Mage::getBaseDir('lib').'/MonetivoApi/autoload.php');

use Monetivo\MerchantApi as MonetivoApi;
use Monetivo\Exceptions\MonetivoException as MonetivoException;
use Monetivo\Api\Transactions as MonetivoTransactions;

/**
 * Class Monetivo_Payment_Model_Payment
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      monetivo <hello@monetivo.com>
 */
class Monetivo_Payment_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const DELIMITER = '-';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'monetivo_payment';

    /**
     * Block type
     *
     * @var string
     */
    protected $_formBlockType = 'monetivo_payment/form';

    /**
     * Transaction id
     *
     * @var int
     */
    protected $_transactionId;

    /**
     * Currently processed order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Payment relation flag
     *
     * @var bool
     */
    protected $_isGateway = false;
    /**
     * Order availability
     *
     * @var bool
     */
    protected $_canOrder = false;
    /**
     * Authorize availability
     *
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * Capture availability
     *
     * @var bool
     */
    protected $_canCapture = false;
    /**
     * Partial capture availability
     *
     * @var bool
     */
    protected $_canCapturePartial = false;
    /**
     * Refund availability
     *
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * Partial refund availability for invoice
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    /**
     * Order payment void availability
     *
     * @var bool
     */
    protected $_canVoid = false;
    /**
     * Internal pages for input payment data
     *
     * @var bool
     */
    protected $_canUseInternal = true;
    /**
     * Regular checkout
     *
     * @var bool
     */
    protected $_canUseCheckout = true;
    /**
     * Multiple shipping address
     *
     * @var bool
     */
    protected $_canUseForMultishipping = false;
    /**
     * Available accept or deny payment
     *
     * @var bool
     */
    protected $_canReviewPayment = true;
    /**
     * Run payment initialize while order place
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Order payment result
     *
     * @var null
     */
    protected $_mOrderPaymentResult = null;

    /**
     * Monetivo_Payment_Model_Payment constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Constructor for Monetivo API
        $this->MonetivoApi();
    }

    /**
     * Get Monetivo Payment helper class
     *
     * @return Monetivo_Payment_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('monetivo_payment');
    }

    /**
     * Set settings to Monetivo Api
     *
     * @return MonetivoApi|null
     */
    private function MonetivoApi()
    {
        try {
            $app_token = $this->getConfig()->getAppToken();
            $login     = $this->getConfig()->getMerchantLogin();
            $password  = $this->getConfig()->getMerchantPassword();
            $api       = new MonetivoApi($app_token);

            // Send platform information to Monetivo
            $api->setPlatform(
                sprintf(
                    'monetivo-magento-%s-%s', Mage::getVersion(),
                    $this->getConfig()->getPluginVersion()
                )
            );

            // Get Auth token from API
            $auth_token = $api->auth($login, $password);
            $api->setAuthToken($auth_token);

            return $api;

        } catch (MonetivoException $e) {
            Mage::throwException(
                $this->_helper()->__(
                    'Wystąpił problem z płatnościa - "%s", skontaktuj się z administratorem.',
                    $e->getMessage()
                )
            );
            Mage::logException($e);
        }

        return null;
    }

    /**
     * Get Monetivo Payment
     *
     * @return Monetivo_Payment_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('monetivo_payment/config');
    }

    /**
     * Set Order Id
     *
     * @param string $extOrderId
     */
    private function _setOrderByOrderId($extOrderId)
    {
        $this->_order = Mage::getModel('sales/order')->load($extOrderId);
    }

    /**
     * Return Order Model from context
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Set Order Model
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * Get Monetivo session namespace
     *
     * @return Monetivo_Payment_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('monetivo_payment/session');
    }

    /**
     * Redirection url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl(
            'monetivo_payment/payment/new', array('_secure' => true)
        );
    }

    /**
     * Get cart model from session
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Get checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Create new order
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function orderCreateRequest(Mage_Sales_Model_Order $order)
    {
        // Save order object to context
        $this->setOrder($order);
        $buyer    = array();
        $response = array();

        try {
            $billingAddressId = $order->getBillingAddressId();
            if ( ! empty($billingAddressId)) {
                $billingAddress = $order->getBillingAddress();
                $customerEmail  = $billingAddress->getEmail();
                if ( ! empty($customerEmail)) {
                    $buyer = [
                        'name'  => $billingAddress->getFirstname() . ' '
                            . $billingAddress->getLastname(),
                        'email' => $billingAddress->getEmail()
                    ];
                }
            }
            $request              = array(
                'order_data' => array(
                    'description' => "Zamówienie #" . $order->getRealOrderId(),
                    // Generate unique id for Monetivo
                    'order_id'    => uniqid(
                        $order->getId() . self::DELIMITER, true
                    )
                ),
                'buyer'      => $buyer,
                // Get language code from Store config and extract only 2 chars from start
                'language'   => substr(
                    Mage::getStoreConfig(
                        'general/locale/code', Mage::app()->getStore()->getId()
                    ), 0, 2
                ),
                'currency'   => $order->getOrderCurrencyCode(),
                // Convert amount to Monetivo reading format
                'amount'     => $this->_toAmount($order->getGrandTotal()),
                'return_url' => $this->getConfig()->getUrl('continuePayment'),
                'notify_url' => $this->getConfig()->getUrl('orderNotifyRequest')
            );
            $transaction          = $this->MonetivoApi()->transactions()
                ->create(
                    $request
                );
            $this->_transactionId = $transaction['identifier'];

            Mage::getSingleton('core/session')->setMonetivoSessionId(
                $this->_transactionId
            );

            $payment = $order->getPayment();
            $payment->setAdditionalInformation(
                'monetivo_payment_status', MonetivoTransactions::TRAN_STATUS_NEW
            )->save();

            $this->_updatePaymentStatusNew($payment);

            $response = array(
                'redirectUri' => $transaction['redirect_url']
            );

        } catch (Exception $e) {
            Mage::throwException(
                $this->_helper()->__(
                    'Wystąpił problem z płatnościa - "%s", skontaktuj się z administratorem.',
                    $e->getMessage()
                )
            );
            Mage::logException($e);
        }

        $order->sendNewOrderEmail()->save();

        return $response;
    }

    /**
     * Accepting payment and setting order status
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);
        $sessionId = $payment->getLastTransId();

        if (empty($sessionId)) {
            return false;
        }

        if ( ! $this->_orderStatusUpdateRequest(
            MonetivoTransactions::TRAN_STATUS_PAID, $sessionId
        )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Decling payment and setting order status
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);
        $sessionId = $payment->getLastTransId();
        if (empty($sessionId)) {
            return false;
        }

        if ( ! $this->_orderStatusUpdateRequest(
            MonetivoTransactions::TRAN_STATUS_DECLINED, $sessionId
        )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Refund process
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return void
     */
    public function refund(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order                = $payment->getOrder();
        $refund['identifier'] = $order->getPayment()->getLastTransId();
        try {
            $refund  = $this->MonetivoApi()->transactions()->refund($refund);
            $comment = $this->_helper()->__(
                'Monetivo: zwrot - kwota: %s, status %s', $amount,
                $refund['status']
            );

            $order->addStatusHistoryComment($comment)->save();

        } catch (MonetivoException $e) {
            Mage::logException($e);
        }
    }

    /**
     * Handle request from Monetivo and set order status
     *
     * @return void
     */
    public function orderNotifyRequest()
    {
        try {
            $transaction = $this->MonetivoApi()->handleCallback();
        } catch (Exception $e) {
            header('X-PHP-Response-Code: 500', true, 500);
            die($e->getMessage());
        }
        if ($transaction !== false) {
            $this->_transactionId = $transaction['identifier'];
            $extOrderId           = explode(
                self::DELIMITER, $transaction['order_data']['order_id']
            );
            $orderId              = array_shift($extOrderId);

            $this->_setOrderByOrderId($orderId);
            $this->_updatePaymentStatus($transaction['status']);

            // Send to API 200 if everything is okay
            header("HTTP/1.1 200 OK");
        }
        exit;
    }

    /**
     * Update payment status
     *
     * @param $paymentStatus
     *
     * @return void
     */
    private function _updatePaymentStatus($paymentStatus)
    {
        $payment = $this->getOrder()->getPayment();

        $currentState = $payment->getAdditionalData('monetivo_payment_status');
        if ($currentState != MonetivoTransactions::TRAN_STATUS_ACCEPTED
            && $currentState != $paymentStatus
        ) {
            try {
                switch ($paymentStatus) {
                    case MonetivoTransactions::TRAN_STATUS_NEW:
                        break;
                    case MonetivoTransactions::TRAN_STATUS_DECLINED:
                        $this->_updatePaymentStatusCanceled($payment);
                        break;
                    case MonetivoTransactions::TRAN_STATUS_ACCEPTED:
                        $this->_updatePaymentStatusCompleted($payment);
                        break;
                    case MonetivoTransactions::TRAN_STATUS_REFUNDED:
                        $this->_updatePaymentStatusRejected($payment);
                        break;
                }
                $payment->setAdditionalInformation(
                    'monetivo_payment_status', $paymentStatus
                )->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Update payment status to new
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return void
     */
    private function _updatePaymentStatusNew(Mage_Sales_Model_Order_Payment $payment
    ) {
        $comment = $this->_helper()->__('Transakcja rozpoczęta.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setIsTransactionApproved(false)
            ->setIsTransactionClosed(false)
            ->save();

        $payment->addTransaction(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false,
            $comment
        )->save();
        $payment->getOrder()->save();
    }

    /**
     * Canceling order process
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return void
     */
    private function _updatePaymentStatusCanceled(Mage_Sales_Model_Order_Payment $payment
    ) {
        $comment = $this->_helper()->__('Transakcja została anulowana.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setIsTransactionApproved(true)
            ->setIsTransactionClosed(true)
            ->save();
        $payment->addTransaction(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false,
            $comment
        )->save();

        $payment->getOrder()->sendOrderUpdateEmail(true, $comment)->cancel()
            ->save();
    }

    /**
     * Change the status to rejected
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return void
     */
    private function _updatePaymentStatusRejected(Mage_Sales_Model_Order_Payment $payment
    ) {
        $comment = $this->_helper()->__(
            'Transakcja została zaakceptowana lub zwrócona.'
        );
        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->save();

        $payment->getOrder()->setState(
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, $comment
        );
    }

    /**
     * Change status to completed
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return void
     */
    private function _updatePaymentStatusCompleted(Mage_Sales_Model_Order_Payment $payment
    ) {
        $comment = $this->_helper()->__('Transakcja przebiegła pomyślnie.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setIsTransactionApproved(true)
            ->setIsTransactionClosed(true)
            ->registerCaptureNotification(
                $this->getOrder()->getTotalDue(), true
            );
        $this->getOrder()->save();

        if ($invoice = $payment->getCreatedInvoice()) {
            $comment = $this->_helper()->__(
                'Wysłano powiadomienie do klienta o fakturze #%s.',
                $invoice->getIncrementId()
            );
            $this->getOrder()->queueNewOrderEmail()->addStatusHistoryComment(
                $comment
            )->setIsCustomerNotified(true)->save();
        }
    }

    /**
     * Handling order status request
     *
     * @param $status
     * @param $sessionId
     *
     * @return bool
     */
    private function _orderStatusUpdateRequest($status, $sessionId)
    {
        if (empty($sessionId)) {
            $sessionId = $this->getOrder()->getPayment()->getLastTransId();
        }

        if (empty($sessionId)) {
            Mage::log('Monetivo sessionId empty: ' . $this->getId());
        }

        if ($status == MonetivoTransactions::TRAN_STATUS_DECLINED) {
            $this->_updatePaymentStatusCanceled($sessionId);
        } elseif ($status == MonetivoTransactions::TRAN_STATUS_ACCEPTED) {
            $this->_updatePaymentStatusCompleted($sessionId);
        } else {
            return false;
        }

        return true;
    }

    /**
     * Converts amount to acceptable format for Monetivo
     *
     * @param $val
     *
     * @return int
     */
    private function _toAmount($val)
    {
        return $this->_helper()->toAmount($val);
    }
}