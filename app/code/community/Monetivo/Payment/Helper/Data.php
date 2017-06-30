<?php
/**
 * Helper for Monetivo Payment plugin
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      monetivo <hello@monetivo.com>
 */

/**
 * Class Monetivo_Payment_Helper_Data
 */
class Monetivo_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Converts the Magento float values (for instance 9.9900) to Monetivo accepted Currency format (999.00)
     *
     * @param string
     * @return float
     */
    public function toAmount($val)
    {
        return (float)round($val, 2);
    }
}