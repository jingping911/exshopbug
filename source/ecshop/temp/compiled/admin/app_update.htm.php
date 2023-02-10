<!-- $Id: shop_config.htm 16865 2009-12-10 06:05:32Z sxc_shop $ -->
<?php echo $this->fetch('pageheader.htm'); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,../js/region.js')); ?>
<div class="tab-div">
        <!-- tab body -->
        <div id="tabbody-div" style="width: 90%; margin: 0 auto; border: 0;">
            <form action="mobile_setting.php?act=app_modify" method="post">
                <table style="width: 500px; margin: 0 auto">

                    <tr>
                        <td style="font-weight: bold;font-size: 12px">app名称:</td>
                        <td><input type="text"  name="app_name" value="<?php echo $this->_var['app']['name']; ?>" style="width:300px;height:30px;"></td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;font-size: 12px">APP当前版本:</td>
                        <td><input type="text" name="app_nowId"  value="<?php echo $this->_var['app']['nowId']; ?>" style="width:300px;height:30px;"></td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;font-size: 12px">APP更新版本:</td>
                        <td><input type="text"  name="app_updateId" value="<?php echo $this->_var['app']['updateId']; ?>" style="width:300px;height:30px;"></td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;font-size: 12px">IOSAPP更新地址:</td>
                        <td><input type="text"  name="app_iosLink"  value="<?php echo $this->_var['app']['iosLink']; ?>" style="width:300px;height:30px;"></td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;font-size: 12px">安卓APP更新地址:</td>
                        <td><input type="text"  name="app_androidLink" value="<?php echo $this->_var['app']['androidLink']; ?>" style="width:300px;height:30px;"></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td><center><input type="submit" value="提交" name="pay_small_submit" style="width: 300px;height: 30px;margin-right: 86px"></center></td>
                    </tr>
                </table>
            </form>

        </div>
    </div>
<?php echo $this->smarty_insert_scripts(array('files'=>'tab.js,validator.js')); ?>

<script language="JavaScript">
        region.isAdmin = true;
        onload = function()
            {
                // 开始检查订单
                startCheckOrder();
        }
        var ReWriteSelected = null;
        var ReWriteRadiobox = document.getElementsByName("value[209]");

            for (var i=0; i<ReWriteRadiobox.length; i++)
            {
                if (ReWriteRadiobox[i].checked)
                {
                    ReWriteSelected = ReWriteRadiobox[i];
            }
        }

            function ReWriterConfirm(sender)
        {
            if (sender == ReWriteSelected) return true;
            var res = true;
            if (sender != ReWriteRadiobox[0]) {
                    var res = confirm('<?php echo $this->_var['rewrite_confirm']; ?>');
                }

                if (res==false)
                {
                    ReWriteSelected.checked = true;
            }
            else
            {
                ReWriteSelected = sender;
            }
            return res;
        }
    </script>

<?php echo $this->fetch('pagefooter.htm'); ?>