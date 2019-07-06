'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var XmlShopfloorDowntime = function () {
    function XmlShopfloorDowntime() {
        _classCallCheck(this, XmlShopfloorDowntime);

        this.parser = null;
        this.xmlDoc = null;
        this.downtimeHourId = null;
        this.machines = null;
        this.translation = null;
        this.minsOptions = null;
        this.downtimeDictionary = null;
        this.sendingXml = false;
        this.isReport = false;
    }

    _createClass(XmlShopfloorDowntime, [{
        key: 'init',
        value: function init(xmlString, options) {
            this.translation = options.translation;
            this.downtimeDictionary = options.downtimeDictionary;
            this.isReport = options.isReport;
            this.parser = new DOMParser();
            this.xmlDoc = this.parser.parseFromString(xmlString, "text/xml");
            this.minsOptions = this.getMinsOptions();
            this.extractData();
            this.render();
            this.registerEvents();
        }
    }, {
        key: 'getMinsOptions',
        value: function getMinsOptions(sel) {
            if (!sel) {
                sel = 0;
            }
            var m = '';
            for (var i = 0; i <= 60; i++) {
                var j = i < 10 ? '0' + i + ":00 mins" : i + ":00 mins";
                m += '<option ' + (sel == i ? 'selected' : '') + ' value="' + i + '">' + j + '</option>';
            }
            return m;
        }
        // extracts planning dataset data

    }, {
        key: 'extractData',
        value: function extractData() {
            this.forDate = this.xmlDoc.getElementsByTagName("forDate");
            if (this.forDate && this.forDate.length > 0) {
                this.forDate = this.forDate[0].textContent;
            } else {
                this.forDate = "";
            }
            var rowTags = this.xmlDoc.getElementsByTagName("row");
            var _rows = [];
            for (var i = 0; i < rowTags.length; i++) {
                var d = {};
                var rowTag = rowTags[i];

                d.machine = rowTag.getElementsByTagName("machine")[0].textContent;
                d.timeInterval = rowTag.getElementsByTagName("timeInterval")[0].textContent;
                d.totalDuration = rowTag.getElementsByTagName("totalDuration")[0].textContent;
                var reasons = [];
                var reasonTags = rowTag.getElementsByTagName("reason");
                for (var j = 0; j < reasonTags.length; j++) {
                    var reasonTag = reasonTags[j];
                    var id = reasonTag.attributes["id"] ? reasonTag.attributes["id"].value : null;
                    if (!id) {
                        continue;
                    }

                    var comment = reasonTag.getElementsByTagName("comment")[0].textContent;
                    var duration = reasonTag.getElementsByTagName("duration")[0].textContent;
                    reasons.push({ id: id, comment: comment, duration: duration });
                }
                d.reasons = reasons;
                _rows.push(d);
            }
            this.machines = _rows;
        }
    }, {
        key: 'findReasonElementById',
        value: function findReasonElementById(rowTag, idSearch) {
            var reasonTags = rowTag.getElementsByTagName("reason");
            for (var j = 0; j < reasonTags.length; j++) {
                var reasonTag = reasonTags[j];
                var id = reasonTag.attributes["id"] ? reasonTag.attributes["id"].value : null;
                if (!id || id == '') {
                    continue;
                } else if (id == idSearch) {
                    return reasonTag;
                }
            }
            return;
        }
    }, {
        key: 'createReasonTimeGroup',
        value: function createReasonTimeGroup(reasons) {
            var hReasons = '';
            if (!reasons || reasons.length == 0) {
                hReasons = this.getHtmlElement();
            } else {
                var hReasons = '';
                for (var i in reasons) {
                    var r = reasons[i];
                    hReasons += this.getHtmlElement(i, r);
                }
            }
            //console.log(hReasons);
            return hReasons;
        }
    }, {
        key: 'getHtmlElement',
        value: function getHtmlElement(i, r) {
            if (!r) {
                r = {};
            }
            var addReadonly = this.isReport ? ' readonly ' : '';
            i = i ? i : 0;
            var dfv = r.comment ? 'data-first-value="' + r.comment + '"' : '';
            var selectDowntime = '<select ' + dfv + addReadonly + ' class="reason" onchange="xmlShopfloorDowntime.updateDowntimeReason(this)" ><option value="">' + translation.placeholderMachine + '</option>';
            for (var id in this.downtimeDictionary) {
                var text = this.downtimeDictionary[id];
                selectDowntime += '<option value="' + text + '" ' + (r.comment == text ? 'selected' : '') + '>' + text + '</option>';
            }
            selectDowntime += '</select>';
            var hReasons = '<div class="input-group reasontimegroup"  data-index="' + i + '"' + (r.id ? ' data-id="' + r.id + '"' : '') + '>\
        <div class="selectable"\
        <div class="input-group-prepend">\
        ' + selectDowntime + '\
        <select' + addReadonly + ' data-first-value="' + r.duration + '" class="mins-select" onchange="xmlShopfloorDowntime.updateDowntimeMins(this)">' + (r.duration ? this.getMinsOptions(r.duration) : this.minsOptions) + '</select>\
        </div>\
        ' + (!this.isReport ? '&nbsp;&nbsp;<a onclick="return xmlShopfloorDowntime.deleteRow(this)" href="#"><i class="fa fa-trash"></i></a>' : '') + '\
        </div>';
            return hReasons;
        }
    }, {
        key: 'deleteRow',
        value: function deleteRow(link) {
            if (!confirm(this.translation.ConfirmDelete)) {
                return false;
            }
            $(link).closest('.reasontimegroup').addClass('deleted');
            this.updateDowntimeMins(link);
            return false;
        }
    }, {
        key: 'render',
        value: function render() {
            var h = '';
            for (var i in this.machines) {
                var m = this.machines[i];
                var hReasons = this.createReasonTimeGroup(m.reasons);
                h += '<tr data-index="' + i + '"><td>' + m.timeInterval + '</td><td>' + m.machine + '</td><td class="downtimeMins"></td><td class="tdReasonTime"><div class="_controls">' + hReasons + '</div><div class="errors"><p class="error-empty">' + this.translation.errorEmpty + '</p><p class="error-duration">' + this.translation.errorDuration + '</p></div></td><td class="actions-col"><button class="btn btn-add">' + this.translation.ADDNEW + '</button></td></tr>';
            }
            $('.downtimemins tbody').append(h);
            $('.downtimemins tbody').find('.reasontimegroup .mins-select').trigger('change');
            $('#forDateField').text(this.forDate);
        }
    }, {
        key: 'updateDowntimeReason',
        value: function updateDowntimeReason(t) {
            var valid1 = this.validateNotEmpty(t);
            if (valid1) {
                valid1 = this.validateTotals(t);
            }
            if (valid1 && this.isAnyDataChange()) {
                $('.btn.btn-save').removeAttr('disabled');
            } else {
                $('.btn.btn-save').attr('disabled', 'disabled');
            }
        }
    }, {
        key: 'updateDowntimeMins',
        value: function updateDowntimeMins(t) {

            var valid1 = this.validateNotEmpty(t);
            if (valid1) {
                valid1 = this.validateTotals(t);
            }
            if (valid1 && this.isAnyDataChange()) {
                $('.btn.btn-save').removeAttr('disabled');
            } else {
                $('.btn.btn-save').attr('disabled', 'disabled');
            }
            var index = $(t).closest('tr').data('index');

            var machine = this.machines[index];
            var total = machine.totalDuration;
            var reasonDuration = 0;
            $(t).closest('.tdReasonTime').find('.mins-select').each(function () {
                if (!$(this).closest('.reasontimegroup').is('.deleted')) {
                    reasonDuration += parseInt($(this).val());
                }
            });
            if (total < 10) {
                total = "0" + total;
            }
            if (reasonDuration <= 0) {
                reasonDuration = "0" + reasonDuration;
            }
            reasonDuration = reasonDuration + ":00";
            total = total + ":00";
            $(t).closest('tr').find('.downtimeMins').text(reasonDuration + "/" + total);
        }
    }, {
        key: 'isAnyDataChange',
        value: function isAnyDataChange() {
            var change = false;
            if (this.sendingXml) {
                return change;
            }
            $('.downtimemins .reasontimegroup').each(function () {
                var sReason = $(this).find('.reason');
                var sMins = $(this).find('.mins-select');
                var reasonsFirst = sReason.data('first-value');
                var minsFirst = sMins.data('first-value');
                if (!reasonsFirst || reasonsFirst != sReason.val()) {
                    change = true;
                }
                if (!minsFirst || minsFirst != sMins.val()) {
                    change = true;
                }
            });
            return change;
        }
    }, {
        key: 'validateTotals',
        value: function validateTotals(t) {
            var index = $(t).closest('tr').data('index');

            var machine = this.machines[index];
            var total = machine.totalDuration;
            var reasonDuration = 0;
            $(t).closest('.tdReasonTime').find('.mins-select').each(function () {
                if (!$(this).closest('.reasontimegroup').is('.deleted')) {
                    reasonDuration += parseInt($(this).val());
                }
            });
            var valid = reasonDuration == total;
            if (!valid) {
                $(t).closest('tr').find('.tdReasonTime').addClass('invalid');
                $(t).closest('tr').find('.tdReasonTime .error-duration').show();
            } else {
                $(t).closest('tr').find('.tdReasonTime .error-duration').hide();
                $(t).closest('tr').find('.tdReasonTime').removeClass('invalid');
            }
            return valid;
        }
    }, {
        key: 'validateNotEmpty',
        value: function validateNotEmpty(t) {
            var valid = true;
            if ($(t).closest('tr').find('.reasontimegroup:not(".deleted")').length == 0) {
                return valid;
            }
            //console.log( $(t).closest('tr').find('.tdReasonTime .reason'));
            $(t).closest('tr').find('.reasontimegroup:not(".deleted")').find('.reason').each(function () {
                if ($(this).val() == '') {
                    valid = false;
                }
            });
            $(t).closest('tr').find('.reasontimegroup:not(".deleted")').find('.mins-select').each(function () {
                if ($(this).val() == 0) {
                    valid = false;
                }
            });
            if (!valid) {
                $(t).closest('tr').find('.tdReasonTime').addClass('invalid');
                $(t).closest('tr').find('.tdReasonTime .error-empty').show();
            } else {
                $(t).closest('tr').find('.tdReasonTime .error-empty').hide();
                $(t).closest('tr').find('.tdReasonTime').removeClass('invalid');
            }
            return valid;
        }
    }, {
        key: 'registerEvents',
        value: function registerEvents() {
            var t = this;
            $('.tdReasonTime .mins-select').trigger('change');
            $('.btn-add').click(function () {
                var valid = t.validateNotEmpty(this);
                //console.log(valid);
                if (valid) {
                    var hReasons = t.createReasonTimeGroup();
                    //console.log(hReasons);
                    $(this).closest('tr').find('.tdReasonTime ._controls').append(hReasons);
                }

                return false;
            });
            $('.btn-save').click(function () {
                var valid = true;
                $('.downtimemins tbody tr .mins-select').each(function () {
                    var _valid = t.validateNotEmpty(this);
                    if (_valid) {
                        _valid = t.validateTotals(this);
                    }
                    valid = valid && _valid;
                });

                if (valid) {
                    t.updateXml();
                    t.sendingXml = true;
                    return true;
                }
                return false;
            });
            if (this.isReport) {
                $('.btn-save').hide();
            }
        }
    }, {
        key: 'updateXml',
        value: function updateXml() {
            var t = this;
            $('.downtimemins tbody tr').each(function () {
                var index = $(this).data('index');
                var rowTags = t.xmlDoc.getElementsByTagName("row");
                var rowTag = rowTags[index];
                var reasonsTag = null;
                var removeElems = [];
                if ($(this).find('.reasontimegroup:eq(0)').data('id')) {
                    reasonsTag = rowTag.getElementsByTagName('reasons')[0];
                } else {
                    reasonsTag = t.xmlDoc.createElement("reasons");
                    rowTag.appendChild(reasonsTag);
                }
                $(this).find('.reasontimegroup').each(function () {
                    if ($(this).is('.deleted')) {
                        removeElems.push($(this).closest('.reasontimegroup').data('id'));
                    } else {
                        var reason = $(this).find('.reason').val();
                        var duration = $(this).find('.mins-select').val();
                        if ($(this).data('id')) {
                            var rstg = reasonsTag.getElementsByTagName('reason');
                            var ix = $(this).data('index');
                            var rtg = rstg[ix];
                            rtg.getElementsByTagName('comment')[0].textContent = reason;
                            rtg.getElementsByTagName('duration')[0].textContent = duration;
                        } else {
                            var reasonTag = t.xmlDoc.createElement("reason");
                            var commentTag = t.xmlDoc.createElement("comment");
                            var durationTag = t.xmlDoc.createElement("duration");

                            var txt = t.xmlDoc.createTextNode(reason);
                            commentTag.appendChild(txt);

                            txt = t.xmlDoc.createTextNode(duration);
                            durationTag.appendChild(txt);

                            reasonTag.appendChild(commentTag);
                            reasonTag.appendChild(durationTag);
                            reasonsTag.appendChild(reasonTag);
                        }
                    }
                });
                //console.log(removeElems);
                for (var i in removeElems) {
                    var r = removeElems[i];
                    if (r && r != '') {
                        var e = t.findReasonElementById(rowTag, removeElems[i]);
                        //console.log(e);
                        if (e) {
                            reasonsTag.removeChild(e);
                        }
                    }
                }
            });
            var oSerializer = new XMLSerializer();
            var sXML = oSerializer.serializeToString(this.xmlDoc);
            //console.log(sXML);
            $('#xmlNode').val(sXML);
        }
    }]);

    return XmlShopfloorDowntime;
}();