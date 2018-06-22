<div class="container top">

    <ul class="breadcrumb">
        <li>
            <a href="<?php echo site_url("admin"); ?>">
                <?php echo '后台管理' ?>
            </a>
            <span class="divider">/</span>
        </li>
        <li>
            <a href="<?php echo site_url("admin") . '/' . $this->uri->segment(2); ?>">
                <?php echo '店铺管理'; ?>
            </a>
            <span class="divider">/</span>
        </li>
        <li class="active">
            <a href="#">新建店铺</a>
        </li>
    </ul>

    <div class="page-header">
        <h2>
            Adding shop
        </h2>
    </div>

    <?php
    //flash messages
    if (isset($flash_message)) {
        if ($flash_message == TRUE) {
            echo '<div class="alert alert-success">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>Well done!</strong> new manufacturer created with success.';
            echo '</div>';
        } else {
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

    //form validation
    echo validation_errors();

    echo form_open('admin/manufacturers/add', $attributes);
    ?>
    <fieldset>
        <div class="control-group">
            <label for="inputError" class="control-label">Name</label>
            <div class="controls">
                <input type="text" id="" name="name" value="<?php echo set_value('name'); ?>">
                <!--<span class="help-inline">Woohoo!</span>-->
            </div>
             <br>
            <label for="inputError" class="control-label">secret</label>
            <div class="controls">
                <input type="text" id="" name="secret" value="<?php echo set_value('secret'); ?>">
            </div>
            <br>
            <label for="inputError" class="control-label">key</label>
            <div class="controls">
                <input type="text" id="" name="key" value="<?php echo set_value('key'); ?>">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">保存</button>
            <button class="btn" type="reset">Cancel</button>
        </div>
    </fieldset>

    <?php echo form_close(); ?>

</div>
     