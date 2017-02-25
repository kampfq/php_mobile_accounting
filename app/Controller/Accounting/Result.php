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
use Model\Accounting\Booking;
use Traits\ViewControllerTrait;

class Result {

    use ViewControllerTrait;

    // Berechnet eine aktuelle Bilanz und liefert
    // sie als Array zurück
    public function getBilanz() {
        $result = array();
        $db = $this -> database;

        $sql =  "select konto, kontenname, saldo from fi_ergebnisrechnungen ";
        $sql .= "where mandant_id = $this->client -> mandant_id and kontenart_id in (1, 2) ";
        $sql .= "order by konto";
        $result = $db -> exec($sql);

        $zeilen = array();
        while($erg = mysqli_fetch_object($rs)) {
            $zeilen[] = $erg;
        }
        $result['zeilen'] = $zeilen;

        $sql = "select kontenart_id, sum(saldo) saldo from fi_ergebnisrechnungen
        where kontenart_id in (1, 2) and mandant_id = $this->client -> mandant_id
        group by kontenart_id
        union 
        select '5', sum(saldo) saldo from fi_ergebnisrechnungen 
        where kontenart_id in (1, 2) and mandant_id = $this->client -> mandant_id";

        $result = $db -> exec($sql);
        $ergebnisse = array();
        while($erg = mysqli_fetch_object($rs)) {
            $ergebnisse[] = $erg;
        }
        $result['ergebnisse'] = $ergebnisse;
        mysqli_close($db);
        return $this -> wrap_response($result);
    }

    // Berechnet eine aktuelle GuV-Rechnung und liefert
    // sie als Array zurück
    public function getGuV($request) {
        $db = $this -> database;
        $year = $request['year'];
        if($this->isValidYear($year)) {

            $query = new QueryHandler("guv_jahr.sql");
            $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
            $query->setParameterUnchecked("jahr_id", $year);
            $sql = $query->getSql();

            $result = $db -> exec($sql);
            $zeilen = array();
            $result = array();
            while($erg = mysqli_fetch_object($rs)) {
                $zeilen[] = $erg;
            }
            $result['zeilen'] = $zeilen;

            $query = new QueryHandler("guv_jahr_summen.sql");
            $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
            $query->setParameterUnchecked("jahr_id", $year);
            $sql2  = $query->getSql();

            $result = $db -> exec($sql2);
            $ergebnisse = array();
            while($erg = mysqli_fetch_object($rs)) {
                $ergebnisse[] = $erg;
            }
            $result['ergebnisse'] = $ergebnisse;
            mysqli_close($db);
            return $this -> wrap_response($result);
        } else {
            return $this -> wrap_response("Der übergebene Parameter year erfüllt nicht die Formatvorgaben für gültige Jahre");
        }
    }

    // Berechnet eine GuV-Rechnung fuer das angegebene oder aktuelle Monat
    // und liefert sie als Array zurück
    public function getGuVMonth($request) {
        $month_id = $this->getMonthFromRequest($request);

        $db = $this -> database;
        $query = new QueryHandler("guv_monat.sql");
        $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
        $query->setParameterUnchecked("monat_id", $month_id);
        $sql = $query->getSql();

        $result = $db -> exec($sql);
        $zeilen = array();
        $result = array();
        while($erg = mysqli_fetch_object($rs)) {
            $zeilen[] = $erg;
        }
        $result['zeilen'] = $zeilen;

        $query = new QueryHandler("guv_monat_summen.sql");
        $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
        $query->setParameterUnchecked("monat_id", $month_id);
        $sql = $query->getSql();

        $result = $db -> exec($sql);
        $ergebnisse = array();
        while($erg = mysqli_fetch_object($rs)) {
            $ergebnisse[] = $erg;
        }
        $result['ergebnisse'] = $ergebnisse;

        mysqli_close($db);
        return $this -> wrap_response($result);
    }

#
    // Laden der GuV-Prognose
    // (GuV aktuelles-Monat + Vormonat)
    public function getGuVPrognose() {
        $db = $this -> database;

        $query = new QueryHandler("guv_prognose.sql");
        $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
        $sql = $query->getSql();

        $result = $db -> exec($sql);

        $result = array();
        $result['detail'] = array();
        while($erg = mysqli_fetch_object($rs)) {
            $result['detail'][] = $erg;
        }

        mysqli_free_result($rs);

        $query = new QueryHandler("guv_prognose_summen.sql");
        $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
        $sql = $query->getSql();

        $result = $db -> exec($sql);

        $result['summen'] = array();
        while($erg = mysqli_fetch_object($rs)) {
            $result['summen'][] = $erg;
        }

        mysqli_close($db);
        return $this -> wrap_response($result);
    }

    // Ermittelt aus dem Request und dessen Parameter "id" das ausgewählte Monat
    // sofern das möglich ist. Ansonsten wird 'Undef' zurückgegeben
    public function getMonthFromRequest($request) {
        // Monat aus dem Request auslesen und dann ggf. verwenden (ansonsten das jetzt verwenden)
        $month_id = 'Undef';
        if(array_key_exists('id', $request)) {
            $month_id = $request['id'];
        }
        if(!is_numeric($month_id)) {
            $month_id = date('Ym');
        }
        return $month_id;
    }

    // Liefert eine Liste der gültigen Monate aus den Buchungen des Mandanten
    public function getMonths() {
        $bookingModel = new Booking();
        $bookings = $bookingModel -> find([
            'mandant_id = ?',$this->client -> mandant_id
        ]);
        $months = array();
        foreach($bookings as $booking){
            $date = new \DateTime();
            $date -> createFromFormat('Y-m-d',$booking->datum);
            $months[] = $date -> format('m');
        }
        $months = array_unique($months);
        return $this -> wrap_response($months);
    }

    // Liefert eine Liste der gültigen Jahre aus den Buchungen des Mandanten
    public function getYears() {
        $bookingModel = new Booking();
        $bookings = $bookingModel -> find([
            'mandant_id = ?',$this->client -> mandant_id
        ]);
        $years = array();
        foreach($bookings as $booking){
            $date = new \DateTime();
            $date -> createFromFormat('Y-m-d',$booking->datum);
            $years[] = $date -> format('Y');
        }
        $years = array_unique($years);
        return $this -> wrap_response($years);
    }

    // Verlauf Aufwand, Ertrag, Aktiva und Passiva in Monatsraster
    public function getVerlauf($request) {
        $db = $this -> database;
        $result = array();

        if(!array_key_exists('id', $request))
            return $result;

        $kontenart_id = $request['id'];
        if(is_numeric($kontenart_id)) {

            if($kontenart_id == 4 || $kontenart_id == 1)
                $sql =  "select (year(datum)*100)+month(datum) as grouping, sum(betrag)*-1 as saldo ";
            else
                $sql =  "select (year(datum)*100)+month(datum) as grouping, sum(betrag) as saldo ";
            $sql .= "from fi_ergebnisrechnungen_base ";
            $sql .= "where kontenart_id = $kontenart_id and gegenkontenart_id <> 5 and mandant_id = $this->client -> mandant_id ";

                // Nur immer die letzten 12 Monate anzeigen
            $sql .= "and (year(datum)*100)+month(datum) >= ((year(now())*100)+month(now()))-100 ";

            $sql .= "group by kontenart_id, year(datum), month(datum) ";
            $sql .= "order by grouping";

            $result = $db -> exec($sql);
        }
        return $this -> wrap_response($result);
    }

    // Verlauf des Gewinns in Monatsraster
    public function getVerlaufGewinn() {
        $db = $this -> database;
        $result = array();

        $db = $this -> database;

        $sql =  "select (year(datum)*100)+month(datum) as grouping, sum(betrag*-1) as saldo ";
        $sql .= "from fi_ergebnisrechnungen_base ";
        $sql .= "where kontenart_id in (3, 4) and gegenkontenart_id <> 5 and mandant_id = $this->client -> mandant_id ";

            // Nur immer die letzten 12 Monate anzeigen
        $sql .= "and (year(datum)*100)+month(datum) >= ((year(now())*100)+month(now()))-100 ";

        $sql .= "group by year(datum), month(datum) ";
        $sql .= "order by grouping";

        $result = $db -> exec($sql);
        while($erg = mysqli_fetch_object($rs)) {
            $result[] = $erg;
        }

        mysqli_close($db);

        return $this -> wrap_response($result);
    }

    // Prüft, ob das Zahlenformat des übergebenen Jahres korrekt ist
    public function isValidYear($year) {
        // Jahr-Regex: [0-9]{4}
        if(preg_match("/[0-9]{4}/", $year, $matches) == 1) {
            if($matches[0] == $year) {
                return true;
            }
        }
        return false;
    }
}

?>
