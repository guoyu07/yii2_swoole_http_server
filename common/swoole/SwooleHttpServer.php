<?php
require __DIR__ . '/../../vendor/autoload.php'; // autoload by PSR
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php'; // Yii Core
require __DIR__ . '/../../common/config/bootstrap.php'; // register namespaces
require_once "SwooleTask.php";

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../config/main.php')
   // require(__DIR__ . '/../config/main-local.php')
);
$config['params'] = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../config/params.php'
    //require __DIR__ . '/../config/params-local.php'
);

class SwooleHttpServer
{
    public  $swooleServer = null;
    public  $currentRequest = null;
    public  $currentResponse = null;
    public static $swooleApp = null;
    public static $logFile = null;
    public static $pidFile = null;
    public static $config = [];
    public static $swooleConfig = [];

    public function __construct($config)
    {
	    $swooleConfig = $config['params']['swoole'];
	    self::$swooleConfig = $swooleConfig;
        unset($config['params']['swoole']);
        self::$config    = $config;
        self::$swooleApp = $this;
	    self::$pidFile = $swooleConfig['pidFile'];
        self::$logFile = $swooleConfig['setting']['log_file'];
        $masterPid = file_exists(self::$pidFile) ? file_get_contents(self::$pidFile) : null;
        global $argv;
        if (!isset($argv[1])) {
            print_r("php {$argv[0]} start|reload|stop");
            return;
        }
        switch ($argv[1]) {
            case 'start':
                if ($masterPid > 0) {
                    print_r('Server is already running. Please stop it first.');
                    return;
                }
                $this->startSwooleServer($swooleConfig);
                break;
            case 'reload':
                if (!empty($masterPid)) {
                    posix_kill($masterPid, SIGUSR1); // reload all worker
                    posix_kill($masterPid, SIGUSR2); // reload all task
                } else {
                    print_r('master pid is null, maybe you delete the pid file we created. you can manually kill the master process with signal SIGUSR1.');
                }
                break;
            case 'stop':
                if (!empty($masterPid)) {
                    posix_kill($masterPid, SIGTERM);
                } else {
                    print_r('master pid is null, maybe you delete the pid file we created. you can manually kill the master process with signal SIGTERM.');
                }
                break;
            default:
                print_r("php {$argv[0]} start|reload|stop");
                break;
        }
    }

    public function startSwooleServer($swooleConfig)
    {
        $this->swooleServer = new swoole_http_server($swooleConfig['host'], $swooleConfig['port']);
        $this->swooleServer->set($swooleConfig['setting']);
        $this->swooleServer->on('Start', function($server) {
            global $argv;
            swoole_set_process_name("php {$argv[0]} master process");
            file_put_contents(self::$pidFile, $server->master_pid);
        });
        $this->swooleServer->on('Shutdown', function($server) {
            unlink(self::$pidFile);
        });
        $this->swooleServer->on('Task', function($server, $taskId, $fromId, $data) {
            echo 'Task Executed';
        });
        $this->swooleServer->on('Finish', function($server, $taskId, $data) {
            echo 'Task Finished';
        });
        $this->swooleServer->on('WorkerStart', function($server, $workerId) {
            echo ' Worker Id:'.$workerId.' Did Start ';
        });
        $this->swooleServer->on('Request', function($request, $response) {
            $_GET    = isset($request->get) ? $request->get : [];
            $_POST   = isset($request->post) ? $request->post : [];
            $_SERVER = array_change_key_case($request->server, CASE_UPPER);
            $_FILES  = isset($request->files) ? $request->files : [];
            $_COOKIE = isset($request->cookie) ? $request->cookie : [];
            if (isset($request->header)) {
                foreach ($request->header as $key => $value) {
                    $key           = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                    $_SERVER[$key] = $value;
                }
            }

            $data = null;
            if (is_array($_GET) && count($_GET)) {
                $data = $_GET;
            }
            if (is_array($_POST) && count($_POST)) {
                $data = $_POST;
            }
            if ($data) {
                $task = new \common\swoole\tasks\SwooleTask($data);
                $task->execute();
            }

            $this->currentRequest  = $request;
            $this->currentResponse = $response;
            $this->currentResponse->header('Access-Control-Allow-Origin', '*');
            $this->currentResponse->header('Access-Control-Allow-Credentials', 'true');
            $this->currentResponse->header('Conent-Type', 'application/json; charset=utf-8');
            $this->currentResponse->end('success');
        });
        $this->swooleServer->start();
    }
}
new yii\web\Application($config);
new SwooleHttpServer($config);
