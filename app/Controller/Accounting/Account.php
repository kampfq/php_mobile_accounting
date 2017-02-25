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
use Model\Accounting\Account as AccountModel;

class Account {


    use ViewControllerTrait;

    private $dispatcher, $mandant_id;

    protected $types = [
        AccountModel::AKTIV => 'Aktiv',
        AccountModel::PASSIV => 'Passiv',
        AccountModel::AUFWAND => 'Aufwand',
        AccountModel::ERTRAG => 'Ertrag',
        AccountModel::NEUTRAL => 'Neutale Konten',
    ];
# Liest eines einzelnes Konto aus und liefert
# sie als Objekt zurück
function getKonto($id) {
    if(is_numeric($id)) {
        $db = $this -> database;
        $result = $db -> exec("select * from fi_konto where kontonummer = $id and mandant_id = $this->client -> mandant_id");
        $erg = mysqli_fetch_object($rs);
        mysqli_close($db); 
        return $this -> wrap_response($erg);
    } else throw Exception("Kontonummer nicht numerisch");
}

# Ermittelt den aktuellen Saldo des Kontos
function getSaldo() {
    $idParsedFromRequest = $this -> f3 -> get('PARAMS.id');
    if(is_numeric($idParsedFromRequest)) {
        $db = $this -> database;
        $result = $db -> exec("select saldo from fi_ergebnisrechnungen where mandant_id = ".$this->client -> mandant_id." and konto = '$idParsedFromRequest'");
        $saldo = 0;
        foreach($result as $erg){
            $saldo = $erg->saldo;
        }
        return $this -> wrap_response($saldo);
    } else throw \Exception("Kontonummer nicht numerisch");
}

# Erstellt eine Liste aller Kontenarten
function getKonten() {
    $db = $this -> database;
    $result = array();
    $result = $db -> exec("select * from fi_konto where mandant_id = ".$this->client -> mandant_id." order by kontenart_id, kontonummer");
    return $this -> wrap_response($result);
}

# Speichert das als JSON-Objekt übergebene Konto
function saveKonto($request) {
    $db = $this -> database;
    $inputJSON = file_get_contents('php://input');
    $input = json_decode( $inputJSON, TRUE );
    if($this->isValidKonto($input)) { 
        $sql = "update fi_konto set bezeichnung = '".$input['bezeichnung']."', kontenart_id = ".$input['kontenart_id']
              ." where kontonummer = ".$input['kontonummer']." and mandant_id = ".$this->client -> mandant_id;
        mysqli_query($db, $sql);
        mysqli_close($db);
        $void = array();
        return $this -> wrap_response($void);
    } else {
        throw new Exception("Kontenobjekt enthaelt ungueltige Zeichen");
    }
}

# legt das als JSON-Objekt übergebene Konto an
function createKonto($request) {
    $db = $this -> database;
    $inputJSON = file_get_contents('php://input');
    $input = json_decode( $inputJSON, TRUE );
    if($this->isValidKonto($input)) {
        $sql = "insert into fi_konto (kontonummer, bezeichnung, kontenart_id, mandant_id) values ('"
              .$input['kontonummer']."', '".$input['bezeichnung']
              ."', ".$input['kontenart_id'].", ".$this->client -> mandant_id.")";
        mysqli_query($db, $sql);
        mysqli_close($db);
        $void = array();
        return $this -> wrap_response($void);
    } else {
        throw new Exception("Kontenobjekt enthaelt ungueltige Zeichen");
    }
}

# Prüft, ob das angegebene Konto valide ist
# (passende Typen, richtige Felder etc.)
function isValidKonto($konto) {
    if(count($konto) < 3 && count($konto) > 4) {
        return false;
    }
    foreach($konto as $key => $value) {
        if(!$this->isValidFieldAndValue($key, $value)) return false;
    }
    return true;
}

# Prüft ein einzelnes Feld und seinen Inhalt auf Gültigkeit
function isValidFieldAndValue($key, $value) {
    switch($key) {
        case 'kontonummer': 
        case 'kontenart_id':
        case 'mandant_id': 
            return is_numeric($value);
        case 'bezeichnung':
        case 'tostring':
            $pattern = '/[\']/';
            preg_match($pattern, $value, $results);
            return count($results) == 0;
        default:
            return false;
    }
}

    public function getKontenArten()
    {
        $returnValue = [];
        foreach ($this -> types as $id => $type){
            $returnValue[] = [
                'kontenart_id' => $this -> types[$id],
                'bezeichnung' =>$this -> types[$id]
            ];
        }
        return $this->wrap_response($returnValue);
    }

    public function getKontenart($id)
    {
        $returnValue = [
            'kontenart_id' => $this -> types[$id],
            'bezeichnung' =>$this -> types[$id]
        ];

        return $this->wrap_response($returnValue);
    }

}

?>
