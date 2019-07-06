<?php
assert(isset($title) && is_string($title));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($translationErrors) && is_array($translationErrors));
assert(isset($breaksOptions) && is_array($breaksOptions));
assert(isset($xmlNextWeek) && is_string($xmlNextWeek));
assert(isset($xmlCurrentWeek) && is_string($xmlCurrentWeek));

?>
<link
    rel="stylesheet" href="<?=SITE_URL?>management/assets/vendors/wickedpicker/wickedpicker.min.css">
<script src="<?=SITE_URL?>management/assets/vendors/wickedpicker/wickedpicker.min.js">
</script>

<script src="<?=SITE_URL?>management/assets/js/breaks.js">
</script>

<div class="content">
    <div class="row">
        <div class="col-md-8">

            <div class="tab1">
                <ul class="nav nav-tabs">
                    <li><a class="active show" data-toggle="tab" href="#sectionB"><?=$translationTitles["Tab2Title"]?></a></li>
                    <li><a data-toggle="tab" href="#sectionA"><?=$translationTitles["Tab1Title"]?></a></li>
                </ul>
                <div class="tab-content">
                    <div id="sectionA" class="tab-pane fade">
                    <br/><p><?=$translationTitles["Tab1Text"]?></p>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="subtotalfields">

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pull-right"><strong><?=$translationTitles["Tab1BreaksForShiftText"]?> </strong><span id="shift_starting_from"></span>
                                    <br/>
                                        <br/>
                                </div>
                            </div>
                        </div>
                        <table id="tblBreaks" class="lined_table centered">
                            <thead>
                                <tr>
                                    <th>
                                        <?=$translationTitles["SHIFT"]?>
                                    </th>
                                    <th>
                                        <?=$translationTitles["Interval"]?>
                                    </th>
                                    <th>
                                        <?=$translationTitles["BreakStart"]?>
                                    </th>
                                    <th>
                                        <?=$translationTitles["BreakEnd"]?>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <form method="post" id="frmData">
                            <textarea class="hidden" name="xmlOutput" id="xmlNode" style="display:none;">
                            <?=$xmlNextWeek?>
                            </textarea>
                            <textarea class="hidden" id="xmlCurrentNode" style="display:none;">
                            <?=$xmlCurrentWeek?>
                            </textarea>
                            <button class="btn btn-save">Save</button>
                        </form>
                    </div>
                    <!-- /.tab-pane -->
                    <div id="sectionB" class="tab-pane fade in active show">
                        <br/><p><?=$translationTitles["Tab2Text"]?></p>
                        <table id="tblBreaksCurrent" class="lined_table centered">
                            <thead>
                                <tr>
                                    <th>
                                        <?=$translationTitles["SHIFT"]?>
                                    </th>
                                    <th>
                                        <?=$translationTitles["Interval"]?>
                                    </th>
                                    <th>
                                        <?=$translationTitles["BreakStart"]?>
                                    </th>
                                    <th>
                                        <?=$translationTitles["BreakEnd"]?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>

        </div>
    </div>

</div>
<script>
    var xmlBreaks, xmlCurrentBreaks;
    ignoreForm = true;
    var options = {
        now: "12:35", //hh:mm 24 hour format only, defaults to current time
        twentyFour: true, //Display 24 hour format, defaults to false
        upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
        downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
        close: 'wickedpicker__close', //The close class selector to use, for custom CSS
        hoverState: 'hover-state', //The hover state class to use, for custom CSS
        title: 'Timepicker', //The Wickedpicker's title,
        showSeconds: false, //Whether or not to show seconds,
        timeSeparator: ':', // The string to put in between hours and minutes (and seconds)
        secondsInterval: 1, //Change interval for seconds, defaults to 1,
        minutesInterval: 5, //Change interval for minutes, defaults to 1
        beforeShow: null, //A function to be called before the Wickedpicker is shown
        afterShow: null, //A function to be called after the Wickedpicker is closed/hidden
        show: null, //A function to be called when the Wickedpicker is shown
        clearable: false, //Make the picker's input clearable (has clickable "x")
    };

    $(function() {
        $('.nav-tabs a.active').click();
        xmlBreaks = new XmlBreaks();
        xmlCurrentBreaks = new XmlBreaks();
        var breaksOptions = <?=json_encode($breaksOptions)?>;
        var alertShowMinSeconds = 60;
        xmlBreaks.init($('#xmlNode').val(), breaksOptions);
        xmlCurrentBreaks.init($('#xmlCurrentNode').val(), breaksOptions);
        var html = xmlBreaks.getHtml();
        var currentBreaksHtml = xmlCurrentBreaks.getHtml(1);
        $('#tblBreaks tbody').append(html);
        $('#tblBreaksCurrent tbody').append(currentBreaksHtml);
        $('#shift_starting_from').text(xmlBreaks.startingWith);
        $('.btn-edit').click(function() {
            var breakRow = $(this).closest('tr');
            breakRow.find('.timeBox').each(function() {
                var t = $(this).data('value');
                options.now = t;
                var o = $(this).find('input').wickedpicker(options);
            });
            xmlBreaks.markEditNode(breakRow);
            saveBreak = true;
            formDataChanged = true;
        });
        $('.btn-save').click(function() {

            var error = [];
            if (xmlBreaks.validate(error)) {
                xmlBreaks.saveBreak();
                var xml = xmlBreaks.getXml();
                $('#xmlNode').val(xml);
                var data = $('#frmData').serialize();
                $(this).attr({
                    'disabled': 'disabled'
                });
                var t = this;
                $.post('<?=SITE_URL?>' + "management/planning/breaks/save", data, function(res) {
                    res = JSON.parse(res);
                    if (res.success) {
                        xmlBreaks.markSaved();
                        window.location.reload();
                    } else {
                        alert(res.error);
                    }
                    $(t).removeAttr('disabled');
                });

            } else {
                alert(error.message);
            }
            return false;
        });
        $('input').on('focus', function() {
            if ($(this).attr('aria-showingpicker') == "false") {
                $('.wickedpicker__close').click();
            }
        });
    });
</script>