<?php
/**
 * 插件设置页面日志调用页的模板
 */
!defined('EMLOG_ROOT') && exit('access deined!');
?>
<div class="containertitle2">
    <a class="navi1" href="?plugin=kl_data_call">调用列表</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add">文章调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=1">微语调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=2">EM相册调用</a>
    <a class="navi3" href="?plugin=kl_data_call&act=about" style="color:orange;">关于作者</a>
    <?php if (isset($_GET['active_save'])): ?><span class="actived">保存成功</span><?php endif; ?>
</div>
<div style="height: 100px;">
    <table width="100%" border="0" cellpadding="0" cellspacing="1" class="table_b">
        <tr>
            <td colspan="2"><h5 style="margin:5px 0px;">联系我：</h5></td>
        </tr>
        <tr>
            <td style="text-align:center;width:50%;">QQ</td>
            <td style="text-align:center;width:50%;">邮箱</td>
        </tr>
        <tr>
            <td style="text-align:center;">421525858</td>
            <td style="text-align:center;">kller@foxmail.com</td>
        </tr>
    </table>
</div>
<div style="margin-top: 20px;">
    <table width="100%" border="0" cellpadding="0" cellspacing="1" class="table_b">
        <tr>
            <td colspan="2"><h5 style="margin:5px 0px;">如果您觉得此插件对你帮助，欢迎打赏我一杯咖啡。^_^</h5></td>
        </tr>
        <tr>
            <td style="text-align:center;width:50%;">支付宝打赏</td>
            <td style="text-align:center;width:50%;">微信打赏</td>
        </tr>
        <tr>
            <td style="text-align:center;"><img style="width:256px;height:256px;" src="<?php echo $this->_getDirPath('res')?>/alipay.png"></td>
            <td style="text-align:center;"><img style="width:256px;height:256px;" src="<?php echo $this->_getDirPath('res')?>/weixin.png"></td>
        </tr>
    </table>
</div>