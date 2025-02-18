<?php


namespace AclManager\Controller;

use Acl\Controller\Component\AclComponent;
use AclManager\Controller\AppController;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use AclManager\AclExtras;
use Cake\Event\EventInterface;

class AclController extends AppController {

    protected $AclExtras;

    /**
     * Model
     *
     * @var NULL
     */
    public $model = NULL;
    public $lookup = 0;


    /**
     * Initialize
     */
    public function initialize(): void{
        parent::initialize();
       $this->loadComponent('Acl.Acl');
       $this->loadComponent('AclManager.AclManager');

        /**
         * Initialize ACLs
         */
        $registry = new ComponentRegistry();
        $this->Acl = new AclComponent($registry, Configure::read('Acl'));
        $this->AclExtras = new AclExtras();
        $this->AclExtras->startup($this);

        /**
         * Loading required Model
         */
        $models = Configure::read('AclManager.models');
        foreach ($models as $model) {
            $this->loadModel($model);
        }
        $this->loadModel('Acl.Permissions');
        $this->loadModel('Acos');

        /**
         * Pagination
         */
        $aros = Configure::read('AclManager.aros');
        foreach ($aros as $aro) {

            $l = Configure::read("AclManager.{$aro}.limit");
            $limit = empty($l) ? 4 : $l;
            $this->paginate[$this->{$aro}->getAlias()] = array(
                'recursive' => -1,
                'limit' => $limit
            );
        }

        $this->viewBuilder()->setLayout('acl');

        return;

    }

    public function beforeFilter(EventInterface $event) {
        $actions = [
            'index',
            'permissions'
        ];
        if (in_array($this->request->getParam('action'), $actions)) {
            $this->Security->setConfig('unlockedActions', $actions);
            $this->Auth->allow($actions);
        }
        parent::beforeFilter($event);
        
    }

    /**
     * AclManager main page
     */
    public function index() {
        if(!$this->Auth->user()){
            $this->Flash->error(__('Non tes permisos para acceder ao panel de administración. Loguéate.'));
            $this->redirect('/users/logout');
        }
        $manage = Configure::read('AclManager.aros');
        $this->set(compact('manage'));
    }

    /**
     * Manage Permissions
     */
    public function permissions($model = NULL) {

        if(!$this->Auth->user()){
            $this->Flash->error(__('Non tes permisos para acceder ao panel de administración. Loguéate.'));
            $this->redirect('/users/logout');
        }

        $this->model = $model;

        // Saving permissions
        if ($this->request->is('post') || $this->request->is('put')) {
            $perms = !empty($this->request->getData('Perms')) ? $this->request->getData('Perms') : array();
            foreach ($perms as $aco => $aros) {
                $action = str_replace(":", "/", $aco);
                foreach ($aros as $node => $perm) {
                    list($model, $id) = explode(':', $node);
                    $node = array('model' => $model, 'foreign_key' => $id);
                    if ($perm == 'allow') {
                        $this->Acl->allow($node, $action);
                    } elseif ($perm == 'inherit') {
                        $this->Acl->inherit($node, $action);
                    } elseif ($perm == 'deny') {
                        $this->Acl->deny($node, $action);
                    }
                }
            }
        }

        if (!$model || !in_array($model, Configure::read('AclManager.aros'))) {
            $m = Configure::read('AclManager.aros');
            $model = $m[0];
        }

        $Aro = $this->{$model};

        $arosRes = $this->paginate($Aro->getAlias());
        $aros = $this->_parseAros($arosRes);
        $permKeys = $this->_getKeys();

        /**
         * Build permissions info
         */
        $acosRes = $this->Acl->Aco->find('all', ['order' => 'lft ASC'])->contain(['Aros'])->toArray();
        $this->acos = $acos = $this->_parseAcos($acosRes);

        $perms = array();
        $parents = array();
        foreach ($acos as $key => $data) {
            $aco = & $acos[$key];
            $aco = array('Aco' => $data['Aco'], 'Aro' => $data['Aro'], 'Action' => array());
            $id = $aco['Aco']['id'];

            // Generate path
            if ($aco['Aco']['parent_id'] && isset($parents[$aco['Aco']['parent_id']])) {
                $parents[$id] = $parents[$aco['Aco']['parent_id']] . '/' . $aco['Aco']['alias'];
            } else {
                $parents[$id] = $aco['Aco']['alias'];
            }

            $aco['Action'] = $parents[$id];

            // Fetching permissions per ARO
            $acoNode = $aco['Action'];

            foreach ($aros as $aro) {
                $aroId = $aro[$Aro->getAlias()]['id'];
                $evaluate = $this->_evaluate_permissions($permKeys, array('id' => $aroId, 'alias' => $Aro->getAlias()), $aco, $key);
                $perms[str_replace('/', ':', $acoNode)][$Aro->getAlias() . ":" . $aroId . '-inherit'] = $evaluate['inherited'];
                $perms[str_replace('/', ':', $acoNode)][$Aro->getAlias() . ":" . $aroId] = $evaluate['allowed'];
            }
        }

        //$this->request->data = ['Perms' => $perms];

        $permisos = ['Perms' => $perms];
        $this->set('permisos', $permisos);

        $this->set('limit',3);
        $this->set('model', $model);
        $this->set('manage', Configure::read('AclManager.aros'));
        $this->set('hideDenied', Configure::read('AclManager.hideDenied'));
        $this->set('aroAlias', $Aro->getAlias());
        $this->set('aroDisplayField', $Aro->getDisplayField());
        $this->set(compact('acos', 'aros'));
    }


    /**
     * Update ACOs
     */
    public function updateAcos() {

        $this->AclExtras->acoUpdate();

        $url = ($this->request->referer() == '/') ? ['plugin' => 'AclManager','controller' => 'Acl','action' => 'index'] : $this->request->referer();
        $this->redirect($url);
    }

    /**
     * Update AROs
     */
    public function updateAros() {
	    $arosCounter = $this->AclManager->arosBuilder();
        $this->Flash->success(sprintf(__("%d AROs foron creados, actualizados ou eliminados"), $arosCounter));
        $url = ($this->request->referer() == '/') ? ['plugin' => 'AclManager','controller' => 'Acl','action' => 'index'] : $this->request->referer();
        $this->redirect($url);
    }

    /**
     * Delete permissions
     */
    public function revokePerms() {
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute('TRUNCATE TABLE aros_acos');
        $info = $stmt->errorInfo();

        if ($info != null && !empty($info)) {
            $this->Flash->success(__("Eliminador topos os permisos!"));
        } else {
            $this->Flash->error(__("Produciuse un erro ao tentar eliminar os permisos"));
        }

        /**
         * Get  Model
         */
        $models = Configure::read('AclManager.aros');
        $mCounter = 0;
        foreach ($models as $model) {
            if($mCounter == (count($models)-1)) {
                $f = $this->{$model}->find('all',
                    ['order' => [$model.'.id' => 'ASC']
                ])->first();

                $this->log($f, LOG_DEBUG);

                $this->Acl->allow([$model => ['id' => $f->id]], 'controllers');
                $this->Flash->success(__("Permisos outorgados a {0} co id {1}", $model, (int)$f->id));
            }
            $mCounter++;
        }

        $this->redirect(array("action" => "permissions"));
    }

    /**
     * Delete everything (ACOs and AROs)
     */
    public function drop() {

        $conn = ConnectionManager::get('default');

        $stmt1 = $conn->execute('TRUNCATE TABLE aros_acos');
        $info1 = $stmt1->errorInfo();
        if ($info1[1] != null) {
            $this->log($info1, LOG_ERR);
            if(!empty($info1)) {
                $this->Flash->error($info1);
            }
        }

        $stmt2 = $conn->execute('TRUNCATE TABLE acos');
        $info2 = $stmt2->errorInfo();
        if ($info2[1] != null) {
            $this->log($info2, LOG_ERR);
            if(!empty($info2)) {
                $this->Flash->error($info2);
            }
        }

        $stmt3 = $conn->execute('TRUNCATE TABLE aros');
        $info3 = $stmt3->errorInfo();
        if ($info3[1] != null) {
            $this->log($info3, LOG_ERR);
            if(!empty($info3)) {
                $this->Flash->error($info3);
            }
        }

        $this->Flash->success(__("ACOs e AROs foron eliminados."));
        $this->redirect(["action" => "index"]);
    }

    /**
     * Delete everything (ACOs and AROs)
     *
     * TODO: Check $stmt->errorInfo();
     */
    public function defaults() {
        $conn = ConnectionManager::get('default');

        $stmt1 = $conn->execute('TRUNCATE TABLE aros_acos');
        $info1 = $stmt1->errorInfo();
        if ($info1[1] != null) {
            $this->log($info1, LOG_ERR);
            if(!empty($info1)) {
                $this->Flash->error($info1);
            }
        }

        $stmt2 = $conn->execute('TRUNCATE TABLE acos');
        $info2 = $stmt2->errorInfo();
        if ($info2[1] != null) {
            $this->log($info2, LOG_ERR);
            if(!empty($info2)) {
                $this->Flash->error($info2);
            }
        }

        $stmt3 = $conn->execute('TRUNCATE TABLE aros');
        $info3 = $stmt3->errorInfo();
        if ($info3[1] != null) {
            $this->log($info3, LOG_ERR);
            if(!empty($info3)) {
                $this->Flash->error($info3);
            }
        }

        $this->Flash->success(__("ACOs e AROs foron eliminados."));


        /**
         * ARO Sync
         */
        $aros = $this->AclManager->arosBuilder();
        $this->Flash->success(sprintf(__("%d AROs foron creados, actualizados ou eliminados"), $aros));
        // $this->Flash->success(__("AROs update complete"));

        /**
         * ACO Sync
         */
        $this->AclExtras->acoUpdate();

        /**
         * Get  Model
         */
        $models = Configure::read('AclManager.aros');
        $mCounter = 0;
        foreach ($models as $model) {
            if($mCounter == (count($models)-1)) {
                $f = $this->{$model}->find('all',
                    ['order' => [$model.'.id' => 'ASC']
                ])->first();

                $this->log($f, LOG_DEBUG);

                $this->Acl->allow([$model => ['id' => $f->id]], 'controllers');
                $this->Flash->success(__("Permisos outorgados a {0} co id {1}", $model, (int)$f->id));
            }
            $mCounter++;
        }

        $this->Flash->success(__("Parabéns! Todo restaurado por defecto."));
        $this->redirect(["action" => "index"]);
    }

	public function logout(){
        $session = $this->getRequest()->getSession();
        $session->destroy();
		return $this->redirect('/users/logout');
	}

    /**
     * Recursive function to find permissions avoiding slow $this->Acl->check().
     */
    private function _evaluate_permissions($permKeys, $aro, $aco, $aco_index) {

        $this->acoId = $aco['Aco']['id'];
        $result = $this->Acl->Aro->find('all', [
                    'contain' => ['Permissions' => function ($q) {
                            return $q->where(['aco_id' => $this->acoId]);
                        }
                            ],
                            'conditions' => [
                                'model' => $aro['alias'],
                                'foreign_key' => $aro['id']
                            ]
                        ])->toArray();

        $permissions = array_shift($result);
        $permissions = array_shift($permissions->permissions);

        $allowed = false;
        $inherited = false;
        $inheritedPerms = array();
        $allowedPerms = array();

        /**
         * Manually checking permission
         * Part of this logic comes from DbAcl::check()
         */
        foreach ($permKeys as $key) {
            if (!empty($permissions)) {
                if ($permissions[$key] == '-1') {
                    $allowed = false;
                    break;
                } elseif ($permissions[$key] == '1') {
                    $allowed = true;
                    $allowedPerms[$key] = 1;
                } elseif ($permissions[$key] == '0') {
                    $inheritedPerms[$key] = 0;
                }
            } else {
                $inheritedPerms[$key] = 0;
            }
        }

        if (count($allowedPerms) === count($permKeys)) {
            $allowed = true;
        } elseif (count($inheritedPerms) === count($permKeys)) {
            if ($aco['Aco']['parent_id'] == null) {
                $this->lookup +=1;
                $acoNode = (isset($aco['Action'])) ? $aco['Action'] : null;
                $aroNode = array('model' => $aro['alias'], 'foreign_key' => $aro['id']);
                $allowed = $this->Acl->check($aroNode, $acoNode);
                $this->acos[$aco_index]['evaluated'][$aro['id']] = array(
                    'allowed' => $allowed,
                    'inherited' => true
                );
            } else {
                /**
                 * Do not use Set::extract here. First of all it is terribly slow,
                 * besides this we need the aco array index ($key) to cache are result.
                 */
                foreach ($this->acos as $key => $a) {
                    if ($a['Aco']['id'] == $aco['Aco']['parent_id']) {
                        $parent_aco = $a;
                        break;
                    }
                }
                // Return cached result if present
                if (isset($parent_aco['evaluated'][$aro['id']])) {
                    return $parent_aco['evaluated'][$aro['id']];
                }

                // Perform lookup of parent aco
                $evaluate = $this->_evaluate_permissions($permKeys, $aro, $parent_aco, $key);

                // Store result in acos array so we need less recursion for the next lookup
                $this->acos[$key]['evaluated'][$aro['id']] = $evaluate;
                $this->acos[$key]['evaluated'][$aro['id']]['inherited'] = true;

                $allowed = $evaluate['allowed'];
            }
            $inherited = true;
        }

        return array(
            'allowed' => $allowed,
            'inherited' => $inherited,
        );
    }

    /**
     * Returns permissions keys in Permission schema
     * @see DbAcl::_getKeys()
     */
    protected function _getKeys() {
        $keys = $this->Permissions->getSchema()->columns();
        $newKeys = array();
        foreach ($keys as $key) {
            if (!in_array($key, array('id', 'aro_id', 'aco_id'))) {
                $newKeys[] = $key;
            }
        }
        return $newKeys;
    }

    /**
     * Returns all the ACOs including their path
     */

    protected function _getAcos() {
        $acos = $this->Acl->Aco->find('all', array('order' => 'Acos.lft ASC', 'recursive' => -1))->toArray();
        $parents = array();
        foreach ($acos as $key => $data) {

            $aco = & $acos[$key];
            $aco = $aco->toArray();
            $id = $aco['id'];

            // Generate path
            if ($aco['parent_id'] && isset($parents[$aco['parent_id']])) {
                $parents[$id] = $parents[$aco['parent_id']] . '/' . $aco['alias'];
            } else {
                $parents[$id] = $aco['alias'];
            }
            $aco['action'] = $parents[$id];
        }
        return $acos;
    }

    /**
     * Returns an array with acos
     * @param Acos $acos Parse Acos entities and store into array formated
     * @return array
     */
    private function _parseAcos($acos) {
        $cache = [];
        foreach ($acos as $aco) {
            $data['Aco'] = [
                'id' => $aco->id,
                'parent_id' => $aco->parent_id,
                'foreign_key' => $aco->foreign_key,
                'alias' => $aco->alias,
                'lft' => $aco->lft,
                'rght' => $aco->rght,
            ];
            if (isset($aco->model)) {
                $data['Aco']['model'] = $aco->model;
            }

            $d = [];
            foreach ($aco['aros'] as $aro) {
                $d[] = [
                    'id' => $aro->id,
                    'parent_id' => $aro->parent_id,
                    'model' => $aro->model,
                    'foreign_key' => $aro->foreign_key,
                    'alias' => $aro->alias,
                    'lft' => $aro->lft,
                    'rght' => $aro->rght,
                    'Permission' => [
                        'aro_id' => $aro->_joinData->aro_id,
                        'id' => $aro->_joinData->id,
                        'aco_id' => $aro->_joinData->aco_id,
                        '_create' => $aro->_joinData->_create,
                        '_read' => $aro->_joinData->_read,
                        '_update' => $aro->_joinData->_update,
                        '_delete' => $aro->_joinData->_delete,
                    ]
                ];
            }

            $data['Aro'] = $d;

            array_push($cache, $data);
        }

        return $cache;
    }

    /**
     * Returns an array with aros
     * @param Aros $aros Parse Aros entities and store into an array.
     * @return array
     */
    private function _parseAros($aros) {
        $cache = array();
        foreach ($aros as $aro) {
            $data[$this->model] = $aro;
            array_push($cache, $data);
        }

        return $cache;
    }

}
