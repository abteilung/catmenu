<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Typo3.' . $_EXTKEY,
    'Catmenu',
	array(
		'Start' => 'index,show,recent,single',
	),
	array(
		'Start' => 'index,show,recent,single',
	)
);

?>