<?php
/**
 * kl_data_call_setting.php
 * design by KLLER
 */
!defined('EMLOG_ROOT') && exit('access deined!');

function plugin_setting_view(){
	KlDataCall::getInstance()->settingView();
}

function plugin_setting(){
	KlDataCall::getInstance()->setting();
}
