<?php
assert(isset($softwareVersionLine) && is_string($softwareVersionLine));
assert(isset($copyrightLine) && is_string($copyrightLine));
?>
 
<!-- REQUIRED JS SCRIPTS -->

<!-- Bootstrap 3.3.5 -->
<script src="<?= SITE_URL ?>management/assets/vendors/bootstrap/js/bootstrap.min.js" ></script>
<!-- AdminLTE App -->
<script src="<?= SITE_URL ?>management/assets/vendors/adminlte/js/app.min.js"></script>

<script src="<?= SITE_URL ?>management/assets/script.js?v=<?php echo rand(); ?>"></script>


</body>
</html>