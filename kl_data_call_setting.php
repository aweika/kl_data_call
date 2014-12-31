<?php
/*
Plugin Name: 数据调用
Version: 3.0
Plugin URL: https://github.com/aweika/kl_data_call
Description: 实现emlog相关的数据调用功能。
ForEmlog: 5.x版本
Author: 阿维卡
Author Email: kller@foxmail.com
Author URL: http://www.aweika.com
*/
!defined('EMLOG_ROOT') && exit('access deined!');

function plugin_setting_view()
{
    KlDataCall::getInstance()->settingView();
}

function plugin_setting()
{
    KlDataCall::getInstance()->setting();
}
