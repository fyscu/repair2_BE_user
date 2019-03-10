<?php
require_once("Util.php");

class Elastic_REST {
    public static $db_type = "Elastic_REST";
    private $_http_header = array("Content-Type"=>"application/json");
    private $_baseurl;

    /**
     * @param array $dbconfig from config.php
     */
    public function __construct($dbconfig){
        $this->_baseurl = 'http://'.$dbconfig['server'].':'.$dbconfig['port'].'/';
    }

    /**
     * @param string $index host which document belongs to
     * @param string $type type which document is
     * @param string $id id of document
     * @param mixed false when miss, an object when found
     */
    public function select($index, $type, $id){
        $receive = GET($this->_baseurl.$index.'/'.$type.'/'.$id, $this->_http_header);
        if (empty($receive)){
            return false;
        }
        $receive = json_decode($receive);
        if ($receive->{'found'} === false) {
            return false;
        } else {
            return $receive->{'_source'};
        }
    }

    /**
     * @param string $index host which document belongs to
     * @param string $type type which document is
     * @param mixed $data data of doucment
     * @param string $id id of document, null if use auto increasing id
     * @param mixed false when fail to connect, an object when executed
     */
    public function insert($index, $type, $data, $id = null){
        if (is_null($id)) {
            $receive = POST($this->_baseurl.$index.'/'.$type.'/', $this->_http_header, $data);
        } else {
            $receive = PUT($this->_baseurl.$index.'/'.$type.'/'.$id.'/_create', $this->_http_header, $data);
        }
        if (empty($receive)){
            return false;
        }
        $receive = json_decode($receive);
        return $receive;
    }

    /**
     * @param string $index host which document belongs to
     * @param string $type type which document is
     * @param string $id id of document
     * @param mixed false when miss, an object when found
     */
    public function delete($index, $type, $id){
        $receive = DELETE($this->_baseurl.$index.'/'.$type.'/'.$id, $this->_http_header);
        
        if (empty($receive)){
            return false;
        }
        $receive = json_decode($receive);
        return $receive;
    }
    
    /**
     * @param string $index host which document belongs to
     * @param string $type type which document is
     * @param array $dsl
     * @param mixed false when miss, an object when found
     */
    public function deletequery($index, $type, $dsl){
        $receive = POST($this->_baseurl.$index.'/'.$type.'/_delete_by_query', $this->_http_header, $dsl);
        if (empty($receive)){
            return false;
        }
        $receive = json_decode($receive);
        return $receive;
    }

    /**
     * @param string $index host which document belongs to
     * @param string $type type which document is
     * @param array $data data of doucment
     * @param string $id id of document
     * @param bool false when fail to connect, an object when executed
     */
    public function update($index, $type, $data, $id){
        $receive = POST($this->_baseurl.$index.'/'.$type.'/'.$id.'/_update',
                        $this->_http_header,
                        array("doc"=>$data));
        if (empty($receive)){
            return false;
        }
        $receive = json_decode($receive);
        return $receive;
    }
    
    /**
     * @param string $index host which document belongs to
     * @param string $type type which document is
     * @param array $dsl query in DSL
     * @param array hits arrays
     */
    public function search($index, $type, $dsl){
        $receive = GET($this->_baseurl.$index.'/'.$type.'/_search', $this->_http_header, $dsl);
        if (empty($receive)){
            return false;
        }
        $receive = json_decode($receive);
        return $receive->{'hits'}->{'hits'};
    }
}
