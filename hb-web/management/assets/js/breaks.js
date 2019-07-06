"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

// contains the contents of a plain <To NextDay="Yes">03:15</To> tag (or correspondent "From" tag)
var HoursAndMinutes = function () {
    function HoursAndMinutes(hoursAndMinutes, nextDay) {
        _classCallCheck(this, HoursAndMinutes);

        var hm = hoursAndMinutes.split(":");
        this.hours = hm[0];
        this.minutes = hm[1];
        this.nextDay = nextDay;
    }

    _createClass(HoursAndMinutes, [{
        key: "getTimestampInSeconds",
        value: function getTimestampInSeconds() {
            var secondsFromDay = this.nextDay == 1 ? 24 * 60 * 60 : 0;
            return secondsFromDay + this.hours * 60 * 60 + this.minutes * 60;
        }
    }]);

    return HoursAndMinutes;
}();

// Contains from / to information for a break


var Break = function () {
    function Break(from, fromNextDay, to, toNextDay) {
        _classCallCheck(this, Break);

        this.from = from;
        this.fromTime = new HoursAndMinutes(from, fromNextDay);
        this.to = to;
        this.toTime = new HoursAndMinutes(to, toNextDay);
    }

    _createClass(Break, [{
        key: "getDurationInSeconds",
        value: function getDurationInSeconds() {
            return this.toTime.getTimestampInSeconds() - this.fromTime.getTimestampInSeconds();
        }
    }, {
        key: "isValid",
        value: function isValid() {
            return this.getDurationInSeconds() >= 0;
        }
    }, {
        key: "containsBreak",
        value: function containsBreak(abreak) {
            return abreak.fromTime.getTimestampInSeconds() >= this.fromTime.getTimestampInSeconds() && abreak.toTime.getTimestampInSeconds() <= this.toTime.getTimestampInSeconds();
        }
    }, {
        key: "overlapsBreak",
        value: function overlapsBreak(abreak) {
            var valid = abreak.isValid() && this.isValid() && (abreak.fromTime.getTimestampInSeconds() >= this.toTime.getTimestampInSeconds() || abreak.toTime.getTimestampInSeconds() <= this.fromTime.getTimestampInSeconds());
            return !valid;
        }
    }]);

    return Break;
}();

// Contains from to information for a shift, alongside breaks


var Shift = function Shift(name, breaks, breaksLocationIndex, from, fromNextDay, to, toNextDay) {
    _classCallCheck(this, Shift);

    this.period = new Break(from, fromNextDay, to, toNextDay);
    this.name = name;
    this.breaks = breaks;
    this.breaksLocationIndex = breaksLocationIndex; // Index of  shift's parent Breaks tag
};

var XmlBreaks = function () {
    function XmlBreaks() {
        _classCallCheck(this, XmlBreaks);

        this.parser = null;
        this.xmlDoc = null;
        this.shifts = null;
        this.startingWith = null;
        this.timeoutSeconds = null; // timeout when the form will expire
        this.initTime = null; // timestamp in seconds when the form was opened, will be used for timeout calculations
        this.location = null; // current location we are working on
        this.messages = null; // error messages + info messages
        this.breaksDurationPerShift = null; // total breaks in minutes per shift (typically 40 minutes)
        this.breaksEditing = [];
    }

    _createClass(XmlBreaks, [{
        key: "init",
        value: function init(xmlString, options) {
            this.parser = new DOMParser();
            this.xmlDoc = this.parser.parseFromString(xmlString, "text/xml");
            var timeoutNode = this.xmlDoc.getElementsByTagName("timeOut");
            this.startingWith = this.xmlDoc.getElementsByTagName("startingWith")[0].textContent;
            this.timeoutSeconds = parseInt(timeoutNode[0].textContent);
            this.initTime = new Date().getTime() / 1000;
            this.location = options.location;
            this.messages = options.messages;
            this.breaksDurationPerShift = options.breaksDurationPerShift;
            this.extractData();
            this.renderSubTotal();
            this.updateSubTotals();
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
    }, {
        key: "renderSubTotal",
        value: function renderSubTotal() {
            var html = '';
            for (var i in this.shifts) {
                var shift = this.shifts[i];
                html += '<p data-shift-name="' + shift.name + '"><strong>SUBTOTAL ' + shift.name + ': </strong><span></span></p>';
            }
            $('.subtotalfields').html(html);
        }
    }, {
        key: "updateSubTotals",
        value: function updateSubTotals() {
            var subtotals = this.getBreaksSubTotalSeconds();
            for (var name in subtotals) {
                $('.subtotalfields  [data-shift-name=' + name + '] span').text(subtotals[name] + ' minutes');
            }
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

        // extracts shift data

    }, {
        key: "extractData",
        value: function extractData() {
            var breaksNodes = this.xmlDoc.getElementsByTagName("breaks");
            var shifts = [];
            for (var i = 0; i < breaksNodes.length; i++) {
                var breaksNode = breaksNodes[i];
                if (breaksNode.attributes["location"].value != this.location) {
                    continue;
                }
                var shiftNodes = breaksNode.getElementsByTagName("shift");
                for (var j = 0; j < shiftNodes.length; j++) {
                    var shiftNode = shiftNodes[j];
                    var shiftName = shiftNode.attributes["name"].value;
                    var shiftFromNode = shiftNode.getElementsByTagName("from")[0];
                    var shiftToNode = shiftNode.getElementsByTagName("to")[0];

                    var shiftFrom = shiftFromNode.textContent;
                    var shiftTo = shiftToNode.textContent;

                    var shiftFromNextDay = shiftFromNode.attributes["nextDay"] && shiftFromNode.attributes["nextDay"].value == 'Yes' ? 1 : 0;
                    var shiftToNextDay = shiftToNode.attributes["nextDay"] && shiftToNode.attributes["nextDay"].value == 'Yes' ? 1 : 0;

                    var breaks = [];
                    var shiftData = new Shift(shiftName, breaks, i, shiftFrom, shiftFromNextDay, shiftTo, shiftToNextDay);
                    shifts.push(shiftData);
                    var breakNodes = shiftNode.getElementsByTagName("break");
                    for (var k = 0; k < breakNodes.length; k++) {
                        var breakNode = breakNodes[k];
                        var fromNode = breakNode.getElementsByTagName("from")[0];
                        var toNode = breakNode.getElementsByTagName("to")[0];
                        var breakFrom = fromNode.textContent;
                        var breakTo = toNode.textContent;
                        var breakFromNextDay = fromNode.attributes["nextDay"] && fromNode.attributes["nextDay"].value == 'Yes' ? 1 : 0;
                        var breakToNextDay = toNode.attributes["nextDay"] && toNode.attributes["nextDay"].value == 'Yes' ? 1 : 0;
                        var _break = new Break(breakFrom, breakFromNextDay, breakTo, breakToNextDay);
                        breaks.push(_break);
                    }
                }
            }
            this.shifts = shifts;
        }

        // get the html for a given shifts setup

    }, {
        key: "getHtml",
        value: function getHtml(readonly) {
            var shifts = this.shifts;
            var html = '';
            for (var i in shifts) {
                var shift = shifts[i];
                for (var j in shift.breaks) {
                    var _break = shift.breaks[j];
                    html += '<tr data-break-index="' + j + '" data-shift-index="' + i + '">\
                    <td>' + shift.name + '</td>\
                    <td>' + shift.period.from + '-' + shift.period.to + '</td>\
                    <td><span class="timeDisplay">' + _break.from + '</span><div class="timeBox" data-value="' + _break.from + '"><input type="text" class="fromTimeControl" value="" onchange = "xmlBreaks.updateFromTime(' + i + ', ' + j + ', this)"/></div></td>\
                    <td><span class="timeDisplay">' + _break.to + '</span><div class="timeBox" data-value="' + _break.to + '"><input type="text" class="toTimeControl" value="" onchange = "xmlBreaks.updateToTime(' + i + ', ' + j + ', this)"/></div></td>';
                    if (!readonly) {
                        html += '<td><button class="btn btn-edit">Edit</button><button class="btn btn-undo" onclick="return xmlBreaks.undoBreak(this)">Undo</button></td>';
                    }

                    html += '</tr>';
                }
            }
            return html;
        }
    }, {
        key: "updateFromTime",
        value: function updateFromTime(shiftIndex, breakIndex, _this) {
            //console.log(shiftIndex, breakIndex, _this);
            var breakRow = $(_this).closest('tr');
            var breakValidatingObject = this.getBreaksEditingObject(breakIndex, shiftIndex);
            if (!breakValidatingObject) return;

            var fromTime = _this.value;

            var shift = this.shifts[shiftIndex];
            var toTime = breakRow.find('.toTimeControl').val();
            var _fromTime = fromTime.split(":");
            var _toTime = toTime.split(":");
            var addNextDay = this.getAddNextDay(_fromTime, _toTime, shift);
            var addNextInFrom = addNextDay.from;

            breakValidatingObject.newBreak.from = fromTime;
            breakValidatingObject.newBreak.fromTime = new HoursAndMinutes(fromTime, addNextInFrom);
            this.updateSubTotals();
        }
    }, {
        key: "updateToTime",
        value: function updateToTime(shiftIndex, breakIndex, _this) {
            //console.log(shiftIndex, breakIndex, _this);
            var breakRow = $(_this).closest('tr');
            var breakValidatingObject = this.getBreaksEditingObject(breakIndex, shiftIndex);
            if (!breakValidatingObject) return;
            var toTime = _this.value;

            var shift = this.shifts[shiftIndex];
            var fromTime = breakRow.find('.fromTimeControl').val();

            var _fromTime = fromTime.split(":");
            var _toTime = toTime.split(":");
            var addNextDay = this.getAddNextDay(_fromTime, _toTime, shift);
            var addNextInTo = addNextDay.to;

            breakValidatingObject.newBreak.to = toTime;
            breakValidatingObject.newBreak.toTime = new HoursAndMinutes(toTime, addNextInTo);
            this.updateSubTotals();
        }
    }, {
        key: "getShiftByNameAndBreaksIndex",
        value: function getShiftByNameAndBreaksIndex(name, breaksLocationIndex) {
            for (var i in this.shifts) {
                var s = this.shifts[i];
                if (s.name == name && s.breaksLocationIndex == breaksLocationIndex) {
                    return s;
                }
            }
            return null;
        }

        // generates the XML with the modified situation

    }, {
        key: "getXml",
        value: function getXml() {
            var breaksNodes = this.xmlDoc.getElementsByTagName("breaks");
            for (var i = 0; i < breaksNodes.length; i++) {
                var breaksNode = breaksNodes[i];
                if (breaksNode.attributes["location"].value != this.location) {
                    continue;
                }
                var shiftNodes = breaksNode.getElementsByTagName("shift");
                for (var j = 0; j < shiftNodes.length; j++) {
                    var shiftNode = shiftNodes[j];
                    var breakNodes = shiftNode.getElementsByTagName("break");
                    var shiftName = shiftNode.attributes["name"].value;
                    var shift = this.getShiftByNameAndBreaksIndex(shiftName, i);
                    //  console.log(j, shift);
                    if (!shift) {
                        continue;
                    }
                    for (var k = 0; k < shift.breaks.length; k++) {
                        var _break = shift.breaks[k];
                        var breakNode = breakNodes[k];
                        var fromNode = breakNode.getElementsByTagName("from")[0];
                        var toNode = breakNode.getElementsByTagName("to")[0];

                        fromNode.textContent = _break.from;
                        toNode.textContent = _break.to;

                        // For Yes / No Format
                        //fromNode.attributes["NextDay"].value = _break.fromNextDay == 1 ? 'Yes' : 'No';
                        //toNode.attributes["NextDay"].value =  _break.toNextDay == 1 ? 'Yes' : 'No';
                        if (_break.fromTime.nextDay == 1) {
                            fromNode.setAttribute("nextDay", 'Yes');
                        } else {
                            if (fromNode.attributes["nextDay"]) {
                                fromNode.removeAttribute("nextDay");
                            }
                        }
                        if (_break.toTime.nextDay == 1) {
                            toNode.setAttribute("nextDay", 'Yes');
                        } else {
                            if (toNode.attributes["nextDay"]) {
                                toNode.removeAttribute("nextDay");
                            }
                        }
                    }
                }
            }
            //console.log(this.xmlDoc.documentElement.innerHTML);
            var oSerializer = new XMLSerializer();
            var sXML = oSerializer.serializeToString(this.xmlDoc);
            return sXML;
        }
        // breakIndex = index of break being changed (inside shift.breaks)

    }, {
        key: "isBreakInShiftInterval",
        value: function isBreakInShiftInterval(newBreak, breakIndex, shift, error) {
            if (!newBreak.isValid()) {
                //  console.log("not valid");
                error.message = this.messages.WrongInterval;
                return false;
            }
            if (!shift.period.containsBreak(newBreak)) {
                //  console.log("containsBreak not valid", newBreak);
                error.message = this.messages.Shift;
                return false;
            }
            //console.log("containsBreak valid", newBreak);
            return true;
        }
    }, {
        key: "getBreaksSubTotalSeconds",
        value: function getBreaksSubTotalSeconds() {
            var total = this.getBreaksSubTotal();
            var newObject = {};
            for (var i in this.shifts) {
                var shift = this.shifts[i];
                newObject[shift.name] = total['s_' + i] / 60;
            }
            return newObject;
        }
    }, {
        key: "getBreaksSubTotal",
        value: function getBreaksSubTotal() {
            var secondsSum = {};
            for (var shiftIndex in this.shifts) {
                var shift = this.shifts[shiftIndex];
                for (var i in shift.breaks) {
                    var breakValidatingObject = this.getBreaksEditingObject(i, shiftIndex);
                    // exclude editing rows
                    if (breakValidatingObject) {
                        //console.log("exclude ", breakValidatingObject);
                        continue;
                    }
                    var _break = shift.breaks[i];
                    if (!secondsSum['s_' + shiftIndex]) {
                        secondsSum['s_' + shiftIndex] = 0;
                    }
                    secondsSum['s_' + shiftIndex] += _break.getDurationInSeconds();
                    //console.log("sum1", secondsSum);
                }
            }

            //calculate editing breaks duration
            for (var i in this.breaksEditing) {
                var newEdit = this.breaksEditing[i];
                var shiftIndex = newEdit.shiftIndex;
                if (!secondsSum['s_' + shiftIndex]) {
                    secondsSum['s_' + shiftIndex] = 0;
                }
                var duration = newEdit.newBreak.getDurationInSeconds();
                // console.log(duration);
                secondsSum['s_' + shiftIndex] += duration;
            }
            return secondsSum;
        }

        // checks if fromTime, toTime is a valid break
        // breaksEditingIndex = index of row (inside this.breaksEditing)

    }, {
        key: "isBreaksDurationValid",
        value: function isBreaksDurationValid(error) {
            var secondsSum = this.getBreaksSubTotal();
            // console.log("new sum", secondsSum);
            for (var i in secondsSum) {
                var sum = secondsSum[i];
                if (sum != this.breaksDurationPerShift * 60) {
                    //  console.log(sum , this.breaksDurationPerShift , sum != this.breaksDurationPerShift * 60);
                    error.message = this.messages.BreaksDuration;
                    return false;
                }
            }

            return true;
        }

        // breaksEditingIndex = index of row (inside this.breaksEditing)
        // breakIndex = index of break being changed (inside shift.breaks)

    }, {
        key: "breaksDoNotOverlap",
        value: function breaksDoNotOverlap(breaksEditingIndex, shiftIndex, error) {
            var isTimeOverlap = false;
            var shift = this.shifts[shiftIndex];
            var newEdit = this.breaksEditing[breaksEditingIndex];
            var newBreak = newEdit.newBreak;
            for (var i in shift.breaks) {
                var breakValidatingObject = this.getBreaksEditingObject(i, shiftIndex);
                // exclude editing rows
                if (breakValidatingObject) {
                    continue;
                }
                var _break = shift.breaks[i];
                if (newBreak.overlapsBreak(_break)) {
                    isTimeOverlap = true;
                }
            }
            //find overlap in other editing breaks
            for (var i in this.breaksEditing) {
                // exclude breaksEditingIndex
                if (breaksEditingIndex == i) {
                    continue;
                }
                var anotherEdit = this.breaksEditing[i];
                var anotherBreak = anotherEdit.newBreak;
                if (newBreak.overlapsBreak(anotherBreak)) {
                    isTimeOverlap = true;
                }
            }
            if (isTimeOverlap) {
                error.message = this.messages.Overlap;
                return false;
            }
            return true;
        }
    }, {
        key: "getAddNextDay",
        value: function getAddNextDay(_fromTime, _toTime, shift, log) {
            var addNextInFrom = 0,
                addNextInTo = 0;
            if (shift.period.fromTime.nextDay || _fromTime[0] < _toTime[0] && shift.period.toTime.nextDay == 1 || _fromTime[0] < shift.period.fromTime.hours && shift.period.toTime.nextDay == 1) {
                addNextInFrom = 1;
            }
            if (addNextInFrom || _fromTime[0] > _toTime[0] && shift.period.toTime.nextDay == 1) {
                addNextInTo = 1;
            }
            if (log) {
                console.log(shift.period.fromTime.nextDay);
                console.log(_fromTime[0] < _toTime[0] && shift.period.toTime.nextDay == 1);
                console.log(_fromTime, _fromTime[0], shift.period.fromTime.hours, _fromTime[0] < shift.period.fromTime.hours);
                console.log(shift.period.toTime.nextDay);
            }

            return { from: addNextInFrom, to: addNextInTo };
        }
    }, {
        key: "getBreaksEditingObject",
        value: function getBreaksEditingObject(breakIndex, shiftIndex) {
            var result = null;
            for (var i in this.breaksEditing) {
                var newEdit = this.breaksEditing[i];
                if (newEdit.breakIndex == breakIndex && newEdit.shiftIndex == shiftIndex) {
                    result = newEdit;
                    break;
                }
            }
            return result;
        }
    }, {
        key: "validate",
        value: function validate(error) {
            if (!this.isBreaksDurationValid(error)) {
                return false;
            }
            for (var i in this.breaksEditing) {
                var newEdit = this.breaksEditing[i];
                var newBreak = newEdit.newBreak;
                var breakIndex = newEdit.breakIndex;
                var shift = newEdit.shift;
                var shiftIndex = newEdit.shiftIndex;
                if (!this.isBreakInShiftInterval(newBreak, breakIndex, shift, error)) {
                    return false;
                } else if (!this.breaksDoNotOverlap(i, shiftIndex, error)) {
                    return false;
                }
            }
            return true;
        }
    }, {
        key: "markEditNode",
        value: function markEditNode(breakRow) {
            breakRow.addClass('editing');
            var breakIndex = breakRow.attr('data-break-index');
            var shiftIndex = breakRow.attr('data-shift-index');
            var shift = this.shifts[shiftIndex];
            var fromTime = breakRow.find('.fromTimeControl').val();
            var toTime = breakRow.find('.toTimeControl').val();
            var _fromTime = fromTime.split(":");
            var _toTime = toTime.split(":");
            var addNextDay = this.getAddNextDay(_fromTime, _toTime, shift);
            var addNextInFrom = addNextDay.from;
            var addNextInTo = addNextDay.to;
            var newBreak = new Break(fromTime, addNextInFrom, toTime, addNextInTo);
            var newEdit = { breakIndex: breakIndex, shiftIndex: shiftIndex, shift: shift, newBreak: newBreak };
            this.breaksEditing.push(newEdit);
        }
    }, {
        key: "undoBreak",
        value: function undoBreak(_this) {
            var breakRow = $(_this).closest('tr');
            var breakIndex = breakRow.attr('data-break-index');
            var shiftIndex = breakRow.attr('data-shift-index');

            var fromInput = breakRow.find('.fromTimeControl');
            var toInput = breakRow.find('.toTimeControl');
            var fromTime = fromInput.closest('td').find('.timeDisplay').text();
            fromInput.val(fromTime);
            var toTime = toInput.closest('td').find('.timeDisplay').text();
            toInput.val(toTime);
            var index = this.getBreaksEditingIndex(breakIndex, shiftIndex);
            if (index > -1) {
                this.breaksEditing.splice(index, 1);
            }
            console.log("after edit", this.breaksEditing);

            breakRow.removeClass('editing');
        }
    }, {
        key: "saveBreak",
        value: function saveBreak() {
            var t = this;
            var temp = $('#tblBreaks tbody tr.editing');
            temp.each(function () {
                var breakRow = $(this);
                var breakIndex = breakRow.data('break-index');
                var shiftIndex = breakRow.data('shift-index');
                var shift = t.shifts[shiftIndex];
                var fromInput = breakRow.find('.fromTimeControl');
                var toInput = breakRow.find('.toTimeControl');
                var fromTime = fromInput.val();
                var toTime = toInput.val();
                var _fromTime = fromTime.split(":");
                var _toTime = toTime.split(":");
                fromInput.closest('td').find('.timeDisplay').text(fromTime);
                toInput.closest('td').find('.timeDisplay').text(toTime);

                var nextDay = t.getAddNextDay(_fromTime, _toTime, shift);

                var newBreak = new Break(fromTime, nextDay.from, toTime, nextDay.to);
                shift.breaks[breakIndex] = newBreak;
            });
        }
    }, {
        key: "markSaved",
        value: function markSaved() {
            var temp = $('#tblBreaks tbody tr.editing');
            temp.removeClass('editing');
            this.breaksEditing = [];
        }
    }, {
        key: "getBreaksEditingIndex",
        value: function getBreaksEditingIndex(breakIndex, shiftIndex) {
            var result = -1;
            for (var i in this.breaksEditing) {
                var newEdit = this.breaksEditing[i];
                if (newEdit.breakIndex == breakIndex && newEdit.shiftIndex == shiftIndex) {
                    result = i;
                    break;
                }
            }
            return i;
        }
    }]);

    return XmlBreaks;
}();