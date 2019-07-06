<?php 

assert(isset($translationHeader) && is_array($translationHeader));
assert(isset($linesList) && is_array($linesList));
assert(isset($shiftTypesList) && is_array($shiftTypesList));
?>
<link rel="stylesheet" href="<? echo SITE_URL; ?>management/assets/vendors/datepicker/datepicker3.css"/>
<script src="<? echo SITE_URL; ?>management/assets/vendors/datepicker/bootstrap-datepicker.js"></script>

<script>
 
$(function(){
    $('#selectDate').datepicker({format:"yyyy/mm/dd", endDate: "dateToday"});
    $('#btnSave').click(function(){
         
          
    });
    
});
</script>
<div class="wrapp">
<div class="error-msg-wrapp">
<?php
$debug = true;
if (isset($errorHtml)) {
    echo $errorHtml;
};
?>
</div>
<div class="info-msg-wrapp">
</div>
<form method="post" action="<?= SITE_URL ?>management/reports/historical-billboard/load">
    <div class="form-group">
        <label for="selectDate" class="control-label"><?= $translationHeader['SelectDate'] ?><sup>*</sup></label>
        <input required class="form-control" name="selectDate" id="selectDate" type="text" />
        </select>
    </div>
    <div class="form-group">
        <label for="line" class="control-label"><?= $translationHeader['SelectLine'] ?><sup>*</sup></label>
        <select required class="form-control" name="line" id="line">
        <option value=""><?= $translationHeader['Select'] ?></option>
        <?php foreach ($linesList as $id => $title) { ?>
        <option value="<?= $id ?>"><?= $title ?></option>
        <?php

    }
    ?>
        </select>
    </div>
    <div class="form-group">
        <label for="shiftType" class="control-label"><?= $translationHeader['SelectShiftType'] ?><sup>*</sup></label>
        <select required class="form-control" name="shiftType" id="shiftType">
        <option value=""><?= $translationHeader['Select'] ?></option>
        <?php foreach ($shiftTypesList as $id => $title) { ?>
        <option value="<?= $id ?>"><?= $title ?></option>
        <?php

    }
    ?>
        </select>
    </div>
    <div class="form-group">
    <div class="col-lg-offset-2 col-sm-offset-4 col-lg-10 col-sm-8">
    <input class="btn-large btn-primary btn" id="btnSave" value="<?= $translationHeader['ViewReport'] ?>" type="submit">
    </div>
    </div>
</form>
</div>