<?php
// File to force Dolibarr wizard installer choices.
//
// This file must be present into htdocs/install directory
// during install process to be used.
//
//
$force_install_noedit=0;		// 1=To block vars specific to distrib, 2 to block all technical parameters
$force_install_message='KeepDefaultValuesWamp';
$force_install_main_data_root='c:/dolibarr/dolibarr_documents';
$force_install_type='mysqli';
$force_install_dbserver='localhost';
$force_install_port='3306';
$force_install_database='dolibarr';
$force_install_createdatabase='1';
$force_install_databaselogin='dolibarrmysql';
$force_install_databasepass='changeme';
$force_install_createuser='1';
$force_install_databaserootlogin='root';
$force_install_databaserootpass='changeme';
$force_install_dolibarrlogin='admin';
$force_install_nophpinfo='1';
$force_install_lockinstall='644';

$force_install_module='';
?>