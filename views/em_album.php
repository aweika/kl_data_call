<?php !defined('EMLOG_ROOT') && exit('access deined!');?>
<div class="containertitle2">
	<a class="navi1" href="?plugin=kl_data_call">调用列表</a>
	<a class="navi4" href="?plugin=kl_data_call&act=add">文章调用</a>
	<a class="navi4" href="?plugin=kl_data_call&act=add&kl_t=1">微语调用</a>
	<a class="navi3" href="?plugin=kl_data_call&act=add&kl_t=2">EM相册调用</a>
	<?php if(isset($_GET['active_save'])):?><span class="actived">保存成功</span><?php endif;?>
</div>
<?php if($the_notice) exit($the_notice);?>
<form action="./plugin.php?plugin=kl_data_call&action=setting&act=<?php echo $act; ?>" method="POST">
	<input type="hidden" id="kl_t" name="kl_t" value="2">
	<table width="100%" border="0" cellpadding="0" cellspacing="1" class="table_b">
		<tr><td align="left" width="120">ID2：</td><td><input id="did" name="did" style="padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onFocus="this.blur()" type="text" value="<?php echo $did; ?>" /><font color="green"> * 此ID由系统自动分配。</font></td></tr>
		<tr><td align="left">描述：</td><td><input id="description" name="description" type="text" value="<?php if(isset($module['description'])) echo $module['description']; ?>" /> <font color="green"> * 请输入适当的描述，以利于数据管理。</font></td></tr>
		<tr><td align="left">相册：</td><td><select id="em_album" name="em_album"><?php echo $kl_album_option_str; ?></select></td></tr>
		<tr><td align="left">数据的起始行数：</td><td><input id="start_num" name="start_num" type="text" value="<?php echo isset($module['start_num']) ? $module['start_num'] : 0; ?>" /></td></tr>
		<tr><td align="left">数据的显示条数：</td><td><input id="dis_rows" name="dis_rows" type="text" value="<?php echo isset($module['dis_rows']) ? $module['dis_rows'] : 10; ?>" /></td></tr>
		<tr><td align="left">数据的缓存时间(秒)：</td><td><input id="cache_limit" name="cache_limit" type="text" value="<?php echo isset($module['cache_limit']) ? $module['cache_limit'] : 300; ?>" /></td></tr>
		<tr><td align="left">排序方式：</td><td><select id="order_style" name="order_style"><?php echo $order_style_option_str; ?></select> * 设置随机时请将数据缓存时间设置为0</td></tr>
		<tr><td align="left">时间样式：</td><td><select id="date_style" name="date_style"><?php echo $date_style_option_str; ?></select></td></tr>
		<tr><td align="left">可用的变量：</td><td>自增ID{auto_id}, 相册名称{album_name}, 相册描述{album_description}, 相册创建时间{album_datetime}, 相册地址{album_url}<br />图片地址(缩略){thum_photo_url}, 图片地址(原图){photo_url}图片描述{photo_description}, 图片创建时间{photo_datetime}, 相册封面{album_cover}</td></tr>
		<tr><td align="left">数据调用模版：</td><td><textarea id="code" name="code" rows="5" style="width:630px;"><?php if(isset($module['code'])) echo base64_decode($module['code']); ?></textarea></td></tr>
		<tr><td align="left">内部调用方法：</td><td><div style="position:relative;"><input type="text" id="internal_call_function" name="internal_call_function" value="kl_data_call_for_internal(<?php echo $did; ?>, $cols=1, $col=1)" style="width:270px;padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onfocus="this.select()" /><font color="green"> * $cols和COLS代表把数据分为几组，$col和COL代表显示第几组。</font></div></td></tr>
		<tr><td align="left">外部html调用方法：</td><td><div style="position:relative;"><input type="text" id="external_html_call_function" name="external_html_call_function" value="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_do.php?callback=html&ID=<?php echo $did; ?>&COLS=1&COL=1" style="width:630px;padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onfocus="this.select()" /></div></td></tr>
		<tr><td align="left">外部js调用方法：</td><td><div style="position:relative;"><textarea id="external_js_call_function" name="external_js_call_function" style="width:630px;padding:2px; border:1px solid; border-color:#666 #ccc #ccc #666; background:#F9F9F9; color:#333;" onfocus="this.select()"><script charset="utf-8" type="text/javascript" src="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_do.php?ID=<?php echo $did; ?>&COLS=1&COL=1"></script></textarea></div></td></tr>
		<tr><td align="left">预览区域：</td><td><div id="preview" style="width:615px; padding:10px; border:1px dashed #ccc; height:100px;overflow:auto;/*background-color:#bbd9e2;*/"></div></td></tr>
		<tr><td align="left">操作：</td><td><input id="preview_do" type="button" value="预览" /> <input type="submit" value="确认提交" /><input type="hidden" id="ajaxUrl" name="ajaxUrl" value="<?php echo BLOG_URL; ?>content/plugins/kl_data_call/kl_data_call_preview.php"></td></tr>
	</table>
</form>