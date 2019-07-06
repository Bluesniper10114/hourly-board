<?php
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($translationErrors) && is_array($translationErrors));
assert(isset($xmlInput) && is_string($xmlInput));
$errorTimeout = $translationErrors ['Timeout'];
?>
<link
    rel="stylesheet" href="<?= SITE_URL ?>management/assets/switch_radio.css">

<script src="<?=SITE_URL?>management/assets/js/rights.js">
</script>
<div class="content">
    <div class="row">
        <div class="col-md-8"> 

            <table id="tblRights" class="lined_table">
                <thead>
                    <tr>
                        <th>
                            <?= $translationTitles["Role"] ?>
                        </th>
                        <th class="switch_block">
                            <?= $translationTitles["Hourly Sign-OFF"] ?>
                        </th>
                        <th class="switch_block">
                            <?= $translationTitles["Shift Sign-off"] ?>
                        </th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <form method="post">
                <textarea class="hidden" name="xmlOutput" id="xmlNode" style="display:none;">
                <?= $xmlInput ?>
                </textarea>
                <div class="row">
                    <div class="col-sm-8">
                        <div id="notificatiinBlock" style="color:red;">Save work, or we will refresh soon in ( <span id="countNotificatiinBlock">20</span>
                            ) seconds</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right">
                            <input type="submit" class="btn btn-main" value="Save" onclick="return save()" />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
<script>
    $(function() {
        var alertShowMinSeconds = 30;
        xmlRights.init($('#xmlNode').val());
        var html = xmlRights.getHtml();
        $('#tblRights tbody').append(html);
        var handle = setInterval(function() {
            var secs = xmlRights.getTimeoutSeconds();
            if (secs <= alertShowMinSeconds) {
                $('#notificatiinBlock').show();
                $('#countNotificatiinBlock').text(secs);
                if (secs <= 0) {
                    clearInterval(handle);
                    handle = null;
                    window.location = window.location.href;
                }
            } else {
                $('#notificatiinBlock').hide();
            }

        }, 300);
    });

    function save() {
        try {
            if (xmlRights.isTimeout()) {
                alert('<?= $errorTimeout ?>');
                window.location = window.location.href;
                return false;
            } else {
                var xml = xmlRights.getXml();
                $('#xmlNode').val(xml);
                return true;
            }
        } catch (e) {
            console.log(e);
            return false;
        }
    }
ignoreForm = true;
$(function(){
    $('.switch input').each(function(){
        $(this).data({'val':$(this).is(':checked')});
        console.log( $(this).data());
    });
    $('.switch input').change(function(){
        setChangedFlag();
    });
});
function setChangedFlag(){
    var change = false;
    $('form input').each(function(){
        //console.log($(this).data('val') , $(this).val());
        if($(this).data('val') != $(this).is(':checked')){
            change = true;
        }
    });
    if(change){
        formDataChanged = true; 
    } else{
        formDataChanged = false;
    }
}
</script>