<?php
namespace common\swoole\tasks;

use Yii;

class SwooleTask
{
    public $modelData = null;
    public $modelClass = null;
    public function __construct($data){
        $this->modelData = $data['modelData'];
        $this->modelClass = $data['modelClass'];
    }

    public function execute() {
        if ($this->modelClass) {
            $class =  'common\\swoole\\tasks\\'.$this->modelClass;
            $model = new $class($this->modelData);
            $result = $model->execute();
            return $result;
        }
        return false;
    }
}