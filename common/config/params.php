<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'swoole'                        => [
        'setting' => [
            'worker_num'        => 2, //worker process num
            'backlog'           => 4, //listen backlog
            'max_request'       => 5000,
            'open_tcp_nodelay'  => 1,
            'enable_reuse_port' => 1,
            'task_worker_num'   => 2,
            'task_worker_max'   => 512,
            'log_file'          => '/data/logs/swoole.log',
            'log_level'         => 0,
        ],
        'host'    => '0.0.0.0',
        'port'    => 9501,
        'pidFile' => sys_get_temp_dir() . '/swooleserver.pid',
    ],
];
