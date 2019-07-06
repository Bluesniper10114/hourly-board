<?php
assert(isset($softwareVersionLine) && is_string($softwareVersionLine));
assert(isset($copyrightLine) && is_string($copyrightLine));
?>
 
</div> <!-- end of main-content -->
</div> <!-- end of col-md-10 -->
</div> <!-- end of row -->
</div> <!-- end of content-wrapper -->
		<!-- Main Footer -->
		<footer class="main-footer">
			<!-- To the right -->
			<div class="pull-right hidden-xs">
			<?= $copyrightLine; ?></div>
			<!-- Default to the left -->
			<strong class="software_version"><?= $softwareVersionLine; ?></strong>
		</footer>

<!-- Add the sidebar's background. This div must be placed
	immediately after the control sidebar -->
	<div class="control-sidebar-bg"></div>
 

<!-- REQUIRED JS SCRIPTS -->

<!-- Bootstrap 3.3.5 -->
<script src="<?= SITE_URL ?>management/assets/vendors/bootstrap/js/bootstrap.min.js" ></script>
<!-- AdminLTE App -->
<script src="<?= SITE_URL ?>management/assets/vendors/adminlte/js/app.min.js"></script>

<script src="<?= SITE_URL ?>management/assets/script.js?v=<?php echo rand(); ?>"></script>


</body>
</html>