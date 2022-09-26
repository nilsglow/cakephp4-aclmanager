<?php

namespace AclManager\Controller;

use App\Controller\AppController as BaseController;
use Cake\Event\EventInterface;

class AppController extends BaseController
{
     /**
     * beforeFitler
     */
    public function beforeFilter(EventInterface $event) {
        parent::beforeFilter($event);
    }
}
