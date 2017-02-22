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
use Model\Card;
use Model\Node;
use Model\User;

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
        $status = 0;
        $nodes = null;
        $servers = array();   
        try {
            $nodes = Node::getNodeArray(0);//普通节点
            foreach ($nodes as $node) {
                $servers[] = $node->server;
            }
        } catch (Exception $e) {
            return array("statusCode" => 1, "servers" => $servers, "msg" => 'get nodes list fail!');
        }
        return array("statusCode" => 0, "servers" => $servers,"msg" => 'success');
    }
    /**
     * @JSON
     */
    public function appLogin() {
        $port = $_POST['port'];
        $password = $_POST['password'];
        
        if (empty($port) || empty($password)) {// isset then
            $user = User::getUserByPort($port);
            if ($user && $user->sspwd ==md5($password)) {//
                return array("statusCode" => 0, "success"=>1, "msg" => 'success');
            }
            return array("statusCode" => 1, "success"=>0, "msg" => 'user not found');           
        }else{//port psw empty
            return array("statusCode" => 2, "success"=>0, "msg" => 'port or psw must not be empty!');
        }

    }    
}