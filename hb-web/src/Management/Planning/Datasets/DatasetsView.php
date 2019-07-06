<?php
assert(isset($title) && is_string($title));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($translationErrors) && is_array($translationErrors));
assert(isset($availablePlanningTypes) && is_array($availablePlanningTypes));
assert(isset($xmlOptions) && is_array($xmlOptions));
assert(isset($xmlInput) && is_string($xmlInput));
$xmlOptions = json_encode($xmlOptions);
?>
<link
    rel="stylesheet" href="<?= SITE_URL ?>management/assets/vendors/select2/css/select2.min.css">
<script src="<?= SITE_URL ?>management/assets/vendors/select2/js/select2.min.js">
</script>
<script src="<?= SITE_URL ?>management/assets/js/planningDatasets.js">
</script>
<div class="error-msg-wrapp">

</div>
<div class="content">
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <label>Date</label>
                <select id="dateSelect" class="form-control" multiple>
                </select>
                <span class="hint"><?= $translationTitles['HintDate'] ?></span>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Line</label>
                <select class="form-control" id="lineSelect">
                </select>
                <span class="hint"><?= $translationTitles['HintLine'] ?></span>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Shift</label>
                <select class="form-control" id="shiftSelect" multiple>
                </select>
                <span class="hint"><?= $translationTitles['HintShift'] ?></span>
            </div>
        </div>
        <div class="col-sm-3">
            <br/>
                <a class="btn btn-addnew" href="#" id="add_new"><?= $translationTitles['AddNewDataset'] ?></a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">


            <div class="alert alert-info" id="messageField"></div>

            <table id="tblDatasets" class="datasets_table">
                <thead>
                    <tr>
                        <th rowspan="2">
                            <?= $translationTitles["LINE"] ?>
                        </th>
                        <th rowspan="2">
                            <?= $translationTitles["DATE"] ?>
                        </th>
                        <th rowspan="2">
                            <?= $translationTitles["Type"] ?>
                        </th>
                        <th rowspan="2">
                            <?= $translationTitles["Billboard"] ?>
                        </th>
                        <th rowspan="2">
                            <?= $translationTitles["SHIFTS"] ?>
                        </th>
                        <th colspan="8">
                            <?= $translationTitles["HOURLY"] ?>
                        </th>
                        <th rowspan="2">
                            <?= $translationTitles["TOTAL"] ?>
                        </th>
                        <th rowspan="2" class="no_border"></th>
                    </tr>
                    <tr>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>

                    </tr>
                </thead>
                <tbody>
                    <tr><td colspnan="14"><?= $translationErrors["EmptyRow"] ?></td></tr> 
                </tbody>
            </table>
            <form method="post" id="frmData">
                <textarea class="hidden" name="xmlOutput" id="xmlNode" style="display:none;">
                            <?= $xmlInput ?>
                            </textarea>

            </form>


        </div>
    </div>

</div>
<script>
    var xmlDatasets;
    $(function() {
        xmlDatasets = new XmlDatasets();
        var options = <?= $xmlOptions ?>;
        options.translationTitles = <?= json_encode($translationTitles) ?>;
        options.routes = {'Day': '<?= SITE_URL ?>management/planning/by-day', 
           'PartNumber': '<?= SITE_URL ?>management/planning/by-partnumber',
           'billboardUpdate': '<?= SITE_URL ?>management/planning/datasets/billboard-update',
           };

        options.lineSelect = $('#lineSelect');
        options.dateSelect = $('#dateSelect');
        options.shiftSelect = $('#shiftSelect');
        options.messageField = $('#messageField');
        options.tbody = $('#tblDatasets tbody');
        xmlDatasets.init($('#xmlNode').val(), options);
        $('#add_new').click(function(){
            $('#addDialog').modal('toggle');
            return false;
        })
        
    });
</script>


<div id="addDialog" class="modal" data-width="460">
        <div class="modal-header">
       
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-close"></i></button>
        </div>
         
            <div class="modal-body">
            <h4 style="text-align:center;"><?= $translationTitles["AddNewPopupTitle"] ?></h4>
                <?php 
                foreach ($availablePlanningTypes as $type) {
                    $text = $type->title;
                    $link = SITE_URL . "management/" . $type->path;
                ?>
                    <p>
                        <a href="<?= $link; ?>" class="btn btn-addLarge"><?= $text ?></a>
                    </p>
                <?php
                } 
                ?>                                           
                                   
            </div>
            <div class="modal-footer">
                
            </div>
        
    </div>    