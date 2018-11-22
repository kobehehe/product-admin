<!DOCTYPE html>
<html lang="en-US">

<head>
	<title>商品管理平台</title>
	<meta charset="utf-8">
	<link href="<?php echo base_url(); ?>assets/css/admin/global.css" rel="stylesheet" type="text/css">
	<!-- 引入样式 -->
	<link href="<?php echo base_url(); ?>assets/lib/index.css" rel="stylesheet" type="text/css">
	<!-- 引入组件库 -->
	<!-- 开发环境版本，包含了有帮助的命令行警告 -->
	<script src="<?php echo base_url(); ?>assets/lib/vue.js"></script>
	<script src="<?php echo base_url(); ?>assets/lib/index.js"></script>
	
	<script src="<?php echo base_url(); ?>assets/js/jquery-1.7.1.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/admin.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/other.js"></script>
	<!-- <script src="<?php echo base_url(); ?>assets/lib/VueLazyImages.js"></script> -->
	<!-- <script src="https://unpkg.com/vue-lazyload/vue-lazyload.js"></script> -->
 
</head>
<body>
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" style='color:#fff;'>商品管理平台</a>
				<ul class="nav">
					<li <?php if($this->uri->segment(2) == 'orders'){echo 'class="active"';}?>>
						<a href="<?php echo base_url(); ?>admin/orders">订单管理</a>
					</li>
					<li <?php if($this->uri->segment(2) == 'manufacturers'){echo 'class="active"';}?>>
						<a href="<?php echo base_url(); ?>admin/manufacturers">店铺管理</a>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">系统设置
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="<?php echo base_url(); ?>admin/logout">退出登录</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</div>