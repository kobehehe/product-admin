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
	.no_break {
		white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
	}
	.important {
	    color:red;
		font-weight:bold;
	}
	.important1 {
	    color:red;
		font-weight:bold;
		font-size:18px;
	}
	.header {
		height:20px;
		text-align:center;
		white-space:nowrap; text-overflow:ellipsis;
		padding:8px;
	}
</style>
<!--商品列表可以显示商品缩略图功能-->
<style type="text/css">
.pic {display: block; position: relative;}
.pic a {position:relative; display:block; background:transparent;}
.pic a .img1 {max-width:90px; max-height:90px; border:0;}
.pic a .img2 {max-width:380px;max-height:380px; border:0; position:absolute;left:90px; top:-80px; display:none; z-index:99999}
.pic a:hover .img2{display:block}
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
        <div class="span12 columns">
            <div class="well">

                <?php

                $attributes = array('class' => 'form-inline reset-margin', 'id' => 'myform');

                $options_manufacture = array(0 => "all");
                foreach ($manufactures as $row) {
                    $options_manufacture[$row['shop_id']] = $row['name'];
                }
                //save the columns names in a array that we will use as filter
                $options_orders = array();
                foreach ($orders as $array) {
                    foreach ($array as $key => $value) {
                        $options_orders[$key] = $key;
                    }
                    break;
                }

                echo form_open('admin/orders', $attributes);

                echo form_label(' 搜索订单id:', 'search_string');
                echo form_input('search_string', $search_string_selected, 'style="width: 170px;
height: 26px;"');

                echo form_label('选择店铺:', 'manufacture_id');
                echo form_dropdown('manufacture_id', $options_manufacture, $manufacture_selected, 'class="span2"');
                $logistics = [0=>'all',1=>'未导入',2=>'已导入'];
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
                <input type="hidden" id="exporturl" value=" <?php echo site_url('admin') .'/orders/exportorder';?> ">
                <input type="hidden" id="uploadurl" value=" <?php echo site_url('admin') .'/orders/uploadorder/' ?> ">
                <input type="hidden" id="delivery_url" value=" <?php echo site_url('admin') .'/auth/delivery/' ?> ">
                <input type="hidden" id="deliveryone_url" value=" <?php echo site_url('admin') .'/auth/deliveryone/' ?> ">
            </div>
<!--            <div class="well">-->
<!--                --><?php
//
//                ?>
<!--            </div>-->

            <table class="table table-striped table-bordered table-condensed">
                <thead>
                <tr>                   
                    <th class="header" style="min-width:80px">缩略图</th>
					<th class="header" style="min-width:200px">产品标题和选项</th>
                    <th class="header" style="min-width:80px">地址信息</th>
                    <th class="header" style="min-width:80px">客户留言和备注</th>
                    <th class="header" style="min-width:80px">物流</th>
                    <th class="header" style="min-width:60px">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($orders as $row){

                    foreach ($row as $key=>$value) {
                        if($key==0){
							echo '<tr><td colspan="6" height="30px" style="padding:15px 0px 0px 10px;">';
                            echo  $value['order_id'].' <a>'.$shopid2name[$value['shop_id']].'</a>';
							echo '</td></tr>';
                        }
						else{
							echo '<tr>';
						}  

                        echo '<td><div class="pic"><a>';
						echo '<img class="img1" src="' . str_replace("_170x135.","_300x300.",$value['product_img']) . '" title="'.$value['listings_title'].'">' ;
						echo '<img class="img2" src="' . str_replace("_170x135.","_300x300.",$value['product_img']) . '">' ;
						echo '</a></div></td>';
						echo '<td>' ;
						echo  $value['formatted_name_a'] . '： <span class="important">' . $value['formatted_value_a'] .'</span>';
						echo '</br>' . $value['formatted_name_b'] . '： <span class="important">' .  $value['formatted_value_b'] .'</span>';
						echo '</br></br>' . $value['listings_sku'] .' X ';
						if($value['number']==1){
							echo '<span>' . $value['number'] . '</span>';
						}
                        else {
						    echo '<span class="important1">' . $value['number'] . '</span>';
						}
						echo '</td>';						
						
						echo '<td class="no_break">';
                        //echo '<td>' .
						if($key==0){
							echo
							'<a>姓名:</a>' . $value['name'] .
							'<br><a>地址1:</a>' . $value['first_line'] .
							'<br><a>地址2:</a>' . $value['second_line'] .
							'<br><a>城市:</a>' . $value['city'] .
							'<br><a>省/州:</a>' . $value['state'] .
							'<div style="display:none"> <a>zip:</a>' . $value['zip'] .
							'<br><a>国家:</a>' . $value['country'] .
							'<br><a>电话:</a>' . $value['phone'];
						}
                        echo   '</div></td><td>';
						if($key==0){
							echo $value['message_from_buyer'];
							if ($value['is_gift']) echo "</br><span class='no_break important'>礼品包装</span>";
							echo '</br><span class="important">'.$value['message_from_seller'].'</span>';
						}
                        
                        echo '</td><td>';
						if($key==0){
							
							echo $value['Logistics_mode'] . '</br>'. $value['Logistics_number'];
						}							
						echo '</td>';
                        echo '<td>';
						if($key==0){
							echo '<a href="' . site_url("admin") . '/orders/update/' . $value['id'] . '" class="btn">编辑</a>';
							echo '</br><a href="#" name="'.$value['order_id'] .'" class="btn" id="btn-ship">发货</a> ';
						}
                        echo '</td></tr>';

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
                    var orderid = $("input[name='search_string']").val();
                    var shopid =  $("select[name='manufacture_id']").val();
                    var logstype =  $("select[name='logistics_id']").val();
                    var url = $.trim($("#exporturl").val());

                    window.location.href = url+'?shopid='+shopid+'&logstype='+logstype+'&orderid='+orderid;
                }else{
                    return false;
                }
            });


            $("#btn-ship").click(function () {
                
				var oid = $(this).attr('name');
				var urlone = $("#deliveryone_url").val();
				$.ajax({
					url : urlone,
					type : 'POST',
					data : {oid:oid},
					dataType: 'json',
					success : function(responseStr) {
						if(responseStr.code ==0){
							alert('发货成功');
							return;
						}else{
							alert('发货失败');
							return;
						}
					}
				});

            });

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
            var validfiletype = 'csv,xls,xlsx';
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