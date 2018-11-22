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
                    <a  href="<?php echo site_url("admin").'/auth/pullorder';?>" class="btn">拉取订单</a>
                </h2>
    </div>
    <div class="row">
        <div class="span12 columns">
            <div class="well">
				<table>
				<tr><td>
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

                echo form_label('订单id:', 'search_string');
                echo form_submit($data_submit);
                echo form_input('search_string', $search_string_selected, 'style="width:100px;"');

                echo form_label('店铺:', 'manufacture_id');
                echo form_dropdown('manufacture_id', $options_manufacture, $manufacture_selected, 'style="width:100px;"');
                $logistics = [0=>'all',1=>'未导入',2=>'已导入'];
                echo form_label('物流:');
                echo form_dropdown('logistics_id', $logistics, $logistics_selected, 'style="width:80px;"');
                $status = [0=>'all',1=>'未发货',2=>'已发货',3=>'已取消'];
                echo form_label('状态:');
                echo form_dropdown('status', $status, $status_selected, 'style="width:80px;"');
				
                $data_submit = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => '搜索');
                echo form_submit($data_submit);

                $data_button = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => '导出文档');
                echo '<input type="button" style="margin-left:5px;" class=" btn-success" id="exportOrder" value="下载excel表">';

                //echo form_label('上传excel表更新物流:', 'order');

                echo form_close();
                ?>
                <input type="hidden" id="exporturl" value=" <?php echo site_url('admin') .'/orders/exportorder';?> ">
                <input type="hidden" id="uploadurl" value=" <?php echo site_url('admin') .'/orders/uploadorder/' ?> ">
                <input type="hidden" id="delivery_url" value=" <?php echo site_url('admin') .'/auth/delivery/' ?> ">
                <input type="hidden" id="deliveryone_url" value=" <?php echo site_url('admin') .'/auth/deliveryone/' ?> ">
                <input type="hidden" id="delete_url" value=" <?php echo site_url('admin') .'/auth/deleteone/' ?> ">
                <input type="hidden" id="list_url" value=" <?php echo site_url('admin') .'/orders' ?> ">
            </td></tr>
			<tr><td>
                <?php
					echo '<a href="javascript:;" class="file">上传excel表更新物流
                            <input type="file" name="" id="uploadOrder" onchange="fileuploaduserpic();">
                       </a>';
				    echo '<input type="button" style="margin-left:5px; margin-top: 5px;" class=" btn-success" id="delivery" value="发货并更新物流">';
                ?>
			</td></tr>
			</table>
           </div>

            <table class="table table-bordered table-condensed">
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
                $statusArr=[1=>'未发货',2=>'已发货',3=>'已取消'];
                foreach ($orders as $row){
					
                    foreach ($row as $key=>$value) {
                        if($key==0){
							if($i!=0) echo '<tr><td colspan="6" height="30px"> </td></tr>';
							$i=1;
							echo '<tr><td colspan="6" height="30px" style="border-bottom:1px solid #ddd; padding:15px 0px 0px 10px; background-color:#f9f9f9">';
                            echo  $value['order_id'].' <a>'.$shopid2name[$value['shop_id']].'</a><a style="margin-left:10px; color:red;">'.$statusArr[$value['import']].'</a>';
							echo '</td></tr>';
                        }
						else{
							echo '<tr>';
						}  

                        echo '<td style="border-top:none;border-bottom:none"><div class="pic"><a>';
						echo '<img class="img1" src="' . str_replace("_170x135.","_300x300.",$value['product_img']) . '" title="'.$value['listings_title'].'">' ;
						echo '<img class="img2" src="' . str_replace("_170x135.","_300x300.",$value['product_img']) . '">' ;
						echo '</a></div></td>';
						echo '<td style="border-top:none;border-bottom:none">' ;
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
						
						echo '<td  style="border-top:none;border-bottom:none">';
                        //echo '<td>' .
						if($key==0){
							echo
							'<a>姓名:</a>' . $value['name'] .
							'<br><a>地址1:</a>' . $value['first_line'];
							
							if($value['  ']) echo '<br><a>地址2:</a>' . $value['second_line'];
							
							echo
							'<br><a>城市:</a>' . $value['city'] .
							'<br><a>省/州:</a>' . $value['state'] .
							' <a>zip:</a>' . $value['zip'] .
							'<br><a>国家:</a>' . $value['country'];
							
							if($value['phone']) echo '<br><a>电话:</a>' . $value['phone'];
						}
                        echo '</td><td style="border-top:none;border-bottom:none">';
						if($key==0){
							echo $value['message_from_buyer'];
							if ($value['is_gift']) echo "</br><span class='no_break important'>礼品包装</span>";
							echo '</br><span class="important">'.$value['message_from_seller'].'</span>';
						}
                        
                        echo '</td><td style="border-top:none;border-bottom:none">';
						if($key==0){
							
							echo $value['carrier_name'] . '</br>'. $value['tracking_code'];
						}							
						echo '</td>';
                        echo '<td style="border-top:none;border-bottom:none">';
						if($key==0){
							echo '<a href="' . site_url("admin") . '/orders/update/' . $value['id'] . '" class="btn">编辑</a>';
							echo '</br><a href="#" name="'.$value['order_id'] .'" class="btn" id="btn-ship">发货</a> ';
							if($value['import'] == 3){
                                echo '</br><a  name="'.$value['order_id'] .'" class="btn recover" >恢复</a> ';
                            }else{
                                echo '</br><a  name="'.$value['order_id'] .'" class="btn delete" >取消</a> ';
							}
							
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

    new Vue({
        el:'.container',
        data:{

        },
        created() {
            
        },
        mounted() {
            
        },
        methods:{

        }
    })
        $(function(){
            var urls = $("#list_url").val();
            $.ajax({
					url : urls,
					type : 'GET',
					// data : {oid:oid},
					// dataType: 'json',
					success : function(responseStr) {
						console.log(responseStr)
					}
				});
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
			
            $(".delete").click(function () {
                var flag = confirm('确认取消吗？');
                if(flag){
				var oid = $(this).attr('name');
                    var url = $("#delete_url").val();
                    var list_url = $("#list_url").val();
				$.ajax({
                        url : url,
					type : 'POST',
                        data : {oid:oid,type:3},
					dataType: 'json',
					success : function(responseStr) {
						if(responseStr.code ==0){
                                alert('取消成功');
                                location.reload();
							return;
						}else{
                                alert('取消失败');
                                location.reload();
                                return;
                            }
                        }
                    });
                }else{
                    return false;
                }
            });
            $(".recover").click(function () {
                var flag = confirm('确认恢复吗？');
                if(flag){
                    var oid = $(this).attr('name');
                    var url = $("#delete_url").val();
                    var list_url = $("#list_url").val();
                    $.ajax({
                        url : url,
                        type : 'POST',
                        data : {oid:oid,type:1},
                        dataType: 'json',
                        success : function(responseStr) {
                            if(responseStr.code ==0){
                                alert('恢复成功');
                                location.reload();
                            }else{
                                alert('恢复失败');
                                location.reload();
							return;
						}
					}
				});
                }else{
                    return false;
                }

            });

            $("#delivery").click(function () {
                var flag = confirm('确认发货吗？');

                if(flag){
                    var url = $("#delivery_url").val();
                    window.location.href = url;

                }else{
                    return false;
                }
            })
        });

        function fileuploaduserpic(){
            var filefullpath = $('#uploadOrder').val();
            if(filefullpath == ''){return;}
            var filetype=filefullpath.substring(filefullpath.lastIndexOf(".")+1,filefullpath.length);
            var validfiletype = 'xls';
            var validfiletypearr = validfiletype.split(',');
            filetype = filetype.toLowerCase();
            // if($.inArray(filetype,validfiletypearr) == -1 ){alert('文件类型不支持，请另存为 xls 格式！');return;}
            var formData = new FormData();
            formData.append("file",$("#uploadOrder")[0].files[0]);

            var url = $("#uploadurl").val();


            var xhrOnProgress=function(fun) {
  xhrOnProgress.onprogress = fun; //绑定监听
  //使用闭包实现监听绑
  return function() {
    //通过$.ajaxSettings.xhr();获得XMLHttpRequest对象
    var xhr = $.ajaxSettings.xhr();
    //判断监听函数是否为函数
    if (typeof xhrOnProgress.onprogress !== 'function')
      return xhr;
    //如果有监听函数并且xhr对象支持绑定时就把监听函数绑定上去
    if (xhrOnProgress.onprogress && xhr.upload) {
      xhr.upload.onprogress = xhrOnProgress.onprogress;
    }
    return xhr;
  }
}
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
                xhr:xhrOnProgress(function(e){
    var percent=e.loaded / e.total;//计算百分比
    console.log(percent)
  }),
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