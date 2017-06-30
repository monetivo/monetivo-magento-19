<?php
/**
 * Frontend block for Monetivo Payment plugin
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      monetivo <hello@monetivo.com>
 */

/**
 * Class Monetivo_Payment_Block_Form
 */
class Monetivo_Payment_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_methodCode = 'monetivo_payment';

    /**
     * Get Plugin Configuration
     *
     * @return Monetivo_Payment_Model_Config|Mage_Core_Model_Abstract
     */
    protected function getConfig()
    {
        return Mage::getModel('monetivo_payment/config');
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        // Add css file to header
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addCss('css/monetivo/monetivo.css');
        }

        return parent::_prepareLayout();
    }

    /**
     * Construtor
     */
    public function _construct()
    {
        $this->setTemplate('monetivo_payment/form.phtml');

        // Get display name from plugin configuration
        $displayName = $this->getConfig()->getDisplayName();

        // Display default name if not defined
        $this->setMethodTitle(
            (empty($displayName) ? $this->__('Zapłać przez Monetivo')
                : $displayName)
        );

        // Display logo Monetivo
        $this->setMethodLabelAfterHtml(
            '<img src="' . $this->getMonetivoLogo()
            . '" alt="Monetivo" class="formMonetivoLogo" />'
        );
        parent::_construct();
    }

    /**
     * Payment method code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Get Monetivo Logo
     *
     * @return string
     */
    private function getMonetivoLogo()
    {
        return $this->getSkinUrl('images/monetivo/mvo.png');
    }
}