'use strict';

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var ProdutionLines = function () {
    function ProdutionLines(options) {
        _classCallCheck(this, ProdutionLines);

        this.translationErrors = options.translationErrors;
        this.translationLabels = options.translationLabels;
        this.parser = new DOMParser();
        this.xmlDoc = this.parser.parseFromString(options.xmlInput, "text/xml");
        this.lineSelect = options.lineSelect;
        this.cellSelect = options.cellSelect;
        this.tableMain = options.tableMain;
        this.tableDetail = options.tableDetail;
        this.selectedLine = '';
        this.selectedCell = '';
        this.location = options.location;
        this.messageField = options.messageField;
    }

    _createClass(ProdutionLines, [{
        key: 'init',
        value: function init() {
            var t = this;
            this.parseXml();

            this.updateLineOptions();
            this.updateCellOptions();
            this.lineSelect.change(function () {
                t.updateCellOptions()
                t.filterByLine(this.value);
            }).trigger('change');
            this.cellSelect.change(function () {
                t.filterByCell(this.value);
            }).trigger('change');
        }
    }, {
        key: 'parseXml',
        value: function parseXml() {
            var lineNodes = this.xmlDoc.getElementsByTagName("line");
            var cellNodes = this.xmlDoc.getElementsByTagName("cell");
            var machineNodes = this.xmlDoc.getElementsByTagName("machine");
            var lines = {};
            var cells = [];
            var machines = [];

            for (var i = 0; i < lineNodes.length; i++) {
                var lineNode = lineNodes[i];
                var locationNode = lineNode.getElementsByTagName("location");
                if (locationNode.length == 0) {
                    continue;
                }
                var location = locationNode[0].textContent;
                if (location != this.location) {
                    continue;
                }
                var name = lineNode.getElementsByTagName("name")[0].textContent;
                lines[name] = { descr: '' };
            }

            for (var i = 0; i < cellNodes.length; i++) {
                var cellNode = cellNodes[i];
                var locationNode = cellNode.getElementsByTagName("location");
                if (locationNode.length == 0) {
                    continue;
                }
                var location = locationNode[0].textContent;
                if (location != this.location) {
                    continue;
                }
                var name = cellNode.getElementsByTagName("name")[0].textContent;
                var line = cellNode.getElementsByTagName("line")[0].textContent;
                cells.push({ name: name, line: line });
            }

            for (var i = 0; i < machineNodes.length; i++) {
                var machineNode = machineNodes[i];
                var locationNode = machineNode.getElementsByTagName("location");
                if (locationNode.length == 0) {
                    continue;
                }
                var location = locationNode[0].textContent;
                if (location != this.location) {
                    continue;
                }
                var name = machineNode.getElementsByTagName("name")[0].textContent;
                var line = machineNode.getElementsByTagName("line")[0].textContent;
                var cell = machineNode.getElementsByTagName("cell")[0].textContent;
                var description = machineNode.getElementsByTagName("description")[0].textContent;
                var reference = machineNode.getElementsByTagName("reference")[0].textContent;
                var previousMachine = machineNode.getElementsByTagName("previousMachine")[0].textContent;
                var eol = machineNode.getElementsByTagName("eol")[0].textContent;
                var routing = machineNode.getElementsByTagName("routing")[0].textContent;
                var capacity = machineNode.getElementsByTagName("capacity")[0].textContent;
                machines.push({ name: name, line: line, cell: cell, description: description, reference: reference, previousMachine: previousMachine, eol: eol, routing: routing, capacity: capacity });
            }
            this.lines = lines;
            this.cells = cells;
            this.machines = machines;
        }
    }, {
        key: 'updateCellOptions',
        value: function updateCellOptions() {
            var html = '<option value="">' + this.translationLabels.EmptyOption + '</option>';
            var selectedLine = this.lineSelect.val();
            if (selectedLine != '') {
                for (var i in this.cells) {
                    var cell = this.cells[i];
                    var name = cell.name;
                    if (cell.line == selectedLine) {
                        html += '<option value="' + name + '">' + name + '</option>';
                    }
                }
            }

            this.cellSelect.html(html);
        }
    }, {
        key: 'updateLineOptions',
        value: function updateLineOptions() {
            var html = '';
            for (var name in this.lines) {
                html += '<option value="' + name + '">' + name + '</option>';
            }
            this.lineSelect.append(html);
        }
    }, {
        key: 'filterByLine',
        value: function filterByLine(selectedLine) {
            this.cellSelect.val('');
            this.selectedLine = selectedLine;
            this.selectedCell = '';
            this.filterData();
        }
    }, {
        key: 'filterByCell',
        value: function filterByCell(selectedCell) {
            this.selectedCell = selectedCell;
            this.filterData();
        }
    }, {
        key: 'filterData',
        value: function filterData() {
            this.updateMainTable();
            this.showMessgae();
        }
    }, {
        key: 'showMessgae',
        value: function showMessgae() {
            if (this.selectedLine == '') {
                this.messageField.text(this.translationErrors.SelectLineAndCell).show();
            } else if (this.selectedCell == '') {
                this.messageField.text(this.translationErrors.SelectCell).show();
            } else {
                this.messageField.text('').hide();
            }
        }
    }, {
        key: 'updateMainTable',
        value: function updateMainTable() {
            var html = '';
            var dataRows = 0;
            if (this.selectedLine != '' && this.selectedCell != '') {
                for (var i in this.machines) {
                    var machine = this.machines[i];
                    if (this.selectedLine == '' || machine.line == this.selectedLine) {
                        if (this.selectedCell == '' || machine.cell == this.selectedCell) {
                            dataRows++;
                            var addClass = machine.eol == 1 ? 'class="red_row"' : '';
                            html += '<tr ' + addClass + '><td>' + machine.name + '</td><td>' + machine.line + '</td><td>' + machine.cell + '</td><td>' + machine.eol + '</td><td><button onclick="produtionLines.updateInnerTable(' + i + ')">View</button></td></tr>';
                        }
                    }
                }
            }
            if (dataRows == 0) {
                html += '<tr><td colspan="4">' + this.translationErrors.EmptyRow + '</td></tr>';
            }
            this.tableMain.find('tbody').html(html);
        }
    }, {
        key: 'updateInnerTable',
        value: function updateInnerTable(index) {
            var html = '';
            var machine = this.machines[index];
            if (this.selectedLine == '' || machine.line == this.selectedLine) {
                if (this.selectedCell == '' || machine.cell == this.selectedCell) {
                    html += '<tr><td>' + machine.name + '</td><td>' + machine.reference + '</td><td>' + machine.routing + '</td><td>' + machine.capacity + '</td><td>' + machine.description + '</td></tr>';
                }
            }
            this.tableDetail.find('tbody').html(html);
            this.tableDetail.show();
            $(window).scrollTop(this.tableDetail.offset().top);
        }
    }]);

    return ProdutionLines;
}();