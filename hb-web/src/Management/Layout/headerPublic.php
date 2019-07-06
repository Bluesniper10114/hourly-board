<?php
assert(isset($title) && is_string($title));
assert(isset($translations) && is_array($translations));

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
<body class="hold-transition login-page">
<?php
