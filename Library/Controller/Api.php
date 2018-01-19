<?php
/**
 * Project: shadowsocks-panel
 * Author: Sendya <18x@loacg.com>
 * Time: 2016/3/23 22:14
 */


namespace Controller;

use Core\Error;
use Helper\Http;
use Helper\Option;
use Helper\Utils;
use Helper\RedisManager;
use Model\Card;
use Model\Node;
use Model\NodeVO;
use Model\User;
use Helper\Logger;

class Api
{

    /**
     * 查询 IP 详细信息
     *
     * @JSON
     */
    public function queryCountry()
    {
        $ipAddress = Utils::getUserIP();
        $ch = curl_init();
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ipAddress;

        // 执行HTTP请求
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);
        echo $res;
        exit();
    }


    /**
     * 淘宝自动发货API
     * 创建卡号
     *
     * @JSON
     */
    public function createCard()
    {
        $CURR_KEY = $_SERVER['HTTP_AUTHORIZATION'];
        if (!$CURR_KEY) {
            header("HTTP/1.1 405 Method Not Allowed");
            exit();
        }

        $KEY = Option::get('SYSTEM_API_KEY');
        if ($KEY == null) {
            $KEY = password_hash(Utils::randomChar(12) . time(), PASSWORD_BCRYPT);
            Option::set('SYSTEM_API_KEY', $KEY);
        }

        $CURR_KEY = str_replace('Basic ', '', $CURR_KEY);
        $CURR_KEY = md5($CURR_KEY . ENCRYPT_KEY);
        $KEY = md5($KEY . ENCRYPT_KEY);

        if (strtoupper($KEY) == strtoupper($CURR_KEY)) {
            $card = new Card();
            $card->card = substr(hash("sha256", time() . Utils::randomChar(10)) . time(), 1, 26);
            $card->add_time = time();
            $card->type = intval(trim($_POST['type']));
            $card->info = trim($_POST['info']);
            $card->status = 1;

            $card->save();
            return array('error' => 0, 'message' => 'success', 'card' => $card);
        } else {
            return array('error' => 1, 'message' => 'Bad Request');
        }

    }

    /**
     * @JSON
     * @Authorization
     */
    public function nodeStatus()
    {
        $API_BASE = "https://nodequery.com/api/";

        $API_KEY = Option::get('SERVER_NODE_QUERY_API_KEY');
        if (!$API_KEY) {
            throw new Error('API_KEY is not available', 500);
        }

        $status = array();
        $nodes = Node::getNodeArray();

        foreach ($nodes as $node) {
            $result = Http::doGet($API_BASE . 'servers/' . $node->api_id . '?api_key=' . $API_KEY);
            $result = json_decode($result, true);
            $status[] = array('id' => $node->id,
                'current_rx' => $result['data'][0]['current_rx'],
                'current_tx' => $result['data'][0]['current_tx'],
                'total_rx' => $result['data'][0]['total_rx'],
                'total_tx' => $result['data'][0]['total_tx'],
                'availability' => $result['data'][0]['availability']);
            unset($result);
        }
        return $status;
    }

    /**
     * @JSON
     * @Authorization
     */
    public function nodeQuery()
    {
        $API_BASE = "https://nodequery.com/api/";

        $API_KEY = Option::get('SERVER_NODE_QUERY_API_KEY');
        if (!$API_KEY) {
            throw new Error('API_KEY is not available', 500);
        }

        $status = array();
        $result = Http::doGet($API_BASE . 'servers?api_key=' . $API_KEY, array());
        if($result) {
            $result = json_decode($result, true);

            foreach ($result['data'] as $node) {
                $status[] = array('id' => $node['id'],
                    'status' => $node['status'],
                    'availability' => $node['availability'],
                    'update_time' => $node['update_time'],
                    'name' => $node['name'],
                    'load_percent' => $node['load_percent'],
                    'load_average' => $node['load_average'],
                    'ram_total' => $node['ram_total'],
                    'ram_usage' => $node['ram_usage'],
                    'disk_total' => $node['disk_total'],
                    'disk_usage' => $node['disk_usage_'],
                    'current_rx' => $node['current_rx'],
                    'current_tx' => $node['current_tx']
                    );
            }


        }
        return $status;
    }

    /**
     * @JSON
     */
    public function nodes() {
        $port = $_POST['port'];
        $timestamp = $_POST['timestamp'];
        $token = $_POST['token'];
        $sign = $_POST['sign'];
        if ($this->securityProcess($timestamp,$token,$sign)) {//通过api安全检验
            $nodes = null;
            $servers = array();   
            $nodes = Node::getNodeArray(0);//普通节点
            foreach ($nodes as $node) {
                $VO = new NodeVO();
                $VO->id = $node->id;
                $VO->name = $node->name;
                $VO->server = $node->server;
                $VO->remark = $node->info;
                $servers[] = $VO;
            }
            if (empty($nodes)) {
                throw new Error("get nodes list fail!", 6001);
                
            }
            return array("statusCode" => 6000, "output" => $servers,"message" => 'success');  
           }   
        throw new Error("token incorrect", 7005);
    }
    /**
     * @JSON
     */
    public function appLogin() {
        $port = $_POST['port'];
        $password = $_POST['password'];
        $data = array();
        if (empty($port) || empty($password)) {
            throw new Error('port or psw must not be empty!', 8002);
        }
        $user = User::getUserByPort($port);
        if ($user) {
            Logger::getInstance()->info('received password:' . $password);
            Logger::getInstance()->info('local MD5 password:' . md5($user->sspwd));            
            Logger::getInstance()->info('local sha1 password:' . sha1($user->sspwd));
        }

        if ($user) {//exist
            if (strcmp($password, md5($user->sspwd))==0 || strcmp($password, $user->sspwd)==0 ||strcmp($password, sha1($user->sspwd))==0) {
                $flow_down = Utils::flowAutoShow($user->flow_down);
                $transfer = Utils::flowAutoShow($user->transfer);
                $data['token'] = $this->getToken($port);
                $data['plan'] = $user->plan;
                $data['transfer'] = $transfer;
                $data['flow_down'] = $flow_down;
                $data['flow_left'] = Utils::flowAutoShow($user->transfer - $user->flow_down);
                $data['encryption'] = $user->method==null?'aes-256-cfb':$user->method;
                $data['expire_time'] = $user->expireTime;
                $data['status'] = intval($user->enable);
                // return array("statusCode" => 8000, "output"=>'noOutput', "message" => 'success');////为兼容,data无输出时候,不能用null判断,固定noOutput
                return array("statusCode" => 8000, "output"=>$data, "message" => 'success');
            }
            throw new Error('password incorrect', 8001);
        }else{
            throw new Error('user not exists', 8003); 
        }
    }

    /**
    * 获取用户token
    * @param $port 用户端口
    */
    protected function getToken($port) {
        $redis = RedisManager::getRedisConn();
        $token = $redis->get($port);
        if (!$token) {//token过期,重新生成,有效期为2小时
            $encryptedToken = Utils::encrypt(strtolower(Utils::randomChar(16) . '_' . $port),ENCRYPT_API_KEY);
            while (strpos($encryptedToken, '+')!==false) {//discard '+' 
                $encryptedToken = Utils::encrypt(strtolower(Utils::randomChar(16) . '_' . $port),ENCRYPT_API_KEY);
            }
            $redis->set($port, $encryptedToken, 7200);
        }
        return $redis->get($port);
    }

    /**
    * 验证token是否有效
    * @param $port 用户端口
    */
    protected function checkToken($port,$token) {
        $redis = RedisManager::getRedisConn();
        $redisToken = $redis->get($port);
        Logger::getInstance()->info('received token:' . $token);
        Logger::getInstance()->info('redis token:' . $redisToken);

        if (strcmp($redisToken, $token)==0) {//token存在且相等
            return true;
        }
        return false;
    }

    /**
    * api安全性校验
    * @param $timestamp 用户发起请求时间戳
    * @param $token 用户token
    * @param $sign 用户签名
    */
    protected function securityProcess($timestamp,$token,$sign){
        Logger::getInstance()->info('before sign urldecode :' . $sign);
        $sign = urldecode($sign);
        Logger::getInstance()->info('after sign urldecode :' . $sign);
        if (empty($timestamp) || empty($token) || empty($sign)) {//missing param
            throw new Error("param missing", 7001);
        }
        if (abs(time() - $timestamp) >600) {
            Logger::getInstance()->info('now:' . time());
            Logger::getInstance()->info('received timestamp:' . $timestamp());
            throw new Error("invilid timestamp", 7002);
        }
        Logger::getInstance()->info('before token urldecode :' . $token);
        $token = urldecode($token);
        Logger::getInstance()->info('after token urldecode :' . $token);
        $decryptedToken = Utils::decrypt($token,ENCRYPT_API_KEY);
        $port = explode('_', $decryptedToken)[1];
        Logger::getInstance()->info('decryptedToken:' . $decryptedToken);
        Logger::getInstance()->info('port:' . $port);
        if (!$this->checkToken($port,$token)) {
            throw new Error("token expired", 7003);
        }
        if (strcmp($sign, strtolower(md5($timestamp . $token)))!=0 ) {//compare sign
            if (strcmp($sign, strtolower(sha1($timestamp . $token)))==0){
                return true;
            }
            Logger::getInstance()->info('received sign:' . $sign);
            Logger::getInstance()->info('local sign:' . strtolower(md5($timestamp . $token)));   
            throw new Error("sign incorrect", 7004);
        }
        return true;
    }

    /**
    * @JSON
    */
    protected function getVersion($appName){
        $appName = $_POST['appName'];
        if (is_null($appName)) {
            throw new Error("appName must not be empty!", 7005);
        }
        $data = array();
        $data['version'] = Option::get($appName);
    throw new Error("not finish yet!", 7004);
    }          
}
