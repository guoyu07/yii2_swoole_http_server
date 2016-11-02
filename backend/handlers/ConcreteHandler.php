<?php
namespace backend\handlers;

use Yii;
use common\swoole\SwooleHttpClient;

class ConcreteHandler
{
    public static function concreteRequest($model) {
        $modelData = ['id'=>$model->id,'type_id'=>$model->type_id,'push_range'=>$model->push_range,'push_to_user_id'=>$model->push_to_user_id,'push_to_tag_id'=>$model->push_to_tag_id];
        $data = ['modelData'=> $modelData,'modelClass'=>'ConcreteTask'];
        $client = new SwooleHttpClient(SwooleHttpClient::HTTPPostMethod,$data);
        return $client->sendRequest();
    }
}