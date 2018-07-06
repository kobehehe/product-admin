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
        $search_string = $this->input->post('search_string');
        $order = $this->input->post('order');
        $order_type = $this->input->post('order_type');
        //pagination settings
        $config['per_page'] = 10;
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
        $page = $this->uri->segment(3);

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
                $order_type = 'Asc';
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
        if ($manufacture_id !== false && $search_string !== false && $logistics_id !== false || $this->uri->segment(3) == true) {

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
            $data['manufacture_selected'] = $manufacture_id;
            $data ['logistics_selected'] = $logistics_id;
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
            $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
            $shopid2name=[];
            foreach ($data['manufactures'] as $val){
                $shopid2name[$val['shop_id']] = $val['name'];
            }
            $data['shopid2name'] =$shopid2name;
            $data['count_orders'] = $this->orders_model->count_orders($manufacture_id, $search_string, $order,$logistics_id);
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
                    $data['orders'] = $this->orders_model->get_orders($manufacture_id, '', $order, $order_type, $config['per_page'], $limit_end);
                } else {
                    $data['orders'] = $this->orders_model->get_orders($manufacture_id, '', '', $order_type, $config['per_page'], $limit_end,$logistics_id);
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

        //initializate the panination helper
        $this->pagination->initialize($config);

        //整理数据
        $handledata=[];
        foreach ($data['orders'] as $val){
            $handledata[$val['order_id']][] = $val;
        }
        $data['orders']=$handledata;
        //load the view
        $data['main_content'] = 'admin/orders/list';
        $this->load->view('includes/template', $data);

    }//index

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
        $id = $this->uri->segment(4);

        //if save button was clicked, get the data sent via post
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            //form validation
            $this->form_validation->set_rules('name', 'name', 'required');
            $this->form_validation->set_rules('first_line', 'first_line', 'required');
            $this->form_validation->set_rules('city', 'city', 'required|string');
            $this->form_validation->set_rules('state', 'state', 'required|string');
            $this->form_validation->set_rules('zip', 'zip', 'required');
            //$this->form_validation->set_rules('phone', 'phone', 'required|numeric');
            $this->form_validation->set_error_delimiters('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>', '</strong></div>');
            //if the form has passed through the validation
            if ($this->form_validation->run()) {

                $data_to_store = array(
                    'name' => $this->input->post('name'),
                    'first_line' => $this->input->post('first_line'),
                    'second_line' => $this->input->post('second_line'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'zip' => $this->input->post('zip'),
                    'country' => $this->input->post('country'),
                    'phone' => $this->input->post('phone'),
                    'message_from_buyer' => $this->input->post('message_from_buyer'),
                    'message_from_seller' => $this->input->post('message_from_seller'),
                );
                //if the insert has returned true then we show the flash message
                if ($this->orders_model->update_product($id, $data_to_store) == TRUE) {
                    $this->session->set_flashdata('flash_message', 'updated');
                } else {
                    $this->session->set_flashdata('flash_message', 'not_updated');
                }
                redirect('admin/orders/update/' . $id . '');

            }//validation run

        }

        //if we are updating, and the data did not pass trough the validation
        //the code below wel reload the current data

        //product data
        $data['product'] = $this->orders_model->get_product_by_id($id);
        //fetch manufactures data to populate the select field
        $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
        //load the view
        $data['main_content'] = 'admin/orders/edit';
        $this->load->view('includes/template', $data);

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
        $orderinfo = $this->db->where('status', 1)->get('orders')->result_array();
        foreach ($orderinfo as $key => $val) {
            $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($key + 2), $val['order_id']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($key + 2), $val['listings_sku']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('C' . ($key + 2), $val['number']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('D' . ($key + 2), $val['price']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('E' . ($key + 2), $val['name']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('F' . ($key + 2), $val['first_line']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('H' . ($key + 2), $val['city']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('I' . ($key + 2), $val['state']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('J' . ($key + 2), $val['country_code']);
            $objPHPExcel->getActiveSheet(0)->setCellValue('K' . ($key + 2), $val['zip']);
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('订单汇总表');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel5)
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="订单汇总表(' . date('Ymd') . ').xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

    }

    //更新物流
    public function uploadorder()
    {
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
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $data[] = [
                'order_id' => $cell = $sheet->getCell('A' . $rowIndex)->getValue(),
                'Logistics_mode' => $cell = $sheet->getCell('B' . $rowIndex)->getValue(),
                'Logistics_number' => $cell = $sheet->getCell('C' . $rowIndex)->getValue(),
                'import' =>2,
            ];
        }


        $this->db->update_batch('orders', $data, 'order_id');
        $res = $this->db->affected_rows();

        if($res !== null){
            echo json_encode(['code'=>0,'msg'=>'success']);
        }else{
            echo json_encode(['code'=>-1,'msg'=>'fileds']);
        }

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

}