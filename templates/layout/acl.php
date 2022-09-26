<!DOCTYPE html>
<html lang="<?= $lang ?? 'es' ?>">
    <head>
        <?= $this->Html->charset(); ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            <?= $cakeDescription ?? null; ?>:
            <?= $this->fetch('title'); ?>
        </title>
        <?=   $this->fetch('meta'); ?>
        <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans" rel="stylesheet">
        <?= $this->Html->meta('icon'); ?>
        <?=
         $this->Html->css([
            'bootstrap.min.css',
            '../font-awesome/css/fontawesome-all.min.css',
            'animate.css',
            '/css/plugins/toastr/toastr.min.css',
            'style.css',
            ]);
        ?>
        <?= $this->fetch('css') ?>
    </head>
    <body class="top-navigation">
        <div id="wrapper">
            <div id="page-wrapper" class="gray-bg">
                <div class="row border-bottom white-bg">
                    <nav class="navbar navbar-static-top" role="navigation">
                        <div class="navbar-header">
                            <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
                                <i class="fa fa-reorder"></i>
                            </button>
                            <?= $this->Html->link(__('ACL'), ['controller' => 'Instalaciones', 'action' => 'index', 'admin' => false, 'plugin' => false], ['class' => 'navbar-brand', 'title' => __('ACL')]); ?>
                        </div>
                        <div class="navbar-collapse collapse" id="navbar">
                            <ul class="nav navbar-nav">
                                <li class="dropdown">
                                    <a aria-expanded="false" role="button" href="#" class="dropdown-toggle" data-toggle="dropdown"><?= __('Xestión') ?><span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu options">
                                        <?php
                                        foreach ($manage as $k => $item) {
                                          $title_link = $item;
                                          switch ($item) {
                                            case 'Groups':
                                              $title_link = 'Grupos';
                                              break;
                                            case 'Roles':
                                              $title_link = 'Roles';
                                              ?>
                                              <li><?php echo $this->Html->link($title_link, ['controller' => 'Acl', 'action' => 'Permissions', $item]); ?></li>
                                              <?php
                                              break;
                                            case 'Users':
                                              $title_link = 'Usuarios';
                                              break;
                                          }
                                        }
                                        ?>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a aria-expanded="false" role="button" href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __('Actualización') ?><span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu options">
                                        <li><?= $this->Html->link(__('Actualizar ACOs'), ['controller' => 'Acl', 'action' => 'UpdateAcos']); ?></li>
                                        <li><?= $this->Html->link(__('Actualizar AROs'), ['controller' => 'Acl', 'action' => 'UpdateAros']); ?></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a aria-expanded="false" role="button" href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo __('Eliminar e Restaurar') ?><span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu options">
                                        <li><?= $this->Html->link(__('Revocar permisos e establecer valores predeterminados'), ['controller' => 'Acl', 'action' => 'RevokePerms'], ['confirm' => __('Desexas revocar todos os permisos? Esto eliminará todos os permisos asignados anteriormente e establecerá os valores predeterminados. Só o primeiror elemento do último ARO terá acceso ao panel.')]); ?></li>
                                        <li><?= $this->Html->link(__('Eliminar ACOs e AROs'), ['controller' => 'Acl', 'action' => 'drop'], ['confirm' => __('Desexas eliminar ACO e ARO? Isto eliminará todos os permisos asignados anteriormente.')]); ?></li>
                                        <li><?= $this->Html->link(__('Actualiza ACO e ARO e configura os valores predeterminados'), ['controller' => 'Acl', 'action' => 'defaults'], ['confirm' => __('Desexas restaurar os valores predeterminados? Isto eliminará todos os permisos asignados anteriormente. Só o primeiro elemento do último ARO terá acceso ao panel.')]); ?></li>
                                    </ul>
                                </li>
                            </ul>
                            <ul class="nav navbar-top-links navbar-right">
                                <li>
                                    <a href="<?php echo $this->Url->build(["action" => "logout"]); ?>">
                                        <i class="fas fa-sign-out-alt"></i> <?php echo __('Saír') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
                <?php echo $this->Flash->render(); ?>

                <?php echo $this->fetch('content'); ?>
            </div>
        </div>
        <?=
        $this->Html->script([
            "/js/jquery-3.4.1.min.js",
            "/js/bootstrap.min.js",
            "/js/plugins/metisMenu/jquery.metisMenu.js",
            "/js/plugins/slimscroll/jquery.slimscroll.min.js",
            '/js/plugins/toastr/toastr.min'
        ]);
        ?>

        <?= $this->fetch('script'); ?>
    </body>
</html>
