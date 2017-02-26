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
use Traits\ViewControllerTrait;

class Office {

    use ViewControllerTrait;

    // Erstellt eine Liste aller Buchungen
    public function getJournal($request) {

        $format = "csv";

        if(isset($request['format'])) {
            if($request['format'] == "json") {
                $format = $request['format'];
            }
        }

        $result = array();
        $db = $this -> database;

        $query = new QueryHandler("export_journal_to_excel.sql");
        $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
        $sql = $query->getSql();

        $result = $db -> exec($sql);

        return $this -> wrap_response($result, $format);
    }

    // Erstellt eine Liste aller GuV-Monatssalden
    public function getGuvMonate($request) {

        $format = "csv";

        if(isset($request['format'])) {
            if($request['format'] == "json") {
                $format = $request['format'];
            }
        }

        $result = array();
        $db = $this -> database;

        $query = new QueryHandler("guv_monat_csv.sql");
        $query->setParameterUnchecked("mandant_id", $this->client -> mandant_id);
        $sql = $query->getSql();

        $result = $db -> exec($sql);
        return $this -> wrap_response($result, $format);
    }


}

?>