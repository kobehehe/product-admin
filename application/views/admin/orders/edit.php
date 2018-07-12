    <div class="container top">
      
      <ul class="breadcrumb">
        <li>
          <a href="<?php echo site_url("admin"); ?>">
            <?php echo ucfirst($this->uri->segment(1));?>
          </a> 
          <span class="divider">/</span>
        </li>
        <li>
          <a href="<?php echo site_url("admin").'/'.$this->uri->segment(2); ?>">
            <?php echo ucfirst($this->uri->segment(2));?>
          </a> 
          <span class="divider">/</span>
        </li>
        <li class="active">
          <a href="#">Update</a>
        </li>
      </ul>
      
      <div class="page-header">
        <h2>
          Updating Orders
        </h2>
      </div>

 
      <?php
      //flash messages
      if($this->session->flashdata('flash_message')){
        if($this->session->flashdata('flash_message') == 'updated')
        {
          echo '<div class="alert alert-success">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>Well done!</strong> product updated with success.';
          echo '</div>';       
        }else{
          echo '<div class="alert alert-error">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>Oh snap!</strong> change a few things up and try submitting again.';
          echo '</div>';          
        }
      }
      ?>
      
      <?php
      //form data
      $attributes = array('class' => 'form-horizontal', 'id' => '');
      $options_manufacture = array('' => "Select");
      foreach ($manufactures as $row)
      {
        $options_manufacture[$row['id']] = $row['name'];
      }

      //form validation
      echo validation_errors();

      echo form_open('admin/orders/update/'.$this->uri->segment(4).'', $attributes);
      ?>
        <fieldset>
          <div class="control-group">
            <label for="inputError" class="control-label">姓名</label>
            <div class="controls">
              <input type="text" id="" name="name" value="<?php echo $product[0]['name']; ?>" >
              <!--<span class="help-inline">Woohoo!</span>-->
            </div>
          </div>
          <div class="control-group">
            <label for="inputError" class="control-label">地址1</label>
            <div class="controls">
              <input type="text" id="" name="first_line" value="<?php echo $product[0]['first_line']; ?>">
              <!--<span class="help-inline">Cost Price</span>-->
            </div>
          </div>          
          <div class="control-group">
            <label for="inputError" class="control-label">地址2</label>
            <div class="controls">
              <input type="text" id="" name="second_line" value="<?php echo $product[0]['second_line'];?>">
              <!--<span class="help-inline">Cost Price</span>-->
            </div>
          </div>
          <div class="control-group">
            <label for="inputError" class="control-label">城市</label>
            <div class="controls">
              <input type="text" name="city" value="<?php echo $product[0]['city']; ?>">
              <!--<span class="help-inline">OOps</span>-->
            </div>
          </div>
            <div class="control-group">
                <label for="inputError" class="control-label">省/州</label>
                <div class="controls">
                    <input type="text" name="state" value="<?php echo $product[0]['state']; ?>">
                    <!--<span class="help-inline">OOps</span>-->
                </div>
            </div>

            <div class="control-group">
                <label for="inputError" class="control-label">邮编</label>
                <div class="controls">
                    <input type="text" name="zip" value="<?php echo $product[0]['zip']; ?>">
                    <!--<span class="help-inline">OOps</span>-->
                </div>
            </div>
            <div class="control-group">
                <label for="inputError" class="control-label">国家</label>
                <div class="controls">
                    <input type="text" name="country" value="<?php echo $product[0]['country']; ?>">
                    <!--<span class="help-inline">OOps</span>-->
                </div>
            </div>
            <div class="control-group">
                <label for="inputError" class="control-label">电话/手机</label>
                <div class="controls">
                    <input type="text" name="phone" value="<?php echo $product[0]['phone']; ?>">
                    <!--<span class="help-inline">OOps</span>-->
                </div>
            </div>
            <div class="control-group">
                <label for="inputError" class="control-label">买家留言</label>
                <div class="controls">
                    <textarea name="message_from_buyer" rows="5" readonly="true"><?php echo $product[0]['message_from_buyer']; ?></textarea>
<!--                    <input type="text" name="message_from_buyer" value="--><?php //echo $product[0]['message_from_buyer']; ?><!--">-->
                    <!--<span class="help-inline">OOps</span>-->
                </div>
            </div>
            <div class="control-group">
                <label for="inputError" class="control-label">备注</label>
                <div class="controls">
                    <textarea name="message_from_seller" rows="5" cols="20"><?php echo $product[0]['message_from_seller']; ?></textarea>
                    <!--<span class="help-inline">OOps</span>-->
                </div>
            </div>
          <?php
//          echo '<div class="control-group">';
//            echo '<label for="manufacture_id" class="control-label">Manufacture</label>';
//            echo '<div class="controls">';
//              //echo form_dropdown('manufacture_id', $options_manufacture, '', 'class="span2"');
//
//              echo form_dropdown('manufacture_id', $options_manufacture, $product[0]['manufacture_id'], 'class="span2"');
//
//            echo '</div>';
//          echo '</div">';
          ?>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit">保存</button>
              <a href="<?php echo  site_url("admin").'/orders' ?>" class="btn">
                  取消
              </a>
          </div>
        </fieldset>

      <?php echo form_close(); ?>

    </div>
     