<?php

class Admin_orders extends CI_Controller
{

    /**
     * Responsable for auto load the model
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('orders_model');
        $this->load->model('manufacturers_model');
        $this->load->library('phpexcel');

        if (!$this->session->userdata('is_logged_in')) {
            redirect('admin/login');
        }
    }

    /**
     * Load the main view with all the current model model's data.
     * @return void
     */
    public function index()
    {

        //all the posts sent by the view
        $manufacture_id = $this->input->post('manufacture_id');
        $logistics_id = $this->input->post('logistics_id');
        $status = $this->input->post('status');
        $search_string = $this->input->post('search_string');
        $order = $this->input->post('order'); 
        $order_type = $this->input->post('order_type');
        $time = $this->input->post('time');
        //默认时间降序
        if(!$order){
            $order='orders.creatdTime';
        }
        if($status==1){
            $order_type='Asc';
        }else{
            $order_type='Desc';
        }
        //pagination settings
        $config['per_page'] = 100;
        $config['base_url'] = base_url() . 'admin/orders';
        $config['use_page_numbers'] = TRUE;
        $config['num_links'] = 20;
        $config['full_tag_open'] = '<ul>';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        //limit end
        $page =  $this->input->post('page');
        // $page = $this->uri->segment(3);
        //math to get the initial record to be select in the database
        $limit_end = ($page * $config['per_page']) - $config['per_page'];
        if ($limit_end < 0) {
            $limit_end = 0;
        }
//
//        //if order type was changed
        if ($order_type) {
            $filter_session_data['order_type'] = $order_type;
        } else {
            //we have something stored in the session?
            if ($this->session->userdata('order_type')) {
                $order_type = $this->session->userdata('order_type');
            } else {
                //if we have nothing inside session, so it's the default "Asc"
                $order_type = 'Desc';
            }
        }
//        //make the data type var avaible to our view
        $data['order_type_selected'] = $order_type;
//
//
        //we must avoid a page reload with the previous session data
        //if any filter post was sent, then it's the first time we load the content
        //in this case we clean the session filter data
        //if any filter post was sent but we are in some page, we must load the session data

        //filtered && || paginated
        if ($manufacture_id !== false && $search_string !== false && $logistics_id !== false || $status !== false || $this->uri->segment(3) == true || $time) {

            /*
            The comments here are the same for line 79 until 99

            if post is not null, we store it in session data array
            if is null, we use the session data already stored
            we save order into the the var to load the view with the param already selected
            */
            //var_dump($manufacture_id);die;


                if ($manufacture_id || $manufacture_id!==false) {
                    $filter_session_data['manufacture_selected'] = $manufacture_id;
                } else {
                    $manufacture_id = $this->session->userdata('manufacture_selected');
                }
                //var_dump($manufacture_id);die;

                if ($logistics_id  || $logistics_id!==false) {
                    $filter_session_data['logistics_selected'] = $logistics_id;
                } else {
                    $logistics_id = $this->session->userdata('logistics_selected');
                }

            if ($status  || $status!==false) {
                $filter_session_data['status_selected'] = $logistics_id;
            } else {
                $status = $this->session->userdata('status_selected');
            }
            $data['manufacture_selected'] = $manufacture_id;
            $data ['logistics_selected'] = $logistics_id;
            $data['status_selected'] = $status;
            if ($search_string) {
                $filter_session_data['search_string_selected'] = $search_string;
            } else {
                //$search_string = $this->session->userdata('search_string_selected');
            }
            $data['search_string_selected'] = $search_string;

            if ($order) {
                $filter_session_data['order'] = $order;
            } else {
                $order = $this->session->userdata('order');
            }
            $data['order'] = $order;

            //save session data into the session
            $this->session->set_userdata($filter_session_data);

            //fetch manufacturers data into arrays
            $data['manufactures'] = $this->manufacturers_model->get_manufacturers2($logistics_id,$status,$time);
            // $shops = $this->manufacturers_model->count_manufacturers(); 
           
            $shopid2name=[];
            foreach ($data['manufactures'] as $val){
                // echo json_encode($val['shop_id']);die;
                $shopid2name[$val['shop_id']] = $val['name'];
            }
            $data['shopid2name'] =$shopid2name;
            $data['count_orders'] = $this->orders_model->count_orders($manufacture_id, $search_string, $order,$logistics_id,$status,$time);

            // 订单状态分类 逻辑

            $data['menuList'] =[
                "全部订单"=>[
                    "len"=>$this->orders_model->count_orders(0, '', '',0,0),
                    "type" =>'0,0'
                ],
                "新订单"=>[
                    "len"=> $this->orders_model->count_orders(0, '', '',1,1),
                    "type" =>'1,1'
                ],
                "待发货"=>[
                    "len"=>$this->orders_model->count_orders(0, '', '',2,1),
                    "type" =>'1,2'
                ],
                "已发货"=>[
                    "len"=>$this->orders_model->count_orders(0, '', '',2,2),
                    "type" =>'2,2'
                ],
                "缺货"=>[
                    "len"=>$this->orders_model->count_orders(0, '', '',0,4),
                    "type" =>'4,0'
                ],
                "已搁置"=>[
                    "len"=>$this->orders_model->count_orders(0, '', '',0,3),
                    "type" =>'3,0'
                ]

            ];

         
            $config['total_rows'] = $data['count_orders'];
            //fetch sql data into arrays

            
            if ($search_string) {
               
                if ($order) {
                    $data['orders'] = $this->orders_model->get_orders($manufacture_id, $search_string, $order, $order_type, $config['per_page'], $limit_end);
                } else {
                    $data['orders'] = $this->orders_model->get_orders($manufacture_id, $search_string, '', $order_type, $config['per_page'], $limit_end);
                }
            } else {
                
                if ($order) {
                    $data['orders'] = $this->orders_model->get_orders($manufacture_id, '', $order, $order_type, $config['per_page'], $limit_end,$logistics_id,$status,$time);
                    // echo 123;die;
                } else {
                    $data['orders'] = $this->orders_model->get_orders($manufacture_id, '', '', $order_type, $config['per_page'], $limit_end,$logistics_id,$status,$time);
                
                }
            }
            //print_r($data);die;
            

        } else {
            

            //clean filter data inside section
            $filter_session_data['manufacture_selected'] = null;
            $filter_session_data['search_string_selected'] = null;
            $filter_session_data['order'] = null;
            $filter_session_data['order_type'] = null;
            $this->session->set_userdata($filter_session_data);

            //pre selected options
            $data['search_string_selected'] = '';
            $data['manufacture_selected'] = 0;
            $data['order'] = 'id';
            $data['logistics_selected']=1;
            $data['status_selected']=1;
            //fetch sql data into arrays
            $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
            $shopid2name=[];
            foreach ($data['manufactures'] as $val){
                $shopid2name[$val['shop_id']] = $val['name'];
            }
            $data['shopid2name'] =$shopid2name;
            $data['count_orders'] = $this->orders_model->count_orders();

            $data['orders'] = $this->orders_model->get_orders('', '', '', $order_type, $config['per_page'], $limit_end,$data['logistics_selected']);
            
            $config['total_rows'] = $data['count_orders'];
            //print_r($data['orders']);die;

        }//
        // echo json_encode($data);die;
        
        //initializate the panination helper
        $this->pagination->initialize($config);

        //整理数据
        // $handledata=[];
      
        // foreach ($data['orders'] as $val){
        //     $val['checked']=false;
        //     $handledata[$val['order_id']][] = $val;
        // }
        // $data['orders']=$handledata;
        // $this->apiOut($data['orders']);
        //load the view
        $data['main_content'] = 'admin/orders/list';
        $this->apiOut($data);
        // echo json_encode("'店铺：' $manufacture_id ，'物流：' $logistics_id ,'状态：'  $status ，'id'  $search_string  ,'order' $order ,'type' $order_type" ); die;
        $this->load->view('includes/template', $data);

    }//index
    public function indexView(){
        $data['main_content'] = 'admin/orders/order';
        $this->load->view('includes/template', $data);
    }

    public function add()
    {
        //if save button was clicked, get the data sent via post
        if ($this->input->server('REQUEST_METHOD') === 'POST') {

            //form validation
            $this->form_validation->set_rules('description', 'description', 'required');
            $this->form_validation->set_rules('stock', 'stock', 'required|numeric');
            $this->form_validation->set_rules('cost_price', 'cost_price', 'required|numeric');
            $this->form_validation->set_rules('sell_price', 'sell_price', 'required|numeric');
            $this->form_validation->set_rules('manufacture_id', 'manufacture_id', 'required');
            $this->form_validation->set_error_delimiters('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>', '</strong></div>');

            //if the form has passed through the validation
            if ($this->form_validation->run()) {
                $data_to_store = array(
                    'description' => $this->input->post('description'),
                    'stock' => $this->input->post('stock'),
                    'cost_price' => $this->input->post('cost_price'),
                    'sell_price' => $this->input->post('sell_price'),
                    'manufacture_id' => $this->input->post('manufacture_id')
                );
                //if the insert has returned true then we show the flash message
                if ($this->orders_model->store_product($data_to_store)) {
                    $data['flash_message'] = TRUE;
                } else {
                    $data['flash_message'] = FALSE;
                }

            }

        }
        //fetch manufactures data to populate the select field
        $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
        //load the view
        $data['main_content'] = 'admin/orders/add';
        $this->load->view('includes/template', $data);
    }

    /**
     * Update item by his id
     * @return void
     */
    public function update()
    {
        //product id
        $id = $this->input->post('id');
        $data['id']=$id;
        //if save button was clicked, get the data sent via post
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
                $data_to_store = array(
                    'name' => $this->input->post('name'),
                    'first_line' => $this->input->post('first_line'),
                    'second_line' => $this->input->post('second_line'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'zip' => $this->input->post('zip'),
                    'country' => $this->input->post('country'),
                    'phone' => $this->input->post('phone'),
                    'carrier_name' => $this->input->post('carrier_name'),
                    'tracking_code' => $this->input->post('tracking_code'),
                    'message_from_buyer' => $this->input->post('message_from_buyer'),
                    'message_from_seller' => $this->input->post('message_from_seller'),
                );
                // $this->apiOut($data_to_store);
                //if the insert has returned true then we show the flash message
				$data['order'] = $this->orders_model->get_product_by_id($id);
				$order_id = $data['order'][0]['order_id'];
				//echo json_encode($order_id);die;
                if ($this->orders_model->update_product_by_order_id($order_id, $data_to_store) == TRUE) {
                    $this->session->set_flashdata('flash_message', 'updated');
                    $data['code']=1;
                } else {
                    $this->session->set_flashdata('flash_message', 'not_updated');
                    $data['code']=0;
                }
        }
        $this->apiOut($data);
    }//update

    /**
     * Delete product by his id
     * @return void
     */
    public function delete()
    {
        //product id
        $id = $this->uri->segment(4);
        $this->orders_model->delete_product($id);
        redirect('admin/orders');
    }//edit

    public function exportorder()
    {
        $objPHPExcel = $this->phpexcel;
        // Set document properties

        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");


//set font size bold
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        $objPHPExcel->getActiveSheet()->getStyle('A1:U1')->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle('A1:U1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:U1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// set table header content
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '*订单号')
            ->setCellValue('B1', '*sku')
            ->setCellValue('C1', '*数量(大于0的整数)')
            ->setCellValue('D1', '单价（USD）')
            ->setCellValue('E1', '*买家姓名')
            ->setCellValue('F1', '*地址1')
            ->setCellValue('G1', '地址2')
            ->setCellValue('H1', '*城市')
            ->setCellValue('I1', '*省/州')
            ->setCellValue('J1', '*国家二字码')
            ->setCellValue('K1', '*邮编')
            ->setCellValue('L1', '电话')
            ->setCellValue('M1', '手机')
            ->setCellValue('N1', '订单备注')
            ->setCellValue('O1', '图片网址')
            ->setCellValue('P1', '售出链接')
            ->setCellValue('Q1', '中文报关名')
            ->setCellValue('R1', '英文报关名')
            ->setCellValue('S1', '申报金额(USD)')
            ->setCellValue('T1', '申报重量(USD)')
            ->setCellValue('U1', '海关编码(USD)');

        $orderid = isset($_POST['orderid']) ?  $_POST['orderid'] :0;
        $shopid = isset($_POST['shopid']) ?  $_POST['shopid'] : 0;
        $logistics = isset($_POST['logistics']) ? $_POST['logistics'] : 0;
        $import = isset($_POST['impt']) ? $_POST['impt'] : 0;
        $query = $_POST['query'];
        $time= $_POST['time'];

        // echo json_encode($query);die;
        // echo  'import:'.$import.' 物流：'.$logistics.' arr:'.json_encode($query).' orderid:'.$orderid.' shopid:'.$shopid;die;
        $this->db->where('status', 1);
        if(!empty($orderid)){
            $this->db->where('order_id', $orderid);
        }
        if(!empty($shopid)){
            $this->db->where('shop_id', $shopid);
        }
        if(!empty($logistics)){
            if($logistics == 1){
                $this->db->where('tracking_code', '');
            }else{
                $this->db->where('tracking_code !=', '');
            }
        }
        if(!empty($import)){
            $this->db->where('import', $import);
        }
        if($time&&$time!=null&&$time!=""){
            $this->db->where('creatdTime >=', $time[0]/1000);
            $this->db->where('creatdTime <=', $time[1]/1000+86400);
        }
        if($query){
            $this->db->where_in('order_id', $query);
        }
        if($import==1){
            $this->db->order_by('orders.creatdTime', 'Asc');
        }else{
            $this->db->order_by('orders.creatdTime', 'Desc');
        }
        // 

        $orderinfo = $this->db->get('orders')->result_array();
        foreach ($orderinfo as $key => $val) {
            $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($key + 2), $val['order_id']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($key + 2), $val['listings_sku']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($key + 2), $val['number']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($key + 2), $val['price']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($key + 2), $val['name']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($key + 2), $val['first_line']);
			$objPHPExcel->getActiveSheet(0)->setCellValue('G' . ($key + 2), $val['second_line']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($key + 2), $val['city']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($key + 2), $val['state']?$val['state']:$val['city']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('J' . ($key + 2), $val['country_code']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('K' . ($key + 2), $val['zip']);
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('订单汇总表');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $fileName = '订单汇总表(' . date('Ymd') . ').xls';
        // Redirect output to a client’s web browser (Excel5)
        ob_end_clean();//清除缓冲区,避免乱码

        $savePath='/assets/excel/';
       
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $_fileName = iconv("utf-8", "gb2312", $fileName);
        $_savePath = '.'.$savePath.$_fileName;
        try{
            $objWriter->save($_savePath);
        }catch(Excplode $e){
            $res=$e;
        }
        $ret=['code'=>1,'path'=>$savePath.$fileName];

        if($res){
            $ret['code']=0;
            $ret['path']= $res;
        }
       $this->apiOut($ret);
	}

    //更新物流
    public function uploadorder()
    {
        $this -> output -> enable_profiler(TRUE); 
        $reader = PHPExcel_IOFactory::createReader('Excel2007'); //设置以Excel5格式(Excel97-2003工作簿)
        if (!$reader->canRead($_FILES["file"]["tmp_name"])) {
            $reader = PHPExcel_IOFactory::createReader('Excel5');
            if (!$reader->canRead($_FILES["file"]["tmp_name"])) {
                echo 'no Excel';
                exit();
            }
        }
        $PHPExcel = $reader->load($_FILES["file"]["tmp_name"]); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $data = []; //下面是读取想要获取的列的内容
        $num = $highestRow - 1 ;
        $num1 = 0 ;
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $temp= [
                'order_id' => $cell = $sheet->getCell('A' . $rowIndex)->getValue(),
                'carrier_name' => $cell = $sheet->getCell('B' . $rowIndex)->getValue(),
                'tracking_code' => $cell = $sheet->getCell('C' . $rowIndex)->getValue(),
                //'import' =>2,
            ];
            if($temp['order_id']!=null&&$temp['carrier_name']!=null&&$temp['tracking_code']!=null){
                try{
                    //updata  缺货 if have id ; import=4 ;  => import = 1 ;

                    $this->db->update('orders',array('import'=>1),array('order_id'=>$temp['order_id'],'import'=>4));
                    // by  wennjie 
                    $this->db->select('*');
                    $this->db->from('orders');
                    $this->db->where('order_id', $temp['order_id']);
                    $query = $this->db->get()->result_array();
                    $len = count($query);
                    if($len){
                        $this->db->where('order_id', $temp['order_id'])->update('orders', $temp);
                        $res=$this->db->affected_rows();  //yrue 成功更新 //0 更新失败 -1无该id 其他都为成功
                    }else{
                        $res = -1 ; // 查无id
                    }
                    
                }catch(Excplode $e){
                    $res=$e;
                }
            }else{
                $num1+=1;
            }
            if($res>0){
                $num--;
            }
            $str='';
            if($temp['order_id']==null) {
              $str="订单id为空,"; 
            }
            if($temp['carrier_name']==null) {
                $str="$str 物流公司名为空,";
            };
            if($temp['tracking_code']==null){
                $str="$str 物流单号为空";
            };
          
            $temp["res"]=$res;
            $temp["len"]=$len;
            $temp['msg']=$str;
            // $temp["sql"]=$this->db->last_query();
            $data[] = array($temp);
        }
        $datares['data']=$data;
        $datares['err']=$num;
        $datares['err1']=$num1;
        $this->apiOut($datares);
    }

    //发货
//    public function delivery(){
//        $orders = $this->db->where('import',2)->get('orders')->result_array();
//        $orderdata=[];
//        foreach ($orders as $val){
//            ///shops/:shop_id/receipts/:receipt_id/tracking
//
//            $shopArr = json_decode($etsyService->request('/shops/15774639/receipts/1333892909/tracking','post',['tracking_code'=>'0B0480284000701032955','carrier_name'=>'usps']), true);
//        }
//        print_r($orders);die;
//    }



//TODO  by wennjie
    public function noStock(){
        $array = $this->input->post('arr');
        $data = array('import'=>4);
        try{
            foreach ($array as $val){
                $id = array('order_id'=>$val['order_id']);
                $this->db->update('orders',$data,$id);
            };
        }catch(Excplode $e){
            $res=$e;
        }
    
        $res=[
            'code'=>1,
            "message"=>'缺货成功'
        ];
        $this->apiOut($res);
    }
    public function recoverys(){
        $array = $this->input->post('arr');
        $data = array('import'=>1);

        try{
            foreach ($array as $val){
                // $id = array('order_id'=>$val['order_id'],'import'=>4);
                $id = array('order_id'=>$val['order_id']);
                $this->db->update('orders',$data,$id);
            };
        }catch(Excplode $e){
            $res=$e;
        }

        $res=[
            'code'=>1,
            "message"=>'恢复成功'
        ];
        $this->apiOut($res);
        
        
    }

}