<?php
/**
 * 插件设置页面日志调用页的模板
 */
!defined('EMLOG_ROOT') && exit('access deined!');
?>
<div class="containertitle2">
    <a class="navi1" href="?plugin=kl_data_call">调用列表</a>
    <a class="navi3" href="?plugin=kl_data_call&act=add">文章调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=1">微语调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=2">EM相册调用</a>
    <a class="navi4" href="?plugin=kl_data_call&act=about" style="color:orange;">关于作者</a>
    <?php if (isset($_GET['active_save'])): ?><span class="actived">保存成功</span><?php endif; ?>
</div>
<form action="./plugin.php?plugin=kl_data_call&action=setting&act=<?php echo $act; ?>" method="POST">
    <input type="hidden" id="kl_t" name="kl_t" value="0">
    <table width="100%" border="0" cellpadding="0" cellspacing="1" class="table_b">
        <tr>
            <td align="left" width="120">ID0：</td>
            <td>
                <input id="did" name="did" style="padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onFocus="this.blur()" type="text" value="<?php echo $did; ?>"/><font color="green"> * 此ID由系统自动分配。</font>
            </td>
        </tr>
        <tr>
            <td align="left">描述：</td>
            <td>
                <input id="description" name="description" type="text" value="<?php if (isset($module['description'])) echo $module['description']; ?>"/>
                <font color="green"> * 请输入适当的描述，以利于数据管理。</font></td>
        </tr>
        <tr>
            <td align="left">个人定制：</td>
            <td>
                <input id="custom_tailor" name="custom_tailor" type="text" value="<?php echo isset($module['custom_tailor']) ? $module['custom_tailor'] : ''; ?>"/><font color="green"> * 此选项可以填入要调用的<strong>文章id</strong>,用<strong>半角的逗号(,)</strong>分隔即可，如(20,15,16,25)。</font><font color="red">(优先级：高)</font>
            </td>
        </tr>
        <tr>
            <td align="left">所在分类：</td>
            <td><select name="sort" id="sort">
                    <option value="-1">所有分类</option><?php echo $sort_option_str ?></select></td>
        </tr>
        <tr>
            <td align="left">作者：</td>
            <td><select id="author" name="author"><?php echo $author_option_str; ?></select></td>
        </tr>
        <tr>
            <td align="left">数据范围一(置顶)：</td>
            <td>
                <select id="filter" name="filter"><?php echo $filter_option_str; ?></select>　　　　　　<label><input id="nopwd" name="nopwd" type="checkbox" value="1" <?php if (isset($module['nopwd']) && $module['nopwd'] == 1) echo 'checked'; ?>/>不包含密码访问的文章</label>
            </td>
        </tr>
        <tr>
            <td align="left">数据范围二(图片)：</td>
            <td><select id="is_include_img" name="is_include_img"><?php echo $is_include_img_option_str; ?></select>
            </td>
        </tr>
        <tr>
            <td align="left">数据的起始行数：</td>
            <td>
                <input id="start_num" name="start_num" type="text" value="<?php echo isset($module['start_num']) ? $module['start_num'] : 0; ?>"/>
            </td>
        </tr>
        <tr>
            <td align="left">数据的显示条数：</td>
            <td>
                <input id="dis_rows" name="dis_rows" type="text" value="<?php echo isset($module['dis_rows']) ? $module['dis_rows'] : 10; ?>"/>
            </td>
        </tr>
        <tr>
            <td align="left">数据的缓存时间(秒)：</td>
            <td>
                <input id="cache_limit" name="cache_limit" type="text" value="<?php echo isset($module['cache_limit']) ? $module['cache_limit'] : 300; ?>"/>
            </td>
        </tr>
        <tr>
            <td align="left">链接打开方式：</td>
            <td><select id="link_style" name="link_style"><?php echo $link_style_option_str; ?></select></td>
        </tr>
        <tr>
            <td align="left">排序方式：</td>
            <td>
                <select id="order_style" name="order_style"><?php echo $order_style_option_str; ?></select> * 设置随机时请将数据缓存时间设置为0
            </td>
        </tr>
        <tr>
            <td align="left">时间样式：</td>
            <td><select id="date_style" name="date_style"><?php echo $date_style_option_str; ?></select></td>
        </tr>
        <tr>
            <td align="left">可用的变量：</td>
            <td>文章链接{log_url}, 标题{title}, 不带链接的标题{title_without_link}, 摘要{excerpt}, 带阅读全文的摘要{excerpt_include_readmore}<br/>自增ID{auto_id}, 时间{date}, 所属分类{sort}, 作者{author}, 浏览次数{views}, 评论数{comment_count}<br/>文章中第一张图片：不带链接的{image}, 带链接的{image_include_link}, 图片地址{imageurl}
            </td>
        </tr>
        <tr>
            <td align="left">数据调用模版：</td>
            <td>
                <textarea id="code" name="code" rows="5" style="width:630px;"><?php if (isset($module['code'])) echo base64_decode($module['code']); ?></textarea>
            </td>
        </tr>
        <tr>
            <td align="left">内部调用方法：</td>
            <td>
                <div style="position:relative;">
                    <input type="text" id="internal_call_function" name="internal_call_function" value="kl_data_call_for_internal(<?php echo $did; ?>, $cols=1, $col=1)" style="width:270px;padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onfocus="this.select()"/><font color="green"> * $cols和COLS代表把数据分为几组，$col和COL代表显示第几组。</font>
                </div>
            </td>
        </tr>
        <tr>
            <td align="left">外部html调用方法：</td>
            <td>
                <div style="position:relative;">
                    <input type="text" id="external_html_call_function" name="external_html_call_function" value="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_do.php?callback=html&ID=<?php echo $did; ?>&COLS=1&COL=1" style="width:630px;padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onfocus="this.select()"/>
                </div>
            </td>
        </tr>
        <tr>
            <td align="left">外部js调用方法：</td>
            <td>
                <div style="position:relative;">
                    <textarea id="external_js_call_function" name="external_js_call_function" style="width:630px;padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onfocus="this.select()"><script charset="utf-8" type="text/javascript" src="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_do.php?ID=<?php echo $did; ?>&COLS=1&COL=1"></script></textarea>
                </div>
            </td>
        </tr>
        <tr>
            <td align="left">预览区域：</td>
            <td>
                <div id="preview" style="width:615px; padding:10px; border:1px dashed #ccc; height:100px;overflow:auto;/*background-color:#bbd9e2;*/"></div>
            </td>
        </tr>
        <tr>
            <td align="left">操作：</td>
            <td><input id="preview_do" type="button" value="预览"/>
                <input id="save" type="submit" value="保存"/><input type="hidden" id="ajaxUrl" name="ajaxUrl" value="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_preview.php">
            </td>
        </tr>
    </table>
</form>