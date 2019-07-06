<?php 
assert(isset($searchDate) && is_string($searchDate));
assert(isset($debug) && is_bool($debug));
assert(isset($xmlData) && is_array($xmlData));
assert(isset($translationHeader) && is_array($translationHeader));
assert(isset($translationError) && is_array($translationError));
assert(isset($translationMonths) && is_array($translationMonths));
assert(isset($translationDays) && is_array($translationDays));
$xmlWeeks = isset($xmlData['weeksXML']) ? $xmlData['weeksXML'] : '';
$xmlTargets = isset($xmlData['targetsXML']) ? $xmlData['targetsXML'] : '';
?>
<script src="<?= SITE_URL ?>management/assets/js/planningByDay.js">
</script>
<script src="<?= SITE_URL ?>management/assets/vendors/php_date.js">
</script>
<textarea class="hidden" name="xmlOutput" id="xmlWeeks" style="display:none;">
<?= $xmlWeeks ?>
</textarea>

<script>
var xmlDailyPlanning;
var firstLoad = null;
var options = <?= json_encode([
                    'translationMonths' => $translationMonths,
                    'translationDays' => $translationDays,
                    'translationHeader' => $translationHeader,
                    'translationError' => $translationError
                ]); ?>
 
$(function(){
    xmlDailyPlanning = new XmlDailyPlanning();
    xmlDailyPlanning.init($('#xmlWeeks').val(), $('#xmlTargets').val(), options);
    var urlSearch = '<?= SITE_URL ?>management/planning/by-day/search';
    var urlIndex = '<?= SITE_URL ?>management/planning/by-day';
    <?php
    if (isset($search)) { ?>
    var tags = '<?= $search ?>';
    var date = <?= !empty($searchDate) ? json_encode($searchDate) : 'null' ?>;
    $('.search-input').val(tags, date);
    search(tags, date);
    <?php

}
?>
    $('.search-input').keypress(function (e) {
        if (e.which == 13) {    
            var tags = $(this).val();
            search(tags);
            return false;
        }
    });    
    
    $('#btnSave').click(function(){
        if(xmlDailyPlanning.isValidTargets()){
            $('#xmlTargets').val( xmlDailyPlanning.getXml() );
            save(this.form);
        }
        return false;
    });
    $('.search-container button').on('click', function(event) {
        var tags = $('.search-container .search-input').val();
        search(tags);
        return false;
    });
    function search(tags, date){
        if(firstLoad && !confirm(options.translationError.RefreshConfirm)){
            return false;
        }
        firstLoad = 1;
        jQuery.ajax({
            type: 'POST',
            url: urlSearch,
            dataType: 'json', 
            data: 'tags='+tags,
            success: function(result) {
                xmlDailyPlanning.extractTargets(result.xmlData.targetsXML, date);
            }
        });
    }
    function save(form){
        var urlSave = $(form).attr('action');
        jQuery.ajax({
            type: 'POST',
            url: urlSave,
            dataType: 'json', 
            data: $(form).serialize(),
            success: function(result) {
                if(!result.success){
                    $('.error-msg-wrapp').html(result.error);
                    $(window).scrollTop(0);
                } else{
                    window.location.href = urlIndex + "?success=1&s="+$('.search-input').val()+"<?= $debug ? '&debug=1' : '' ?>";
                }
            }
        });
    }
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
<form method="post" action="<?= SITE_URL ?>management/planning/by-day/save">
<textarea class="hidden" name="xmlOutput" id="xmlTargets" style="display:none;">
<?= $xmlTargets ?>
</textarea>
<div class="week_headings">
     <div class="row">
         <?php $cls = $debug ? 'col-sm-4' : 'col-sm-6'; ?>
         <div class="<?= $cls ?>">
            <p class="week_heading week_heading_start">Week #23 ( 4th June - 10th June )</p>
         </div>
         <div class="<?= $cls ?>">
            <p class="week_heading week_heading_end">Week #24 ( 11th June - 17th June )</p>
            <a class="pull-right white_link" href="?debug=1">1</a>
         </div>
        
         
         <?php if ($debug) { ?>
         <div class="<?= $cls ?>">
            <p class="debug_timestamp"></p>
         </div>
         <?php 
    } ?>

         </div>
     </div>
 </div>
    <div class="week_headings week_day_names">
        <ul class="first_list">
            <li><p>&nbsp;</p><label><?= $translationDays["Mon"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Tue"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Wed"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Thu"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Fri"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Sat"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Sun"] ?>.</label></li>
        </ul>
        <ul class="second_list">
            <li><p>&nbsp;</p><label><?= $translationDays["Mon"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Tue"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Wed"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Thu"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Fri"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Sat"] ?>.</label></li>
            <li><p>&nbsp;</p><label><?= $translationDays["Sun"] ?>.</label></li>
        </ul>
        <div class="clearfix"></div>
        <div class="scroll-select" style="display:none;">&nbsp;</div>
    </div>
    <table class="cal_days">
        <thead>
        <tr class="search_row">
            <th colspan="4">
                <div class="search-container">
                    <label class ="search-label"><?= $translationHeader["Searchbytag"] ?></label>
                    <input class ="search-input" placeholder="<?= $translationHeader["SearchPlaceholder"] ?>" type="text" name="tags" />
                    <button type="button"><i class="fa fa-search"></i></button>
                </div>
            </th>
            <th colspan="3">
                <button class="btn btn-copyday" id="copy1"><?= $translationHeader["Copyday"] ?></button>
            </th>
            <th colspan="3">
                <button class="btn btn-copyday" id="copy2"><?= $translationHeader["Copyday"] ?></button>
            </th>
            <th colspan="3">
                <button class="btn btn-copyday" id="copy3"><?= $translationHeader["Copyday"] ?></button>
            </th>
        </tr>
    
        </thead>
        <tbody>
        
        </tbody>
    </table>
    <div>
        <div class="row">
            <div class="col-sm-6">

            </div>
            <div class="col-sm-6">
                <div class="errorTarget">

                </div>
            </div>
        </div>
        <div class="buttons_set pull-right">
        <a href="" onclick="return confirm('<?= $translationError["CancelConfirm"] ?>')" class="btn btn-cancel "><?= $translationHeader["Cancel"] ?></a>
        <button disabled id="btnSave" class="btn btn-save "><?= $translationHeader["Save"] ?></button>
</div>
    </div>
</form>
</div>