<?php
assert(isset($title) && is_string($title));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($translationPlaceholders) && is_array($translationPlaceholders));
assert(isset($monitor));
assert(isset($location) && is_string($location));
assert(isset($lines) && is_array($lines));

?>
	<!-- Main content -->
	<section class="content">
	<div class="row">
		<!-- left column -->
		<div class="col-md-12">
		<!-- general form elements -->
		<div class="box box-primary">
			<!-- /.box-header -->
			 
			<!-- form start -->
			<?php 
    if (isset($errorHtml)) {
        echo $errorHtml;
    };
    ?>
			<form role="form" method="post" action="" id="frmOperatii">
			<div class="box-body">
				<div class="form-group">
				<label ><?= $translationTitles["LocationName"] ?></label>
				<input type="text" class="form-control required" autofocus id="Location" name="params[location]" value="<?= $monitor->location ?>" placeholder="<?= $translationPlaceholders["LocationName"] ?>">
				</div>
				
				<div class="form-group">
				<label ><?= $translationTitles["Description"] ?></label>
				<textarea class="form-control"  id="Description" name="params[description]" placeholder="<?= $translationPlaceholders["Description"] ?>"><?= $monitor->description ?></textarea>
				</div>
 
				<div class="form-group">
				<label ><?= $translationTitles["IpAddress"] ?></label>
				<input type="text" class="form-control required" id="ipAddress" name="params[ipAddress]" value="<?= $monitor->ipAddress ?>" placeholder="<?= $translationPlaceholders["IpAddress"] ?>">
				</div>
 
				<div class="form-group">
				<label ><?= $translationTitles["LocationId"] ?></label>
				<input type="text" class="form-control required" id="locationId" name="params[locationId]" value="<?= $location ?>" readonly placeholder="<?= $translationPlaceholders["LocationId"] ?>">
				</div>
				
				<div class="form-group">
				<label ><?= $translationTitles["LineName"] ?></label>
					<select name="params[lineId]" id="lineId" class="form-control required">
					<?php foreach ($lines as $id => $name) { ?>
						<option value="<?= $id ?>" <?= $id === $monitor->lineId ? 'selected' : '' ?>><?= $name ?></option>
					<?php 
} ?>
					</select>
				</div> 

			</div>
			<!-- /.box-body -->

			<div class="box-footer">
				<input type="reset" class="btn btn-warning" value="<?= $translationTitles["Reset"] ?>"/>
				<input type="submit" class="btn btn-success" value="<?= $translationTitles["Save"] ?>"/>
			</div>
			</form>
		</div>
		<!-- /.box -->
		</div>
	</div>

	</section>
	<!-- /.content -->
</div>