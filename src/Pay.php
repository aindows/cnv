<?php

namespace aindows\pay;
class Pay
{

    protected $config;
    protected $curl;

    private static $instance;

    public static function getInstance($config)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    private function __construct($config)
    {
        $this->config = $config;
        $this->curl = new CurlRequest();
    }

    public function setCurl($curl)
    {
        $this->curl = $curl;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    //代付/提现申请

    /**
     * @param array $data
     * @return bool|string
     */
    public function apply(array $data)
    {
        $url = $this->config['gateway'] . '/order/apply';
        $data['sign'] = $this->applySign($data);
        $data['notifyUrl'] = $this->config['notifyUrl'];
        if (!isset($data['requestIp'])) {
            $data['requestIp'] = $this->getIp();
        }
        $this->writelog($data);
        return $this->curl->post($url, $data);
    }
    //提现申请签名
    private function applySign($data){
        return strtolower(md5($data['merchantOrderNo'] . $data['appId'] . $data['channelCode'] . $data['payCode'] . $data['payFee'] . $this->config['secret']));
    }


    //代付充值

    /**
     * @param array $data
     * @return bool|string
     */
    public function recharge(array $data)
    {

        $url = $this->config['gateway'] . '/order/recharge';
        $data['sign'] = $this->applySign($data);
        $data['notifyUrl'] = $this->config['notifyUrl'];
        if (!isset($data['requestIp'])) {
            $data['requestIp'] = $this->getIp();
        }
        $this->writelog($data);
        return $this->curl->post($url, $data);
    }

    //支付下单

    /**
     * @param array $data
     * @return bool|string
     */
    public function createOrder(array $data)
    {
        $url = $this->config['gateway'] . '/order/create';
        $data['sign'] = $this->applySign($data);
        $data['notifyUrl'] = $this->config['notifyUrl'];
        if (!isset($data['requestIp'])) {
            $data['requestIp'] = $this->getIp();
        }
        $this->writelog($data);
        return $this->curl->post($url, $data);
    }

    private function createSign($data){
        return strtolower(md5($data['merchantOrderNo'] . $data['appId'] . $data['channelCode'] . $data['payCode'] . $data['requestedCurrency']. $data['payFee'] . $this->config['secret']));
    }


    //数字货币支付查询报价

    /**
     * @param array $data
     * @return bool|string
     */
    public function getQuote(array $data)
    {
        if (!isset($data['requestIp'])) {
            $data['requestIp'] = $this->getIp();
        }
        $this->writelog($data);
        $url = $this->config['gateway'] . '/order/getQuote';
        return $this->curl->get($url, $data);
    }

    //查单

    /**
     * @param string $merchantOrderNo
     * @return bool|string
     */
    public function cashierQuery(string $merchantOrderNo)
    {
        $url = $this->config['gateway'] . '/order/cashierQuery';
        $params['merchantOrderNo'] = $merchantOrderNo;
        $this->writelog($params);
        return $this->curl->get($url, $params);
    }

    /**
     * @return mixed
     */
    private function getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    private function writelog($data){
        if ($this->config['log']['log']) {
            $log = $this->config['log']['path'] . '/' . date('Ymd') . '.log';
            Logger::writeLogger($log, $data);
        }
    }
}
