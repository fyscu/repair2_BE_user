<?php

class Permission {
    private $_db;
    private $_user_info;
    private $_user_id;
    private $_perm;
    private $_role;
    private $_role_perm;
    private $_user_role;

    /**
     * @param Database $db
     * @param array $config sysconfig in config.php
     */
    public function __construct($db, $config){
        $this->_db = $db;
        $this->_user_info = $config['user_info_table'];
        $this->_user_id = 'user_id';
        $this->_perm = $config['perm_table'];
        $this->_role = $config['role_table'];
        $this->_role_perm = $config['role_perm_table'];
        $this->_user_role = $config['user_role_table'];
    }

    /**
     * @param string $uid
     * @return array result
     */
    public function getPerm($uid){
        $response = $this->_db->search($this->_user_role, 
                                       [
                                           "[><]$this->_role"=>"role_id",
                                           "[><]$this->_role_perm"=>"role_id"
                                       ],
                                       "$this->_role_perm.perm_id",
                                       [
                                            "$this->_user_role.$this->_user_id"=>$uid,
                                            "$this->_user_role.status"=>1
                                       ]
                                       );
        return $response;
    }
    
}
