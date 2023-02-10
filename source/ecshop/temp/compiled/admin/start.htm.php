<!-- $Id: start.htm 17216 2011-01-19 06:03:12Z liubo $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<!-- directory install start -->
<ul id="cloud_list" style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
 
</ul>
<script type="Text/Javascript" language="JavaScript">
<!--
  // Ajax.call('cloud.php?is_ajax=1&act=cloud_remind','', cloud_api, 'GET', 'JSON');
    // function cloud_api(result)
    // {
    //   //alert(result.content);
    //   if(result.content=='0')
    //   {
    //     document.getElementById("cloud_list").style.display ='none';
    //   }
    //   else
    //    {
    //      document.getElementById("cloud_list").innerHTML =result.content;
    //   }
    // } 
   // function cloud_close(id)
   //  {
   //    Ajax.call('cloud.php?is_ajax=1&act=close_remind&remind_id='+id,'', cloud_api, 'GET', 'JSON');
   //  }
  //-->
 </script> 
<ul id="lilist" style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">
  <?php $_from = $this->_var['warning_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'warning');if (count($_from)):
    foreach ($_from AS $this->_var['warning']):
?>
  <li class="Start315"><?php echo $this->_var['warning']; ?></li>
  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
<ul style="padding:0; margin: 0; list-style-type:none; color: #CC0000;">

</ul>
<!-- directory install end -->
<!-- banner area-->
<div class="ban-area" style="margin-bottom: 10px">
  <div class="inn">
<a href="http://www.ecshopjcw.com/" target="_blank" ><img src="http://www.ecshop119.com/ecshop407/59917800.jpg" width="450" height="300" ></a>
  </div>
</div>
<!-- banner area-->

<!-- start personal message -->
<?php if ($this->_var['admin_msg']): ?>
<div class="list-div" style="border: 1px solid #CC0000">
  <table cellspacing='1' cellpadding='3'>
    <tr>
      <th><?php echo $this->_var['lang']['pm_title']; ?></th>
      <th><?php echo $this->_var['lang']['pm_username']; ?></th>
      <th><?php echo $this->_var['lang']['pm_time']; ?></th>
    </tr>
    <?php $_from = $this->_var['admin_msg']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'msg');if (count($_from)):
    foreach ($_from AS $this->_var['msg']):
?>
      <tr align="center">
        <td align="left"><a href="message.php?act=view&id=<?php echo $this->_var['msg']['message_id']; ?>"><?php echo sub_str($this->_var['msg']['title'],60); ?></a></td>
        <td><?php echo $this->_var['msg']['user_name']; ?></td>
        <td><?php echo $this->_var['msg']['send_date']; ?></td>
      </tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </table>
  </div>
<br />
<?php endif; ?>
<!-- end personal message -->
<!-- start order statistics -->
<div class="panel">
  <h2 class="group-title"><?php echo $this->_var['lang']['order_stat']; ?></h2>
  <table cellspacing='1' cellpadding='3'>
    <tr>
      <th width="12%"><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['await_ship']; ?>"><?php echo $this->_var['lang']['await_ship']; ?></a></th>
      <td width="21%"><strong style="color: red"><?php echo $this->_var['order']['await_ship']; ?></strong></td>
      <th width="12%"><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['unconfirmed']; ?>"><?php echo $this->_var['lang']['unconfirmed']; ?></a></th>
      <td width="21%"><strong><?php echo $this->_var['order']['unconfirmed']; ?></strong></td>
      <th width="12%"><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['await_pay']; ?>"><?php echo $this->_var['lang']['await_pay']; ?></a></th>
      <td width="21%"><strong><?php echo $this->_var['order']['await_pay']; ?></strong></td>
    </tr>
    <tr>
      <th><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['finished']; ?>"><?php echo $this->_var['lang']['finished']; ?></a></td>
      <td><strong><?php echo $this->_var['order']['finished']; ?></strong></th>
      <th><a href="goods_booking.php?act=list_all"><?php echo $this->_var['lang']['new_booking']; ?></a></td>
      <td><strong><?php echo $this->_var['booking_goods']; ?></strong></th>
      <th><a href="user_account.php?act=list&process_type=1&is_paid=0"><?php echo $this->_var['lang']['new_reimburse']; ?></a></th>
      <td><strong><?php echo $this->_var['new_repay']; ?></strong></td>
    </tr>
    <tr>
      <th><a href="order.php?act=list&composite_status=<?php echo $this->_var['status']['shipped_part']; ?>"><?php echo $this->_var['lang']['shipped_part']; ?></a></th>
      <td><strong><?php echo $this->_var['order']['shipped_part']; ?></strong></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</div>
<!-- end order statistics -->
<div class="clearfix" style="min-width: 1090px">
  <div class="panel analysis">
    <!-- start goods statistics -->
    <table class="zebra-table">
      <caption class="group-title"><?php echo $this->_var['lang']['goods_stat']; ?></caption>
      <tbody>
        <tr>
          <th><?php echo $this->_var['lang']['goods_count']; ?></th>
          <td><?php echo $this->_var['goods']['total']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&stock_warning=1"><?php echo $this->_var['lang']['warn_goods']; ?></a></th>
          <td><strong style="color: red"><?php echo $this->_var['goods']['warn']; ?></strong></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_new"><?php echo $this->_var['lang']['new_goods']; ?></a></th>
          <td><?php echo $this->_var['goods']['new']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_best"><?php echo $this->_var['lang']['recommed_goods']; ?></a></th>
          <td><?php echo $this->_var['goods']['best']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_hot"><?php echo $this->_var['lang']['hot_goods']; ?></a></th>
          <td><?php echo $this->_var['goods']['hot']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_promote"><?php echo $this->_var['lang']['sales_count']; ?></a></th>
          <td><?php echo $this->_var['goods']['promote']; ?></td>
        </tr>
      </tbody>
    </table>
    <!-- Virtual Card -->
    <table class="zebra-table">
      <caption class="group-title"><?php echo $this->_var['lang']['virtual_card_stat']; ?></caption>
      <tbody>
        <tr>
          <th><?php echo $this->_var['lang']['goods_count']; ?></th>
          <td><?php echo $this->_var['virtual_card']['total']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;stock_warning=1&amp;extension_code=virtual_card"><?php echo $this->_var['lang']['warn_goods']; ?></a></th>
          <td><strong style="color: red"><?php echo $this->_var['virtual_card']['warn']; ?></strong></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_new&amp;extension_code=virtual_card"><?php echo $this->_var['lang']['new_goods']; ?></a></th>
          <td><?php echo $this->_var['virtual_card']['new']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_best&amp;extension_code=virtual_card"><?php echo $this->_var['lang']['recommed_goods']; ?></a></th>
          <td><?php echo $this->_var['virtual_card']['best']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_hot&amp;extension_code=virtual_card"><?php echo $this->_var['lang']['hot_goods']; ?></a></th>
          <td><?php echo $this->_var['virtual_card']['hot']; ?></td>
        </tr>
        <tr>
          <th><a href="goods.php?act=list&amp;intro_type=is_promote&amp;extension_code=virtual_card"><?php echo $this->_var['lang']['sales_count']; ?></a></th>
          <td><?php echo $this->_var['virtual_card']['promote']; ?></td>
        </tr>
      </tbody>
    </table>
    <!-- end -->
  </div>
  <!-- start access statistics -->
  <ul class="access-list" style="margin: 10px 0 0">
    <li>
      <div class="item">
        <img src="images/index/users.png" alt="">
        <p><?php echo $this->_var['lang']['acess_today']; ?></p>
        <b><?php echo $this->_var['today_visit']; ?></b>
      </div>
    </li>
    <li>
      <div class="item">
        <img src="images/index/onlines.png" alt="">
        <p><?php echo $this->_var['lang']['online_users']; ?></p>
        <b><?php echo $this->_var['online_users']; ?></b>
      </div>
    </li>
    <li>
      <div class="item">
        <img src="images/index/message.png" alt="">
        <p><a href="user_msg.php?act=list_all"><?php echo $this->_var['lang']['new_feedback']; ?></a></p>
        <b><?php echo $this->_var['feedback_number']; ?></b>
      </div>
    </li>
    <li>
      <div class="item">
        <img src="images/index/comments.png" alt="">
        <p><a href="comment_manage.php?act=list"><?php echo $this->_var['lang']['new_comments']; ?></a></p>
        <b><?php echo $this->_var['comment_number']; ?></b>
      </div>
    </li>
  </ul>
</div>
<!-- end access statistics -->
<!-- start system information -->
<div class="panel">
<table cellspacing='1' cellpadding='3'>
  <caption class="group-title"><?php echo $this->_var['lang']['system_info']; ?></caption>
  <tr>
    <th width="12%"><?php echo $this->_var['lang']['os']; ?></th>
    <td width="21%"><?php echo $this->_var['sys_info']['os']; ?> (<?php echo $this->_var['sys_info']['ip']; ?>)</td>
    <th width="12%"><?php echo $this->_var['lang']['web_server']; ?></th>
    <td width="21%"><?php echo $this->_var['sys_info']['web_server']; ?></td>
    <th width="12%"><?php echo $this->_var['lang']['php_version']; ?></th>
    <td width="21%"><?php echo $this->_var['sys_info']['php_ver']; ?></td>
  </tr>
  <tr>
    <th><?php echo $this->_var['lang']['mysql_version']; ?></th>
    <td><?php echo $this->_var['sys_info']['mysql_ver']; ?></td>
    <th><?php echo $this->_var['lang']['safe_mode']; ?></th>
    <td><?php echo $this->_var['sys_info']['safe_mode']; ?></td>
    <th><?php echo $this->_var['lang']['safe_mode_gid']; ?></th>
    <td><?php echo $this->_var['sys_info']['safe_mode_gid']; ?></td>
  </tr>
  <tr>
    <th><?php echo $this->_var['lang']['socket']; ?></th>
    <td><?php echo $this->_var['sys_info']['socket']; ?></td>
    <th><?php echo $this->_var['lang']['timezone']; ?></th>
    <td><?php echo $this->_var['sys_info']['timezone']; ?></td>
    <th><?php echo $this->_var['lang']['gd_version']; ?></th>
    <td><?php echo $this->_var['sys_info']['gd']; ?></td>
  </tr>
  <tr>
    <th><?php echo $this->_var['lang']['zlib']; ?></th>
    <td><?php echo $this->_var['sys_info']['zlib']; ?></td>
    <th><?php echo $this->_var['lang']['ip_version']; ?></th>
    <td><?php echo $this->_var['sys_info']['ip_version']; ?></td>
    <th><?php echo $this->_var['lang']['max_filesize']; ?></th>
    <td><?php echo $this->_var['sys_info']['max_filesize']; ?></td>
  </tr>
  <tr>
    <th><?php echo $this->_var['lang']['ecs_version']; ?></th>
    <td><?php echo $this->_var['ecs_version']; ?> RELEASE <?php echo $this->_var['ecs_release']; ?></td>
    <th><?php echo $this->_var['lang']['install_date']; ?></th>
    <td><?php echo $this->_var['install_date']; ?></td>
    <th><?php echo $this->_var['lang']['ec_charset']; ?></th>
    <td><?php echo $this->_var['ecs_charset']; ?></td>
  </tr>
</table>
</div>


<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js')); ?>
<script type="Text/Javascript" language="JavaScript">
<!--
onload = function()
{
  /* 检查订单 */
  startCheckOrder();
}
  // Ajax.call('index.php?is_ajax=1&act=main_api','', start_api, 'GET', 'TEXT','FLASE');
  //Ajax.call('cloud.php?is_ajax=1&act=cloud_remind','', cloud_api, 'GET', 'JSON');
   // function start_api(result)
   //  {
   //    apilist = document.getElementById("lilist").innerHTML;
   //    document.getElementById("lilist").innerHTML =result+apilist;
   //    if(document.getElementById("Marquee") != null)
   //    {
   //      var Mar = document.getElementById("Marquee");
   //      lis = Mar.getElementsByTagName('div');
   //      //alert(lis.length); //显示li元素的个数
   //      if(lis.length>1)
   //      {
   //        api_styel();
   //      }      
   //    }
   //  }
 
      function api_styel()
      {
        if(document.getElementById("Marquee") != null)
        {
            var Mar = document.getElementById("Marquee");
            if (Browser.isIE)
            {
              Mar.style.height = "52px";
            }
            else
            {
              Mar.style.height = "36px";
            }
            
            var child_div=Mar.getElementsByTagName("div");

        var picH = 16;//移动高度
        var scrollstep=2;//移动步幅,越大越快
        var scrolltime=30;//移动频度(毫秒)越大越慢
        var stoptime=4000;//间断时间(毫秒)
        var tmpH = 0;
        
        function start()
        {
          if(tmpH < picH)
          {
            tmpH += scrollstep;
            if(tmpH > picH )tmpH = picH ;
            Mar.scrollTop = tmpH;
            setTimeout(start,scrolltime);
          }
          else
          {
            tmpH = 0;
            Mar.appendChild(child_div[0]);
            Mar.scrollTop = 0;
            setTimeout(start,stoptime);
          }
        }
        setTimeout(start,stoptime);
        }
      }
//-->
</script>

<?php echo $this->fetch('pagefooter.htm'); ?>
