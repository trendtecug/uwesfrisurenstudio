<?php
/**
 * Created by Jainish Senjaliya.
 * User: Jainish
 * Date: 13.06.2015
 * Time: 15:01
 */
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class that adds the wizard icon.
 */
class JsContactFormWizicon {

    /**
     * Processing the wizard items array
     *
     * @param array $wizardItems : The wizard items
     * @return Modified array with wizard items
     */
    function proc( $wizardItems ) {

        $wizardItems['plugins_tx_jscontactform_contactform'] = array(
            'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('js_contact_form') . 'Resources/Public/Icons/wizard_icon.png',
            'title' => $GLOBALS['LANG']->sL('LLL:EXT:js_contact_form/Resources/Private/Language/locallang.xlf:plugin-title'),
            'description' => $GLOBALS['LANG']->sL('LLL:EXT:js_contact_form/Resources/Private/Language/locallang.xlf:plugin-description'),
            'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=jscontactform_contactform'
        );

        return $wizardItems;
    }

}	