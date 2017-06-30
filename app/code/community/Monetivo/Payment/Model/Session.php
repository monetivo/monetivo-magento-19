<?php
/**
 * Session model for Monetivo Payment plugin
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      monetivo <hello@monetivo.com>
 */

/**
 * Class Monetivo_Payment_Model_Session
 */
class Monetivo_Payment_Model_Session extends Mage_Core_Model_Session_Abstract
{
    /**
     * Monetivo_Payment_Model_Session constructor.
     */
    public function __construct()
    {
        $this->init('monetivo_payment');
        parent::__construct();
    }
}