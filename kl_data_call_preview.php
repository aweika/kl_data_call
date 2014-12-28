<?php
/**
 * kl_data_call_preview.php
 * design by KLLER
 */
require_once('../../../init.php');
if (ROLE != 'admin') exit('access deined!');
header('content-type:text/html;charset=utf-8');
KlDataCall::getInstance()->preview();