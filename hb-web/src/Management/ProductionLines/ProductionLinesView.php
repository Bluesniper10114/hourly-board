<?php
assert(isset($title) && is_string($title));
assert(isset($translationTitles) && is_array($translationTitles));
assert(isset($translationErrors) && is_array($translationErrors));
assert(isset($location) && is_string($location));
assert(isset($xmlInput) && is_string($xmlInput));
?>
<script
    src="<?= SITE_URL ?>management/assets/js/productionLines.js">
</script>

<div class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label><?= $translationTitles["Selectline"] ?></label>
                        <select class="form-control" id="selectLine">
                            <option value=""><?= $translationTitles["EmptyOption"] ?></option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label><?= $translationTitles["Selectcell"] ?></label>
                        <select class="form-control" id="selectCell">
                            <option value=""><?= $translationTitles["EmptyOption"] ?></option>

                        </select>
                    </div>
                </div>
            </div>
            <div class="alert alert-info" id="messageField"></div>
            <table id="tblMain" class="lined_table centered">
                <thead>
                    <tr>
                        <th>
                            <?= $translationTitles["Name"] ?>
                        </th>
                        <th>
                            <?= $translationTitles["Line"] ?>
                        </th>
                        <th>
                            <?= $translationTitles["Cell"] ?>
                        </th>
                        <th>
                            <?= $translationTitles["Eol"] ?>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

            <br/>
                <h3><?= $translationTitles["Details"] ?></h3>
                <table id="tblDetail" class="lined_table centered" style="display:none;">
                    <thead>
                        <tr>
                            <th>
                                <?= $translationTitles["Name"] ?>
                            </th>
                            <th>
                                <?= $translationTitles["StationNumber"] ?>
                            </th>
                            <th>
                                <?= $translationTitles["Routing"] ?>
                            </th>
                            <th>
                                <?= $translationTitles["Capacity"] ?>
                            </th>
                            <th>
                                <?= $translationTitles["Description"] ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <textarea class="hidden" id="xmlNode" style="display:none;">
                <?= $xmlInput ?>
                </textarea>
        </div>
    </div>

</div>
<script>
    var produtionLines
    $(function() {
        var options = {};
        options.xmlInput = $('#xmlNode').val();
        options.location = '<?= $location ?>';
        options.lineSelect = $('#selectLine');
        options.cellSelect = $('#selectCell');
        options.tableMain = $('#tblMain');
        options.tableDetail = $('#tblDetail');
        options.messageField = $('#messageField');
        options.translationErrors = <?= json_encode($translationErrors); ?>;
        options.translationLabels = <?= json_encode($translationTitles); ?>;
        produtionLines = new ProdutionLines(options);
        produtionLines.init();
    });
</script>