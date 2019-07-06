"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var XmlDailyPlanning = function () {
    function XmlDailyPlanning() {
        _classCallCheck(this, XmlDailyPlanning);

        this.parser = null;
        this.xmlWeeksDoc = null;
        this.xmlDoc = null;
        this.translationError = null;
        this.translationHeader = null;
        this.translationDays = null;
        this.translationMonths = null;
        this.weeks = null;
        this.targetDays = null;
        this.firstDate = null;
        this.lastDate = null;
        this.currentDate = new Date();
        this.shifts = null;
        this.currentDayNo = null;
        this.daysFull = null;
        this.targetRendered = null;
        this.moreLinesAvailable = false;
        this.errorControls = {};
        this.changedControls = {};
        this.timestamp = null;
    }

    _createClass(XmlDailyPlanning, [{
        key: "init",
        value: function init(xmlWeeks, xmlTargets, options) {
            this.translationHeader = options.translationHeader;
            this.translationError = options.translationError;
            this.translationDays = options.translationDays;
            this.translationMonths = options.translationMonths;
            this.parser = new DOMParser();
            this.xmlWeeksDoc = this.parser.parseFromString(xmlWeeks, "text/xml");
            if (xmlTargets != '') {
                this.xmlDoc = this.parser.parseFromString(xmlTargets, "text/xml");
            }

            this.extractData();
            this.createDaysDefinitions();
            this.render();
            this.registerMainEvents();
        }
    }, {
        key: "getTranslatedDay",
        value: function getTranslatedDay(day) {
            if (this.translationDays[day]) {
                day = this.translationDays[day];
            }
            return day;
        }
    }, {
        key: "getTranslatedMonth",
        value: function getTranslatedMonth(monthName) {
            if (this.translationMonths[monthName]) {
                monthName = this.translationMonths[monthName];
            }
            return monthName;
        }
        // extracts planning dataset data

    }, {
        key: "extractData",
        value: function extractData() {
            var weeks = [];
            var weeksDoc = this.xmlWeeksDoc;
            var weekTags = weeksDoc.getElementsByTagName("week");
            for (var i = 0; i < weekTags.length; i++) {
                var weekTag = weekTags[i];
                var id = weekTag.attributes["id"].value;
                var start = weekTag.getElementsByTagName("start")[0].textContent;
                var end = weekTag.getElementsByTagName("end")[0].textContent;
                weeks.push({ id: id, start: start, end: end });
            }
            this.weeks = weeks;

            var firstWeek = this.weeks[0];
            var lastWeek = this.weeks[1];
            this.firstDate = new Date(firstWeek.start);
            this.lastDate = new Date(lastWeek.end);

            if (this.xmlDoc) {
                extractTargets();
            }
        }
    }, {
        key: "extractTargets",
        value: function extractTargets(xmlTargets, date) {
            if (xmlTargets) {
                this.xmlDoc = this.parser.parseFromString(xmlTargets, "text/xml");
            }
            var doc = this.xmlDoc;
            var targetForLines = [];
            var targets = doc.getElementsByTagName("targets");
            this.moreLinesAvailable = false;
            if (targets[0].attributes["moreLinesAvailable"] && targets[0].attributes["moreLinesAvailable"].value == "yes") {
                this.moreLinesAvailable = true;
            }

            this.timestamp = targets[0].attributes["timeStamp"].value;
            var forLineTags = doc.getElementsByTagName("forLine");
            var shifts = {};
            for (var i = 0; i < forLineTags.length; i++) {
                var forLineTag = forLineTags[i];
                var id = forLineTag.attributes["id"].value;
                var name = forLineTag.attributes["name"].value;
                var tags = forLineTag.attributes["tags"].value;
                var firstOpenShiftLogId = forLineTag.attributes["firstOpenShiftLogId"] ? forLineTag.attributes["firstOpenShiftLogId"].value : null;
                var shiftCapacity = forLineTag.attributes["shiftCapacity"] ? forLineTag.attributes["shiftCapacity"].value : null;
                var targetTags = forLineTag.getElementsByTagName("target");
                var targets = [];
                targetForLines.push({ id: id, name: name, tags: tags, firstOpenShiftLogId: firstOpenShiftLogId, targets: targets, shiftCapacity: shiftCapacity });
                for (var j = 0; j < targetTags.length; j++) {
                    var targetTag = targetTags[j];
                    var shiftLogId = targetTag.attributes["shiftLogId"].value;
                    var day = targetTag.attributes["day"].value;
                    var name = targetTag.attributes["name"].value;
                    var closed = false;
                    if (i == 0 && !shifts.name) {
                        shifts[name] = 1;
                    }
                    if (firstOpenShiftLogId > shiftLogId) {
                        closed = true;
                    }
                    var target = targetTag.textContent;
                    targets.push({ shiftLogId: shiftLogId, day: day, name: name, firstTarget: target.toString(), target: target, closed: closed });
                }
            }
            this.shifts = shifts;
            this.targetForLines = targetForLines;
            this.renderTargetLines();
        }
    }, {
        key: "createDaysDefinitions",
        value: function createDaysDefinitions() {
            var def = {};
            var nowDateStr = date("Y-m-d", this.currentDate);
            for (var i = 1; i <= 14; i++) {
                var x = i - 1;
                var newDate = this.addDays(this.firstDate, x);
                var dayName = date("l", newDate);
                dayName = this.getTranslatedDay(dayName);
                var fulDate = date("Y-m-d", newDate);
                if (fulDate == nowDateStr) {
                    this.currentDayNo = i;
                }
                def['d' + i] = { 'day_no': i, 'dayName': dayName, 'fullDate': fulDate };
            }
            this.daysFull = def;
            this.setOnScreenDays();
        }
    }, {
        key: "setOnScreenDays",
        value: function setOnScreenDays() {
            for (var i = 1; i <= 14; i += 3) {
                if (this.currentDayNo >= i && this.currentDayNo <= i + 2) {
                    var end = i + 2;
                    if (end > 14) {
                        end = 14;
                    }
                    this.onScreenDays = { start: i, end: end };
                    // console.log(this.currentDayNo, this.onScreenDays);
                    break;
                }
            }
        }
    }, {
        key: "addDays",
        value: function addDays(startDate, numberOfDays) {
            var returnDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate() + numberOfDays, startDate.getHours(), startDate.getMinutes(), startDate.getSeconds());
            return returnDate;
        }
    }, {
        key: "openDayBySelection",
        value: function openDayBySelection() {
            var l = $('.scroll-select').position().left;
            var widthSingle = $('.week_headings ul li').width();
            var start = parseInt(l / widthSingle) + 1;
            this.openDay(start);
        }
    }, {
        key: "openDay",
        value: function openDay(d) {
            if (this.currentDayNo == d) {
                return;
            }
            this.currentDayNo = d;
            this.onScreenDays.start = d;
            this.onScreenDays.end = d + 2;
            this.renderTargetLines();
        }
    }, {
        key: "moveLeft",
        value: function moveLeft() {
            this.currentDayNo -= 1;
            if (this.currentDayNo < 1) {
                this.currentDayNo = 1;
            }
            this.onScreenDays.start -= 1;
            if (this.onScreenDays.start < 1) {
                this.onScreenDays.start = 1;
            } else {
                this.onScreenDays.end -= 1;
            }
            this.renderTargetLines();
        }
    }, {
        key: "moveRight",
        value: function moveRight() {
            this.currentDayNo += 1;
            if (this.currentDayNo > 14) {
                this.currentDayNo = 14;
            }

            this.onScreenDays.end += 1;
            if (this.onScreenDays.end > 14) {
                this.onScreenDays.end = 14;
            } else {
                this.onScreenDays.start += 1;
            }
            this.renderTargetLines();
        }
    }, {
        key: "highlightDate",
        value: function highlightDate(date) {
            var d = null;
            for (var i in this.daysFull) {
                var df = this.daysFull[i];
                if (df.fullDate == date) {
                    d = df.day_no;
                }
            }
            if (d) {
                this.openDay(d);
            }
        }
    }, {
        key: "render",
        value: function render() {
            var firstWeek = this.weeks[0];
            var dStartObj = new Date(firstWeek.start);
            var dEndObj = new Date(firstWeek.end);
            var date1 = date("d\\t\\h ", dStartObj) + this.getTranslatedMonth(date("M", dStartObj));
            var date2 = date("d\\t\\h ", dEndObj) + this.getTranslatedMonth(date("M", dEndObj));
            var str1 = this.translationHeader.Week + ' #' + firstWeek.id + " ( " + date1 + " - " + date2 + " ) ";
            $('.week_heading_start').text(str1);

            var lastWeek = this.weeks[1];
            dStartObj = new Date(lastWeek.start);
            dEndObj = new Date(lastWeek.end);
            date1 = date("d\\t\\h ", dStartObj) + this.getTranslatedMonth(date("M", dStartObj));
            date2 = date("d\\t\\h ", dEndObj) + this.getTranslatedMonth(date("M", dEndObj));
            str1 = this.translationHeader.Week + ' #' + lastWeek.id + " ( " + date1 + " - " + date2 + " ) ";
            $('.week_heading_end').text(str1);
        }
    }, {
        key: "getTargetHeadRow",
        value: function getTargetHeadRow() {
            var shiftNamesTd = '',
                _shiftNamesTd = '';

            for (var name in this.shifts) {
                _shiftNamesTd += '<td class="shift_name">' + name + '</td>';
            }
            for (var i = 0; i < 3; i++) {
                shiftNamesTd += _shiftNamesTd;
            }
            var ix1 = this.onScreenDays.start;
            var ix2 = this.onScreenDays.start + 1;
            var ix3 = this.onScreenDays.start + 2;
            var ix3Exists = ix3 <= this.onScreenDays.end;
            //console.log(ix3, ix3Exists);
            var d1 = this.daysFull['d' + ix1];
            var d2 = this.daysFull['d' + ix2];
            var d3 = null;
            if (ix3Exists) d3 = this.daysFull['d' + ix3];
            var html = '<tr>\
            <td rowspan="2" class="hline" >' + this.translationHeader.Line + '</td>\
            <td rowspan="2" class="hcapacity" >' + this.translationHeader.Capacity + '</td>\
            <td rowspan="2" class="htags">' + this.translationHeader.Tags + '</td>\
            <td rowspan="2" class=" days-nav-td" ><a href="#" class="days-nav days-left-nav"><i class="fa fa-angle-left "></i></a></td>\
            <td colspan="3"  class="day1" >' + d1.dayName + '<br/>' + d1.fullDate + '</td>\
            <td colspan="3" class="day2" >' + d2.dayName + '<br/>' + d2.fullDate + '</td>\
            <td colspan="3" class="day3" >' + (ix3Exists ? d3.dayName + '<br/>' + d3.fullDate : '') + '</td>\
            <td rowspan="2" class="no-border days-nav-td" ><a href="#" class="days-nav days-right-nav"><i class="fa fa-angle-right "></i></a></td>\
        </tr>\
        <tr>' + shiftNamesTd + '</tr>';
            return html;
        }
    }, {
        key: "renderTargetLines",
        value: function renderTargetLines(highlightDate) {
            var html = this.getTargetHeadRow();
            var targetRendered = [];
            var startPoint = this.onScreenDays.start - 1;
            for (var i in this.targetForLines) {
                var targetLine = this.targetForLines[i];
                var firstElements = '<td>' + targetLine.name + '</td><td>' + targetLine.shiftCapacity + '</td><td colspan="2">' + targetLine.tags + '</td>';
                var rowHtml = '',
                    elemRendered = 0;
                var targetsObj = [];
                for (var j = 0; j < targetLine.targets.length; j++) {

                    if (startPoint * 3 > j) {
                        //console.log("in coninue", startPoint * 3, j);
                        continue;
                    }
                    var targetObj = targetLine.targets[j];
                    targetsObj.push({ 'obj': targetObj, 'targetLineIndex': j });
                    var cls = targetObj.shiftLogId < targetLine.firstOpenShiftLogId ? 'class="closed"' : '';
                    var txt = targetObj.shiftLogId < targetLine.firstOpenShiftLogId ? targetObj.target : '<input type="text" class="target-input" value= "' + targetObj.target + '" /><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="No Data in cell"></i>';
                    rowHtml += '<td data-target-index="' + j + '" data-target="' + targetObj.target + '"  data-first-target="' + targetObj.firstTarget + '" ' + cls + '>' + txt + '</td>';
                    elemRendered++;
                    if (elemRendered == 9) {
                        break;
                    }
                }
                if (elemRendered < 9) {
                    while (elemRendered < 9) {
                        rowHtml += '<td class="emptyTd"><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="No Data in cell"></i></td>';
                        elemRendered++;
                    }
                }
                targetRendered.push({ 'name': targetLine.name, 'linesIndex': i, 'targets': targetsObj });
                html += '<tr class="data-value-row" data-line-index="' + i + '">' + firstElements + rowHtml + '</tr>';
            }
            $('.cal_days tbody').html(html);
            var h = '';
            if (this.moreLinesAvailable) {
                h = '<div class="alert alert-info">We are displaying the first 10 lines only, Please refine your tag search to display exact lines</div>';
            }
            $('.info-msg-wrapp').html(h);
            $('.debug_timestamp').html(this.timestamp);
            this.registerEvents();
            this.targetRendered = targetRendered;
            this.updateDaysSelection();
            this.updateCopyButtonsState();
            if (highlightDate) {
                this.highlightDate(date);
            }
        }
    }, {
        key: "updateCopyButtonsState",
        value: function updateCopyButtonsState() {
            var filteredRows = [];
            var buttons = [false, false, false];
            $('.cal_days tbody tr.data-value-row').each(function () {
                var found_td = $(this).find('td');
                var filtereredTd = [];
                for (var i = 0; i < found_td.length; i++) {
                    if (i < 2) {
                        continue;
                    }
                    filtereredTd.push(found_td[i]);
                }
                filteredRows.push(filtereredTd);
            });
            for (var i in filteredRows) {
                var filteredRow = filteredRows[i];
                for (var j in filteredRow) {
                    var filteredCell = $(filteredRow[j]);
                    j = parseInt(j);
                    var canCopy = false;

                    if (!filteredCell.is('.closed') && !filteredCell.is('.emptyTd') && filteredCell.find('.target-input').length > 0) {
                        canCopy = true;
                    }
                    if ([0, 1, 2].indexOf(j) > -1) {
                        buttons[0] = buttons[0] || canCopy;
                        //console.log("in 1", buttons[0]);
                    } else if ([3, 4, 5].indexOf(j) > -1) {
                        buttons[1] = buttons[1] || canCopy;
                        //console.log("in 2", buttons[1]);
                    } else if ([6, 7, 8].indexOf(j) > -1) {
                        buttons[2] = buttons[2] || canCopy;
                        //console.log("in 3", buttons[2]);
                    }
                }
            }
            // console.log(buttons);
            for (var i in buttons) {
                i = parseInt(i);
                if (!buttons[i]) {
                    $('#copy' + (i + 1)).attr({ 'disabled': 'disabled' });
                } else {
                    $('#copy' + (i + 1)).removeAttr('disabled');
                }
            }
        }
    }, {
        key: "copyTarget",
        value: function copyTarget(index) {

            var startPoint = this.onScreenDays.start - 1;
            var endPoint = this.onScreenDays.end - 1;
            var copy1Dynamic = false;
            var copy1 = false;
            var copy2 = true;
            var copy3 = true;
            var copyFrom, copyTo;
            if (startPoint > 0 && index == 1) {
                copy1 = true;
            }
            if (copy1) {
                copy1Dynamic = true;
            } else if (index == 2) {
                copyFrom = [0, 1, 2];
                copyTo = [3, 4, 5];
            } else if (index == 3) {
                copyFrom = [3, 4, 5];
                copyTo = [6, 7, 8];
            } else {
                copyFrom = null;
            }

            $('.cal_days tbody tr.data-value-row').each(function () {
                var found_td = $(this).find('td');
                var filtereredTd = [];
                for (var i = 0; i < found_td.length; i++) {
                    if (i < 3) {
                        continue;
                    }
                    filtereredTd.push(found_td[i]);
                }
                if (copyFrom && copyTo) {
                    for (var i in copyFrom) {
                        var cf = $(filtereredTd[copyFrom[i]]);
                        var ct = $(filtereredTd[copyTo[i]]);
                        var val = '';
                        if (!ct.is('.closed') && !ct.is('.emptyTd') && ct.find('.target-input').length > 0) {
                            val = cf.data('target');
                            ct.find('.target-input').val(val).blur();
                            ct.data('target', val);
                        }
                    }
                }
            });
            if (copy1Dynamic) {
                //console.log(index, this.targetRendered);
                var filteredRows = [];
                $('.cal_days tbody tr.data-value-row').each(function () {
                    var found_td = $(this).find('td');
                    var filtereredTd = [];
                    for (var i = 0; i < found_td.length; i++) {
                        if (i < 3) {
                            continue;
                        }
                        filtereredTd.push(found_td[i]);
                    }
                    filteredRows.push(filtereredTd);
                });
                for (var i in this.targetRendered) {
                    var targetLine = this.targetRendered[i];
                    var linesIndex = parseInt(targetLine.linesIndex);
                    var targetLineIndex = targetLine.targets[0].targetLineIndex;
                    var newStartIndex = targetLineIndex - 3;
                    if (newStartIndex < 0) {
                        newStartIndex = 0;
                    }
                    var copyFrom = [];
                    var copyTo = [0, 1, 2];
                    while (newStartIndex < targetLineIndex) {
                        copyFrom.push(newStartIndex);
                        newStartIndex++;
                    }
                    for (var j in copyFrom) {
                        var ix = copyFrom[j];
                        var ix2 = copyTo[j];
                        var val = '';
                        var objTarget = this.targetForLines[linesIndex].targets[ix];
                        var copy2Td = $(filteredRows[i][ix2]);
                        if (!copy2Td.is('.closed') && !copy2Td.is('.emptyTd') && copy2Td.find('.target-input').length > 0) {
                            val = objTarget.target;
                            copy2Td.find('.target-input').val(val).blur();
                            copy2Td.data('target', val);
                        }
                    }
                }
            }
        }
    }, {
        key: "updateDaysSelection",
        value: function updateDaysSelection() {
            var index = (this.onScreenDays - 1) * 3;
            var days = [];
            var targets = this.targetForLines[0].targets;
            for (var i = 0; i < targets.length; i += 3) {
                var shift1 = targets[i];
                var shift2 = targets[i + 1];
                var shift3 = targets[i + 2];
                if (shift1.closed && shift2.closed && shift3.closed) {
                    days.push({ 'closed': '*' });
                } else if (shift1.closed && shift2.closed) {
                    days.push({ 'closed': 2 / 3 });
                } else if (shift1.closed) {
                    days.push({ 'closed': 1 / 3 });
                } else {
                    days.push({ 'closed': null });
                }
            }
            for (var i in days) {
                var day = days[i];
                var parentWeek = null;
                var searchIndex = i;
                if (i > 6) {
                    searchIndex = i - 7;
                    parentWeek = $('.week_day_names ul:eq(1)');
                } else {
                    parentWeek = $('.week_day_names ul:eq(0)');
                }

                var pElement = parentWeek.find('li:eq(' + searchIndex + ')').find('p');
                if (day.closed == '*') {
                    pElement.addClass('closed');
                } else if (day.closed) {
                    var w = parseInt(day.closed * 100);
                    pElement.addClass('partial-bg').width(w + "%");
                } else {}
            }
            var cdayNo = this.onScreenDays.start - 1;
            var ldayNo = this.onScreenDays.end - 1;
            if (cdayNo > 6) {
                cdayNo = cdayNo - 7;
                ldayNo = ldayNo - 7;
                parentWeek = $('.week_day_names ul:eq(1)');
            } else {
                parentWeek = $('.week_day_names ul:eq(0)');
            }
            var liElement1 = parentWeek.find('li:eq(' + cdayNo + ')');
            var liElement2 = liElement1.next();
            var liElement3 = parentWeek.find('li:eq(' + ldayNo + ')');
            var width = liElement1.outerWidth() + liElement3.outerWidth();
            if (!liElement2.is(liElement3)) {
                width += liElement2.outerWidth();
            }
            $('.week_day_names .scroll-select').width(width).show();
            var left = liElement1.outerWidth() * (this.onScreenDays.start - 1);
            $('.week_day_names .scroll-select').css({ 'left': left });
        }
    }, {
        key: "updateTargetInObject",
        value: function updateTargetInObject(inputControl) {
            var lineIndex, targetIndex;
            targetIndex = inputControl.closest('td').data('target-index');
            lineIndex = inputControl.closest('tr').data('line-index');
            var val = inputControl.val();
            if (val != "") {
                val = this.getNumberPlain(val);
            }
            var targetVal = inputControl.closest('td').data('first-target');
            if (val == 0) {
                //val = '';
            } else if (val < 0) {
                inputControl.val('');
                val = 0;
            }
            inputControl.closest('td').data('target', val);
            if (val == 0) {
                //console.log("add", val);
                inputControl.closest('td').addClass('emptyValueTd');
            } else {
                //console.log("remove", val);
                inputControl.closest('td').removeClass('emptyValueTd');
            }

            if (targetIndex != null && lineIndex != null) {
                targetIndex = parseInt(targetIndex);
                lineIndex = parseInt(lineIndex);
                var shiftCapacity = parseInt(xmlDailyPlanning.targetForLines[lineIndex].shiftCapacity);
                xmlDailyPlanning.targetForLines[lineIndex].targets[targetIndex].target = val;
                var valid = shiftCapacity == 0 || shiftCapacity >= val;
                xmlDailyPlanning.targetForLines[lineIndex].targets[targetIndex].valid = valid;
                if (valid) {
                    inputControl.closest('td').removeClass('invalid');
                    this.errorControls["l-" + lineIndex + "-t-" + targetIndex] = null;
                } else {
                    inputControl.closest('td').addClass('invalid');
                    var error = this.translationError.Target;
                    $('.errorTarget').html('<p>' + error + '</p>');
                    this.errorControls["l-" + lineIndex + "-t-" + targetIndex] = 1;
                }
            }
            inputControl.val(this.getNumberFormatted(val));
            var overallValid = this.isValidTargets();
            //console.log(overallValid);
            if (overallValid) {
                //console.log(targetVal, val);
                if (targetVal == val) {
                    this.changedControls["l-" + lineIndex + "-t-" + targetIndex] = null;
                } else {
                    this.changedControls["l-" + lineIndex + "-t-" + targetIndex] = inputControl;
                }
                this.isAnyDataChanged();
            }
        }
    }, {
        key: "isAnyDataChanged",
        value: function isAnyDataChanged() {
            var found = false;
            $('.cal_days tbody td').removeClass('changed');
            for (var i in this.changedControls) {
                if (this.changedControls[i] != null) {
                    this.changedControls[i].closest('td').addClass('changed');
                    found = true;
                }
            }
            //console.log(found);
            if (found) {
                $('#btnSave').removeAttr('disabled');
                formDataChanged = true;
            } else {
                $('#btnSave').attr('disabled', 'disabled');
                formDataChanged = false;
            }
            return found;
        }
    }, {
        key: "isValidTargets",
        value: function isValidTargets() {
            var overallValid = true;
            var texts = [this.translationError.Target];
            for (var i in this.errorControls) {
                if (this.errorControls[i] != null) {
                    overallValid = false;
                    var keys = i.split("-");
                    var line = keys[1];
                    var target = keys[3];
                    var objLine = xmlDailyPlanning.targetForLines[line];
                    var objTarget = objLine.targets[target];
                    var date = this.daysFull["d" + objTarget.day].fullDate;
                    texts.push(" [ " + objLine.name + " | " + date + " ( " + objTarget.name + " ) ] ");
                }
            }
            $('.errorTarget').html(texts.join("<br/>"));
            if (!overallValid) {
                $('.errorTarget').show();
                $('#btnSave').attr('disabled', 'disabled');
            } else {
                $('.errorTarget').hide();
                $('#btnSave').removeAttr('disabled');
            }
            return overallValid;
        }
    }, {
        key: "getXml",
        value: function getXml() {
            var doc = this.xmlDoc;
            var forLineTags = doc.getElementsByTagName("forLine");
            for (var i = 0; i < forLineTags.length; i++) {
                var forLineTag = forLineTags[i];
                var targetTags = forLineTag.getElementsByTagName("target");
                for (var j = 0; j < targetTags.length; j++) {
                    var targetTag = targetTags[j];
                    var refObj = xmlDailyPlanning.targetForLines[i].targets[j];
                    targetTag.textContent = refObj.target;
                }
            }
            //console.log(this.xmlDoc.documentElement.innerHTML);
            var oSerializer = new XMLSerializer();
            var sXML = oSerializer.serializeToString(this.xmlDoc);
            return sXML;
        }
    }, {
        key: "registerEvents",
        value: function registerEvents() {
            var t = this;
            var scrollKeyDown = false,
                firstEvent,
                lastEvent,
                firstLeft;
            $('.days-left-nav').click(function () {
                t.moveLeft();
                return false;
            });
            $('.days-right-nav').click(function () {
                t.moveRight();
                return false;
            });
            $('.week_day_names li').click(function () {
                var ix = $(this).index();
                if ($(this).parent('ul').is('.second_list')) {
                    ix += 7;
                }
                if (ix > 11) {
                    ix = 11;
                }
                t.openDay(ix + 1);
            });
            $(window).mousedown(function (e) {

                if ($(e.target).is('.scroll-select')) {
                    scrollKeyDown = true;
                    firstEvent = e;
                    firstLeft = $('.scroll-select').position().left;
                }
            });
            $('.week_day_names').mousemove(function (e) {
                if (scrollKeyDown) {
                    lastEvent = e;
                    var variation = lastEvent.originalEvent.clientX - firstEvent.originalEvent.clientX;
                    var newPosition = parseInt(firstLeft + variation);
                    var wx = $('.week_day_names').width() - $('.scroll-select').width();
                    if (wx < newPosition) {
                        newPosition = wx;
                    } else if (newPosition < 0) {
                        newPosition = 0;
                    }
                    $('.scroll-select').css({ 'left': newPosition });
                    t.openDayBySelection();
                }
            });
            $(window).mouseup(function () {
                scrollKeyDown = false;
            });

            $('.cal_days .target-input').blur(function () {
                t.updateTargetInObject($(this));
            }).focus(function () {
                $(this).closest('.emptyValueTd').removeClass('emptyValueTd');
            }).keypress(function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                    return false;
                }
            }).blur();
            $('.cal_days .data-value-row td').click(function () {
                $(this).removeClass('emptyValueTd').find('.target-input').focus();
            });
        }
    }, {
        key: "registerMainEvents",
        value: function registerMainEvents() {
            var t = this;
            $('#copy1').click(function () {
                if (!confirm(t.translationError.CopyConfirm)) {
                    return false;
                }
                t.copyTarget(1);
                return false;
            });
            $('#copy2').click(function () {
                if (!confirm(t.translationError.CopyConfirm)) {
                    return false;
                }
                t.copyTarget(2);
                return false;
            });
            $('#copy3').click(function () {
                if (!confirm(t.translationError.CopyConfirm)) {
                    return false;
                }
                t.copyTarget(3);
                return false;
            });
            $('.btn-cancel').click(function () {
                $(this).attr('href', "?s=" + $('.search-input').val());
            });
        }
    }, {
        key: "getNumberFormatted",
        value: function getNumberFormatted(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    }, {
        key: "getNumberPlain",
        value: function getNumberPlain(x) {
            var parts = x.toString().split(",");
            return parseInt(parts.join(""));
        }
    }]);

    return XmlDailyPlanning;
}();