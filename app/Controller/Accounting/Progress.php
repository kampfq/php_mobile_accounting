<?php
/*
 * Copyright (c) 2013 by Wolfgang Wiedermann
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; version 3 of the
 * License.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

namespace Controller\Accounting;
use Controller\QueryHandler;
use Model\Accounting\Account;
use Traits\ViewControllerTrait;

class Progress {

    use ViewControllerTrait;


# Ermittelt die Monats-Salden des Kontos
function getMonatsSalden() {
    $kontonummer = $this -> getIdParsedFromRequest();
    if(!is_numeric($kontonummer) || !$this->is_numeric_list($kontonummer)) {
        throw new \Exception("Mindestens eine Kontonummer ist nicht numerisch");
    }
        $kto_prepared = $this->prepareKontoNummern($kontonummer);
        $rechnungsart = $this->getRechnungsart($kto_prepared);

        switch ($rechnungsart){
            case 0:
                throw new \Exception("Mindestens eine Kontonummer ist unbekannt");
                break;
            case 1:
                $sql = "select x1.grouping, sum(x2.betrag) as saldo "
                    ."from (select distinct (year(datum)*100)+month(datum) as grouping from fi_buchungen_view "
                    ."where mandant_id = '$this->getClient()->mandant_id') x1 "
                    ."inner join (select (year(datum)*100+month(datum)) as grouping, konto, betrag "
                    ."from fi_buchungen_view where mandant_id = ".$this->getClient()->mandant_id.") x2 "
                    ."on x2.grouping <= x1.grouping "
                    ."where konto in ($kto_prepared) and x1.grouping > ((year(now())*100)+month(now()))-100 "
                    ."group by grouping";
                break;
            case 2:
                $sql = "select grouping, sum(saldo)*-1 as saldo from "
                    ."(select grouping, konto, sum(betrag) as saldo from "
                    ."(select (year(v.datum)*100)+month(v.datum) as grouping, v.konto, v.betrag "
                    ."from fi_ergebnisrechnungen_base v inner join fi_konto kt "
                    ."on v.konto = kt.kontonummer and v.mandant_id = kt.mandant_id "
                    ."where v.mandant_id = ".$this->getClient()->mandant_id
                    ."and v.gegenkontenart_id <> 5) as x "
                    ."group by grouping, konto) as y "
                    ."where y.konto in ($kto_prepared) "
                    ."and y.grouping > ((year(now())*100)+month(now()))-100 "
                    ."group by grouping ";
                break;
        }
    $result = $this -> getDatabase() -> exec($sql);
    return $this -> wrap_response($result);
}

# Ermittelt die monatlichen Werte des Zu- oder Abfluss
# ($side = S => Sollbuchungen)
# ($side = H => Habenbuchungen)
# von Aktivkonten. Bei anderen Kontenarten wird eine
# Exception zurückgeliefert
function getCashFlow() {
    $kontonummer = $this -> getIdParsedFromRequest();
    $side = $this -> getFirstOptionParsedFromRequest();
    if(!$this->isAktivKonto($kontonummer)) {
        throw new \Exception("getCashFlow ist nur für Aktiv-Konten verfügbar");
    }

    if($side === 'S'){
        $accountType = 'sollkonto';
    } elseif($side === 'H') {
        $accountType = 'habenkonto';
    } else {
        throw new \Exception("Gültige Werte für side sind S und H");
    }
    $sql  = "select (year(b.datum)*100)+month(b.datum) as grouping, sum(b.betrag) as saldo ";
    $sql .= "from fi_buchungen as b ";
    $sql .= " inner join fi_konto as k ";
    $sql .= " on k.mandant_id = b.mandant_id and k.kontonummer = b.habenkonto ";
    $sql .= " where b.mandant_id = ".$this->getClient()->mandant_id;
    $sql .= " and b.$accountType = ".$kontonummer;
    $sql .= " and year(b.datum) >= year(now())-1 ";
    $sql .= " and year(b.datum) <= year(now()) ";
    $sql .= " and k.kontenart_id <> 5 ";
    $sql .= "group by (year(b.datum)*100)+month(b.datum);";
    $result = $this -> getDatabase() -> exec($sql);
    return $this -> wrap_response($result);
}

# Monats-internen Verlauf ermitteln
function getIntraMonth() {
    $month_id = $this -> getIdParsedFromRequest();
    if(!$month_id OR !$this->is_number($month_id)) {
        return $this -> wrap_response("Parameter month_id fehlt oder ist nicht ausschließlich numerisch");
    }
    $query = new QueryHandler("guv_intramonth_aufwand.sql");
    $query->setParameterUnchecked("mandant_id", $this->getClient()->mandant_id);
    $query->setParameterUnchecked("month_id", $month_id);
    $result = $this -> getDatabase() -> exec($query->getSql());

    return $this -> wrap_response($result);
}

# Prüft, ob das angegebene Konto ein Aktiv-Konto ist.
function isAktivKonto($kontonummer) {
    $account = new Account();
    $account -> load([
        'mandant_id = ? AND kontonummer = ?',
        $this->getClient()->mandant_id,
        $kontonummer
    ]);
    $isActive = false;
    if($account -> kontenart_id ==1){
        $isActive = true;
    }
    return $isActive;
}

# Macht aus einer oder mehreren durch Komma getrennten Kontonummern
# ein Array von Kontonummern-Strings und verwirft dabei
# nichtnumerische Elemente
function kontonummernToArray($value) {
    $list = array();
    if(is_numeric($value)) {
        $list[] = $value;
    } else {
        $tmp = explode(',', $value);
        foreach($tmp as $item) {
            if(is_numeric($item)) {
                $list[] = $item;
            }
        }
    }
    return $list;
}

# Macht aus einer oder mehreren durch Komma getrennten Kontonummern
# eine passende Liste für SQL-IN
function prepareKontoNummern($value) {
    $list = $this->kontonummernToArray($value);

    $result = "";
    foreach($list as $item) {
        $result .= "'".$item."',";
    }
    $result = substr($result, 0, strlen($result)-1);
    return $result;
}

# Prüft mittels RegEx ob $value ausschließlich aus Ziffern und Kommas besteht
function is_numeric_list($value) {
    $pattern = '/[^0-9,]/';
    preg_match($pattern, $value, $results);
    return count($results) == 0;
}

# Prüft mittels RegEx ob der übergebene Wert ausschließlich aus Ziffern besteht
function is_number($value) {
    $pattern = '/[^0-9]/';
    preg_match($pattern, $value, $results);
    return count($results) == 0;
}

# Ermittelt, ob es sich bei den ausgewählten Konten um
# eine GUV-Betrachtung (nur Aufwand und Ertrag) oder
# eine Bestandsbetrachtung (nur Aktiv und Passiv) handelt.
function getRechnungsart($kto_prepared) {
    $type = 0;
    $sql = "select distinct kontenart_id from fi_konto where kontonummer in ($kto_prepared)";
    $rs = $this -> getDatabase() -> exec($sql);
    foreach($rs as $obj) {
        $kontenart_id = $obj['kontenart_id'];
        if($type == 0) {
            // noch ERGEBNISOFFEN
            if($kontenart_id == 1 || $kontenart_id == 2){
                $type = 1;
            }
            else if($kontenart_id == 3 || $kontenart_id == 4) {
                $type = 2;
            }
        } else if($type == 1) {
            // BESTANDSBETRACHTUNG
            if($kontenart_id == 3 || $kontenart_id == 4) {
                throw new Exception("Falsche Mischung von Kontenarten");
            }
        } else if($type == 2) {
            // GUV-BETRACHTUNG
            if($kontenart_id == 1 || $kontenart_id == 2) {
                throw new Exception("Falsche Mischung von Kontenarten");
            }
        }
    }
    return $type;
}

}
