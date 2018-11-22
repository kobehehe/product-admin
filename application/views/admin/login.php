<!DOCTYPE html> 
<html lang="zh">
  <head>
    <title>商品管理系统</title>
    <meta charset="utf-8">
    <link href="<?php echo base_url(); ?>assets/css/admin/global.css" rel="stylesheet" type="text/css">
    <style> 
    body,html,.container.login{
      width:100%;
      height:100%;
      margin:0;
    }
    form{
      position: relative;
    top: 50%;
   
    margin: 0 auto !important;
    margin-top:-150px !important ;
    }
    </style>
  </head>
  <body>
    <div class="container login">
      <?php 
      $attributes = array('class' => 'form-signin');
      echo form_open('admin/login/validate_credentials', $attributes);
      echo '<h2 class="form-signin-heading">登录</h2>';
      echo form_input('user_name', '', 'placeholder="用户名"');
      echo form_password('password', '', 'placeholder="密码"');
      if(isset($message_error) && $message_error){
          echo '<div class="alert alert-error">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>用户名或密码错误</strong>';
          echo '</div>';             
      }
      echo "<br />";
      echo anchor('admin/signup', '点我注册!');
      echo "<br />";
      echo "<br />";
      echo form_submit('submit', '登录', 'class="btn btn-large btn-primary"');
      echo form_close();
      ?>      
    </div><!--container-->
    <script src="<?php echo base_url(); ?>assets/js/jquery-1.7.1.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
  </body>
</html>    
    