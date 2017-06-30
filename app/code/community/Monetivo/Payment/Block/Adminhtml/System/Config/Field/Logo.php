<?php
/**
 * Logo field
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      monetivo <hello@monetivo.com>
 */
class Monetivo_Payment_Block_Adminhtml_System_Config_Field_Logo
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Display logo in Configuration section
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html
            = '
            <div>
                <div class="col-e-2 monetivo-logo">
                </div>
                <div class="col-e-4">
                    <a href="https://merchant.monetivo.com/register" class="m-button" target="_blank">Rejestracja</a>
                    <br>
                    Masz już konto?
                    <a href="https://merchant.monetivo.com/login" class="m-login" target="_blank">Zaloguj</a>
                </div>
            </div>';

        return $html;
    }
}