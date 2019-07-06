"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var XmlPlanningByPart = function () {
    function XmlPlanningByPart() {
        _classCallCheck(this, XmlPlanningByPart);

        this.parser = null;
        this.xmlDoc = null;
        this.translationError = null;
        this.translationHeader = null;
        this.shifts = null;
        this.currentDayNo = null;
        this.daysFull = null;
        this.urlFile = null;
        this.firstClosedShift = null;
        this.dataRows = null;
        this.line = null;
        this.date = null;
        this.cells = null;
        this.cellNames = null;
        this.urlRefresh = null;
        this.dataset = null;
        this.shiftCapacity = null;
        this.urlSaveRedirect = null;
        this.loadUrl = null;
    }

    _createClass(XmlPlanningByPart, [{
        key: "init",
        value: function init(xmlDoc, options) {
            this.translationHeader = options.translationHeader;
            this.translationError = options.translationError;
            this.urlRefresh = options.urlRefresh;
            this.urlSaveRedirect = options.urlSaveRedirect;
            this.loadUrl = options.loadUrl;
            this.parser = new DOMParser();
            if (xmlDoc) {
                this.xmlDoc = this.parser.parseFromString(xmlDoc, "text/xml");
                this.extractData();
            }

            this.urlFile = options.urlFile;
            this.registerMainEvents();
            this.render();
        }
    }, {
        key: "loadNew",
        value: function loadNew(xmlDoc) {
            this.parser = new DOMParser();
            this.xmlDoc = this.parser.parseFromString(xmlDoc, "text/xml");
            this.extractData();
            this.render();
            //alert(xmlDoc);
        }

        // extracts planning dataset data

    }, {
        key: "extractData",
        value: function extractData() {

            var doc = this.xmlDoc;
            var firstClosedShift = doc.getElementsByTagName("firstClosedShift");
            if (firstClosedShift && firstClosedShift.length > 0) {
                this.firstClosedShift = firstClosedShift[0].textContent;
            }
            var _rows = doc.getElementsByTagName("rows");
            var rowsTags = null,
                rowTags = [];
            if (_rows && _rows.length > 0) {
                rowsTags = _rows && _rows.length > 0 ? _rows[0] : [];
                rowTags = rowsTags.getElementsByTagName("row");
            }

            var lineTag = doc.getElementsByTagName("line")[0];
            this.line = lineTag.textContent;
            this.shiftCapacity = parseInt(lineTag.attributes["shiftCapacity"].value);
            // console.log(this.shiftCapacity, lineTag.attributes["shiftCapacity"])
            var dateTag = doc.getElementsByTagName("date")[0];
            this.date = dateTag.textContent;
            var cellsTag = doc.getElementsByTagName("cells")[0];
            var cellTags = cellsTag.getElementsByTagName("cell");
            var cellNames = {};
            var cells = [];

            for (var i = 0; i < cellTags.length; i++) {
                var cellTag = cellTags[i];
                var cellName = cellTag.getElementsByTagName("name")[0].textContent;
                var countCapacity = 0;
                var machineTags = cellTag.getElementsByTagName("machine");
                var machines = [];
                //console.log(machineTags);
                for (var j = 0; j < machineTags.length; j++) {
                    var machineTag = machineTags[j];
                    var name = machineTag.attributes["name"].value;
                    var value = parseInt(machineTag.textContent);
                    countCapacity += value;
                    machines.push({ name: name, capacity: value });
                }
                cellNames[cellName] = countCapacity;
                cells.push({ name: cellName, machines: machines });
            }
            this.cellNames = cellNames;
            this.cells = cells;

            var dataRows = [];
            for (var i = 0; i < rowTags.length; i++) {
                var rowTag = rowTags[i];
                var partNumber = rowTag.getElementsByTagName("partNumber")[0].textContent;
                var initialQuantity = rowTag.getElementsByTagName("initialQuantity")[0].textContent;
                var routing = rowTag.getElementsByTagName("routing")[0].textContent;
                var totals = rowTag.getElementsByTagName("totals")[0].textContent;
                var priority = rowTag.attributes["priority"].value;
                var shiftsTag = rowTag.getElementsByTagName("shifts")[0];
                var shiftTags = shiftsTag.getElementsByTagName("shift");
                var shifts = {},
                    shiftIds = {};
                for (var j = 0; j < shiftTags.length; j++) {
                    var shiftTag = shiftTags[j];
                    var val = parseInt(shiftTag.textContent);
                    var name = shiftTag.attributes["name"].value;
                    shifts[name] = val;
                    //var id = shiftTag.attributes["id"].value;
                    //shiftIds[name] = id;
                }
                dataRows.push({ priority: priority, partNumber: partNumber, initialQuantity: initialQuantity, routing: routing, totals: totals, shifts: shifts, shiftIds: shiftIds });
            }
            this.dataRows = dataRows;
            // var weekTag = weekTags[i];
            //  var id = weekTag.attributes["id"].value;
            // var start = weekTag.getElementsByTagName("start")[0].textContent;
        }
    }, {
        key: "sendFile",
        value: function sendFile(file_data) {
            var t = this;
            var form_data = new FormData();
            form_data.append('file', file_data);
            $.ajax({
                url: t.urlFile,
                dataType: 'json', // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function success(php_script_response) {
                    if (php_script_response.errorHtml) {
                        var error = php_script_response.errorHtml;
                        if (error == 'file_format') {
                            error = t.translationError.InvalidFileFormat;
                            error = '<div class="alert alert-danger" id="messageField">' + error + '</div>';
                        } else if (error == 'invalid_xls_data') {
                            error = t.translationError.invalidXlsData;
                            error = '<div class="alert alert-danger" id="messageField">' + error + '</div>';
                        }

                        $('.error-msg-wrapp').html(error);
                    } else {
                        $('.error-msg-wrapp').html('');
                        t.supplyExcelData(php_script_response.data);
                    }
                }
            });
        }
    }, {
        key: "distributeTotals",
        value: function distributeTotals() {
            var rows = $('.planning_part tbody tr:not(".deleted"):not(".emptyRow")');
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var td_tag = $(row).find('td.total');
                var init_qty = $(row).find('td.init_qty');
                var total = 0;
                total = parseInt(init_qty.find('input').val());
                if (total > 0) {
                    var totalShiftsEditing = 3;
                    var perShift = parseInt(total / totalShiftsEditing);
                    var tdA = td_tag.next();
                    var tdB = tdA.next();
                    var tdC = tdB.next();
                    var noChangeNeeded = {};
                    if (tdA.is('.closed')) {
                        noChangeNeeded.A = 1;
                    }
                    if (tdB.is('.closed')) {
                        noChangeNeeded.B = 1;
                    }
                    if (tdC.is('.closed')) {
                        noChangeNeeded.C = 1;
                    }
                    if (noChangeNeeded.A) {
                        tdA.text(tdA.data('val'));
                        total = total - parseInt(tdA.data('val'));
                        totalShiftsEditing--;
                    }
                    if (noChangeNeeded.B) {
                        tdB.text(tdB.data('val'));
                        total = total - parseInt(tdB.data('val'));
                        totalShiftsEditing--;
                    }
                    if (noChangeNeeded.C) {
                        tdC.text(tdC.data('val'));
                        total = total - parseInt(tdC.data('val'));
                        totalShiftsEditing--;
                    }
                    var perShift = 0,
                        remainder = 0;
                    if (totalShiftsEditing > 0) {
                        perShift = parseInt(total / totalShiftsEditing);
                        remainder = total - parseInt(perShift * totalShiftsEditing);
                    }
                    if (!noChangeNeeded.A) {
                        tdA.find('.inputVal').val(perShift);
                    }
                    if (!noChangeNeeded.B) {
                        tdB.find('.inputVal').val(perShift);
                    }
                    if (!noChangeNeeded.C) {
                        tdC.find('.inputVal').val(perShift + remainder);
                    }
                    tdC.find('.inputVal').change();
                    //console.log(tdC.find('.inputVal'));
                }
            }
            // $('.btn_distribute').attr('disabled', 'disabled');
            // $('.btn_export').removeAttr('disabled');
        }
    }, {
        key: "validateData",
        value: function validateData() {
            var overAllValid = true;

            var errorQuantityMatch = [];
            var errorAvailabletime = [];
            var errorMachineCacpacity = [];
            var rows = $('.planning_part tbody tr:not(".deleted"):not(".emptyRow")');
            var error = null;
            var invalidControls = [];
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var invalid = false;
                var td_tag = $(row).find('td.total');
                var init_qty = $(row).find('td.init_qty');
                var qty_val = parseInt(init_qty.find('input').val());
                var tdA = td_tag.next();
                var tdB = tdA.next();
                var tdC = tdB.next();
                //console.log(td_tag.text() , qty_val,td_tag.text() != qty_val);
                if (parseInt(td_tag.text()) != parseInt(qty_val)) {
                    td_tag.addClass('invalid');
                    invalid = true;
                    errorQuantityMatch.push({ value: td_tag.text(), selector: 'td.total', row: i });
                } else {
                    td_tag.removeClass('invalid');
                }
                if (parseInt(td_tag.text()) < qty_val) {
                    td_tag.addClass('invalid');
                    invalid = true;
                    errorAvailabletime.push({ value: td_tag.text(), selector: 'td.total', row: i });
                } else if (!invalid) {
                    td_tag.removeClass('invalid');
                }
                var error = this.isLineCacpacityValid(tdA, tdB, tdC);
                if (error.invalid) {
                    invalid = error.invalid;
                }
                if (error.tdA) {
                    tdA.addClass('invalid');
                    errorMachineCacpacity.push({ value: tdA.find('.inputVal').val(), selector: '.tdA .inputVal', row: i });
                } else {
                    tdA.removeClass('invalid');
                }
                if (error.tdB) {
                    tdB.addClass('invalid');
                    errorMachineCacpacity.push({ value: tdB.find('.inputVal').val(), selector: '.tdB .inputVal', row: i });
                } else {
                    tdB.removeClass('invalid');
                }
                if (error.tdC) {
                    tdC.addClass('invalid');
                    errorMachineCacpacity.push({ value: tdC.find('.inputVal').val(), selector: '.tdC .inputVal', row: i });
                } else {
                    tdC.removeClass('invalid');
                }
                if (invalid) {
                    overAllValid = false;
                    $('.btn_export').attr('disabled', 'disabled');
                } else {
                    var message = this.translationError.dataReadyForExport;
                    message = '<div class="alert alert-success" id="messageField"> <i class="fa fa-bell"></i>  ' + message + '</div>';
                    $('.error-msg-wrapp').html(message);
                    $('.btn_export').removeAttr('disabled');
                }
            }
            $('#errorBlock1,#errorBlock2,#errorBlock3').html('');
            var errText = null,
                errNo = 1;
            if (!overAllValid) {
                if (errorQuantityMatch.length > 0) {
                    errText = '<p class="errorPara"><i class="fa fa-info-circle" />' + this.translationError.QuantityMatch;
                    errText += " <span>(" + this.getArrayLinkJoin(errorQuantityMatch, {}) + ")</p>";
                    $('#errorBlock' + errNo).html(errText);
                    errNo++;
                }
                if (errorAvailabletime.length > 0) {
                    errText = '<p class="errorPara"><i class="fa fa-info-circle" />' + this.translationError.Availabletimeexceeded;
                    errText += " <span>(" + this.getArrayLinkJoin(errorAvailabletime, {}) + ")</p>";
                    $('#errorBlock' + errNo).html(errText);
                    errNo++;
                }
                if (errorMachineCacpacity.length > 0) {
                    errText = '<p class="errorPara"><i class="fa fa-info-circle" />' + this.translationError.Target;
                    errText += " <span>(" + this.getArrayLinkJoin(errorMachineCacpacity, {}) + ")</p>";
                    $('#errorBlock' + errNo).html(errText);
                    errNo++;
                }
            }
            var rValid = this.isPartNumberRoutingValid();
            // console.log(rValid);
            if (!rValid) {
                var pns = ' ( ';
                $('.invalidPartNumber .partNumberVal').each(function (i, v) {
                    if (i > 0) {
                        pns += this.value + ", ";
                    }
                    pns += this.value;
                });
                pns += " )";
                var err = '<div class="alert alert-danger">' + this.translationError.invalidRouting + pns + "</div>";
                $('.error-msg-wrapp').html(err);
            }
            overAllValid = overAllValid && rValid;
            return overAllValid;
        }
    }, {
        key: "getArrayLinkJoin",
        value: function getArrayLinkJoin(values, data) {
            var data_html = '',
                html = '';
            for (var i in data) {
                var val = data[i];
                data_html += ' data-' + i + ' = ' + val;
            }
            for (var i in values) {
                var v = values[i];
                if (i > 0) {
                    html += ', ';
                }
                html += '<a href="#" onclick="return xmlPlanningByPart.openInput(this)" ' + data_html + ' data-selector="' + v.selector + '" data-row="' + v.row + '">' + v.value + '</a>';
            }
            return html;
        }
    }, {
        key: "openInput",
        value: function openInput(_this) {
            var row = $(_this).data('row');
            var inputSelector = $(_this).data('selector');
            //console.log(row, inputSelector,  $('.planning_part tbody tr:eq('+row+')'))
            var input = $('.planning_part tbody tr:eq(' + row + ')').find(inputSelector);
            input.focus();
            return false;
        }
    }, {
        key: "isPartNumberRoutingValid",
        value: function isPartNumberRoutingValid() {
            return $('.partNumberVal.invalidPartNumber').length == 0;
        }
    }, {
        key: "isLineCacpacityValid",
        value: function isLineCacpacityValid(tdA, tdB, tdC) {
            var shiftCapacity = this.shiftCapacity;
            var dataErrors = { invalid: false };

            if (!tdA.is('.closed')) {
                var val = parseInt(tdA.find('.inputVal').val());
                if (isNaN(val)) {
                    val = 0;
                }
                //console.log(shiftCapacity, val)
                if (shiftCapacity < val) {
                    dataErrors.tdA = true;
                    dataErrors.invalid = true;
                }
            }
            if (!tdB.is('.closed')) {
                var val = parseInt(tdB.find('.inputVal').val());
                if (isNaN(val)) {
                    val = 0;
                }
                if (shiftCapacity < val) {
                    dataErrors.tdB = true;
                    dataErrors.invalid = true;
                }
                //console.log( shiftCapacity, dataErrors, val,shiftCapacity < val);
            }
            if (!tdC.is('.closed')) {
                var val = parseInt(tdC.find('.inputVal').val());
                if (isNaN(val)) {
                    val = 0;
                }
                if (shiftCapacity < val) {
                    dataErrors.tdC = true;
                    dataErrors.invalid = true;
                }
            }
            //console.log(dataErrors);
            return dataErrors;
        }
    }, {
        key: "isDataInObject",
        value: function isDataInObject(data, partNumber) {
            for (var i in data) {
                var d = data[i];
                if (d.partNumber == partNumber) {
                    return d;
                }
            }
            return null;
        }
    }, {
        key: "supplyExcelData",
        value: function supplyExcelData(data) {
            var html = '';
            var dataRows = this.dataRows;
            if (data) {
                //$('.btn_distribute').removeAttr('disabled');
                dataRows = data;
                formDataChanged = true;
            }
            for (var i in dataRows) {
                var d = dataRows[i];
                var objXls = data ? this.isDataInObject(this.dataRows, d.partNumber) : null;
                html += this.getHtml(d, data, objXls);
            }
            if (!dataRows || dataRows.length == 0) {
                html = '<tr class="emptyRow"><td colspan="8">' + this.translationError.EmptyData + '</td></tr>';
            }
            $('.planning_part tbody').html(html);
            this.registerDataEvents();
        }
    }, {
        key: "getHtml",
        value: function getHtml(d, data, objXls) {
            var html;
            var closedA = false;
            var closedB = false;
            var closedC = false;
            var clsA = '';
            var clsB = '';
            var clsC = '';

            var txtA = '';
            var txtB = '';
            var txtC = '';
            var dataA = '0';
            var dataB = '0';
            var dataC = '0';
            if (d.shifts) {
                txtA = d.shifts.A;
                txtB = d.shifts.B;
                txtC = d.shifts.C;
            }

            if (objXls) {
                d.shifts = objXls.shifts;
            }
            if (this.firstClosedShift == "A") {
                closedA = true;

                clsA = 'class = "closed"';
                if (d.shifts) {
                    txtA = dataA = d.shifts.A;
                }
            }
            if (this.firstClosedShift == "B") {
                closedA = true;
                closedB = true;

                clsA = 'class = "closed"';
                clsB = 'class = "closed"';
                if (d.shifts) {
                    dataA = d.shifts.A;
                    txtB = dataB = d.shifts.B;
                }
            }
            if (this.firstClosedShift == "C") {
                closedA = true;
                closedB = true;
                closedC = true;

                clsA = 'class = "closed"';
                clsB = 'class = "closed"';
                clsC = 'class = "closed"';
                if (d.shifts) {
                    txtA = dataA = d.shifts.A;
                    txtB = dataB = d.shifts.B;
                    txtC = dataC = d.shifts.C;
                }
            }

            if (!closedA) {
                txtA = '<input type="text" class="inputVal shiftInput" value="' + txtA + '" data-fvalue="' + txtA + '" />';
            }
            if (!closedB) {
                txtB = '<input type="text" class="inputVal shiftInput" value="' + txtB + '" data-fvalue="' + txtB + '" />';
            }
            if (!closedC) {
                txtC = '<input type="text" class="inputVal shiftInput" value="' + txtC + '" data-fvalue="' + txtC + '" />';
            }
            //console.log(d);
            var partNoInput = '<input type="text" class="inputVal partNumberVal" data-ovalue="' + d.partNumber + '"  data-fvalue="' + d.partNumber + '" value="' + d.partNumber + '" />';
            var qty = data ? d.totals : d.initialQuantity;
            //console.log(data , d.totals , d.initialQuantity);
            var initQtyInput = '<input type="text" class="inputVal qtyVal" data-ovalue="' + qty + '" data-fvalue="' + qty + '" value="' + qty + '" />';
            html += '<tr><td>' + partNoInput + '</td><td class="init_qty">' + initQtyInput + '</td><td class="tdRouting">' + (data ? d.routing : d.routing) + '</td><td class="total">' + d.totals + '</td>\
        <td class="tdA" data-val="' + dataA + '" ' + clsA + '>' + txtA + '</td>\
        <td class="tdB" data-val="' + dataB + '" ' + clsB + '>' + txtB + '</td>\
        <td class="tdC" data-val="' + dataC + '" ' + clsC + '>' + txtC + '</td>\
        <td><a href="#" title="' + this.translationHeader.Delete + '" class="delete"><i class="fa fa-trash"></i></a></td>\
        </tr>';
            return html;
        }
    }, {
        key: "addNew",
        value: function addNew() {
            var d = { 'partNumber': '', 'initialQuantity': '', 'routing': '', 'totals': '', newRecord: 1 };
            var html = this.getHtml(d, null, null);
            if ($('.planning_part tbody .emptyRow').length > 0) {
                $('.planning_part tbody .emptyRow').remove();
            }
            $('.planning_part tbody').append(html);
            var priority = 1;
            if (this.dataRows.length > 0) {
                var lastElem = this.dataRows[this.dataRows.length - 1];
                priority = parseInt(lastElem.priority) + 1;
            }
            var shifts = { A: '', B: '', C: '' };
            this.dataRows.push({ priority: priority, partNumber: '', initialQuantity: '', routing: '', totals: 0, shifts: shifts, shiftIds: null, newRecord: 1 });

            this.registerDataEvents(true);
            return false;
        }
    }, {
        key: "render",
        value: function render() {
            $('.line_headings span').text(this.line);
            var txt = '',
                count = 0;
            for (var i in this.cellNames) {
                var qty = this.cellNames[i];
                if (count > 0) {
                    txt += ", ";
                }
                txt += this.line + " " + i + "(" + qty + ")";
                count++;
            }
            $('.cells_headings span').text(txt);
            $('heading_date').text(this.date);
            this.supplyExcelData();
        }
    }, {
        key: "saveDataInArray",
        value: function saveDataInArray() {
            for (var i in this.dataRows) {
                var trElem = $('.planning_part tbody tr:eq(' + i + ')');
                if (trElem.is('tr:not(".deleted"):not(".emptyRow")')) {
                    var init_qty = trElem.find('.qtyVal');
                    var partNumber = trElem.find('.partNumberVal');
                    var tdRouting = trElem.find('.tdRouting');
                    var total = trElem.find('.total');
                    this.dataRows[i].initialQuantity = init_qty.val();
                    this.dataRows[i].partNumber = partNumber.val();
                    this.dataRows[i].routing = tdRouting.text();
                    this.dataRows[i].total = total.text();
                    var shiftA = trElem.find('.total').next();
                    var shiftB = shiftA.next();
                    var shiftC = shiftB.next();
                    //console.log(this.dataRows[i]);
                    if (!shiftA.is('.closed')) {
                        this.dataRows[i].shifts.A = shiftA.find('.inputVal').val();
                    }
                    if (!shiftB.is('.closed')) {
                        this.dataRows[i].shifts.B = shiftB.find('.inputVal').val();
                    }
                    if (!shiftC.is('.closed')) {
                        this.dataRows[i].shifts.C = shiftC.find('.inputVal').val();
                    }
                }
                if (trElem.is('.deleted')) {
                    this.dataRows[i].deleted = 1;
                }
            }
            this.prepareDataSets();
        }
    }, {
        key: "prepareDataSets",
        value: function prepareDataSets() {
            var aSum = 0,
                bSum = 0,
                cSum = 0;
            var d = { A: [], B: [], C: [] };
            for (var i in this.dataRows) {
                if (this.dataRows[i].deleted == 1) {
                    continue;
                }
                var shiftIds = this.dataRows[i].shiftIds;
                var shifts = this.dataRows[i].shifts;
                aSum += parseInt(shifts.A);
                bSum += parseInt(shifts.B);
                cSum += parseInt(shifts.C);
            }
            // console.log(aSum, bSum, cSum);
            var perHrValueA = parseInt(aSum / 8);
            var perHrRemA = aSum % 8;

            var perHrValueB = parseInt(bSum / 8);
            var perHrRemB = bSum % 8;
            //perHrValueB+=perHrRemB;

            var perHrValueC = parseInt(cSum / 8);
            var perHrRemC = cSum % 8;
            //perHrValueC+=perHrRemC;
            for (var i = 1; i <= 8; i++) {
                var addRemA = i == 8 ? perHrRemA : 0;
                var addRemB = i == 8 ? perHrRemB : 0;
                var addRemC = i == 8 ? perHrRemC : 0;
                d.A.push({ id: i, value: perHrValueA + addRemA });
                d.B.push({ id: i, value: perHrValueB + addRemB });
                d.C.push({ id: i, value: perHrValueC + addRemC });
            }
            this.dataset = d;
        }
    }, {
        key: "getXml",
        value: function getXml() {
            var doc = this.xmlDoc;
            var t = this;
            var rowTags = doc.getElementsByTagName("row");
            for (var i = 0; i < rowTags.length; i++) {
                var rowTag = rowTags[i];
                var refData = this.dataRows[i];
                var partNumberTag = rowTag.getElementsByTagName("partNumber")[0];
                var routingTag = rowTag.getElementsByTagName("routing")[0];
                var initialQuantityTag = rowTag.getElementsByTagName("initialQuantity")[0];
                var totalTag = rowTag.getElementsByTagName("totals")[0];
                partNumberTag.textContent = refData.partNumber;
                initialQuantityTag.textContent = refData.initialQuantity;
                totalTag.textContent = refData.total;
                routingTag.textContent = refData.routing;
                var shiftsTags = rowTag.getElementsByTagName("shifts");
                shiftsTags = shiftsTags[0];
                var shiftTags = rowTag.getElementsByTagName("shift");
                var keyMap = { 0: 'A', 1: 'B', 2: 'C' };
                for (var j = 0; j < shiftTags.length; j++) {
                    var shiftTag = shiftTags[j];
                    shiftTag.textContent = refData.shifts[keyMap[j]];
                }
                if (j < 3) {
                    while (j < 3) {
                        var shiftTag = doc.createElement("shift");
                        shiftTag.setAttribute("name", keyMap[j]);
                        shiftTag.appendChild(doc.createTextNode(refData.shifts[keyMap[j]]));
                        shiftsTags.appendChild(shiftTag);
                        j++;
                    }
                }
            }
            var rowsTag = doc.getElementsByTagName("rows");
            if (rowsTag.length > 0) {
                rowsTag = rowsTag[0];
            } else {
                rowsTag = doc.createElement("rows");
                doc.documentElement.appendChild(rowsTag);
            }

            //delete xml dataset row
            for (var i = 0; i < this.dataRows.length; i++) {
                var refData = this.dataRows[i];
                if (!refData.newRecord && refData.deleted) {

                    var rowTag = rowTags[i];
                    if (!rowTag) {
                        continue;
                    }
                    //console.log(rowTags, i, rowTag)
                    rowsTag.removeChild(rowTag);
                }
            }

            for (var i = 0; i < this.dataRows.length; i++) {
                var refData = this.dataRows[i];
                if (refData.newRecord) {
                    // skip adding newly added deleted row
                    if (refData.deleted) {
                        continue;
                    }
                    var rowTag = doc.createElement("row");
                    rowTag.setAttribute("priority", refData.priority);

                    var shiftsTag = doc.createElement("shifts");

                    var partNumber = doc.createElement("partNumber");
                    var routing = doc.createElement("routing");
                    var initialQuantity = doc.createElement("initialQuantity");
                    var totals = doc.createElement("totals");

                    var shiftA = doc.createElement("shift");
                    var shiftB = doc.createElement("shift");
                    var shiftC = doc.createElement("shift");
                    var txtA = doc.createTextNode(refData.shifts.A);
                    var txtB = doc.createTextNode(refData.shifts.B);
                    var txtC = doc.createTextNode(refData.shifts.C);
                    shiftA.appendChild(txtA);
                    shiftB.appendChild(txtB);
                    shiftC.appendChild(txtC);

                    shiftA.setAttribute("name", 'A');
                    shiftB.setAttribute("name", 'B');
                    shiftC.setAttribute("name", 'C');
                    shiftsTag.appendChild(shiftA);
                    shiftsTag.appendChild(shiftB);
                    shiftsTag.appendChild(shiftC);
                    // console.log(refData, refData.shifts.A, refData.shifts.B, refData.shifts.C);
                    partNumber.appendChild(doc.createTextNode(refData.partNumber));
                    routing.appendChild(doc.createTextNode(refData.routing));
                    initialQuantity.appendChild(doc.createTextNode(refData.initialQuantity));
                    totals.appendChild(doc.createTextNode(refData.total));

                    rowTag.appendChild(partNumber);
                    rowTag.appendChild(initialQuantity);
                    rowTag.appendChild(routing);
                    rowTag.appendChild(totals);
                    rowTag.appendChild(shiftsTag);

                    rowsTag.appendChild(rowTag);
                }
            }
            //console.log(i, this.dataRows.length);


            // prepare dataset
            var datasetTag = doc.getElementsByTagName("dataset");
            if (datasetTag.length == 0) {
                datasetTag = t.xmlDoc.createElement("dataset");
                t.xmlDoc.documentElement.appendChild(datasetTag);
            } else {
                datasetTag = datasetTag[0];
                // delete all shifts
                var shiftTag = datasetTag.getElementsByTagName("shift");
                //console.log(shiftTag);
                if (shiftTag.length > 0) {
                    var removeElems = [];
                    for (var i = 0; i < shiftTag.length; i++) {
                        //console.log(shiftTag[i]);
                        removeElems.push(shiftTag[i]);
                    }
                    for (var i in removeElems) {
                        datasetTag.removeChild(removeElems[i]);
                    }
                }
            }
            var shiftA = this.xmlDoc.createElement("shift");
            var shiftB = this.xmlDoc.createElement("shift");
            var shiftC = this.xmlDoc.createElement("shift");

            shiftA.setAttribute("name", 'A');
            shiftB.setAttribute("name", 'B');
            shiftC.setAttribute("name", 'C');
            for (var i in this.dataset.A) {
                var ds = this.dataset.A[i];
                var hourTag = this.xmlDoc.createElement("hour");
                hourTag.setAttribute("interval", ds.id);
                var txt = this.xmlDoc.createTextNode(ds.value);
                hourTag.appendChild(txt);
                shiftA.appendChild(hourTag);
            }
            for (var i in this.dataset.B) {
                var ds = this.dataset.B[i];
                var hourTag = this.xmlDoc.createElement("hour");
                hourTag.setAttribute("interval", ds.id);
                var txt = this.xmlDoc.createTextNode(ds.value);
                hourTag.appendChild(txt);
                shiftB.appendChild(hourTag);
            }
            for (var i in this.dataset.C) {
                var ds = this.dataset.C[i];
                var hourTag = this.xmlDoc.createElement("hour");
                hourTag.setAttribute("interval", ds.id);
                var txt = this.xmlDoc.createTextNode(ds.value);
                hourTag.appendChild(txt);
                shiftC.appendChild(hourTag);
            }
            datasetTag.appendChild(shiftA);
            datasetTag.appendChild(shiftB);
            datasetTag.appendChild(shiftC);

            //console.log(this.xmlDoc.documentElement.innerHTML);
            var oSerializer = new XMLSerializer();
            var sXML = oSerializer.serializeToString(this.xmlDoc);
            console.log(sXML);
            return sXML;
        }
    }, {
        key: "registerMainEvents",
        value: function registerMainEvents() {
            var t = this;
            $('#xsl_file').change(function () {
                var file_data = $(this).prop('files')[0];
                t.sendFile(file_data);
            });
            $('.btn_distribute').click(function () {
                if ($(this).is("[disabled]")) {
                    return false;
                }
                t.distributeTotals();
                return false;
            });
            $('.btn_import').click(function () {
                if ($(this).is("[disabled]")) {
                    return false;
                }
                if ($(this).is('[disabled]')) {
                    return false;
                }
                $('#xsl_file').click();
                return false;
            });
            $('.btn_validate').click(function () {
                if ($(this).is("[disabled]")) {
                    return false;
                }
                var isValid = t.validateData();
                return false;
            });
            $('.btn_add').click(function () {
                if ($(this).is("[disabled]")) {
                    return false;
                }
                t.addNew();
                return false;
            });
            $('#filterDate,#filterLine').change(function () {
                t.refreshPlanning($('#filterLine').val(), $('#filterDate').val());
            }).change();
            $('.btn_export').click(function () {
                var isValid = t.validateData();
                if (!isValid) {
                    return false;
                }
                t.saveDataInArray();
                $('#xmlTargets').val(t.getXml());
                var form_data = $(this.form).serialize();
                console.log(form_data);
                debugger;
                $.ajax({
                    url: $(this.form).attr('action'),
                    dataType: 'json', // what to expect back from the PHP script, if anything
                    cache: false,
                    data: form_data,
                    type: 'post',
                    success: function success(php_script_response) {

                        if (php_script_response.error) {
                            $('.error-msg-wrapp').html(php_script_response.error);
                        } else {
                            $('.error-msg-wrapp').html('');
                            // redirect
                            window.location.href = t.urlSaveRedirect;
                        }
                    }
                });
                return false;
            });
        }
    }, {
        key: "updateFormDataChanged",
        value: function updateFormDataChanged() {
            formDataChanged = false;
            if ($('.planning_part tbody tr:not(".deleted"):not(".emptyRow")').find('td.changed .inputVal').length > 0) {
                formDataChanged = true;
            }
        }
    }, {
        key: "registerDataEvents",
        value: function registerDataEvents(only_last) {
            var t = this;
            var selectRows = $('.planning_part tbody tr');
            if (only_last) {
                selectRows = $('.planning_part tbody tr:last-child');
            }
            selectRows.find('td .inputVal.shiftInput').change(function () {
                var total = 0;
                $(this).closest('tr').find('td.closed').each(function () {
                    total += parseInt($(this).data('val'));
                });
                $(this).closest('tr').find('td .inputVal.shiftInput').each(function () {
                    var amount = parseInt($(this).val());
                    if (isNaN(amount)) {
                        amount = 0;
                    }
                    if (amount != $(this).val()) {
                        $(this).val(amount);
                    }
                    total += amount;
                });
                $(this).closest('tr').find('td.total').text(total);
                t.updateFormDataChanged();
            }).change();
            selectRows.find('td .inputVal').each(function () {
                $(this).closest('td').addClass('tdInput');
            }).change(function () {
                var amount = parseInt($(this).val());
                if (isNaN(amount)) {
                    amount = 0;
                }
                if (amount != $(this).val()) {
                    $(this).val(amount);
                }

                if ($(this).data('fvalue') != amount) {
                    if ($(this).closest('td').is('.invalidPartNumber')) {
                        $(this).closest('td').addClass('changed');
                    }
                } else {
                    $(this).closest('td').removeClass('changed');
                }
            });
            selectRows.find('td .partNumberVal').change(function () {
                if ($(this).data('ovalue') != $(this).val()) {
                    var v = $(this).val();
                    t.refreshRouting(v, $(this));
                }
                t.updateFormDataChanged();
            });
            selectRows.find('td .delete').click(function () {
                if (!confirm(t.translationError.ConfirmDelete)) {
                    return false;
                }
                var trNum = $(this).closest('tbody').find('tr:not(".deleted"):not(".emptyRow")').length;
                $(this).closest('tr').addClass('deleted');

                if (trNum == 1) {
                    var html = '<tr class="emptyRow"><td colspan="8">' + t.translationError.EmptyData + '</td></tr>';
                    if ($('.planning_part tbody .emptyRow').length > 0) {
                        $('.planning_part tbody .emptyRow').show();
                    } else {
                        $('.planning_part tbody').html(html);
                    }
                }

                return false;
            });
        }
    }, {
        key: "refreshRouting",
        value: function refreshRouting(part_no, input) {
            var t = this;
            var form_data = new FormData();
            form_data.append('partNumber', part_no);
            //console.log(t.urlRefresh);
            $.ajax({
                url: t.urlRefresh,
                dataType: 'json', // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function success(php_script_response) {

                    if (php_script_response.errorHtml) {
                        var err = php_script_response.errorHtml;
                        if (php_script_response.errorHtml == 'invalidRouting') {
                            err = '<div class="alert alert-danger">' + t.translationError.invalidRouting + "</div>";
                            input.closest('td').addClass('invalid invalidPartNumber');
                            input.closest('tr').find('.tdRouting').text('0');
                        }
                        $('.error-msg-wrapp').html(err);
                    } else {
                        input.closest('td').removeClass('invalid invalidPartNumber');
                        $('.error-msg-wrapp').html('');
                        input.val(php_script_response.partNumber).data('ovalue', php_script_response.partNumber);
                        input.closest('tr').find('.tdRouting').text(php_script_response.routing);
                    }
                }
            });
        }
    }, {
        key: "refreshPlanning",
        value: function refreshPlanning(line, date) {
            var t = this;
            if (line != '' && date != '') {
                var form_data = new FormData();
                form_data.append('line', line);
                form_data.append('date', date);
                $.ajax({
                    url: this.loadUrl,
                    dataType: 'json', // what to expect back from the PHP script, if anything
                    cache: false,
                    data: form_data,
                    contentType: false,
                    processData: false,
                    type: 'post',
                    success: function success(php_script_response) {
                        t.loadNew(php_script_response.xml);
                        $('.btn_import,.btn_add,.btn_distribute,.btn_validate,.btn_export').removeAttr('disabled');
                    }
                });
            } else {
                $('.btn_import,.btn_add,.btn_distribute,.btn_validate,.btn_export').attr('disabled', 'disabled');
            }
            $('#filterDate,#filterLine').next('.errorSelect').hide();
            if (line == '') {
                $('#filterLine').next('.errorSelect').show();
            }
            if (date == '') {
                $('#filterDate').next('.errorSelect').show();
            }
        }
    }]);

    return XmlPlanningByPart;
}();