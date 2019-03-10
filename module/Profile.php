<?php

class Profile {
    private $_db;
    private $_user_type;
    private $_user_info;
    private $_user_id;

    /**
     * @param Database $db
     * @param array $config sysconfig in config.php
     */
    public function __construct($db, $config){
        $this->_db = $db;
        $this->_user_type = 'username';
        $this->_user_info = $config['user_info_table'];
        $this->_user_id = 'user_id';
    }

    /**
     * @param string $uid
     * @param array $data
     * @return array result
     */
    public function updateInfo($uid, $data){
        $response = $this->_db->update($this->_user_info, $this->_user_id, $data, $uid);
        return $response;
    }

    /**
     * @param string $uid
     * @return array result
     */
    public function getInfo($uid){
        $response = $this->_db->select($this->_user_info, $this->_user_id, $uid);
        return $response;
    }
    
    /**
     * @param string $uid
     * @return array result
     */
    public function needUpdate($uid){
        $response = $this->_db->select($this->_user_info, $this->_user_id, $uid)[0];
        if (empty($response)){
            return array('code'=>404);
        } else if (empty($response["name"])) {
            return array('code'=>100);
        } else {
            return array('code'=>202);
        }
    }
}
