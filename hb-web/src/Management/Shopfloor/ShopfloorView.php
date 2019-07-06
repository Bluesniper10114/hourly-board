<?php 
assert(isset($readOnly) && is_bool($readOnly));
assert(isset($title) && is_string($title));
assert(isset($xml) && is_string($xml));
assert(isset($monitorId) && is_int($monitorId));
assert(isset($translationHeader) && is_array($translationHeader));
assert(isset($translationBody) && is_array($translationBody));
assert(isset($translationPopup) && is_array($translationPopup));
assert(isset($commentsList) && is_array($commentsList));
assert(isset($escalatedList) && is_array($escalatedList));
?>
<script src="<?= SITE_URL ?>management/assets/js/shopfloor.js">
</script>
<textarea class="hidden" name="xmlOutput" id="xmlNode" style="display:none;">
<?= $xml ?>
</textarea>
<script>
var xmlShopfloor;
var translations = <?= json_encode($translationBody) ?>;
var downtimeUrl = '<?= SITE_URL . 'management/shopfloor/downtime-minutes' ?>';
var isReport = <?= $readOnly ? 'true' : 'false' ?>;
$(function(){
    setTimeout(function(){
        window.location.href = window.location;
    }, 5*60*1000);
    xmlShopfloor = new XmlShopfloor();
    xmlShopfloor.init($('#xmlNode').val(), {translationBody: translations, downtimeUrl: downtimeUrl, isReport: isReport});
    if(!isReport){
        $('.supervisor').keypress(function (e) {
            if (e.which == 13) {    
                var id = $(this).closest('tr').data('hourly-id');
                $('#hourlyId').val(id);
                $('#operatorBarcode').val(this.value);
                this.form.submit();
            }
        });    

        $('#lastshift').keypress(function (e) {
            if (e.which == 13) {    
                var id = xmlShopfloor.shiftLogId;
                $('#shiftLogSignOffID').val(id);
                $('#soperatorBarcode').val(this.value);
                this.form.submit();
            }
        });  
    }
      
    var getCommentText = function(text){
        if(!text){
            text = '';
        }
        return text  +'<a href="#" class="commentLink" title="Edit"><i class="fa fa-edit"></i></a>';
    }    
    var getEscalatedText = function(text){
        if(!text){
            text = '';
        }
        return text  +'<a href="#" class="escalatedLink" title="Edit"><i class="fa fa-edit"></i></a>';
    }
    var trim = function (str, length) {
        return str.length > length ? str.substring(0, length) + "..." : str;
    }
    $('#savecomment').on('click', function(event) {
        jQuery.ajax({
            type: 'POST',
            url: $('#frmComments').attr('action'),
            dataType: 'json', 
            data: $('#frmComments').serialize(),
            success: function(result) {
                    if (result.errorMessage != null)
                        alert(result.errorMessage)
                    else        
                    {                            
                        var comment = getCommentText( result.comment );
                        $('#rcomments'+result.id).html(comment);
                        xmlShopfloor.registerCommentEvents();
                    }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error when saving comment: ' + textStatus + ' ' + errorThrown);
            }
        });
        $('#commentsDialog').modal('toggle');
        return false;
    });

    $('#saveescalated').on('click', function(event) {
        jQuery.ajax({type: 'POST', url: $('#frmEscalated').attr('action'),
            dataType: 'json', data: $('#frmEscalated').serialize(),
            success: function(result) {
                    console.log( '#rescalated'+result.id, result.escalated);
                    if (result.errorMessage != null)
                        alert(result.errorMessage)
                    else 
                    {
                        var escalated = getEscalatedText(result.escalated);
                        $('#rescalated'+result.id).html(escalated);
                        xmlShopfloor.registerEscalatedEvents();
                    }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' ' + errorThrown);
            }
        });
        $('#escalatedDialog').modal('toggle');
        return false;
    });            
});
</script>
<div class="wrapp">
<?php 
if (isset($errorHtml)) {
    echo $errorHtml;
};
?>
<form method="post" action="<?= SITE_URL ?>management/shopfloor/signoffshift">
<input type="hidden" value="<?= $monitorId ?>" name="monitorId" />
<input type="hidden" value="" name="operatorBarcode" id="soperatorBarcode" />
<input type="hidden" value="" name="shiftLogSignOffID" id="shiftLogSignOffID" />
<table class="table shopheader" style="">
        <tr>
            <td width="10%" align="left"><?= $translationHeader["Date"] ?>:</td>
            <td width="35%" align="center" class="labelTopBlue labelDate"></td>
            <td width="%" align="left"><?= $translationHeader["DeliveryTime"] ?>:</td>
            <td width="8%" align="center" class="labelTopBlue labelDT" colspan="2"></td>
        </tr>
        <tr>
            <td align="left"><?= $translationHeader["Shift"] ?>:</td>
            <td align="center" class="labelTopBlue labelShift"></td>
            <td align="left"><?= $translationHeader["MaxHourProduction"] ?>:</td>
            <td align="center" class="labelTopBlue labelMHP" colspan="2"></td>    
        </tr>
        <tr>
            <td align="left"><?= $translationHeader["Line"] ?>:</td>
            <td align="center" class="labelTopBlue labelLine" ></td>

            <td  colspan="3" align="left"><?= $translationHeader["CloseShiftHead"] ?></td>
        </tr>
        <tr>
            <td align="left"><?= $translationHeader["Location"] ?>:</td>
            <td align="center" class="labelTopBlue labelLoc"></td>
            <td colspan="2" class="bgOrange text-right" style=""><?= $translationHeader["CloseShiftText"] ?>:</td>
        
            <td class="bgOrange" style="text-align:center;"><input type="text" name="lastshift" id="lastshift" value=""/></td>    
        </tr>
</table>
</form>
<form method="post" action="<?= SITE_URL ?>management/shopfloor/signoffhour">
<input type="hidden" value="<?= $monitorId ?>" name="monitorId" />
<input type="hidden" value="" name="operatorBarcode" id="operatorBarcode" />
<input type="hidden" value="" name="hourlyId" id="hourlyId" />
<table class="table table-bordered shopfloor" style="">
    <thead>
        <tr>
            <th width="100" ><?= $translationBody["HourInterval"] ?></th>
            <th ><?= $translationBody["Target"] ?></th>
            <th ><?= $translationBody["Achieved"] ?></th>
            <th ><?= $translationBody["CumulativeTarget"] ?></th>
            <th ><?= $translationBody["CumulativeAchieved"] ?></th>
            <th ><?= $translationBody["Defects"] ?></th>
            <th ><?= $translationBody["Downtime"] ?></th>
            <th width="250" ><?= $translationBody["Comments"] ?></th>
            <th width="200" ><?= $translationBody["Escalated"] ?></th>
            <th width="100"><?= $translationBody["ApprovalSupervisor"] ?></th>
        </tr>
    </thead>
    <tbody>
    
    </tbody>
    <tfoot>
        <tr>
            <td class="hour">Total</td>
            <td class="labelStrongBlack targetTotal"></td>
            <td class="labelStrongBlack achievedTotal"></td> <!-- @todo: Show color-coded achieved vs total / Standard is: less => red / equal = black / more => green -->
            <td></td>
            <td></td>
            <td class="labelStrongBlack defectsTotal"></td>
            <td class="labelStrongBlack downtimeTotal"></td>
            <td class="text"></td>
            <td class="text"></td>
            <td class="text"></td>
        </tr>
    </tfoot>
</table>
</form>
    <div id="commentsDialog" class="modal" data-width="460">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        </div>
        <form method="post" action="<?= SITE_URL ?>management/shopfloor/save-comment" id="frmComments">
            <div class="modal-body">
                <label><?= $translationPopup["Comments"] ?></label>
                <select name="comment[]" id="comment" class="filter form-control" multiple style="width:100%">
                <?php foreach ($commentsList as $id => $text) { ?>
                    <option value="<?= $id ?>"><?= $text ?></option>
                <?php 
            } ?>
                </select>                                                                                    
            </div>
            <div class="modal-footer">
                <div class="note"><?= $translationPopup["Note"] ?></div>
                <input type="hidden" name="hourlyId" id="hourlyIdc" value=""/>
                <input type="submit" class="btn blue" id="savecomment" value="<?= $translationPopup["Save"] ?>"/>
                <button type="button" data-dismiss="modal" class="btn btn-default"><?= $translationPopup["Reset"] ?></button>
            </div>
        </form>
    </div>
    

    <div id="escalatedDialog" class="modal" data-width="460">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        </div>
        <form method="post" action="<?= SITE_URL ?>management/shopfloor/save-escalated" id="frmEscalated">
            <div class="modal-body">
            <label><?= $translationPopup["Escalated"] ?></label>
                <select name="escalated[]" id="escalated" class="filter form-control" multiple style="width:100%">
                    <?php foreach ($escalatedList as $id => $text) { ?>
                    <option value="<?= $id ?>"><?= $text ?></option>
                    <?php 
                } ?>
                </select>                                                                        
            </div>
            <div class="modal-footer">
                <div class="note"><?= $translationPopup["Note"] ?></div>
                <input type="hidden" name="hourlyId" id="hourlyIde" value=""/>
                <input type="submit" class="btn blue" id="saveescalated" value="<?= $translationPopup["Save"] ?>"/>
                <button type="button" data-dismiss="modal" class="btn btn-default"><?= $translationPopup["Reset"] ?></button>
            </div>
        </form>
    </div>    
</div>