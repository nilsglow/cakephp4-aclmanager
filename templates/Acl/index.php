<?= $this->Html->css('AclManager.default', ['inline' => false]) ?>

<div id="wrapper">
    <div class="wrapper wrapper-content">
        <div class="row panel">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h2><?php echo sprintf(__('Xestor de permisos')); ?></h2>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-lg-12">
                                <h3><?php echo __('Manage'); ?></h3>
                                <ul class="options">
                                    <?php foreach ($manage as $k => $item): ?>
                                      <li><?php echo $this->Html->link(__('Manage {0}', strtolower($item)), ['controller' => 'Acl', 'action' => 'Permissions', $item]); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-12">
                                <h3><?php echo __('Update'); ?></h3>
                                <ul class="options">
                                    <li><?php echo $this->Html->link(__('Update ACOs'), ['controller' => 'Acl', 'action' => 'UpdateAcos']); ?></li>
                                    <li><?php echo $this->Html->link(__('Update AROs'), ['controller' => 'Acl', 'action' => 'UpdateAros']); ?></li>
                                </ul>
                            </div>
                            <div class="col-12">
                                <h3><?php echo __('Drop and restore'); ?></h3>
                                <ul class="options">
                                    <li><?php echo $this->Html->link(__('Revoke permissions and set defaults'), ['controller' => 'Acl', 'action' => 'RevokePerms'], ['confirm' => __('Do you really want to revoke all permissions? This will remove all above assigned permissions and set defaults. Only first item of last ARO will have access to panel.')]); ?></li>
                                    <li><?php echo $this->Html->link(__('Drop ACOs and AROs'), ['controller' => 'Acl', 'action' => 'drop'], ['confirm' => __('Do you really want delete ACOs and AROs? This will remove all above assigned permissions.')]); ?></li>
                                    <li><?php echo $this->Html->link(__('Update ACOs and AROs and set default values'), ['controller' => 'Acl', 'action' => 'defaults'], ['confirm' => __('Do you want restore defaults? This will remove all above assigned permissions. Only first item of last ARO will have access to panel.')]); ?></li>
                                </ul>
                            </div>
                            <hr>
                            <div class="col-12">
                                <?php if ($this->request->getSession()->read('Flash')) { ?>
                                  <div class="row panel">
                                      <div class="columns large-12">
                                          <h3>Response</h3>
                                          <hr />
                                          <?php echo $this->Flash->render(); ?>
                                      </div>
                                  </div>
                                <?php } ?>

  
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
