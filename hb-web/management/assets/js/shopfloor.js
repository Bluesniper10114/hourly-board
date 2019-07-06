"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var XmlShopfloor = function () {
    function XmlShopfloor() {
        _classCallCheck(this, XmlShopfloor);

        this.parser = null;
        this.xmlDoc = null;
        this.headerData = null;
        this.bodyData = null;
        this.messages = null; // error messages + info messages
        this.shiftLogId = null;
        this.translationBody = null;
        this.isReport = null;
    }

    _createClass(XmlShopfloor, [{
        key: "init",
        value: function init(xmlString, options) {
            this.translationBody = options.translationBody;
            this.downtimeUrl = options.downtimeUrl;
            this.isReport = options.isReport;
            this.parser = new DOMParser();
            this.xmlDoc = this.parser.parseFromString(xmlString, "text/xml");
            this.extractData();
            this.render();
            this.registerEvents();
        }
        // extracts planning dataset data

    }, {
        key: "extractData",
        value: function extractData() {
            var billboard = this.xmlDoc;
            var header = {};
            header.date = billboard.getElementsByTagName("date")[0].textContent;
            header.shift = billboard.getElementsByTagName("shift")[0].textContent;
            header.lineName = billboard.getElementsByTagName("lineName")[0].textContent;
            header.locationName = billboard.getElementsByTagName("locationName")[0].textContent;
            header.deliveryTime = billboard.getElementsByTagName("deliveryTime")[0].textContent;
            header.maxHourProduction = billboard.getElementsByTagName("maxHourProduction")[0].textContent;
            this.headerData = header;

            var hours = this.xmlDoc.getElementsByTagName("hours")[0];
            var hourNodes = this.xmlDoc.getElementsByTagName("hour");
            this.shiftLogId = hours.attributes["shiftLogSignOffID"] ? hours.attributes["shiftLogSignOffID"].value : 0;
            var _hours = [];
            for (var i = 0; i < hourNodes.length; i++) {
                var d = {};
                var hourNode = hourNodes[i];
                d.id = hourNode.attributes["id"] ? hourNode.attributes["id"].value : 0;
                d.firstOpen = hourNode.attributes["firstOpen"] ? true : false;

                d.hourInterval = hourNode.getElementsByTagName("hourInterval")[0].textContent;
                d.target = hourNode.getElementsByTagName("target")[0].textContent;
                d.cumulativeTarget = hourNode.getElementsByTagName("cumulativeTarget")[0].textContent;
                d.achieved = hourNode.getElementsByTagName("achieved")[0].textContent;
                d.cumulativeAchieved = hourNode.getElementsByTagName("cumulativeAchieved")[0].textContent;
                d.defects = hourNode.getElementsByTagName("defects")[0].textContent;
                d.downtime = hourNode.getElementsByTagName("downtime")[0].textContent;
                d.comment = hourNode.getElementsByTagName("comment")[0].textContent;
                d.escalated = hourNode.getElementsByTagName("escalated")[0].textContent;
                d.signoff = hourNode.getElementsByTagName("signoff")[0].textContent;
                d.closed = hourNode.attributes["closed"] && hourNode.attributes["closed"].value == 'yes' ? true : false;
                _hours.push(d);
            }
            this.bodyData = _hours;
        }
    }, {
        key: "render",
        value: function render() {
            $('.shopheader .labelDate').text(this.headerData.date);
            $('.shopheader .labelDT').text(this.headerData.deliveryTime);
            $('.shopheader .labelShift').text(this.headerData.shift);
            $('.shopheader .labelMHP').text(this.headerData.maxHourProduction);
            $('.shopheader .labelLine').text(this.headerData.lineName);
            $('.shopheader .labelLoc').text(this.headerData.locationName);
            var html = '',
                targetTotal = 0,
                achievedTotal = 0,
                defectsTotal = 0,
                downtimeTotal = 0;
            for (var i in this.bodyData) {
                var d = this.bodyData[i];

                var commentText = d.comment;
                var escalatedText = d.escalated;
                var approvalText = d.signoff;

                if (d.firstOpen) {
                    commentText = '<a href="#" class="commentLink">' + this.translationBody.Comments + '</a>';
                    escalatedText = '<a href="#" class="escalatedLink">' + this.translationBody.Escalated + '</a>';
                    approvalText = '<input type="text" class="form-control supervisor" />';
                }
                var achievedComparison = parseInt(d.achieved) < parseInt(d.target);
                var cumulativeComparison = parseInt(d.cumulativeAchieved) < parseInt(d.cumulativeTarget);
                var dhtml = d.downtime > 0 ? '<a href="' + this.downtimeUrl + '/' + d.id + (this.isReport ? '?report=1' : '') + '" class="downtimeLink">' + d.downtime + '</a>' : 0;
                html += '<tr data-hourly-id="' + d.id + '">\
            <td>' + d.hourInterval + '</td>\
            <td class="labelStrongBlack">' + d.target + '</td>\
            <td class="' + (achievedComparison ? 'labelStrongRed' : 'labelStrongGreen') + '">' + d.achieved + '</td>\
            <td class="labelStrongBlack">' + d.cumulativeTarget + '</td>\
            <td class="' + (cumulativeComparison ? 'labelStrongRed' : 'labelStrongGreen') + '">' + d.cumulativeAchieved + '</td>\
            <td class="labelStrongBlack">' + d.defects + '</td>\
            <td class="labelStrongBlue">' + dhtml + '</td>\
            <td id="rcomments' + d.id + '" class="small_comment">' + commentText + '</td>\
            <td id="rescalated' + d.id + '" class="small_comment">' + escalatedText + '</td>\
            <td>' + approvalText + '</td>\
            ';

                targetTotal += parseInt(d.target);
                achievedTotal += parseInt(d.achieved);
                defectsTotal += parseInt(d.defects);
                downtimeTotal += parseInt(d.downtime);
            }
            $('.shopfloor tbody').html(html);
            $('.shopfloor tfoot .targetTotal').text(targetTotal);
            $('.shopfloor tfoot .achievedTotal').text(achievedTotal);
            $('.shopfloor tfoot .defectsTotal').text(defectsTotal);
            $('.shopfloor tfoot .downtimeTotal').text(downtimeTotal);
        }
    }, {
        key: "registerEvents",
        value: function registerEvents() {
            this.registerCommentEvents();
            this.registerEscalatedEvents();
        }
    }, {
        key: "registerCommentEvents",
        value: function registerCommentEvents() {
            $('.commentLink').click(function () {
                $('#commentsDialog').modal('show');
                var id = $(this).closest('tr').data('hourly-id');
                $('#hourlyIdc').val(id);
                return false;
            });
        }
    }, {
        key: "registerEscalatedEvents",
        value: function registerEscalatedEvents() {
            $('.escalatedLink').click(function () {
                $('#escalatedDialog').modal('show');
                var id = $(this).closest('tr').data('hourly-id');
                $('#hourlyIde').val(id);
                return false;
            });
        }
    }]);

    return XmlShopfloor;
}();