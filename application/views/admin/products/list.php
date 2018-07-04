<style>

    .file {
        position: relative;
        background: #5bb75b;
        border: 1px solid #fff;
        border-radius: 4px;
        padding: 7px 12px;
        overflow: hidden;
        color: #fff;
        text-decoration: none;
        text-indent: 0;
        line-height: 15px;
        margin-top: 5px;
        margin-left: 5px;
        display: block;
        width: 130px;
        float: left;
        height: 15px;
    }
    .file input {
        position: absolute;
        font-size: 100px;
        right: 0;
        top: 0;
        opacity: 0;
        width:150px;
    }
    .file:hover {
        background: #AADFFD;
        border-color: #78C3F3;
        color: #004974;
        text-decoration: none;
    }
</style>
<div class="container top">

    <ul class="breadcrumb">
        <li>
            <a href="<?php echo site_url("admin"); ?>">
                <?php echo '后台管理'; ?>
            </a>
            <span class="divider">/</span>
        </li>
        <li class="active">
            <?php echo '订单管理'; ?>
        </li>
    </ul>

    <div class="page-header users-header">
                <h2>
                  <?php echo 'orders';?>
                    <a  href="<?php echo site_url("admin").'/auth/pullorder';?>" class="btn btn-failed">拉取订单</a>
                </h2>
    </div>

    <div class="row">
        <div class="span14 columns">
            <div class="well">

                <?php

                $attributes = array('class' => 'form-inline reset-margin', 'id' => 'myform');

                $options_manufacture = array(0 => "all");
                foreach ($manufactures as $row) {
                    $options_manufacture[$row['shop_id']] = $row['name'];
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
                $logistics = [1=>'未导入',2=>'已导入未发货',3=>'已导入已发货'];
                echo form_label('物流状态:');
                echo form_dropdown('logistics_id', $logistics, $logistics_selected, 'class="span2"');



                $data_submit = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => '搜索');



                echo form_submit($data_submit);

                $data_button = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => '导出文档');
                echo '<input type="button" style="margin-left:5px;" class=" btn-success" id="exportOrder" value="下载excel表">';

                //echo form_label('上传excel表更新物流:', 'order');
                echo '<a href="javascript:;" class="file">上传excel表更新物流
                            <input type="file" name="" id="uploadOrder" onchange="fileuploaduserpic();">
                       </a>';

                echo '<input type="button" style="margin-left:5px; margin-top: 5px;" class=" btn-success" id="delivery" value="发货并更新物流">';

                echo form_close();
                ?>
                <input type="hidden" id="exporturl" value=" <?php echo site_url('admin') .'/products/exportorder/' ?> ">
                <input type="hidden" id="uploadurl" value=" <?php echo site_url('admin') .'/products/uploadorder/' ?> ">
                <input type="hidden" id="delivery_url" value=" <?php echo site_url('admin') .'/auth/delivery/' ?> ">
            </div>
<!--            <div class="well">-->
<!--                --><?php
//
//                ?>
<!--            </div>-->

            <table class="table table-striped table-bordered table-condensed">
                <thead>
                <tr>
                    <th class="header">order_id</th>
                    <th class="red header">选项</th>
                    <th class="red header" style="width: 1000px">缩略图</th>
                    <th class="yellow header headerSortDown">店铺名字</th>
                    <th class="green header">listings.sku</th>
                    <th class="red header">listings.title</th>
                    <th class="red header">数量</th>
                    <th class="red header">地址信息</th>

                    <th class="red header">message_from_buyer</th>
                    <th class="red header">备注</th>
                    <th class="red header">logistics_mode</th>
                    <th class="red header">logistics_number</th>

                    <th class="red header">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($products as $row){

                    foreach ($row as $key=>$value) {
                        if(count($row)>1){
                            echo '<tr style="color:red"><td>';
                        }else{
                            echo '<tr><td>';
                        }

                        if($key==0){
                            echo  $value['order_id'];
                        }

                        echo '</td><td>' . $value['variations_a'] . '<br>' . $value['variations_b'] . '</td>';
                        echo '<td><div style="width: 170px">' . '<img  src="' . $value['product_img'] . '">' . '</div></td><td>';
                        if($key==0){
                            echo  $shopid2name[$value['shop_id']];
                        }
                        echo '</td><td>' . $value['listings_sku'] . '</td>';
                        echo '<td>' . $value['listings_title'] . '</td>';
                        echo '<td>' . $value['number'] . '</td><td>';
                        //echo '<td>' .
                            if($key==0){
                                echo
                                '<a>name:</a>' . $value['name'] .
                                '<br><a>first_line:</a>' . $value['first_line'] .
                                '<br><a>second_line:</a>' . $value['second_line'] .
                                '<br><a>city:</a>' . $value['city'] .
                                '<br><a>state:</a>' . $value['state'] .
                                '<br><a>zip:</a>' . $value['zip'] .
                                '<br><a>country:</a>' . $value['country'] .
                                '<br><a>电话/手机:</a>' . $value['phone'];
                            }
                        echo '</td><td>';
                            if($key==0){
                                echo $value['message_from_buyer'];
                            }
                        echo   '</td>';
                        echo '<td>' . $value['message_from_seller'] . '</td>';
                        echo '<td>' . $value['Logistics_mode'] . '</td>';
                        echo '<td>' . $value['Logistics_number'] . '</td>';
                        echo '<td class="crud-actions">
                  <a href="' . site_url("admin") . '/products/update/' . $value['id'] . '" class="btn btn-info">edit</a>  

                </td>';
                        echo '</tr>';

                    }
                    if(count($row)>1){
                        echo "</div>";
                    }
                }
                ?>
                </tbody>
            </table>

            <?php echo '<div class="pagination">' . $this->pagination->create_links() . '</div>'; ?>

        </div>
    </div>
    <script>
        $(function(){
            $("#exportOrder").click(function () {
                var flag = confirm('确认导出订单？');
                if(flag){
                    var id =1;
                    var url = $("#exporturl").val();
                    window.location.href = url;
                }else{
                    return false;
                }
            })

            $("#delivery").click(function () {
                var flag = confirm('确认发货吗？');

                if(flag){
                    var url = $("#delivery_url").val();
                    window.location.href = url;
//                    $.ajax({
//                        url : url,
//                        type : 'POST',
//                        data : {id:1},
//                        dataType: 'json',
//                        success : function(responseStr) {
//                            if(responseStr.code ==0){
//                                alert('更新成功');
//                                return;
//                            }else{
//                                alert('更新失败');
//                                return;
//                            }
//                        }
//                    });


                }else{
                    return false;
                }
            })
        });

        function fileuploaduserpic(){
            var filefullpath = $('#uploadOrder').val();
            if(filefullpath == ''){return;}
            var filetype=filefullpath.substring(filefullpath.lastIndexOf(".")+1,filefullpath.length);
            var validfiletype = 'xls,xlsx';
            var validfiletypearr = validfiletype.split(',');
            filetype = filetype.toLowerCase();
            if($.inArray(filetype,validfiletypearr) == -1 ){alert('文件类型不支持');return;}
            var formData = new FormData();
            formData.append("file",$("#uploadOrder")[0].files[0]);

            var url = $("#uploadurl").val();
            $.ajax({
                url : url,
                type : 'POST',
                data : formData,
                dataType: 'json',
                processData : false, // 告诉jQuery不要去处理发送的数据
                contentType : false,// 告诉jQuery不要去设置Content-Type请求头
                beforeSend:function(){
                    console.log("正在进行，请稍候");
                },
                success : function(responseStr) {
                    if(responseStr.code ==0){
                        alert('更新成功');
                        return;
                    }else{
                        alert('更新失败');
                        return;
                    }
                }
            });
        }

    </script>