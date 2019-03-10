<?php

class Application {
    private $_db;
    private $_app_table;

    /**
     * @param Database $db
     * @param Database $cache
     * @param array $config sysconfig in config.php
     */
    public function __construct($db, $config){
        $this->_db = $db;
        $this->_app_table = $config['app_table'];
    }

    /**
     * @param string $appid
     * @return array result
     */
    public function getAppInfo($appid){
        $response = $this->_db->select($this->_app_table, 'app_id', $appid);
        if ($response == false){
            return array('code'=>404);
        }
        return array('code'=>200, 'data'=>$response[0]);
    }

    /**
     * @param string $appid
     * @param string $secret
     * @return array result
     */
     public function checkSecret($appid, $secret){
        $challenge = $this->getAppInfo($appid);
        if ($challenge['code'] == 200){
            $flag = hash_equals($secret, $challenge['data']['secret']);
            if ($flag){
                return array('code'=>200);
            } else {
                return array('code'=>400);
            }
        } else {
            return $challenge;  
        }
     }
}

