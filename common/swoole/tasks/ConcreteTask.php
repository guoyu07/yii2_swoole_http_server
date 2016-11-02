<?php
namespace common\swoole\tasks;

use Yii;

class ConcreteTask
{
    public $data = null;

    public function __construct($data){
        $this->data = $data;
    }

    public function execute() {
       //Do the exact consuming tasks.
        return true;
    }
}