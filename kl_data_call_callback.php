<?php
/**
 * kl_data_call_callback.php
 * design by KLLER
 */
function callback_init()
{
    global $plugin;
    require_once "../content/plugins/{$plugin}";
    KlDataCall::getInstance()->callbackInit();
}

function callback_rm()
{
}