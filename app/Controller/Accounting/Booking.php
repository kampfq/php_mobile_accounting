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
use Traits\ViewControllerTrait;

class Booking {

    use ViewControllerTrait;

    //legt das als JSON-Objekt übergebene Konto an
    public function createBuchung($request) {
        $inputJSON = $this -> request -> getBody();
        $input = json_decode( $inputJSON, TRUE );
        $result = $this->createBuchungInternal($input, $this -> getDatabase());
        return $result;
    }

    //Innerhalb dieses Controllers wiederverwendbare Funktion zum
    //Anlegen von Buchungen
    private function createBuchungInternal($input, $db) {
        if($this->isValidBuchung($input)) {
            $booking = new \Model\Accounting\Booking();
            $booking -> mandant_id = $this -> getClient()->mandant_id;
            $booking -> buchungstext = $input['buchungstext'];
            $booking -> sollkonto = $input['sollkonto'];
            $booking -> habenkonto = $input['habenkonto'];
            $booking -> betrag = $input['betrag'];
            $booking -> datum = $input['datum'];
            $booking -> bearbeiter_user_id = $this -> getUser()->user_id;
            $booking -> is_offener_posten= $input['is_offener_posten'];
            $booking -> save();

            return $this -> wrap_response([]);
        } else {
            throw new ErrorException("Das Buchungsobjekt enthält nicht gültige Elemente");
        }

    }

    //liest die aktuellsten 25 Buchungen aus
    function getTop25() {
        $db = getDbConnection();
        $top = array();
        $rs = $this -> getDatabase() -> exec("select * from fi_buchungen "
            ."where mandant_id = ".$this-> getClient() -> mandant_id
            ."order by buchungsnummer desc limit 25");

       foreach($rs as $obj) {
            $top[] = $obj;
        }
        return $this -> wrap_response($top);
    }

    //liest die offenen Posten aus
    function getOpList() {
        $booking = new \Model\Accounting\Booking();
        $bookings = $booking -> load([
            'mandant_id = ? AND is_offener_posten = 1',
            $this->getClient() -> mandant_id,
        ]);
        return $this -> wrap_response($bookings);
    }

     //liest die offenen Posten aus
    function closeOpAndGetList($request) {
        $db = getDbConnection();
        $inputJSON = $this -> request -> getBody();
        $input = json_decode( $inputJSON, TRUE );
        if($this->isValidOPCloseRequest($input)) {
            $db->begin_transaction();
            try {
                // Buchung anlegen
                $buchung = $input['buchung'];
                $this->createBuchungInternal($buchung, $db);
                // Offener-Posten-Flag auf false setzen
                $buchungsnummer = $input['offenerposten'];
                if (is_numeric($buchungsnummer)) {
                    $sql = "update fi_buchungen set is_offener_posten = 0"
                        . " where mandant_id = $this->mandant_id "
                        . " and buchungsnummer = $buchungsnummer";
                    $this -> getDatabase() -> exec($sql);
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            // Aktualisierte Offene-Posten-Liste an den Client liefern
            $top = array();
            $rs = $this -> getDatabase() -> exec("select * from fi_buchungen "
                . "where mandant_id = $this->mandant_id "
                . "and is_offener_posten = 1 "
                . "order by buchungsnummer");

            while ($obj = mysqli_fetch_object($rs)) {
                $top[] = $obj;
            }

            mysqli_free_result($rs);
            mysqli_close($db);
            return $this -> wrap_response($top);
        } else {
            mysqli_close($db);
            throw new ErrorException("Der OP-Close-Request ist ungültig!");
        }
    }

    function getListByKonto($request) {
        $kontonummer = $this -> getIdParsedFromRequest();
        $jahr = $this -> getFirstOptionParsedFromRequest();
        # Nur verarbeiten, wenn konto eine Ziffernfolge ist, um SQL-Injections zu vermeiden
        if(is_numeric($kontonummer) && is_numeric($jahr)) {

            $result = array();
            $result_list = array();

            // Buchungen laden
            $sql =  "SELECT buchungsnummer, buchungstext, habenkonto as gegenkonto, betrag, datum, is_offener_posten ";
            $sql .= "FROM fi_buchungen ";
            $sql .= "WHERE mandant_id = $this->mandant_id and sollkonto = '$kontonummer' and year(datum) = $jahr ";
            $sql .= "union ";
            $sql .= "select buchungsnummer, buchungstext, sollkonto as gegenkonto, betrag*-1 as betrag, datum, is_offener_posten ";
            $sql .= "from fi_buchungen ";
            $sql .= "where mandant_id = $this->mandant_id and habenkonto = '$kontonummer' and year(datum) = $jahr ";
            $sql .= "order by buchungsnummer desc";

            $result['list'] = $this -> getDatabase() -> exec($sql);

            // Saldo laden:
            $sql =  "select sum(betrag) as saldo from (SELECT sum(betrag) as betrag from fi_buchungen ";
            $sql .= "where mandant_id = $this->mandant_id and sollkonto = '$kontonummer' ";
            $sql .= "union SELECT sum(betrag)*-1 as betrag from fi_buchungen ";
            $sql .= "where mandant_id = $this->mandant_id and habenkonto = '$kontonummer' ) as a ";

            $rs = $this -> getDatabase() -> exec($sql);
            if(count($rs)===1) {
                $result['saldo'] = $rs[0];
            } else {
                $result['saldo'] = "unbekannt";
            }
            return $this -> wrap_response($result);
            # Wenn konto keine Ziffernfolge ist, leeres Ergebnis zurück liefern
        } else {
            throw new ErrorException("Die Kontonummer ist nicht numerisch");
        }
    }

# -----------------------------------------------------
# Eingabevalidierung
# -----------------------------------------------------

# Validiert ein Buchungsobjekt und prüft die Gültigkeit
# der einzelnen Felder des Objekts
    function isValidBuchung($buchung) {
        if(count($buchung) < 6 && count($buchung) > 7) {
            return false;
        }
        foreach($buchung as $key => $value) {
            if(!$this->isInValidFields($key)) return false;
            if(!$this->isValidValueForField($key, $value)) return false;
        }
        return true;
    }

# Validiert ein OPCloseRequest-Objekt und prüft seine
# Gültigkeit (auch die zu schließende Buchungsnummer
# muss größer 0 sein!)
    function isValidOPCloseRequest($request) {
        # Hauptgliederung prüfen
        if(!(isset($request['offenerposten'])
            && isset($request['buchung']))) {
            error_log("isValidOPCloseRequest: Hauptgliederung falsch");
            return false;
        }
        $op = $request['offenerposten'];
        $buchung = $request['buchung'];
        # Buchung prüfen
        if(!$this->isValidBuchung($buchung)) {
            error_log("isValidOPCloseRequest: Buchung invalide");
            return false;
        }
        # Offener Posten Buchungsnummer prüfen
        if(is_numeric($op) && $op != 0) {
            return true;
        } else {
            error_log("isValidOPCloseRequest: buchungsnummer == 0");
            error_log(print_r($op,true));
            return false;
        }
    }

# Prüft, ob das gegebene Feld in der Menge der
# gueltigen Felder enthalten ist.
    function isInValidFields($key) {
        switch($key) {
            case 'mandant_id':       return true;
            case 'buchungsnummer':   return true;
            case 'buchungstext':     return true;
            case 'sollkonto':        return true;
            case 'habenkonto':       return true;
            case 'betrag':           return true;
            case 'datum':            return true;
            case 'datum_de':         return true;
            case 'benutzer':         return true;
            case 'is_offener_posten':return true;
            default:                 return false;
        }
    }

# Prüft, ob jeder Feldinhalt valide sein kann
    function isValidValueForField($key, $value) {
        switch($key) {
            case 'buchungsnummer':
            case 'mandant_id':
            case 'betrag':
                return is_numeric($value);
            case 'sollkonto':
            case 'habenkonto':
                $pattern = '/[^0-9]/';
                preg_match($pattern, $value, $results);
                return count($results) == 0;
            case 'buchungstext':
            case 'datum':
            case 'datum_de':
                $pattern = '/[\']/';
                preg_match($pattern, $value, $results);
                return count($results) == 0;
            case 'is_offener_posten':
                return $value === false || $value === true;
            default: return true;
        }
    }

}

?>
