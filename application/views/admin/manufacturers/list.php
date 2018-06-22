    <div class="container top">

      <ul class="breadcrumb">
        <li>
          <a href="<?php echo site_url("admin"); ?>">
            <?php echo '后台管理';?>
          </a> 
          <span class="divider">/</span>
        </li>
        <li class="active">
          <?php echo '店铺管理';?>
        </li>
      </ul>

      <div class="page-header users-header">
        <h2>
          <?php echo  'shops';?>
          <a  href="<?php echo site_url("admin").'/'.$this->uri->segment(2); ?>/add" class="btn btn-success">增加店铺</a>
        </h2>
      </div>
      
      <div class="row">
        <div class="span12 columns">
<!--          <div class="well">-->
<!--           -->
<!--            --><?php
//
//            $attributes = array('class' => 'form-inline reset-margin', 'id' => 'myform');
//
//            //save the columns names in a array that we will use as filter
//            $options_manufacturers = array();
//            foreach ($manufacturers as $array) {
//              foreach ($array as $key => $value) {
//                $options_manufacturers[$key] = $key;
//              }
//              break;
//            }
//
//            echo form_open('admin/manufacturers', $attributes);
//
//              echo form_label('Search:', 'search_string');
//              echo form_input('search_string', $search_string_selected);
//
//              echo form_label('Order by:', 'order');
//              echo form_dropdown('order', $options_manufacturers, $order, 'class="span2"');
//
//              $data_submit = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => 'Go');
//
//              $options_order_type = array('Asc' => 'Asc', 'Desc' => 'Desc');
//              echo form_dropdown('order_type', $options_order_type, $order_type_selected, 'class="span1"');
//
//              echo form_submit($data_submit);
//
//            echo form_close();
//            ?>
<!---->
<!--          </div>-->

          <table class="table table-striped table-bordered table-condensed">
            <thead>
              <tr>
                  <th class="header">id</th>
                  <th class="yellow header headerSortDown">Name</th>
                  <th class="yellow header headerSortDown">user_id</th>
                  <th class="yellow header headerSortDown">shop_id</th>
                  <th class="yellow header headerSortDown">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach($manufacturers as $row)
              {
                echo '<tr>';
                echo '<td>'.$row['id'].'</td>';
                echo '<td>'.$row['name'].'</td>';
                echo '<td>'.$row['user_id'].'</td>';
                echo '<td>'.$row['shop_id'].'</td>';
                echo '<td class="crud-actions">
                  <a href="'.site_url("admin").'/manufacturers/update/'.$row['id'].'" class="btn btn-info">view & edit</a>  
                  <a href="'.site_url("admin").'/manufacturers/delete/'.$row['id'].'" class="btn btn-danger">授权</a>
                </td>';
                echo '</tr>';
              }
              ?>      
            </tbody>
          </table>

          <?php echo '<div class="pagination">'.$this->pagination->create_links().'</div>'; ?>

      </div>
    </div>