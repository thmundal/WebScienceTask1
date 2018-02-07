<?php /* Smarty version 2.6.31, created on 2018-02-06 12:04:24
         compiled from content/html/user_list.html */ ?>
<table>
    <?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
        <tr><td><?php echo $this->_tpl_vars['v']->get('username'); ?>
</td><td><?php if ($this->_tpl_vars['v']->getProfile()): ?><a href="chat?user=<?php echo $this->_tpl_vars['v']->get('id'); ?>
">Start chat</a><?php endif; ?></td></tr>
    <?php endforeach; endif; unset($_from); ?>
</table>