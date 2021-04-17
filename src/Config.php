<?php

namespace aindows\pay;

use aindows\pay\exception\ConfigException;

class Config
{
    /**
     * Config constructor.
     * @param $config
     * @throws ConfigException
     */
    public function __construct($config)
    {
        if (empty($config)) {
            throw  new ConfigException('配置不能为空', 500);
        }
        $init = [
            'gateway' => '',
            'secret' => '',
            'appId' => '',
            'notifyUrl' => '',
            'log' => [
                'log' => true,
                'path' => '/'
            ]
        ];
        $config = array_merge($init, $config);
        if (empty($config['gateway']) || empty($config['secret']) || empty($config['appId']) || empty($config['notifyUrl'])) {
            throw  new ConfigException('配置错误', 500);
        }
        return $config;
    }
}
