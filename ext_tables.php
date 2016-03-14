<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Pits.' . $_EXTKEY,
    'Catmenu',
    'Category Menu'
);


 
// Include flex forms
$pluginSignature = str_replace('_', '', $_EXTKEY) . '_' .    'catmenu'; // from registerPlugin(...)
$TCA['tt_content']['types']['list']['subtypes_addlist']   [$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,    
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_catmenu.xml'
); 
?>