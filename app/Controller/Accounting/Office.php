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
use League\Csv\Writer;
use Traits\ViewControllerTrait;
// Controller für die Schnellbuchungs-Menüeinträge
class Office {

    use ViewControllerTrait;

    // Erstellt eine Liste aller Buchungen
    public function getJournal($request) {

        $format = "json";
        if($this -> getIdParsedFromRequest() === "csv"){
            $format = "csv";
        }
        $query = new QueryHandler("export_journal_to_excel.sql");
        $query->setParameterUnchecked("mandant_id", $this->getClient()->mandant_id);
        $result = $this -> getDatabase() -> exec($query->getSql());
        return $this -> wrap_response($this -> createCSV($result), $format);
    }

    // Erstellt eine Liste aller GuV-Monatssalden
    public function getGuvMonate($request) {
        $format = "json";
        if($this -> getIdParsedFromRequest() === "csv"){
            $format = "csv";
        }
        $query = new QueryHandler("guv_monat_csv.sql");
        $query->setParameterUnchecked("mandant_id", $this->getClient()->mandant_id);
        $result = $this -> getDatabase() -> exec($query->getSql());
        return $this -> wrap_response( $this -> createCSV($result), $format);
    }

    //Erstellt eine Liste aller GuV-Monatssalde
    public function getBilanzMonate($request) {
        $format = "json";
        if($this -> getIdParsedFromRequest() === "csv"){
            $format = "csv";
        }

        $query = new QueryHandler("bilanz_monat_csv.sql");
        $query->setParameterUnchecked("mandant_id", $this->getClient()->mandant_id);
        $result = $this -> getDatabase() -> exec($query->getSql());
        return $this -> wrap_response($this -> createCSV($result), $format);
    }

    protected function createCSV($result){
        $meh = new \SplFileObject(tempnam(sys_get_temp_dir(), rand()),'w+');
        $writer = Writer::createFromFileObject($meh);
        foreach ($result as $row){
            $writer -> insertOne($row);
        }

        return $meh;
    }

}

?>
