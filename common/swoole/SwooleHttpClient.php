<?php

namespace common\swoole;

use Yii;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\RequestEvent;

class SwooleHttpClient {

    public $client;
    public $request;
    public $response;
    public $method;
    const HTTPPostMethod = 'post';
    const HTTPGetMethod = 'get';
    const HTTPPutMethod = 'put';
    const HTTDeleteMethod = 'delete';

    public function __construct($method,$data)
    {
        $this->method = $method;
        $this->client = new Client();
        $this->request = $this->client->createRequest()
            ->setMethod($method)
            ->setUrl('http://127.0.0.1:9501')
            ->setData($data);

        // 发送前触发事件
        //$this->request->on(Request::EVENT_BEFORE_SEND, function (RequestEvent $event) {
        //    $data = $event->request->getData();
            //$signature = md5(http_build_query($data));
            //$data['signature'] = $signature;
        //    $event->request->setData($data);
        //});

        // 发送后响应数据
        //$this->request->on(Request::EVENT_AFTER_SEND, function (RequestEvent $event) {
        //    $data = $event->response->getData();
            //$data['content'] = base64_decode($data['encoded_content']);
        //    $event->response->setData($data);
        //});
    }

    public function sendRequest() {
        $this->response = $this->request->send();
        if ($this->response->isOk) {
            return true;
        } else {
            return false;
        }
    }
}



