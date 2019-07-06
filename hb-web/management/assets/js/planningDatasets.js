"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var XmlDatasets = function () {
    function XmlDatasets() {
        _classCallCheck(this, XmlDatasets);

        this.parser = null;
        this.xmlDoc = null;
        this.rows = null;
        this.timeoutSeconds = null; // timeout when the form will expire
        this.initTime = null; // timestamp in seconds when the form was opened, will be used for timeout calculations
        this.location = null;
        this.dates = {};
        this.shifts = {};
        this.lines = {};
        this.dateSelect = null;
        this.tbody = null;
        this.lineSelect = null;
        this.shiftSelect = null;
        this.selectedLine = "";
        this.selectedDates = [];
        this.selectedShifts = [];
        this.messageField = null;
        this.translationTitles = null;
        this.isRowsRendered = false;
        this.routes = null;
    }

    _createClass(XmlDatasets, [{
        key: "init",
        value: function init(xmlString, options) {
            this.parser = new DOMParser();
            this.xmlDoc = this.parser.parseFromString(xmlString, "text/xml");
            var timeoutNode = this.xmlDoc.getElementsByTagName("timeOut");
            this.timeoutSeconds = parseInt(timeoutNode[0].textContent);
            this.initTime = new Date().getTime() / 1000;
            this.location = options.location;
            this.messages = options.messages;
            this.dateSelect = options.dateSelect;
            this.lineSelect = options.lineSelect;
            this.shiftSelect = options.shiftSelect;
            this.tbody = options.tbody;
            this.breaksDurationPerShift = options.breaksDurationPerShift;
            this.messageField = options.messageField;
            this.translationTitles = options.translationTitles;
            this.routes = options.routes;
            this.extractData();
            this.updateDates();
            this.updateLine();
            this.updateShift();

            this.registerEvents();
        }
    }, {
        key: "registerEvents",
        value: function registerEvents() {
            var t = this;
            this.lineSelect.change(function () {
                t.selectedLine = $(this).val();
                t.render();
            }).trigger('change');
            this.dateSelect.change(function () {
                t.selectedDates = $(this).val();
                t.render();
            }).trigger('change');
            this.shiftSelect.change(function () {
                t.selectedShifts = $(this).val();
                t.render();
            });
        }
        // checks if the form should timeout

    }, {
        key: "isTimeout",
        value: function isTimeout() {
            var currentTime = new Date().getTime() / 1000;
            if (this.timeoutSeconds + this.initTime < currentTime) {
                return true;
            }
            return false;
        }
        // gets number of seconds remaining to timeout
        // show this on the user's form to allow them to save

    }, {
        key: "getTimeoutSeconds",
        value: function getTimeoutSeconds() {
            var currentTime = new Date().getTime() / 1000;
            var t = parseInt(this.timeoutSeconds - (currentTime - this.initTime));
            return t;
        }

        // extracts planning dataset data

    }, {
        key: "extractData",
        value: function extractData() {
            var rowNodes = this.xmlDoc.getElementsByTagName("row");
            var rows = [];
            for (var i = 0; i < rowNodes.length; i++) {
                var rowNode = rowNodes[i];
                if (rowNode.attributes["location"].value != this.location) {
                    continue;
                }

                var open = rowNode.attributes["open"].value == 'Yes' ? true : false;
                var lineTag = rowNode.getElementsByTagName("line")[0];
                var lineId = lineTag.attributes["id"].value;
                var line = lineTag.textContent;
                var date = rowNode.getElementsByTagName("date")[0].textContent;
                var shift = rowNode.getElementsByTagName("shift")[0].textContent;
                var qtyHour_1 = rowNode.getElementsByTagName("qtyHour_1")[0].textContent;
                var qtyHour_2 = rowNode.getElementsByTagName("qtyHour_2")[0].textContent;
                var qtyHour_3 = rowNode.getElementsByTagName("qtyHour_3")[0].textContent;
                var qtyHour_4 = rowNode.getElementsByTagName("qtyHour_4")[0].textContent;
                var qtyHour_5 = rowNode.getElementsByTagName("qtyHour_5")[0].textContent;
                var qtyHour_6 = rowNode.getElementsByTagName("qtyHour_6")[0].textContent;
                var qtyHour_7 = rowNode.getElementsByTagName("qtyHour_7")[0].textContent;
                var qtyHour_8 = rowNode.getElementsByTagName("qtyHour_8")[0].textContent;
                var qtyTotal = rowNode.getElementsByTagName("qtyTotal")[0].textContent;
                var type = rowNode.getElementsByTagName("type")[0].textContent;
                var billboard = rowNode.getElementsByTagName("billboard")[0].textContent;
                var dailyTargetID = rowNode.attributes["dailyTargetID"].value;
                var row = { open: open, lineId: lineId, line: line, date: date, shift: shift, qtyHour_1: qtyHour_1, qtyHour_2: qtyHour_2, qtyHour_3: qtyHour_3, qtyHour_4: qtyHour_4, qtyHour_5: qtyHour_5, qtyHour_6: qtyHour_6, qtyHour_7: qtyHour_7, qtyHour_8: qtyHour_8, qtyTotal: qtyTotal, type: type, billboard: billboard, dailyTargetID: dailyTargetID };

                rows.push(row);

                if (this.dates[date]) {
                    this.dates[date]++;
                } else {
                    this.dates[date] = 1;
                }

                if (this.lines[line]) {
                    this.lines[line]++;
                } else {
                    this.lines[line] = 1;
                }

                if (this.shifts[shift]) {
                    this.shifts[shift]++;
                } else {
                    this.shifts[shift] = 1;
                }
            }
            this.rows = rows;
        }
    }, {
        key: "render",
        value: function render() {
            var h = '';
            if (this.selectedLine == '' && this.selectedDates.length == 0) {
                this.messageField.text(this.messages.SelectLineAndDate).show();
            } else if (this.selectedDates.length == 0) {
                this.messageField.text(this.messages.SelectDate).show();
            } else if (this.selectedLine.length == 0) {
                this.messageField.text(this.messages.SelectLine).show();
            } else {
                h = this.getHtml();
                if (!this.isRowsRendered) {
                    this.messageField.text(this.messages.EmptyRow).show();
                } else {
                    this.messageField.text('').hide();
                }
            }
            this.tbody.html(h);
        }

        // get the html  

    }, {
        key: "getHtml",
        value: function getHtml() {
            var rows = this.rows;
            var html = '',
                isRows = false;

            for (var i in rows) {
                var row = rows[i];
                if (this.selectedLine != row.line) {
                    continue;
                }
                var foundDateIndex = this.selectedDates.indexOf(row.date);
                if (foundDateIndex < 0) {
                    continue;
                }
                if (this.selectedShifts.length > 0) {
                    var foundShiftIndex = this.selectedShifts.indexOf(row.shift);
                    if (foundShiftIndex < 0) {
                        continue;
                    }
                }
                var clsBg = row.open ? '' : ' class="closed-bg"';
                var billboardHtml = '<input onchange="xmlDatasets.changeBillboard(this)" type="radio" class="billboardCheck" ' + (row.billboard == "Yes" ? 'checked="checked"' : '') + ' />';
                html += '<tr data-tid="' + row.dailyTargetID + '" data-row-index="' + i + '" >\
                <td>' + row.line + '</td>\
                <td>' + row.date + '</td>\
                <td>' + row.type + '</td>\
                <td>' + billboardHtml + '</td>\
                <td' + clsBg + '>' + row.shift + (row.open ? '' : '(closed)') + '</td>\
                <td' + clsBg + '>' + row.qtyHour_1 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_2 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_3 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_4 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_5 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_6 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_7 + '</td>\
                <td' + clsBg + '>' + row.qtyHour_8 + '</td>\
                <td' + clsBg + '>' + row.qtyTotal + '</td>\
                <td class="no_border">';
                var url;
                // console.log(row.type);
                if (row.type !== 'PN') {
                    url = this.routes.Day;
                } else {
                    url = this.routes.PartNumber;
                }
                html += '<a class="btn btn-edit" href="' + url + '?line=' + row.lineId + '&date=' + row.date + '">' + this.translationTitles.Edit + '</a>';
                isRows = true;
                html += '</td></tr>';
            }
            if (!isRows) {
                return '<tr><td colspan="14">' + this.messages.EmptyRow + '</td></tr> ';
            }
            this.isRowsRendered = isRows;
            return html;
        }
    }, {
        key: "updateDates",
        value: function updateDates() {
            var html = '';
            var dates = this.getSortedDates();
            for (var i in dates) {
                var date = dates[i];
                html += '<option value="' + date + '">' + date + '</option>';
            }
            this.dateSelect.append(html).select2();;
        }
    }, {
        key: "getSortedDates",
        value: function getSortedDates() {
            var dates = [];
            for (var i in this.dates) {
                dates.push(i);
            }
            dates.sort(function (a, b) {
                return a > b;
            });
            return dates;
        }
    }, {
        key: "updateLine",
        value: function updateLine() {
            var html = '<option value="">' + this.translationTitles.SELECT + '</option>';
            for (var line in this.lines) {
                html += '<option value="' + line + '">' + line + '</option>';
            }
            this.lineSelect.append(html).select2();;
        }
    }, {
        key: "updateShift",
        value: function updateShift() {
            var html = '';
            for (var shift in this.shifts) {
                html += '<option value="' + shift + '" selected>' + shift + '</option>';
            }
            this.shiftSelect.append(html).select2();;
        }
    }, {
        key: "changeBillboard",
        value: function changeBillboard(input) {
            if (!input.checked) {
                input.checked = true;
                // return false;
            }
            var dailyTargetID = $(input).closest('tr').data('tid');
            var t = this;
            var form_data = new FormData();
            form_data.append('dailyTargetID', dailyTargetID);
            //console.log(t.urlRefresh);
            $.ajax({
                url: t.routes.billboardUpdate,
                dataType: 'json', // what to expect back from the PHP script, if anything
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function success(php_script_response) {

                    if (php_script_response.errorHtml) {
                        $('.error-msg-wrapp').html(php_script_response.errorHtml);
                        $('document').scrollTop(0);
                    } else {
                        window.location.href = window.location.href;
                        $('.error-msg-wrapp').html('');
                        window.location.href = window.location.href;
                    }
                }
            });
            return false;
        }
    }]);

    return XmlDatasets;
}();