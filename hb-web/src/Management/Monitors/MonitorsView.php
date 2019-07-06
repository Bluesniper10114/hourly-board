<?php
assert(isset($title) && is_string($title));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($messages) && is_array($messages));

?>
	<section class="content">
		 
		<?php 
    if (isset($errorHtml)) {
        echo $errorHtml;
    }
    ?>
		<div class="row">
			<div class="col-md-12">
				<div class="box box-primary">
					<div class="box-header with-border">
						<a href="<?= SITE_URL ?>management/monitors/add" class="btn btn-success pull-right"><?= $translationTitles["Add"] ?></a>
					</div>
					<div class="box-body no-padding">
						<table class="table table-striped">
							<tr>
								<th><?= $translationTitles["Id"] ?></th>
								<th><?= $translationTitles["Location"] ?></th>
								<th><?= $translationTitles["LocationName"] ?></th>
								<th><?= $translationTitles["Description"] ?></th>
								<th><?= $translationTitles["IpAddress"] ?></th>
								<th><?= $translationTitles["LineName"] ?></th>
								<th></th>
							</tr>
							<?php
        if (!empty($monitors)) {
            foreach ($monitors as $monitor) {
                ?>
											<tr>
												<td><?= $monitor->id ?></td>
												<td><?= $monitor->location ?></td>
												<td><?= $monitor->locationName ?></td>
												<td><?= $monitor->description ?></td>
												<td><?= $monitor->ipAddress ?></td>
												<td><?= $monitor->lineName ?></td>
												<td>
													<a href="<?= SITE_URL . 'management/monitors/edit/' . $monitor->id ?>" class="btn btn-success"><?= $translationTitles["Edit"] ?></a>
													
													<a href="<?= SITE_URL . 'management/shopfloor/' . $monitor->id ?>" target="_blank" class="btn btn-success"><?= $translationTitles["View"] ?></a>
													
													<a href="<?= SITE_URL . 'management/monitors/delete/' . $monitor->id ?>" class="btn btn-warning" onclick="return confirm('<?= $messages["DeleteConfirm"] ?>')"><?= $translationTitles["Delete"] ?></a>
													
													 
												</td>
											</tr>
										<?php

        }
    }
    ?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</section>
 
<script>
	$(document).ready(function() {
		$('.search').keydown(function(event) {
			if (event.keyCode == 13) {
				this.form.submit();
				return false;
			}
		});
	});
</script>