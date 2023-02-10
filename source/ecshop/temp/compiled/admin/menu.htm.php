<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ECSHOP Menu</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="styles/general.css" rel="stylesheet" type="text/css" />

<link href="styles/nav.css" rel="stylesheet" type="text/css" />

<script>
<!--
var noHelp   = "<p align='center' style='color: #666'><?php echo $this->_var['lang']['no_help']; ?></p>";
var helpLang = "<?php echo $this->_var['help_lang']; ?>";
//-->
</script>
</head>
<body class="nav">
<div class="menu">
  <div id="logo-div">
    <a href="index.php"><img width="87" class="logo" src="images/ecshop_logo@2x.png" alt="ECSHOP - power for e-commerce" /></a>
    <?php if ($this->_var['http_host'] == 'localhost' || ! $this->_var['single_url']): ?>
    <a href="javascript:;" class="noauthorize"><img src="images/noauthorize.png" class="icon" width="12"> 未授权用户</a>
    <?php else: ?>
    <a class="<?php if ($this->_var['authorization']): ?>authorize<?php else: ?>noauthorize<?php endif; ?>" href="<?php echo $this->_var['single_url']; ?>" target="_blank"><img src="images/<?php if ($this->_var['authorization']): ?>authorize<?php else: ?>noauthorize<?php endif; ?>.png" class="icon" width="12"> <?php if ($this->_var['authorization']): ?><?php echo $this->_var['authorize_name']; ?><?php else: ?>未授权用户<?php endif; ?></a>
    <?php endif; ?>
  </div>
  <div id="license-div"></div>
  <!-- <div id="tabbar-div">
    <p>
      <span class="tab-front" id="menu-tab"><?php echo $this->_var['lang']['menu']; ?></span>
    </p>
  </div> -->
  <div id="main-div">
    <div id="menu-list">
      <ul class="menu" id="menu-ul">
      <?php $_from = $this->_var['menus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['menu']):
?>
      <?php if ($this->_var['menu']['action']): ?>
        <li><a href="<?php echo $this->_var['menu']['action']; ?>" target="main-frame"><?php echo $this->_var['menu']['label']; ?></a></li>
      <?php else: ?>
        <li key="<?php echo $this->_var['k']; ?>" class="icon-<?php echo $this->_var['menu']['icon']; ?>" data-url="<?php echo $this->_var['menu']['url']; ?>" data-key="<?php echo $this->_var['menu']['key']; ?>" name="menu" onclick="showsub(this)">
          <?php echo $this->_var['menu']['label']; ?>
          <?php if ($this->_var['menu']['children']): ?>
          <div class="submenu">
            <div class="title"><?php echo $this->_var['menu']['label']; ?></div>
            <ul>
            <?php $_from = $this->_var['menu']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['child']):
?>
              <li id="sub-menu-<?php echo $this->_var['key']; ?>" class="menu-item" onclick="showact(this, event)"><a href="<?php echo $this->_var['child']['action']; ?>" target="main-frame"><?php echo $this->_var['child']['label']; ?></a></li>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
          </div>
          <?php endif; ?>
        </li>
      <?php endif; ?>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
      <script language="JavaScript" src="https://api.ecshop.com/menu_ext.php?charset=<?php echo $this->_var['charset']; ?>&lang=<?php echo $this->_var['help_lang']; ?>"></script>
    </div>
    <div id="help-div" style="display:none">
      <h1 id="help-title"></h1>
      <div id="help-content"></div>
    </div>
  </div>
  <div id="foot-div" onmouseover="showBar(this)" onmouseout="hideBar(this)">
    <a href="privilege.php?act=modif" target="main-frame"><?php echo $this->_var['admin_name']; ?></a>
    <div class="panel-hint">
      <ul>
        <li>
          <a href="index.php?act=clear_cache" target="main-frame" class="fix-submenu"><?php echo $this->_var['lang']['clear_cache']; ?></a>
        </li>
        <li class="btn-exit">
          <a href="privilege.php?act=logout" target="_top" class="fix-submenu"><?php echo $this->_var['lang']['signout']; ?></a>
        </li>
      </ul>
    </div>
  </div>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/global.js,../js/utils.js,../js/transport.js')); ?>
<?php echo $this->smarty_insert_scripts(array('files'=>'./js/menu.js')); ?>
<script language="JavaScript">
window.setInterval(crontab,30000);
function crontab(){
  Ajax.call('cloud.php?is_ajax=1&act=load_crontab','','', 'GET', 'JSON');
}
function showBar(item){
  var silb = item.lastElementChild;
  silb.style.display = "block";
}
function hideBar(item){
  var silb = item.lastElementChild;
  silb.style.display = "none";
}
function showsub(el) {
  var act = el.parentNode.getElementsByClassName('active');
  var url = el.getAttribute('data-url') || '';
  var key = el.getAttribute('data-key') || '';

  if (act.length) {
    Array.prototype.slice.call(act).forEach(function(el) {
      el.className = el.className.replace(/\sactive\b/i, '');
    });
  }
  el.className = el.className + ' active';
  top.document.getElementById('frame-body').cols = '240,*';
  if (url) {
    top.document.getElementById('main-frame').src=url;
    document.getElementById('sub-menu-'+key).className = 'menu-item active';
  }
}
function showact(el, e) {
  e.stopPropagation();
  var act = el.parentNode.getElementsByClassName('active');
  if (act.length) {
    Array.prototype.slice.call(act).forEach(function(el) {
      el.className = el.className.replace(/\sactive\b/i, '');
    });
  }
  el.className = el.className + ' active';
}


/**
 * 创建XML对象
 */
function createDocument()
{
  var xmlDoc;

  // create a DOM object
  if (window.ActiveXObject)
  {
    try
    {
      xmlDoc = new ActiveXObject("Msxml2.DOMDocument.6.0");
    }
    catch (e)
    {
      try
      {
        xmlDoc = new ActiveXObject("Msxml2.DOMDocument.5.0");
      }
      catch (e)
      {
        try
        {
          xmlDoc = new ActiveXObject("Msxml2.DOMDocument.4.0");
        }
        catch (e)
        {
          try
          {
            xmlDoc = new ActiveXObject("Msxml2.DOMDocument.3.0");
          }
          catch (e)
          {
            alert(e.message);
          }
        }
      }
    }
  }
  else
  {
    if (document.implementation && document.implementation.createDocument)
    {
      xmlDoc = document.implementation.createDocument("","doc",null);
    }
    else
    {
      alert("Create XML object is failed.");
    }
  }
  xmlDoc.async = false;

  return xmlDoc;
}


</script>

</body>
</html>
