<?php defined("MAIN_PAGE") or die("Fehlende Berechtigung, Seite darf nur aus index.php geladen werden"); ?>
<!-- Funktionsauswahl - Buchungen -->
<div id="ergebnis_form" class="content_form">
<ul data-role="listview">
    <li data-role="list-divider">Standard-Auswertungen</li>
    <li><a href="#" id="ergebnis_action_bilanz" class="ergebnis_form_item">Bilanz</a></li>
    <li><a href="#" id="ergebnis_action_guv" class="ergebnis_form_item">Gewinn und Verlust</a></li>
    <li><a href="#" id="ergebnis_action_guv_month" class="ergebnis_form_item">GuV aktueller Monat</a></li>
    <li data-role="list-divider">Verlaufs-Auswertungen</li>
    <li><a href="#" id="ergebnis_action_verlauf_aufwand" class="ergebnis_form_item">Aufwand (Monate)</a></li>
    <li><a href="#" id="ergebnis_action_verlauf_ertrag" class="ergebnis_form_item">Ertrag (Monate)</a></li>
    <li><a href="#" id="ergebnis_action_verlauf_gewinn" class="ergebnis_form_item">Gewinn (Monate)</a></li>
    <li><a href="#" id="ergebnis_action_verlauf_frei" class="ergebnis_form_item">Frei kombiniert (Monate)</a></li>
</ul>
</div>
<!-- Bilanz -->
<div id="ergebnis_form_bilanz" class="content_form">
</div>
<!-- GuV -->
<div id="ergebnis_form_guv" class="content_form">
</div>
<!-- Verlauf -->
<div id="ergebnis_form_verlauf" class="content_form">
</div>
<!-- Verlaufsauswertung frei, Vorauswahl -->
<div id="ergebnis_form_verlauf_vorauswahl" class="content_form">
    <h2>Ausw&auml;hlbare Konten</h2>
    <ul data-role="listview" data-filter="true" data-filter-placeholder="Search account" data-bind="foreach: konten_selectable, jqmRefreshList: konten_selectable">
        <li><a href="#" data-bind="text: tostring, attr: {'data-key': kontonummer}, click: $root.selectKonto"></a></li>
    </ul><br/>
    <h2>Ausgew&auml;hlte Konten</h2>
    <ul data-role="listview" data-bind="foreach: konten_selected, jqmRefreshList: konten_selected">
        <li><a href="#" data-bind="text: tostring, attr: {'data-key': kontonummer}, click: $root.unselectKonto"></a></li>
    </ul>
    <br/>
    <button id="ergebnis_form_verlauf_anzeigen">Anzeigen</button>
</div>
<!-- JavaScript-Code -->
<script type="text/javascript">
var ergebnisForm = {

registerErgebnisFormEvents : function() {
    $(".ergebnis_form_item").unbind("click");
    $("#ergebnis_action_bilanz").click(ergebnisForm.showBilanz);
    $("#ergebnis_action_guv").click(ergebnisForm.showGuV);
    $("#ergebnis_action_guv_month").click(ergebnisForm.showGuVMonth);
    $("#ergebnis_action_verlauf_aufwand").click(ergebnisForm.showVerlaufAufwand);
    $("#ergebnis_action_verlauf_ertrag").click(ergebnisForm.showVerlaufErtrag);
    $("#ergebnis_action_verlauf_gewinn").click(ergebnisForm.showVerlaufGewinn);
    $("#ergebnis_action_verlauf_frei").click(ergebnisForm.showVerlaufFreiVorauswahl);
},    

showBilanz : function() {
    $(".content_form").hide();
    $("#ergebnis_form_bilanz").show();
    ergebnisForm.loadBilanz();
},

showGuV : function() {
    $(".content_form").hide();
    $("#ergebnis_form_guv").show();
    ergebnisForm.loadGuV();
},

showGuVMonth : function() {
    $(".content_form").hide();
    $("#ergebnis_form_guv").show();
    ergebnisForm.loadGuVMonth();
},

showVerlaufAufwand : function() {
    $(".content_form").hide();
    $("#ergebnis_form_verlauf").show();
    ergebnisForm.loadVerlauf(3);
},

showVerlaufErtrag : function() {
    $(".content_form").hide();
    $("#ergebnis_form_verlauf").show();
    ergebnisForm.loadVerlauf(4);
},

showVerlaufGewinn : function() {
    $(".content_form").hide();
    $("#ergebnis_form_verlauf").show();
    ergebnisForm.loadVerlauf(-1);
},

showVerlaufFreiVorauswahl : function() {
    $(".content_form").hide();
    $("#ergebnis_form_verlauf_vorauswahl").show();
    ergebnisForm.loadVerlaufFreiVorauswahl();
},

showVerlaufFrei : function() {
    $(".content_form").hide();
    $("#ergebnis_form_verlauf").show();
    alert('Auswertung laden');
    //ergebnisForm.loadVerlaufFrei();
},

loadBilanz : function() {
    doGETwithCache("ergebnis", "bilanz", [], 
        function(data) {
            var html = "Bilanzdarstellung:<br/><table>";
            for(var key in data.zeilen) {
                var line = data.zeilen[key];
                html += "<tr><td>"+line.konto+"</td><td>"+line.kontenname+"</td><td>"+line.saldo+"</td></tr>";
            }
            html += "</table>";
            html += "<br/><b>Ergebnis:</b><br/><table>";
            for(var key in data.ergebnisse) {
                var erg = data.ergebnisse[key];
                var bezeichnung;
                if(erg.kontenart_id === '1') bezeichnung = 'Aktiva';
                else if(erg.kontenart_id === '2') bezeichnung = 'Passiva';
                else if(erg.kontenart_id === '5') bezeichnung = 'Saldo'; 
                html += "<tr><td>"+bezeichnung+"</td><td>"+erg.saldo+"</td></tr>";
            }
            html += "</table>";
            $("#ergebnis_form_bilanz").html(html);
        }, 
        function(error) {
            alert(error);
        }
    );
},

loadGuV : function() {
    doGETwithCache("ergebnis", "guv", [], 
        function(data) {
            var html = "Gewinn und Verlust:<br/><table>";
            for(var key in data.zeilen) {
                var line = data.zeilen[key];
                html += "<tr><td>"+line.konto+"</td><td>"+line.kontenname+"</td><td>"+line.saldo+"</td></tr>";
            }
            html += "</table>";
            html += "<br/><b>Ergebnis:</b><br/><table>";
            for(var key in data.ergebnisse) {
                var erg = data.ergebnisse[key];
                var bezeichnung;
                if(erg.kontenart_id === '3') bezeichnung = 'Aufwand';
                else if(erg.kontenart_id === '4') bezeichnung = 'Ertrag';
                else if(erg.kontenart_id === '5') bezeichnung = 'Saldo';
                html += "<tr><td>"+bezeichnung+"</td><td> "+erg.saldo+"</td></tr>";
            }
            html += "</table>";
            $("#ergebnis_form_guv").html(html);
        }, 
        function(error) {
            alert(error);
        }
    );
},

loadGuVMonth : function() {
    doGETwithCache("ergebnis", "guv_month", [], 
        function(data) {
            var html = "<b>Gewinn und Verlust</b><br/> aktueller Monat:<br/><table>";
            for(var key in data.zeilen) {
                var line = data.zeilen[key];
                html += "<tr><td>"+line.konto+"</td><td>"+line.kontenname+"</td><td>"+line.saldo+"</td></tr>";
            }
            html += "</table>";
            html += "<br/><b>Ergebnis:</b><br/><table>";
            for(var key in data.ergebnisse) {
                var erg = data.ergebnisse[key];
                var bezeichnung;
                if(erg.kontenart_id === '3') bezeichnung = 'Aufwand';
                else if(erg.kontenart_id === '4') bezeichnung = 'Ertrag';
                else if(erg.kontenart_id === '5') bezeichnung = 'Saldo';
                html += "<tr><td>"+bezeichnung+"</td><td> "+erg.saldo+"</td></tr>";
            }
            html += "</table>";
            $("#ergebnis_form_guv").html(html);
        }, 
        function(error) {
            alert(error);
        }
    );
},

loadVerlauf : function(kontenart_id) {
        $("#account_show_monatssalden").html("Monatssalden werden geladen");

        var kontenart_txt = '';
        var action = 'verlauf';
        if(kontenart_id === 4) kontenart_txt = 'Ertrag';
        if(kontenart_id === 3) kontenart_txt = 'Aufwand';
        if(kontenart_id === -1) {
            kontenart_txt = 'Gewinn';
            action = 'verlauf_gewinn';
        }

        doGET("ergebnis", action, {'id':kontenart_id},
            function(data) {
                var table = "<table>";
                var code = "<b>Verlauf in Monaten: Kontenart: "+kontenart_txt+"</b><br/>";
                code += '<div id="ergebnis_show_monatssalden_table"></div>';
                code += '<canvas id="ergebnis_show_monatssalden_canvas" width="300px" height="300px"></canvas>';
                $("#ergebnis_form_verlauf").html(code);
                d.init("ergebnis_show_monatssalden_canvas");
                d.setToWindowWidth();
                var diagrammData = [];
                for(var key in data) {
                    diagrammData.push(data[key].saldo);
                    table += "<tr><td>"+data[key].grouping+"</td><td>"+data[key].saldo+"</td></tr>";
                }
                table += "</table>";
                $("#ergebnis_show_monatssalden_table").html(table);
                d.drawLineDiagramFor(diagrammData);
            }, 
            function(error) {
            }
        );
    },

loadVerlaufFreiVorauswahl : function() {
    model.konten_selected.removeAll();
    $("#ergebnis_form_verlauf_anzeigen").unbind("click");
    $("#ergebnis_form_verlauf_anzeigen").click(ergebnisForm.showVerlaufFrei);

},

};
</script>
