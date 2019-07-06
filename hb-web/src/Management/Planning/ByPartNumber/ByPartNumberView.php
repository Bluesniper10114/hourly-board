<?php
assert(isset($searchLine) && is_string($searchLine));
assert(isset($searchDate) && is_string($searchDate));
assert(isset($xmlData) && is_string($xmlData));
assert(isset($translationHeader) && is_array($translationHeader));
assert(isset($translationError) && is_array($translationError));
assert(isset($linesList) && is_array($linesList));
$jsOptions =
    [
    'urlRefresh' => SITE_URL . 'management/planning/by-partnumber/refresh-routing',
    'translationHeader' => $translationHeader,
    'urlFile' => SITE_URL . 'management/planning/by-partnumber/upload',
    'urlSaveRedirect' => SITE_URL . 'management/planning/datasets',
    'translationError' => $translationError,
    'loadUrl' => SITE_URL . 'management/planning/by-partnumber/load',
];
?>
<script src="<?= SITE_URL ?>management/assets/js/planningByPartNumber.js">
</script>
<script src="<?= SITE_URL ?>management/assets/vendors/php_date.js">
</script>
<link rel="stylesheet" href="<?= SITE_URL; ?>management/assets/vendors/datepicker/datepicker3.css">
<script src="<?= SITE_URL; ?>management/assets/vendors/datepicker/bootstrap-datepicker.js"></script>
<script>
var xmlPlanningByPart;
var options = <?= json_encode($jsOptions) ?>;


$(function(){
    try{
    xmlPlanningByPart = new XmlPlanningByPart();
    xmlPlanningByPart.init( $('#xmlTargets').val(), options);
    }
    catch(e){
        console.log(e);
    }
    
    $('#filterDate').datepicker({format:"yyyy/mm/dd"});

});
</script>
<div class="wrapp">
<div class="error-msg-wrapp">
<?php
if (isset($errorHtml)) {
    echo $errorHtml;
}

?>
</div>
<div class="info-msg-wrapp">
</div>
<form method="post" action="<?= SITE_URL ?>management/planning/by-partnumber/save">
<textarea class="hidden" name="xmlOutput" id="xmlTargets" style="display:none;">
<?= $xmlData ?>
</textarea>
<div class="week_headings">
     <div class="row">
         <div class="col-sm-9">
            <div class="row">
                <div class="col-sm-4">
                    <div class="line_headings"><label>Line:</label>
                        <select class="form-control form-control-sm" id="filterLine">
                        <option value="">Select</option>    
                        <?php foreach ($linesList as $id => $name) { ?>
                            <option value="<?= $id ?>" <?= intval($searchLine) === intval($id) ? 'selected' : '' ?>><?= $name ?></option>
                            <?php 
                        } ?>
                        </select>
                        <p class="errorSelect">
                            <?= $translationError['SelectLine'] ?>
                        </p>
                    </div>
                </div>
                <div class="col-sm-8">
                    <p class="cells_headings"><label>Cells:</label><span></span></p>
                </div>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="heading_date pull-right">
                <input type="text" class="form-control form-control-sm" placeholder="YYYY/MM/DD" id="filterDate" value="<?= $searchDate ?>"/>
                <p class="errorSelect">
                    <?= $translationError['SelectDate'] ?>
                </p>
            </div>
         </div>
     </div>
 </div>

    <table class="bordered_table planning_part">
        <thead>
         <tr>
             <th><?= $translationHeader["Pn"] ?></th>
             <th><?= $translationHeader["Initialquantity"] ?></th>
             <th><?= $translationHeader["RoutingASSYPN"] ?></th>
             <th><?= $translationHeader["Total"] ?></th>
             <th><?= $translationHeader["A"] ?></th>
             <th><?= $translationHeader["B"] ?></th>
             <th><?= $translationHeader["C"] ?></th>
             <th></th>
        </thead>
        <tbody>
        <tr class="emptyRow"><td colspan="8"><?= $translationError['EmptyData'] ?></td></tr>
        </tbody>
    </table>
    <div>
    <div class="row">
         <div class="col-sm-6">
            <a class="btn btn-copyday btn_import" href="#" disabled><?= $translationHeader["IMPORTEXCEL"] ?></a>
            <input type="file" id="xsl_file" />
         </div>
         <div class="col-sm-6">
         <a class="btn btn-copyday btn_add" href="#"><?= $translationHeader["Add"] ?></a>
         <button class="btn btn-copyday btn_distribute" ><?= $translationHeader["DISTRIBUTETOTAL"] ?></button>
         <button class="btn btn-copyday btn_validate" ><?= $translationHeader["Validate"] ?></button>
         <button class="btn btn-copyday btn_export" ><?= $translationHeader["EXPORT"] ?></button>
         </div>
     </div>

    </div>
    <div class="row">
        <div class="col-sm-4" id="errorBlock1">

        </div>
        <div class="col-sm-4" id="errorBlock2">

        </div>
        <div class="col-sm-4" id="errorBlock3">

        </div>
    </div>
</form>
</div>