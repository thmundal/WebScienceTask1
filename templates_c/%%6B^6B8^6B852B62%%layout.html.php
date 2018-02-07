<?php /* Smarty version 2.6.31, created on 2018-02-06 13:02:05
         compiled from content/html/layout.html */ ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="content/style/layout.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="content/script/default.js"></script>
    <script src="content/script/api.js"></script>
</head>
<body>

<?php if ($this->_tpl_vars['user'] !== null): ?>
<nav class="main-menu">
    <a href="/websciencetask1">Hjem</a>
    <a href="profile">Profil</a>
    <a href="edit-profile">Rediger profil</a>
    <a href="users">Brukerliste</a>
    <a href="logout">Logg ut</a>
</nav>
<?php endif; ?>

<div class="main-content">
    <?php echo $this->_tpl_vars['template_content']; ?>

</div>

</body>
</html>