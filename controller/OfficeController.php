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

class OfficeController {

private $dispatcher, $mandant_id;

# Einsprungpunkt, hier übergibt das Framework
function invoke($action, $request, $dispatcher) {
    $this->dispatcher = $dispatcher;
	$this->mandant_id = $dispatcher->getMandantId();
	
    switch($action) {
	    case "timetypes":
		    return $this->getTimeTypes();
        case "timeslices":
            return $this->getTimeSlices($request);
        default:
            throw new ErrorException("Unbekannte Action");
    }
}

# Liefert eine Liste der zulässigen Phasen-Typen
# Woche, Monat, Jahr
function getTimeTypes() {
    $types = array();
	$types[] = "Kalenderwoche";
	$types[] = "Monat";
	$types[] = "Jahr";
    return $types;
}

# Erstellt eine Liste aller Kontenarten
function getTimeSlices($request) {
    
	if(!isset($request['timetype'])) {
	    throw new ErrorException("Der Parameter 'timetype' ist nicht gesetzt");
    }
	
    $timetype = $request['timetype'];
	if($this->isValidTimeType($timetype)) {
	    $sql = "";
		$orderby = "";
	    if($timetype === 'Kalenderwoche') { 
	        $sql =  "select (year(datum)*100)+weekofyear(datum) as zeitscheibe_id, ";
			$sql .= "concat(year(datum), ' KW ', WEEKOFYEAR(datum)) as zeitscheibe_ktxt ";
		    $orderby = "group by (year(datum)*100)+weekofyear(datum) order by (year(datum)*100)+weekofyear(datum)";
		} else if($timetype === 'Monat') {
		    $sql =  "select (year(datum)*100)+month(datum) as zeitscheibe_id, ";
			$sql .= "concat(monthname(datum), ' ', year(datum)) as zeitscheibe_ktxt ";
		    $orderby = "group by (year(datum)*100)+month(datum) order by (year(datum)*100)+month(datum)";
		} else if($timetype === 'Jahr') {
		    $sql = "select year(datum) as zeitscheibe_id, year(datum) as zeitscheibe_ktxt ";
		    $orderby = "group by year(datum) order by year(datum)";
		} else {
		    throw new ErrorException("Ungültiger Zustand, nach isValidTimeType darf kein falscher timetype möglich sein");
		}
		$sql .= "from fi_buchungen where mandant_id = ".$this->mandant_id." ".$orderby;
		
		$result = array();
		$db = getDbConnection();
		$rs = mysqli_query($db, $sql);
		
		while($obj = mysqli_fetch_object($rs)) {
		    $result[] = $obj;
		}
		
		mysqli_close($db);
		
		return $result;
	} else {
	    throw new ErrorException("Ungültiger Zeittyp ausgewählt");
	}
}

private function isValidTimeType($timetype) {
    $validTypes = $this->getTimeTypes();
	foreach($validTypes as $item) {
	    if($item === $timetype) {
		    return true;
		}
	}
	return false;
}

}

?>
