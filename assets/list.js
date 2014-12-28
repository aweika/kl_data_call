jQuery.fn.onlyPressNum = function(){$(this).css('ime-mode','disabled');$(this).css('-moz-user-select','none');$(this).bind('keydown',function(event){var k=event.keyCode;if(!((k==13)||(k==9)||(k==35)||(k == 36)||(k==8)||(k==46)||(k>=48&&k<=57)||(k>=96&&k<=105)||(k>=37&&k<=40))){event.preventDefault();}})}
jQuery(function($){
    $('#internal_call_function, #external_html_call_function, #external_js_call_function').zclip({path:'../content/plugins/kl_data_call/res/ZeroClipboard.swf',copy:function(){return $(this).val();},afterCopy:function(){alert('调用地址已复制');}});
    $('input[name=start_num],input[name=dis_rows],input[name=cache_limit]').onlyPressNum();
    $('#save').click(function(){if($.trim($('#custom_tailor').val())!=''&&!/^\d(\d|,)*$/.test($.trim($('#custom_tailor').val()))){alert('"个人定制"那里只允许输入数字和半角逗号的组合');return false};});
    $('#preview_do').click(function(){
        var kl_t = $('#kl_t').val();
        if(kl_t == 1){
            if($.trim($('#custom_tailor').val())!=''&&!/^\d(\d|,)*$/.test($.trim($('#custom_tailor').val()))){alert('"个人定制"那里只允许输入数字和半角逗号的组合');return false};
            var arguArr = ['kl_t', 'start_num', 'dis_rows', 'custom_tailor', 'author', 'is_include_img', 'order_style', 'date_style'];
        }else if(kl_t == 2){
            var arguArr = ['kl_t', 'start_num', 'dis_rows', 'em_album', 'order_style', 'date_style'];
        }else{
            if($.trim($('#custom_tailor').val())!=''&&!/^\d(\d|,)*$/.test($.trim($('#custom_tailor').val()))){alert('"个人定制"那里只允许输入数字和半角逗号的组合');return false};
            var arguArr = ['kl_t', 'sort', 'start_num', 'dis_rows', 'custom_tailor', 'author', 'filter', 'is_include_img', 'nopwd', 'link_style', 'order_style', 'date_style'];
        }
        var ajaxUrl = $('#ajaxUrl').val();
        var arguStr = '';
        $.each(arguArr, function(){arguStr+=this+'='+$('#'+this).val()+'&'});
        ajaxUrl += '?' + arguStr.substring(0,arguStr.length-1);
        $('#preview').html("<div><span style=\"background-color:#FFFFE5; color:#666666;\">加载中...</span></div>");
        $.post(ajaxUrl,{code:$('#code').val()},function(data){$('#preview').html(data)});
    });
})
