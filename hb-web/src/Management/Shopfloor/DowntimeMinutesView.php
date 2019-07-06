<?php 
assert(isset($title) && is_string($title));
assert(isset($xml) && is_string($xml));
assert(isset($hourlyId));
assert(isset($translationDowntime) && is_array($translationDowntime));
assert(isset($downtimeDictionary) && is_array($downtimeDictionary));
?>
<script src="<?= SITE_URL ?>management/assets/js/shopfloorDowntime.js">
</script>

<script>
var xmlShopfloorDowntime;
var translation = <?= json_encode($translationDowntime) ?>;
var downtimeDictionary = <?= json_encode($downtimeDictionary) ?>;
var isReport = <?= isset($readOnly) ? 'true' : 'false' ?>;
$(function(){
    xmlShopfloorDowntime = new XmlShopfloorDowntime();
    xmlShopfloorDowntime.init($('#xmlNode').val(), {translation: translation, downtimeDictionary: downtimeDictionary, isReport:isReport});
    $(window).click();
});
window.onbeforeunload = confirmExit;
  function confirmExit()
  { 
      if(xmlShopfloorDowntime.isAnyDataChange()){
        return  translation.ConfirmExit ;
      }
   
  }
</script>
<div class="wrapp">
<h2 class="mtitle"><?= $title ?></h2>
<?php if (isset($errorHtml)) {
    echo $errorHtml;
} ?>
<form method="post" action="<?= SITE_URL ?>management/shopfloor/savedowntime">
<input type="hidden" value="<?= $hourlyId ?>" name="hourlyId" />
<textarea class="hidden" name="xmlOutput" id="xmlNode" style="display:none;">
<?= $xml ?>
</textarea>
<div class="pull-right" id="forDateField"></div>
<table class="table downtimemins" style="">
    <thead>
        <tr>
            <th ><?= $translationDowntime["TimeInterval"] ?></th>
            <th ><?= $translationDowntime["Machine"] ?></th>
            <th ><?= $translationDowntime["Downtime(min)"] ?></th>
            <th ><?= $translationDowntime["Reasonminutes"] ?></th>
            <th ><?= $translationDowntime["Action"] ?></th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<button class="btn btn-save">SAVE</button>
</form>  

</div>