<?php # -*- coding: utf-8 -*-
$parent_dir = dirname( dirname( __DIR__ ) ) . '/';
$vendor = $parent_dir . '/vendor/';
if ( ! realpath( $vendor ) ) {
	die('Please run composer install before running the tests.');
}

require_once $vendor . 'antecedent/patchwork/Patchwork.php';
require_once $vendor . '/autoload.php';

require_once $parent_dir . 'agb-connector.php';
require_once $parent_dir . 'inc/AGB_Connector_API.php';

unset( $vendor );
unset( $parent_dir );