<div class="container top">

    <ul class="breadcrumb">
        <li>
            <a href="<?php echo site_url("admin"); ?>">
                <?php echo '后台管理'; ?>
            </a>
            <span class="divider">/</span>
        </li>
        <li class="active">
            <?php echo '商品管理'; ?>
        </li>
    </ul>

    <div class="page-header users-header">
                <h2>
                  <?php echo ucfirst($this->uri->segment(2));?>
                  <a  href="
        <?php echo site_url("admin").'/'.$this->uri->segment(2); ?>/add" class="btn btn-success">Add a new</a>
                </h2>
    </div>

    <div class="row">
        <div class="span12 columns">
            <div class="well">

                <?php

                $attributes = array('class' => 'form-inline reset-margin', 'id' => 'myform');

                $options_manufacture = array(0 => "all");
                foreach ($manufactures as $row) {
                    $options_manufacture[$row['id']] = $row['name'];
                }
                //save the columns names in a array that we will use as filter
                $options_products = array();
                foreach ($products as $array) {
                    foreach ($array as $key => $value) {
                        $options_products[$key] = $key;
                    }
                    break;
                }

                echo form_open('admin/products', $attributes);

                echo form_label(' 搜索订单id:', 'search_string');
                echo form_input('search_string', $search_string_selected, 'style="width: 170px;
height: 26px;"');

                echo form_label('选择店铺:', 'manufacture_id');
                echo form_dropdown('manufacture_id', $options_manufacture, $manufacture_selected, 'class="span2"');

                echo form_label('排序:', 'order');
                echo form_dropdown('order', $options_products, $order, 'class="span2"');

                $data_submit = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => '搜索');

                $options_order_type = array('Asc' => '顺序', 'Desc' => '倒序');
                echo form_dropdown('order_type', $options_order_type, $order_type_selected, 'class="span1"');

                echo form_submit($data_submit);

                echo form_close();
                ?>

            </div>

            <table class="table table-striped table-bordered table-condensed">
                <thead>
                <tr>
                    <th class="header">order_id</th>
                    <th class="yellow header headerSortDown">seller_user_id</th>
                    <th class="green header">listings.sku</th>
                    <th class="red header">listings.title</th>
                    <th class="red header">数量</th>
                    <th class="red header">地址信息</th>
                    <th class="red header">message_from_buyer</th>
                    <th class="red header">message_from_seller</th>
                    <th class="red header">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($products as $row) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td>' . $row['stock'] . '</td>';
                    echo '<td>' . $row['cost_price'] . '</td>';
                    echo '<td>' . $row['sell_price'] . '</td>';
                    echo '<td>' . $row['manufacture_name'] . '</td>';
                    echo '<td>' . $row['manufacture_name'] . '</td>';
                    echo '<td>' . $row['manufacture_name'] . '</td>';
                    echo '<td class="crud-actions">
                  <a href="' . site_url("admin") . '/products/update/' . $row['id'] . '" class="btn btn-info">view & edit</a>  
                  <a href="' . site_url("admin") . '/products/delete/' . $row['id'] . '" class="btn btn-danger">delete</a>
                </td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>

            <?php echo '<div class="pagination">' . $this->pagination->create_links() . '</div>'; ?>

        </div>
    </div>