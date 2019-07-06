<?php
    
    assert(isset($title) && is_string($title));
    assert(isset($labelsTitleTranslations) && is_array($labelsTitleTranslations));
    assert(isset($softwareVersionLine) && is_string($softwareVersionLine));
    assert(isset($copyrightLine) && is_string($copyrightLine));
    assert(isset($datababaseVersion) && is_string($datababaseVersion));
?>
<div class="login-box">
    <div class="login-logo">
        <a href="<?= SITE_URL ?>/management">
            <img src="<?= SITE_URL ?>/management/assets/img/logo.png">
        </a>
    </div>

    <div class="login-box-body">
    <?php 
        if (isset($messageHtml)) {
    ?>
        <div id="frontendMsg" class="container">
            <?php echo $messageHtml; ?>
        </div>
    <?php
        }
    ?>
    <p class="login-box-msg"><?= $title ?></p>
    <p class="login-box-msg"><?php echo $softwareVersionLine; ?></p>
    <form action="<?= SITE_URL ?>/management/login/process" method="POST">
        <div class="form-group has-feedback">
            <input type="text" class="form-control" id="username" name="username" placeholder="<?= $labelsTitleTranslations["Username"]; ?>">
            <span class="fa fa-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password" class="form-control" id="password" name="password" placeholder="<?= $labelsTitleTranslations["Password"]; ?>" >
            <span class="fa fa-lock form-control-feedback"></span>
        </div>
        <div class="row">
            <div class="col-sm-12 align-center">
 
              <button type="submit" class="btn btn-primary btn-login btn-flat"><?= $labelsTitleTranslations["LoginButton"]; ?></button>
              <br/><br/>
            </div>
            <!-- /.col -->
        </div>
    </form>
    <div class="app-data text-center mt-40">
        
            <!-- Default to the left -->
        <strong><?php echo $copyrightLine; ?></strong>
    </div>

    </div>
</div>
 