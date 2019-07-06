<?php
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($translationForm) && is_array($translationForm));
assert(isset($translationMessages) && is_array($translationMessages));
assert(isset($usersList) && is_array($usersList));
assert(isset($paginationView) && is_string($paginationView));

?>
<div class="content">
    <div class="row">
        <div class="col-md-8">
            <?=$paginationView;?>
        </div>
        <div class="col-lg-4">
            <form action="">
                <div class="input-group">
                    <input type="text" class="form-control required" id="SearchFilter" name="SearchFilter" value="<?=isset($_GET['SearchFilter']) ? $_GET['SearchFilter'] : '';?>" placeholder="<?=$translationForm['Search for']?>">
                    <span class="input-group-btn">
                        <button class="btn btn-success" type="submit"><?=$translationForm['Filter']?></button>
                    </span>
                </div><!-- /input-group -->
            </form>
        </div><!-- /.col-lg-4 -->
    </div><!-- /.row -->


    <div class="box">
        <div class="box-body no-padding">
            <table class="table table-striped table-hover">
                <tr>
                    <th style="width:5%"><?=$translationTitles['Id']?></th>
                    <th style="width:20%"><?=$translationTitles['User Name']?></th>
                    <th style="width:20%"><?=$translationTitles['First name']?></th>
                    <th style="width:20%"><?=$translationTitles['Last name']?></th>
                    <th style="width:12%"><?=$translationTitles['level']?></th>
                    <th style="width:12%"><?=$translationTitles['Operations']?></th>
                </tr>
                <?php foreach ($usersList as $key => $user) {
    ?>
                        <tr>
                            <td><?=$user->userId?></td>
                            <td><?=$user->userName?></td>
                            <td><?=$user->firstName?></td>
                            <td><?=$user->lastName?></td>
                            <td><?=$user->level?></td>
                            <td>
                                <a class="btn btn-primary" href="<?=SITE_URL?>management/users/edit/<?=$user->userId?>" title="Edit"><i class="fa fa-edit"></i></a>
                                <a class="btn btn-danger" onclick="return confirm('<?=$translationMessages['Confirm']?>')" href="<?=SITE_URL?>management/users/delete/<?=$user->profileId?>" title="Remove"><i class="fa fa-times"></i></a>
                            </td>
                        </tr>
                <?php
}
?>
            </table>
        </div> <!-- end of box-body no-padding -->
    </div> <!-- end of box -->

</div> <!-- end of content -->
