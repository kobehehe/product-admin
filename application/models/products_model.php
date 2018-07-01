<?php
class Products_model extends CI_Model {
 
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
		$this->db->from('products');
		$this->db->where('id', $id);
		$query = $this->db->get();
		return $query->result_array(); 
    }

    /**
    * Fetch products data from the database
    * possibility to mix search, filter and order
    * @param int $manufacuture_id 
    * @param string $search_string 
    * @param strong $order
    * @param string $order_type 
    * @param int $limit_start
    * @param int $limit_end
    * @return array
    */
    public function get_products($manufacture_id=null, $search_string=null, $order=null, $order_type='Asc', $limit_start, $limit_end,$logistics_id='')
    {

		$this->db->select('*');
//		$this->db->select('products.description');
//		$this->db->select('products.stock');
//		$this->db->select('products.cost_price');
//		$this->db->select('products.sell_price');
//		$this->db->select('products.manufacture_id');
//		$this->db->select('manufacturers.name as manufacture_name');
		$this->db->from('products');
		if($manufacture_id != null && $manufacture_id != 0){
			$this->db->where('products.shop_id', $manufacture_id);
		}
		if($search_string){
			$this->db->where('order_id', $search_string);
		}
		if($logistics_id){
            $this->db->where('import', $logistics_id);
		}
		//$this->db->join('manufacturers', 'products.shop_id = manufacturers.shop_id', 'left');
		//$this->db->group_by('products.shop_id');
//		if($order){
//			$this->db->order_by($order, $order_type);
//		}else{
		    $this->db->order_by('products.id', $order_type);
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
    function count_products($manufacture_id=null, $search_string=null, $order=null,$logistics_id=null)
    {
		$this->db->select('*');
		$this->db->from('products');
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
		$insert = $this->db->insert('products', $data);
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
		$this->db->update('products', $data);
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
		$this->db->delete('products'); 
	}
 
}
?>	
