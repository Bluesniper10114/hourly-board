<?php 
assert(isset($profile) && is_object($profile));
assert(isset($menu) && is_string($menu));
assert(isset($language) && is_string($language));
assert(isset($translations) && is_array($translations));
assert(isset($title) && is_string($title));

/** @var \DAL\Entities\User */
$localProfile = $profile;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $title; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?= SITE_URL ?>management/assets/vendors/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= SITE_URL ?>management/assets/vendors/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= SITE_URL ?>management/assets/vendors/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= SITE_URL ?>management/assets/vendors/adminlte/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
    page. However, you can choose any other skin. Make sure you
    apply the skin class to the body tag so the changes take effect.
    -->
    <link rel="stylesheet" href="<?= SITE_URL ?>management/assets/vendors/adminlte/css/skins/skin-blue.min.css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="<?= SITE_URL ?>management/assets/vendors/bootstrap/js/html5shiv.min.js"></script>
        <script src="<?= SITE_URL ?>management/assets/vendors/bootstrap/js/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery 2.1.4 -->
     <script src="<?= SITE_URL ?>management/assets/vendors/jquery-3.3.1.min.js" ></script>

    <script src="<?= SITE_URL ?>management/assets/vendors/popper.min.js" ></script>
     

    <link rel="stylesheet" href="<?= SITE_URL ?>management/assets/style.css?v=<?php echo rand(); ?>">
    <link rel="stylesheet" media="print" href="<?= SITE_URL ?>management/assets/print.css?v=<?php echo rand(); ?>">
    <script>
        var formDataChanged = false, ignoreForm= false;
        $(function(){
            $('.sidebar-menu a, .navbar a').click(function(){
                if($(this).parent('li.treeview').length>0 || ($(this).attr('href')=='#')){
                    return;
                }
                var formExist = document.getElementsByTagName("form").length > 0;
                if((ignoreForm || formExist) && formDataChanged){
                    return confirm("<?= $translations["Exit"] ?>");
                }
            });
            $('#openHelpBlock').click(function(){
                $('#helpDialog').modal('toggle');
                return false;
            });
             
        });
    </script>
</head>
<body class="hold-transition skin-blue sidebar-mini layout-top-nav">

    <!-- Main Header -->

        <header class="main-header">
            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top" role="navigation">
             <div class="wrapp">
                <div class="navbar-header">
                  <a href="<?= SITE_URL ?>management" class="navbar-brand">
                        <img src="<?= SITE_URL ?>management/assets/img/logo.jpg" class="main-logo" alt="Logo Image">
                    </a>

                </div>

                <!-- Navbar Right Menu -->
                <div class="navbar-custom-menu">
                    <ul class="nav ">
                        <!-- Messages: style can be found in dropdown.less-->
                        <!-- User Account Menu -->
                        
                        <li class="profile_link_li">
                            <!-- Menu Toggle Button -->
                            <a href="<?= SITE_URL ?>management/profile">
                                <!-- The user image in the navbar-->
                                <img src="<?= SITE_URL ?>management/assets/img/user.png" class="user-image bg-white" alt="User Image">
                                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                                <span class="hidden-xs"> <?= $localProfile->getFullName() . " (" . $localProfile->levelName . ")"; ?></span>
                            </a>

                        </li>
                        <?php
                        $enLink = SITE_URL . 'management/set-language/en';
                        $roLink = SITE_URL . 'management/set-language/ro';
                        $dropdownText = $translations["Romanian"];
                        if ($language === 'en') {
                            $dropdownText = $translations["English"];
                        }
                        ?>
                        <li class="logout_link_li">
                            <a href="<?= SITE_URL ?>management/logout" >LOGOUT</a>
                        </li>
                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                <?= $dropdownText ?> <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                            <li><a href="<?= $roLink ?>"><?= $translations["Romanian"] ?></a></li>
                            <li><a href="<?= $enLink ?>"><?= $translations["English"] ?></a></li>
                            </ul>
                        </li>
                        <li class="help_lang">
                            <a href="#" id="openHelpBlock" title="<?= $translations["HelpTitle"] ?>">
                            <i class="fa fa-question-circle" ></i>
                            </a>
                        </li>
                        <!-- Control Sidebar Toggle Button -->
                    </ul>
                </div>
                </div>
            </nav>
        </header>
        <div id="helpDialog" class="modal" data-width="460">
            <div class="modal-header">
                <h3><?= $translations["HelpTitle"] ?></h3>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-close"></i>          
            </button>
            </div>
                <div class="modal-body">
                    <?php if (isset($helpContent)) { ?>
                    <?= $helpContent ?>
                     <?php 
                } ?>                                              
                </div>
                <div class="modal-footer">
                     
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?= $translations["Cancel"] ?></button>
                </div>
            
        </div>    
<div class="content-wrapper">
<div class="row">
    <div class="col-md-2">
    <?php if (!empty($menu)) { ?>
        <!-- Left side column. contains the sidebar -->
		<aside class="main-sidebar1">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">

				<!-- Menu Start -->

				<?php echo $menu; ?>

				<!-- Menu END -->
				<!-- /.sidebar-menu -->
			</section>
			<!-- /.sidebar -->
		</aside>
    <?php 
} ?>
    </div>
    <div class="col-md-10">
        <div class="main-content">
        <?php if (isset($message)) { ?>
        <section class="msg"><?= $message; ?></section>
        <?php 
    }; ?>

        <section class="content-header">
            <h2  class="card-title">
                <?= $title; ?>
            </h2>
           
        </section>