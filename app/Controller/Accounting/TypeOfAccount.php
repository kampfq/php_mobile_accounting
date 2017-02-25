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

class TypeOfAccount {

    use ViewControllerTrait;

private $dispatcher;

# Einsprungpunkt, hier übergibt das Framework
function invoke($action, $request, $dispatcher) {
    $this->dispatcher = $dispatcher;
    switch($action) {
        case "get":
            return $this->getKontenart($request['id']);
        case "list":
            return $this->getKontenarten();
        default:
            throw new ErrorException("Unbekannte Action");
    }
}

# Liest eines einzelne Kontenart aus und liefert
# sie als Objekt zurück
function getKontenart($id) {
    if(is_numeric($id)) {
        $db = $this -> f3->get('DB');
        $result = $db -> exec("select * from fi_kontenart where kontenart_id = $id");
        $erg = mysqli_fetch_object($rs);
        mysqli_close($db);
        return $this -> wrap_response($erg);
    } else {
        throw new ErrorException("Eine nicht numerische Kontenart-ID ist ungültig");
    }
}

# Erstellt eine Liste aller Kontenarten
function getKontenarten() {
    $db = $this -> f3->get('DB');
    $result = $db -> exec("select * from fi_kontenart");
    return $this -> wrap_response($result);
}

}

?>
