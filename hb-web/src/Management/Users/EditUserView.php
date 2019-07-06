<?php
assert(isset($title) && is_string($title));
assert(isset($errors) && is_array($errors));
assert(isset($user) && is_object($user));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($levelsList) && is_array($levelsList));

use Former\Facades\Former;
?>
<div class="content">
	<div class="row">
		<div class="col-md-12">

			<?php
                Former::framework('TwitterBootstrap3');

             if (!empty($message)) {
            ?>
			<div class="alert alert-success">
				<?= $message ?>
			</div>
			<?php }  elseif (count($errors) > 0) {
                ?>
			<div class="alert alert-danger">
				<ul>
					<?php
                            foreach ($errors as $message) {
                            ?>
					<li>
						<?= $message ?>
					</li>
					<?php
                            }
                        ?>
				</ul>
			</div>
			<?php }  ?>
			<div class="row">
				<!-- left column -->
				<div class="col-md-6">
					<!-- general form elements -->
					<div class="box box-primary">
						<div class="box-body">
							<?php
                                echo Former::horizontal_open();
                                echo Former::hidden('userId')->value($user->userId);
                                echo Former::hidden('profileId')->value($user->profileId);
                                 echo Former::xlarge_text('firstName')
                                ->class('form-control')
                                ->label($translationTitles['First name'])
                                ->value($user->firstName)
                                ->required();

                                echo Former::xlarge_text('lastName')
                                ->class('form-control')
                                ->label($translationTitles['Last name'])
                                ->value($user->firstName)
                                ->required();

                                echo Former::select('levelId')
                                ->options($levelsList)
                                ->value($user->levelId)
                                ->class('form-control')
                                ->required();

                                echo Former::xlarge_text('userName')
                                ->class('form-control')
                                ->label($translationTitles['User Name'])
                                ->value($user->userName)
                                ->readonly();

                                echo Former::actions()
                                ->large_primary_submit('Save')
                                ->large_inverse_reset('Reset');

                                /**
                                 * @SuppressWarnings checkAliases
                                 */
                                echo Former::close();
                            ?>
						</div>
						<!-- /.box-body -->

						<div class="box-footer">

						</div>

					</div>
					<!-- /.box -->
				</div>


				<div class="col-md-6">

					<div class="box box-primary">


						<div class="box-body">
							<?php
                            echo Former::horizontal_open();
                            echo Former::hidden('profileId')->value($user->profileId);

                            echo Former::password('password')
                            ->class('form-control')
                            ->label($translationTitles['Password'])
                            ->required();

                            echo Former::password('password_confirmation')
                            ->class('form-control')
                            ->label($translationTitles['Confirm Password'])
                            ->required();

                            echo Former::actions()
                            ->large_primary_submit('Save')
                            ->large_inverse_reset('Reset');

                            /**
                             * @SuppressWarnings checkAliases
                             */
                            echo Former::close();
                        ?>
						</div>
						<!-- /.box-body -->

						<div class="box-footer">

						</div>

					</div>
				</div>

			</div>

		</div>

	</div>
</div>