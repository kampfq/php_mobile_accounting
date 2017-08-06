/*
* Hilfsfunktionen
*/
var util = util || {};

// Ganzzahlige Division
util.intDivision = function(dividend, divisor) {
    return (dividend/divisor)-(dividend%divisor)/divisor;
};

// Datum von 2013-01-01 nach 01.01.2013 umformatieren
util.formatDateAtG = function(dateStringIn) {
    if(dateStringIn.length != 10) return dateStringIn;
    var dateStringOut = dateStringIn.substr(8,2)+"." +dateStringIn.substr(5,2)+"." +dateStringIn.substr(0,4);

    return dateStringOut;
};

// Fehlermeldung ausgeben
util.showErrorMessage = function(error, message) {
    if(!!message) {
        util.showMessage(message+": "+error.status+" "+error.statusText);
    } else {
        util.showMessage('Fehler aufgetreten: '+error.status+" "+error.statusText);
    }
    //console.log(error);
    //console.log(JSON.stringify(error));
};

util.showMessage = function(msg){
        $("<div class='ui-loader ui-overlay-shadow ui-body-e ui-corner-all'><h3>"+msg+"</h3></div>")
            .css({ display: "block",
                opacity: 0.90,
                position: "fixed",
                padding: "7px",
                "text-align": "center",
                width: "270px",
                left: ($(window).width() - 284)/2,
                top: $(window).height()/2 })
            .appendTo( $.mobile.pageContainer ).delay( 1500 )
            .fadeOut( 400, function(){
                $(this).remove();
            });
};

// Ersetzt < mit &lt; und > mit &gt;
util.escapeGtLt = function(string) {
    var result = string.replace("<", "&lt;");
    result = result.replace(">", "&gt;");
    return result;
};

// Ermittelt, ob der aktuelle Browser auf iOS läuft
util.isiOS = function() {
    return ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
};
