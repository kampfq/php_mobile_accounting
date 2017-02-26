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

    public function getQuickMenu() {
        $db = $this -> database;
        $result = $result = $db -> exec("select * from fi_quick_config where mandant_id = ".$this->client->mandant_id." order by config_knz");
        return $this -> wrap_response($result);
    }


    public function getQuickMenuById() {
        ;
        if(!is_numeric($this -> idParsedFromRequest)){
            throw new \ErrorException("Die fi_quick_config id ist fehlerhaft");
        }
        $template = new Template();
        $template -> load([
            'mandant_id = ? AND config_id = ?',$this->client -> mandant_id,$this -> idParsedFromRequest
        ]);
        if($template -> loaded() !== 1){
            return $this -> wrap_response(null);
        }
        return $this -> wrap_response($template);
    }

    public function addQuickMenu($request) {
        $db = $this -> database;
        $inputJSON = $this -> request -> getBody();
        $input = json_decode( $inputJSON, TRUE );
        if($this->isValidQuickMenu($input)) {
            $sql = "insert into fi_quick_config(config_knz, sollkonto, habenkonto, buchungstext,";
            $sql .= " betrag, mandant_id) values ('".$input['config_knz']."', '".$input['sollkonto']."', ";
            $sql .= "'".$input['habenkonto']."', '".$input['buchungstext']."', ".$input['betrag'].", ".$this->client -> mandant_id.")";

            mysqli_query($db, $sql);
            $error = mysqli_error($db);
            if($error) {
                error_log($error);
                error_log($sql);
            }
            mysqli_close($db);
            return $this -> wrap_response("Fehler: $error");
        } else {
            mysqli_close($db);
            throw new ErrorException("Die uebergebene Schnellbuchungsvorlage ist nicht valide: ".$inputJSON);
        }
    }

    public function removeQuickMenu($request) {
        $db = $this -> database;
        $id = $request['id'];
        if(is_numeric($id)) {
            $sql =  "delete from fi_quick_config where mandant_id = $this->client -> mandant_id";
            $sql .= " and config_id = $id";

            mysqli_query($db, $sql);
            mysqli_close($db);

            return $this -> wrap_response(null);
        } else {
            throw new ErrorException("Die id der Schnellbuchungsvorlage muss numerisch sein!");
        }
    }

    // Prüft ob $menu ein valides QuickMenu-Objekt ist
    // Typen und Felder prüfen
    public function isValidQuickMenu($menu) {
        if(count($menu) < 4 && count($menu) > 7) {
            return false;
        }
        foreach($menu as $key => $value) {
            if(!$this->isValidFieldAndValue($key, $value)) return false;
        }
        return true;
    }

    // Prüft ein einzelnes Feld uns seinen Inhalt auf Gültigkeit
    public function isValidFieldAndValue($key, $value) {
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
