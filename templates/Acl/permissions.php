<?php

use Cake\Core\Configure;
use Cake\Utility\Inflector;

echo $this->Html->css('AclManager.default',['plugin' => true, 'inline' => false]);
$title_page = $model;
switch($model){
    /*case 'Groups':
        $title_page = 'Grupos';
        break;*/
    case 'Roles':
        $title_page = 'Roles';
        break;
    /*case 'Users':
        $title_page = 'Usuarios';
        break;*/
}
?>
<div id="wrapper">
	<div class="wrapper wrapper-content">
        <div class="row panel">
            <div class="col-lg-12">
                <div class="ibox">
					<div class="ibox-title">
                        <h2><?php echo sprintf(__($title_page)); ?></h2>
                    </div>
					<div class="ibox-content">
                        <?php echo $this->Form->create(null); ?>
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Acción')?></th>
                                            <?php
                                                foreach ($aros as $aro){
                                                    $aro = array_shift($aro);
                                                    ?>
                                                    <th>
                                                        <?php
                                                        $parentNode = $aro->parentNode();
                                                        if (!is_null($parentNode)) {
                                                            $key = key($parentNode);
                                                            $subKey = key($parentNode[$key]);
                                                            $gData = $this->AclManager->getName($key, $parentNode[key($parentNode)][$subKey]);
                                                            echo h($aro[$aroDisplayField]) . ' ( ' . $gData['name'] . ' )';
                                                        } else {
                                                            echo h($aro[$aroDisplayField]);
                                                        }
                                                        ?>
                                                    </th>
                                                <?php
                                                }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $uglyIdent = Configure::read('AclManager.uglyIdent');
                                        $lastIdent = null;
                                        foreach ($acos as $id => $aco) {
                                            $action = $aco['Action'];
                                            $alias = $aco['Aco']['alias'];
                                            $ident = substr_count($action, '/');

                                            if ($ident <= $lastIdent && !is_null($lastIdent)) {
                                                for ($i = 0; $i <= ($lastIdent - $ident); $i++) {
                                                    echo "</tr>";
                                                }
                                            }

                                            if ($ident != $lastIdent) {
                                                echo "<tr class='aclmanager-ident-" . $ident . "'>";
                                            }

                                            $uAllowed = true;
                                            if($hideDenied) {
                                                $uAllowed = $this->AclManager->Acl->check(['Users' => ['id' => $this->request->getSession()->read('Auth.User.id')]], $action);
                                            }

                                            if ($uAllowed) {
                                                echo "<td>";
                                                echo Inflector::humanize(($ident == 1 ? "<strong>" : "" ) . ($uglyIdent ? str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $ident) : "") . h($alias) . ($ident == 1 ? "</strong>" : "" ));
                                                echo "</td>";

                                                foreach ($aros as $aro):
                                                    $inherit = $this->AclManager->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}-inherit",$permisos);
                                                    $allowed = $this->AclManager->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}",$permisos);

                                                    $mAro = $model;
                                                    $mAllowed = $this->AclManager->Acl->check($aro, $action);
                                                    $mAllowedText = ($mAllowed) ? 'Permitir' : 'Negar';

                                                    // Originally based on 'allowed' above 'mAllowed'
                                                    $icon = ($mAllowed) ? $this->Html->image('AclManager.allow_24.png') : $this->Html->image('AclManager.deny_24.png');

                                                    if ($inherit) {
                                                        $icon = $this->Html->image('AclManager.inherit_24.png');
                                                    }

                                                    if ($mAllowed && !$inherit) {
                                                        $icon = $this->Html->image('AclManager.allow_24.png');
                                                        $mAllowedText = 'Permitir';
                                                    }

                                                    if ($mAllowed && $inherit) {
                                                        $icon = $this->Html->image('AclManager.allow_inherited_24.png');
                                                        $mAllowedText = 'Herdar';
                                                    }

                                                    if (!$mAllowed && $inherit) {
                                                        $icon = $this->Html->image('AclManager.deny_inherited_24.png');
                                                        $mAllowedText = 'Herdar';
                                                    }

                                                    if (!$mAllowed && !$inherit) {
                                                        $icon = $this->Html->image('AclManager.deny_24.png');
                                                        $mAllowedText = 'Negar';
                                                    }

                                                    echo "<td class=\"select-perm\">";
                                                            echo $icon . ' ' . $this->Form->select("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}", array('inherit' => __('Herdar'), 'allow' => __('Permitir'), 'deny' => __('Negar')), array('empty' => __($mAllowedText), 'class' => 'form-control'));
                                                    echo "</td>";
                                                endforeach;

                                                $lastIdent = $ident;
                                            }
                                        }

                                        for ($i = 0; $i <= $lastIdent; $i++) {
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="paginator">
                                    <?php
                                    if($this->Paginator->total() > $limit){
                                        ?>
                                        <ul class="pagination">
                                            <?php
                                            echo $this->Paginator->prev('<');
                                            echo $this->Paginator->numbers();
                                            echo $this->Paginator->next('>');
                                            ?>
                                        </ul>
                                        <p><?php echo $this->Paginator->counter(__('Páxina {{page}} de {{pages}}<br/>{{current}} rexistros dun total de {{count}}')); ?></p>
                                        <?php
                                    }else{
                                        ?>
                                        <p><?php echo $this->Paginator->counter(__('{{current}} rexistros dun total de {{count}}')); ?></p>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <button type="submit" class="btn btn-primary"><?php echo __("Gardar"); ?></button>
                            </div>
                        </div>
                        <?php echo $this->Form->end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
