<?php
/**
 * 插件设置页面列表页的模板
 */
!defined('EMLOG_ROOT') && exit('access deined!');
?>
<div class="containertitle2">
    <a class="navi3" href="?plugin=kl_data_call">调用列表</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add">文章调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=1">微语调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=2">EM相册调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=about" style="color:orange;">关于作者</a>
    <?php if (isset($_GET['active_del'])): ?><span class="actived">删除成功</span><?php endif; ?>
    <?php if (isset($_GET['active_update'])): ?><span class="actived">更新缓存成功</span><?php endif; ?>
    <span style="float:right;">
        <form action="./plugin.php?plugin=kl_data_call&action=setting&update=true" method="POST" onsubmit="return confirm('确定要更新所有数据调用缓存吗？');">
            <input name="kl_data_call_do" class="copy" type="submit" value="一键更新所有数据调用缓存"/>
        </form>
    </span>
</div>
<script type="text/javascript">
    function delmenu(id) {
        if (confirm('确定删除此菜单栏目？')) {
            document.location = './plugin.php?plugin=kl_data_call&action=setting&act=del&id=' + id;
        }
    }
    jQuery(function ($) {
        $('input[id^=internal_call_function], input[id^=external_html_call_function], input[id^=external_js_call_function]').zclip({
            path: '../content/plugins/kl_data_call/res/ZeroClipboard.swf',
            copy: function () {
                return $(this).prev().val();
            },
            afterCopy: function () {
                alert('调用地址已复制');
            }
        });
    });
</script>
<table width="100%" id="kl_data_call_list" class="item_list">
    <thead>
    <tr>
        <th height="25" class="tdcenter"><b>自增序号</b></th>
        <th class="tdcenter"><b>调用ID</b></th>
        <th class="tdcenter"><b>调用类型</b></th>
        <th class="tdcenter"><b>模块描述</b></th>
        <th class="tdcenter"><b>内部地址</b></th>
        <th class="tdcenter"><b>外部html调用</b></th>
        <th class="tdcenter"><b>外部js调用</b></th>
        <th class="tdcenter"><b>操作</b></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 1;
    if ($data_call_module_config):
        foreach ($data_call_module_config as $id => $module):
            $kl_t = isset($module['kl_t']) && in_array($module['kl_t'], array_keys($kl_t_array)) ? $module['kl_t'] : 0;
            $kl_t_str = $kl_t_array[$kl_t];
            ?>
            <tr>
                <td height="25" class="tdcenter"><?php echo $i; ?></td>
                <td class="tdcenter"><?php echo $module['did']; ?></td>
                <td class="tdcenter"><?php echo $kl_t_str; ?></td>
                <td class="tdcenter"><?php echo $module['description']; ?></td>
                <td class="tdcenter">
                    <div style="position:relative;">
                        <input type="hidden" value="kl_data_call_for_internal(<?php echo $module['did']; ?>, $cols=1, $col=1)"/><input type="button" id="internal_call_function_<?php echo $module['did']; ?>" value="复制" class="copy"/>
                    </div>
                </td>
                <td class="tdcenter">
                    <div style="position:relative;">
                        <input type="hidden" value="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_do.php?callback=html&ID=<?php echo $module['did']; ?>&COLS=1&COL=1"/><input type="button" id="external_html_call_function_<?php echo $module['did']; ?>" value="复制" class="copy"/>
                    </div>
                </td>
                <td class="tdcenter">
                    <div style="position:relative;">
                        <input type="hidden" value="<?php echo htmlspecialchars('<script charset="utf-8" type="text/javascript" src="' . BLOG_URL . 'content/plugins/kl_data_call/kl_data_call_do.php?ID=' . $module['did'] . '&COLS=1&COL=1"></script>');?>"/><input type="button" id="external_js_call_function_<?php echo $module['did']; ?>" value="复制" class="copy"/>
                    </div>
                </td>
                <td class="tdcenter">
                    <form action="./plugin.php?plugin=kl_data_call&action=setting&act=del&id=<?php echo $id; ?>" method="POST" onsubmit="javascript:if(!confirm('确定要删除？')) return false;">
                        <input type="button" value="编辑" class="edit" onclick="location.href='./plugin.php?plugin=kl_data_call&act=edit&id=<?php echo $id; ?>'">
                        <input name="del" type="submit" class="del" value="删除"/>
                    </form>
                </td>
            </tr>
            <?php
            $i++;endforeach;
    else:?>
        <tr>
            <td class="tdcenter" colspan="8">还没有添加数据调用</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
<script type="text/javascript">
    $(document).ready(function () {
        $("#kl_data_call_list tbody tr:odd").addClass("tralt_b");
        $("#kl_data_call_list tbody tr").mouseover(function () {
            $(this).addClass("trover")
        }).mouseout(function () {
            $(this).removeClass("trover")
        })
    });
</script>