<?php
require_once("Util.php");

class Login {
    private $_db;
    private $_user_index;
    private $_user_type;
    private $_user_id;
    private $_sid_table;
    private $_token_life;

    /**
     * @param Database $db
     * @param Database $cache
     * @param array $config sysconfig in config.php
     */
    public function __construct($db, $config){
        $this->_db = $db;
        $this->_user_index = $config['local_auth_table'];
        $this->_user_type = "username";
        $this->_user_id = 'user_id';
        $this->_sid_table = $config['sid_table'];
        $this->_token_life = $config['token_life'];
    }

    /**
     * @param string $username
     * @param string $password
     * @return array result
     */
    public function checkInfo($username, $password){
        $response = $this->_db->select($this->_user_index, $this->_user_type, $username);
        if ($response == false){
            return array('code'=>400);
        }
        $hash = $response[0]['password'];
        $flag = hash_equals($hash, md5($username.$password));
        if ($flag){
            return array('code'=>200, 'uid'=>$response[0]['user_id']);
        } else {
            return array('code'=>400);
        }
    }
    
    /**
     * @param string $username
     * @return array result
     */
    public function checkUsername($username){
        $response = $this->_db->select($this->_user_index, $this->_user_type, $username);
        if ($response == false){
            return array('code'=>400);
        }
        return array('code'=>200, 'uid'=>$response[0]['user_id']);
    }

    /**
     * @param string $username
     * @param string $password
     * @return array result
     */
     public function login($username, $password, $callback){
        $challenge = $this->checkInfo($username, $password);
        if ($challenge['code'] == 200){
            $response = $this->genSession($challenge['uid']);
            if ($response['code'] == 201){
                if (empty($callback)){
                    return array('code'=>200,
                             'uid'=>$challenge['uid'],
                             'token'=>$response['sid']
                            );
                } else {
                    return array('code'=>200,
                             'uid'=>$challenge['uid'],
                             'callback'=>$callback.'?uid='.$challenge['uid'].'&token='.$response['sid']
                            ); 
                }
            } else {
                return $response; 
            }
        } else {
            return $challenge;  
        }
     }
     
    /**
     * @param string $username
     * @return array result
     */
     public function fastlogin($username){
        $challenge = $this->checkUsername($username);
        if ($challenge['code'] == 200){
            $response = $this->genSession($challenge['uid']);
            if ($response['code'] == 201){
                return array('code'=>200,
                         'uid'=>$challenge['uid'],
                         'token'=>$response['sid']
                        );
            } else {
                return $response; 
            }
        } else {
            return $challenge;  
        }
     }

    /**
     * @param string $uid
     * @param array result
     */
    public function genSession($uid){
        $rand = randomGen(32,2);

        $endtime = time() + $this->_token_life;
        $response = $this->_db->insert($this->_sid_table, "sid", 
                                       array('user_id'=>$uid,
                                             'end_time'=>$endtime),
                                       $rand);
        if (isset($response["error"])){
            return array('code'=>500);
        } else {
            return array('code'=>201, 'sid'=>$rand);
        }
    }

    /**
     * @param string $sid
     * @param string $username
     * @param array result
     */
    public function chkSession($sid, $uid){
        $response = $this->_db->select($this->_sid_table, "sid", $sid);
        if ($response == false){
            return array('code'=>404);
        } elseif ($response[0]['end_time'] < time()) {
            return array('code'=>408);
        } 
        $flag = hash_equals($response[0]['user_id'], $uid);
        if ($flag === false) {
            return array('code'=>401);
        } else {
            return array('code'=>200);
        }
    }

    /**
     * @param string $sid
     * @param array result
     */
    public function getInfo($sid){
        $response = $this->_db->select($this->_sid_table, "sid", $sid);
        if ($response == false){
            return array('code'=>404);
        } elseif ($response[0]['end_time'] < time()) {
            return array('code'=>408);
        } 
        //TODO
        return array('code'=>200);
    }
    
    /**
     * @param bool action result
     */
    public function clearOutDatedSID() {
        $response = $this->_db->deletequery($this->_sid_table, "", 
                                        array("end_time[<]"=> time())
                                      );
        if (isset($response['error'])) return array('code'=>409);
        else return array('code'=>200, 'data'=>$response);
    }
}

