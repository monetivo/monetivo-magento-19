<?php
/**
 * Fieldset renderer
 *
 * @category    Monetivo
 * @package     Monetivo_Payment
 * @author      Adrian 'eKsiK' Pranga <core@magentocommerce.com>
 */
class Monetivo_Payment_Block_Adminhtml_System_Config_Fieldset_Information
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Return header comment part of html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return bool
     */
    protected function _getCollapseState($element)
    {
        return false;
    }

    /**
     * Render information field
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html
            = '
            <h2>Monetivo - just like that</h2>
            <dl class="monetivo-content">
                <dt>&middot; Prosta oferta, to proste zasady.</dt>
                <dt>&middot; Najszybsza integracja, to szybsza sprzedaż</dt>
                <dt>&middot; Prosta ścieżka transakcyjna, to więcej zrealizowanych zakupów</dt>
                <dt>&middot; Stworzony przez sprzedawców dla sprzedawców, rozumiemy Twoje potrzeby</dt>
                <dt>&middot; Przelewy, karty, raty - mamy to wszystko</dt>
                <dt>&middot; Pełne wdrożenie 100% online!</dt>
                <dt>&middot; Darmowa rejestracja.</dt>
                <dt>&middot; Bezpłatne prowadzenie konta.</dt>
                <dt>&middot; Darmowe wsparcie.</dt>
                <dt>&middot; Wsparcie działalności charytatywnej.</dt>
            </dl>
       ';

        return $html;
    }
}