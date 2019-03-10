<?php

class Vip {
    private $_db;
    private $_vip_table;

    /**
     * @param Database $db
     * @param array $config sysconfig in config.php
     */
    public function __construct($db, $config){
        $this->_db = $db;
        $this->_vip_table = $config['vip_table'];
    }

    /**
     * @param int $id (database primary key)
     * @param array $data
     * @return array result
     */
    public function updateInfo($id, $data){
        $response = $this->_db->update($this->_vip_table, "id", $data, $id);
        return $response;
    }

    /**
     * @param int $id (database primary key)
     * @return array result
     */
    public function getInfoById($id){
        $response = $this->_db->select($this->_vip_table, "id", $id);
        return $response;
    }
    
    /**
     * @param string $cell
     * @return array result
     */
    public function getInfoByCell($cell){
        $response = $this->_db->search($this->_vip_table, "*", array("cell"=>$cell, "ORDER"=>array("id"=>"DESC")));
        return $response;
    }
    
}
