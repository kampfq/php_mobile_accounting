<?php
/*
 * Copyright (c) 2015 by Wolfgang Wiedermann
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
class Config {

    use ViewControllerTrait;

    public function listConfigEntries() {
        $rs = $this -> getDatabase() -> exec("select * from fi_config_params where mandant_id = ".$this->getClient()->mandant_id." order by param_desc");
        return $this -> wrap_response($rs);
    }

    public function getConfigEntry() {
        if(!$this -> getIdParsedFromRequest()) {
            throw new ErrorException("Parameter param_id nicht im Request enthalten");
        }
        $id = $this -> getIdParsedFromRequest();
        if(is_numeric($id)) {
            $rs = $this -> getDatabase() -> exec("select * from fi_config_params where mandant_id = ".$this-> getClient -> mandant_id." and param_id = $id");
            return $this -> wrap_response($rs);
        } else {
            throw new \ErrorException("Die fi_config_entries.param_id ist fehlerhaft");
        }
    }

    public function updateConfigEntry() {
        $inputJSON = $this -> request -> getBody();
        $input = json_decode( $inputJSON, TRUE );
        if($this->isValidConfigEntry($input)) {
            $sql = "update fi_config_params set param_knz='".$input['param_knz']."', ";
            $sql .= "param_desc='".$input['param_desc']."', param_value='".$input['param_value']."' ";
            $sql .= "where mandant_id = ".$this->getClient()->mandant_id." and param_id = ".$input['param_id'];

            $this -> getDatabase() -> exec($sql);

            return $this -> wrap_response("Fehler: ");
        } else {
            throw new ErrorException("Der übergebene Konfigurationsparameter ist nicht valide: ".$inputJSON);
        }
    }


# Prüft ob $menu ein valides QuickMenu-Objekt ist
# Typen und Felder prüfen
    private function isValidConfigEntry($menu) {
        if(count($menu) < 4 && count($menu) > 6) {
            return false;
        }
        foreach($menu as $key => $value) {
            if(!$this->isValidFieldAndValue($key, $value)) return false;
        }
        return true;
    }

# Prüft ein einzelnes Feld uns seinen Inhalt auf Gültigkeit
    private function isValidFieldAndValue($key, $value) {
        switch($key) {
            case 'mandant_id':
            case 'param_id':
                return $value == null || is_numeric($value);
            case 'param_knz':
            case 'param_desc':
            case 'param_value':
                $pattern = '/[\']/';
                preg_match($pattern, $value, $results);
                return count($results) == 0;
            case 'description':
                return true;
            default:
                return false;
        }
    }
}
?>
