<?php
class Manufacturers_model extends CI_Model {
 
    /**
    * Responsable for auto load the database
    * @return void
    */
    public function __construct()
    {
        $this->load->database();
    }

    /**
    * Get product by his is
    * @param int $product_id 
    * @return array
    */
    public function get_manufacture_by_id($id)
    {
		$this->db->select('*');
		$this->db->from('manufacturers');
		$this->db->where('id', $id);
		$query = $this->db->get();
		return $query->result_array(); 
    }    

    /**
    * Fetch manufacturers data from the database
    * possibility to mix search, filter and order
    * @param string $search_string 
    * @param strong $order
    * @param string $order_type 
    * @param int $limit_start
    * @param int $limit_end
    * @return array
    */
    public function get_manufacturers($search_string=null, $order=null, $order_type='Asc', $limit_start=null, $limit_end=null)
    {
	    
		$this->db->select('*');
		$this->db->from('manufacturers');

		if($search_string){
			$this->db->like('name', $search_string);
		}
		$this->db->group_by('id');

		if($order){
			$this->db->order_by($order, $order_type);
		}else{
		    $this->db->order_by('id', $order_type);
		}

        if($limit_start && $limit_end){
          $this->db->limit($limit_start, $limit_end);	
        }

        if($limit_start != null){
          $this->db->limit($limit_start, $limit_end);    
        }
        
		$query = $this->db->get();
		
		return $query->result_array(); 	
    }

    public function get_manufacturers2($logistics_id=null,$status=null,$time=null)
    {
	    
		$this->db->select('*');
		$this->db->from('manufacturers');
        
        $result_array = $this->db->get()->result_array();
        $array = array();
        foreach ($result_array as $val){
            $this->db->select('*');
		    $this->db->from('orders');
            $this->db->where('orders.shop_id', $val['shop_id']);
            if($logistics_id){
                if($logistics_id == 1){
                    $this->db->where('tracking_code', '');
                }else{
                    $this->db->where('tracking_code !=', '');
                }
            }
            if($status){
                $this->db->where('import', $status);
            }
            if($time&&$time!=null&&$time!=""){
                // echo json_encode($time[0]/1000);die;
                $this->db->where('creatdTime >=', $time[0]/1000);
                $this->db->where('creatdTime <=', $time[1]/1000+86400);
            }
            $len = $this->db->get()->result_array();
            $val['len']= count($len);
            array_push($array,$val);
            
        }

		
		return $array; 	
    }

    /**
    * Count the number of rows
    * @param int $search_string
    * @param int $order
    * @return int
    */
    function count_manufacturers($search_string=null, $order=null)
    {
		$this->db->select('*');
		$this->db->from('manufacturers');
		if($search_string){
			$this->db->like('name', $search_string);
		}
		if($order){
			$this->db->order_by($order, 'Asc');
		}else{
		    $this->db->order_by('id', 'Asc');
		}
		$query = $this->db->get();
		return $query->num_rows();        
    }

    /**
    * Store the new item into the database
    * @param array $data - associative array with data to store
    * @return boolean 
    */
    function store_manufacture($data)
    {
		$insert = $this->db->insert('manufacturers', $data);
	    return $insert;
	}

    /**
    * Update manufacture
    * @param array $data - associative array with data to store
    * @return boolean
    */
    function update_manufacture($id, $data)
    {
		$this->db->where('id', $id);
		$this->db->update('manufacturers', $data);
		$report = array();
		$report['error'] = $this->db->_error_number();
		$report['message'] = $this->db->_error_message();
		if($report !== 0){
			return true;
		}else{
			return false;
		}
	}

    /**
    * Delete manufacturer
    * @param int $id - manufacture id
    * @return boolean
    */
	function delete_manufacture($id){
		$this->db->where('id', $id);
		$this->db->delete('manufacturers'); 
	}
 
}
?>	
