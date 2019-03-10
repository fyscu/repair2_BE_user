<?php
require_once("Util.php");

class Register {
    private $_db;
    private $_user_index;
    private $_user_type;
    private $_user_info_table;
    private $_user_id;
    private $_otp_table;
    private $_otp_cell_life;
    private $_otp_cell_frequency;
    private $_sms_api;
    
    /**
     * @param Database $db
     * @param Database $cache
     * @param SMSAPI $sms
     * @param array $config sysconfig in config.php
     */
    public function __construct($db, $sms, $config){
        $this->_db = $db;
        $this->_user_index = $config['local_auth_table'];
        $this->_user_type = "username";
        $this->_user_info_table = $config['user_info_table'];
        $this->_otp_table = $config['otp_table'];
        $this->_otp_cell_life = $config['otp_cell_life'];
        $this->_otp_cell_frequency = $config['otp_cell_frequency'];
        $this->_sms_api = $sms;
    }
    
    /**
     * @param int $cellnum
     * @return array status
     */
    public function sendOTPbySMS($cellnum){
        $numRegex = '/^1(3[0-9]|4[579]|5[0-35-9]|7[0-9]|8[0-9]|9[89])\d{8}$/';
        preg_match_all($numRegex, $cellnum, $numMatches, PREG_SET_ORDER, 0);
        if (count($numMatches) === 0) return array('code'=>404);
        
        $isValidReq = true;
        $sentResult = $this->_db->select($this->_otp_table, "number", $cellnum);
        if (count($sentResult) != 0) {
            foreach ($sentResult as $res){
                if ($res['start_time'] + $this->_otp_cell_frequency > time()){
                    $isValidReq = false;
                    break;
                }
            }
        }
        if ($isValidReq === false) return array('code'=>403);
        
        $rand = randomGen(6, 0);
        $smstext = "验证码:$rand 十分钟内有效";
        $smsreq = $this->_sms_api->send($cellnum, $smstext, array("data"=>array("code"=>$rand)));
        
        if ($smsreq['code']!=200) return $smsreq;
        
        $endtime = time() + $this->_otp_cell_life;
        $response = $this->_db->insert($this->_otp_table, "code", 
                                       array('type'=>'sms',
                                             'number'=>$cellnum,
                                             'start_time'=>time(),
                                             'end_time'=>$endtime
                                       ), $rand);
        
        if (isset($response["error"])){
            return array('code'=>500, 'error'=> $response['error']);
        } else {
            return array('code'=>200); 
        }
    }
    
    /**
     * @param int $cellnum
     * @param int $code
     * @return bool if is correct
     */
    public function checkOTPbySMS($cellnum, $code){
        $sentResult = $this->_db->search($this->_otp_table, "*", 
                                         array("AND"=>array("number"=>$cellnum, "code"=>$code))
                                        );
        if (count($sentResult) === 0) return false;
        $result = $sentResult[0]['end_time'] > time();
        $this->_db->delete($this->_otp_table, "id", $sentResult[0]["id"]);
        return $result;
    }
    
    /**
     * @param int $cellnum
     * @param string $password
     * @return array action result
     */
    public function register($cellnum, $password){
        $hashedpass = md5($cellnum.$password);
        $uid = randomGen(32,2);
        $check = $this->_db->select($this->_user_index, "username", $cellnum);
        if ($check != false) return array('code'=>409);
        
        $response = $this->_db->insert($this->_user_index, "user_id", 
                                       array( "username" => $cellnum,
                                              "password" => $hashedpass,
                                            ),
                                       $uid
                                       );
        if (isset($response['error'])) return array('code'=>409);
        $response = $this->_db->insert($this->_user_info_table, "user_id", 
                                       array( "cell" => $cellnum,
                                              "reg_time" => time(),
                                            ),
                                       $uid
                                       );
        return array('code'=>200);
    }

    /**
     * @param int $cellnum
     * @param string $password
     * @return array action result
     */
    public function passwordReset($cellnum, $password) {
        $hashedpass = md5($cellnum.$password);
        $response = $this->_db->update($this->_user_index, $this->_user_type, 
                                       array( "password" => $hashedpass ),
                                       $cellnum);
        if (isset($response['error'])) return array('code'=>409);
        else return array('code'=>200);
    }
    
    /**
     * @param bool action result
     */
    public function clearOutDatedOTP() {
        $response = $this->_db->deletequery($this->_otp_table, "", array("end_time[<]"=> time()));
        if (isset($response['error'])) return array('code'=>409);
        else return array('code'=>200, 'data'=>$response);
    }
    
}