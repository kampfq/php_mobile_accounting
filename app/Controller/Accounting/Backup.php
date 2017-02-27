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
namespace Accounting\Controller;

use Model\Accounting\Account;
use Model\Accounting\Booking;
use Traits\ViewControllerTrait;

class Backup
{

    use ViewControllerTrait;

    //Erstellt ein Datenbankbackup (Insert-Statements) von
    //den Buchungen und Konten des aktuell angemeldeten Mandanten
    function getMysqlBackup()
    {
        $backup_sql = $this->getBuchungenBackup();
        $backup_sql .= $this->getKontenBackup();
        $result = gzencode($backup_sql);

        return $this->wrap_response($result, "gz");
    }

    //Insert-Statements für alle Buchungen des Mandanten generieren
    private function getBuchungenBackup()
    {
        $booking = new Booking();
        $rs = $booking -> load([
            'mandant_id = ?',$this->getClient()->mandant_id,
        ]);
        $result = "";
        foreach ($rs as $obj) {
            $result .= "insert into fi_buchungen (mandant_id, buchungsnummer, buchungstext, sollkonto, habenkonto, ";
            $result .= "betrag, datum, bearbeiter_user_id, is_offener_posten) values ";
            $result .= "(".$obj->mandant_id.", ".$obj->buchungsnummer.", ";
            $result .= "'".mysqli_escape_string($db, $obj->buchungstext)."', ";
            $result .= "'".mysqli_escape_string($db, $obj->sollkonto)."', ";
            $result .= "'".mysqli_escape_string($db, $obj->habenkonto)."', ";
            $result .= "".$obj->betrag.", '".$obj->datum."', ";
            $result .= "".$obj->bearbeiter_user_id.", ".$obj->is_offener_posten."); \n";
        }

        return $result;
    }

    //Insert-Statements für alle Konten des Mandanten generieren
    private function getKontenBackup()
    {

        $account = new Account();
        $rs = $account -> load([
            'mandant_id = ?',$this->getClient()->mandant_id
        ]);
        $result = "";
        foreach ($rs as $obj) {
            {
                $result .= "insert into fi_konto (mandant_id, kontonummer, bezeichnung, kontenart_id) values ";
                $result .= "(".$obj->mandant_id.", ";
                $result .= "'".mysqli_escape_string($db, $obj->kontonummer)."', ";
                $result .= "'".mysqli_escape_string($db, $obj->bezeichnung)."', ";
                $result .= "".$obj->kontenart_id."); \n";
            }
            return $result;
        }
    }
}
