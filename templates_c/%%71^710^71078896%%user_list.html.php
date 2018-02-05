<?php /* Smarty version 2.6.31, created on 2018-01-31 15:02:12
         compiled from content/html/user_list.html */ ?>
<table>
    <?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
        <tr><td><?php echo $this->_tpl_vars['v']->get('username'); ?>
</td><td><a href="chat?user=<?php echo $this->_tpl_vars['v']->get('id'); ?>
">Start chat</a></td></tr>
    <?php endforeach; endif; unset($_from); ?>
</table>