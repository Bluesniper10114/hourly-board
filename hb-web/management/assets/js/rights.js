var xmlRights = {
    parser: null,
    xmlDoc: null,
    results: null,
    timeoutSeconds: null,
    initTime: null,
    init: function(xmlString){
        this.parser = new DOMParser();
        this.xmlDoc = this.parser.parseFromString(xmlString, "text/xml");
        var timeoutNode = this.xmlDoc.getElementsByTagName("timeOut");
        //console.log(timeoutNode, xmlString, this.xmlDoc);
        this.timeoutSeconds = parseInt( timeoutNode[0].textContent );
        this.initTime = ((new Date()).getTime()) / 1000;
    },
    isTimeout: function(){
        var currentTime = ((new Date()).getTime()) / 1000;
        if(this.timeoutSeconds + this.initTime < currentTime){
            return true;
        }
        return false;
    },
    getTimeoutSeconds: function(){
        var currentTime = ((new Date()).getTime()) / 1000;
        var t = parseInt( this.timeoutSeconds - ( currentTime-this.initTime ) );
        return t;
    },
    getHtml: function(){
        var rightNodes = this.xmlDoc.getElementsByTagName("right");
        var results = [];
        for (var i=0;i< rightNodes.length;i++){
            var rightNode = rightNodes[i];
            var LevelID = rightNode.attributes ? rightNode.attributes["levelID"].value : 0;
            var level = rightNode.getElementsByTagName("level");
            var hrlySignoff = rightNode.getElementsByTagName("hourly-sign-off");
            var shiftSignoff = rightNode.getElementsByTagName("shift-sign-off");
            var obj = {
                id: LevelID,
                name: level[0].textContent,
                hourlysignoff: hrlySignoff[0].textContent,
                shiftsignoff: shiftSignoff[0].textContent,
                hourlysignoff_enabled: hrlySignoff[0].attributes["enabled"].value,
                shiftsignoff_enabled: shiftSignoff[0].attributes["enabled"].value
            };
            results.push(obj);
        }
        this.results = results;
        var html = '';
        for (var i in results){
            var right = results[i];
            var attr1 = right.hourlysignoff_enabled != 1 ? ' disabled' : '';
            attr1 += right.hourlysignoff == 1 ? ' checked' : '';

            var attr2 = right.shiftsignoff_enabled != 1 ? ' disabled' : '';
            attr2 += right.shiftsignoff == 1 ? ' checked' : '';

            html +=
            '<tr>\
                <td>' + right.name + '</td>\
                <td class="switch_block"><label class="switch"><input class="chk_hourlysignoff" type="checkbox"' + attr1 + '><span class="slider round"></span></label></td>\
                <td class="switch_block"><label class="switch"><input class="chk_shiftsignoff" type="checkbox"' + attr2 + '><span class="slider round"></span></label></td>\
            </tr>';

        }
        return html;
    },
    getXml: function(){
        var results = this.results;
        $('#tblRights tbody tr').each(function(i, v){
            var chk_hourlysignoff = $(this).find('.chk_hourlysignoff');
            var chk_shiftsignoff = $(this).find('.chk_shiftsignoff');
            if (!chk_hourlysignoff.attr('disabled')){
                results[i].hourlysignoff = chk_hourlysignoff.is(':checked') ? 1 : 0;
            }
            if (!chk_shiftsignoff.attr('disabled')){
                results[i].shiftsignoff = chk_shiftsignoff.is(':checked') ? 1 : 0;
            }
        });
        return this.generateXml(results);
    },
    generateXml: function(results){
        var rightNodes = this.xmlDoc.getElementsByTagName("right");
        for (var i=0;i< rightNodes.length;i++){
            var rightNode = rightNodes[i];
            var hrlySignoff = rightNode.getElementsByTagName("hourly-sign-off");
            var shiftSignoff = rightNode.getElementsByTagName("shift-sign-off");
            hrlySignoff[0].textContent = results[i].hourlysignoff;
            shiftSignoff[0].textContent = results[i].shiftsignoff;
        }
        //console.log(this.xmlDoc.documentElement.innerHTML);
        var oSerializer = new XMLSerializer();
        var sXML = oSerializer.serializeToString(this.xmlDoc);
        return sXML;
    }
};