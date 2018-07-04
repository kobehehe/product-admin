<?php
class orders_model extends CI_Model {
 
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
    public function get_product_by_id($id)
    {
		$this->db->select('*');
		$this->db->from('orders');
		$this->db->where('id', $id);
		$query = $this->db->get();
		return $query->result_array(); 
    }

    /**
    * Fetch orders data from the database
    * possibility to mix search, filter and order
    * @param int $manufacuture_id 
    * @param string $search_string 
    * @param strong $order
    * @param string $order_type 
    * @param int $limit_start
    * @param int $limit_end
    * @return array
    */
    public function get_orders($manufacture_id=null, $search_string=null, $order=null, $order_type='Asc', $limit_start, $limit_end,$logistics_id='')
    {

		$this->db->select('*');
//		$this->db->select('orders.description');
//		$this->db->select('orders.stock');
//		$this->db->select('orders.cost_price');
//		$this->db->select('orders.sell_price');
//		$this->db->select('orders.manufacture_id');
//		$this->db->select('manufacturers.name as manufacture_name');
		$this->db->from('orders');
		if($manufacture_id != null && $manufacture_id != 0){
			$this->db->where('orders.shop_id', $manufacture_id);
		}
		if($search_string){
			$this->db->where('order_id', $search_string);
		}
		if($logistics_id){
            $this->db->where('import', $logistics_id);
		}
		//$this->db->join('manufacturers', 'orders.shop_id = manufacturers.shop_id', 'left');
		//$this->db->group_by('orders.shop_id');
//		if($order){
//			$this->db->order_by($order, $order_type);
//		}else{
		    $this->db->order_by('orders.id', $order_type);
		//}

		$this->db->limit($limit_start, $limit_end);


		$query = $this->db->get();
        //var_dump(456);die;
		return $query->result_array(); 	
    }

    /**
    * Count the number of rows
    * @param int $manufacture_id
    * @param int $search_string
    * @param int $order
    * @return int
    */
    function count_orders($manufacture_id=null, $search_string=null, $order=null,$logistics_id=null)
    {
		$this->db->select('*');
		$this->db->from('orders');
		if($manufacture_id != null && $manufacture_id != 0){
			$this->db->where('shop_id', $manufacture_id);
		}
		if($search_string){
			$this->db->where('order_id', $search_string);
		}
        if($logistics_id){
            $this->db->where_in('import', $logistics_id);
        }
		if($order){
			$this->db->order_by($order, 'Asc');
		}else{
		    $this->db->order_by('id', 'Asc');
		}
		$query = $this->db->get();
		//var_dump($query->num_rows());die;
		return $query->num_rows();        
    }

    /**
    * Store the new item into the database
    * @param array $data - associative array with data to store
    * @return boolean 
    */
    function store_product($data)
    {
		$insert = $this->db->insert('orders', $data);
	    return $insert;
	}

    /**
    * Update product
    * @param array $data - associative array with data to store
    * @return boolean
    */
    function update_product($id, $data)
    {
		$this->db->where('id', $id);
		$this->db->update('orders', $data);
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
    * Delete product
    * @param int $id - product id
    * @return boolean
    */
	function delete_product($id){
		$this->db->where('id', $id);
		$this->db->delete('orders'); 
	}
 
}
?>	
