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
use Model\Accounting\Template;
use Traits\ViewControllerTrait;
// Controller für die Schnellbuchungs-Menüeinträge
class Menu {

    use ViewControllerTrait;

function getQuickMenu() {
    $rs = $this -> getDatabase() -> exec("select * from fi_quick_config where mandant_id = ".$this->getClient()->mandant_id." order by config_knz");
    return $this -> wrap_response($rs);
}

function getQuickMenuById() {
    $id = $this -> getIdParsedFromRequest();
    if(is_numeric($id)) {
        $rs = $this -> getDatabase() -> exec("select * from fi_quick_config where mandant_id = ".$this->getClient()->mandant_id." and config_id = $id");
        return $this -> wrap_response($rs);
    } else {
        throw new \ErrorException("Die fi_quick_config id ist fehlerhaft");
    }
}

function addQuickMenu() {
    $inputJSON = $this -> request -> getBody();
    $input = json_decode( $inputJSON, TRUE );
    if($this->isValidQuickMenu($input)) { 
        $sql = "insert into fi_quick_config(config_knz, sollkonto, habenkonto, buchungstext,";
        $sql .= " betrag, mandant_id) values ('".$input['config_knz']."', '".$input['sollkonto']."', ";
        $sql .= "'".$input['habenkonto']."', '".$input['buchungstext']."', ".$input['betrag'].", ".$this->mandant_id.")";
        $this -> getDatabase() -> exec($sql);
        // return $this -> wrap_response("Fehler: $error");
        return $this -> wrap_response("Fehler: ");
    } else {
        throw new ErrorException("Die uebergebene Schnellbuchungsvorlage ist nicht valide: ".$inputJSON);
    }
}

function updateQuickMenu($request) {
    $inputJSON = $this -> request -> getBody();
    $input = json_decode( $inputJSON, TRUE );
    if($this->isValidQuickMenu($input)) {
        $sql = "update fi_quick_config set ";
        $sql .= "config_knz = '".$input['config_knz']."', ";
        $sql .= "buchungstext = '".$input['buchungstext']."', ";
        $sql .= "sollkonto = '".$input['sollkonto']."', ";
        $sql .= "habenkonto = '".$input['habenkonto']."', ";
        $sql .= "betrag = '".$input['betrag']."' ";
        $sql .= "where mandant_id = ".$this->mandant_id;
        $sql .= " and config_id = ".$input['config_id'];

        $this -> getDatabase() -> exec($sql);
        return $this -> wrap_response("Gelöscht");
    } else {
        throw new \ErrorException("Die uebergebene Schnellbuchungsvorlage ist nicht valide: ".$inputJSON);
    }
}

function removeQuickMenu($request) {
    $id = $this -> getIdParsedFromRequest();
    if(is_numeric($id)) {
        $sql =  "delete from fi_quick_config where mandant_id = ".$this->getClient() -> mandant_id;
        $sql .= " and config_id = $id";

        $this -> getDatabase() -> exec($sql);
        return $this -> wrap_response(null);
    } else {
        throw new \ErrorException("Die id der Schnellbuchungsvorlage muss numerisch sein!");
    }
}

# Prüft ob $menu ein valides QuickMenu-Objekt ist
# Typen und Felder prüfen
function isValidQuickMenu($menu) {
    if(count($menu) < 4 && count($menu) > 7) {
        return false;
    } 
    foreach($menu as $key => $value) {
        if(!$this->isValidFieldAndValue($key, $value)) return false;
    }
    return true;
}

# Prüft ein einzelnes Feld uns seinen Inhalt auf Gültigkeit
function isValidFieldAndValue($key, $value) {
    switch($key) {
        case 'config_id': 
        case 'sollkonto':
        case 'habenkonto': 
        case 'betrag':
        case 'mandant_id':
            return $value == null || is_numeric($value);
        case 'buchungstext':
        case 'config_knz':
            $pattern = '/[\']/';
            preg_match($pattern, $value, $results);
            return count($results) == 0;
        default: // throw new ErrorException("Key: $key, Value: $value");
            return false;
    }
}
}
?>
