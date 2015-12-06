<?php
/**
 * Copyright (c) 2015, Infinys System Indonesia
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

date_default_timezone_set('Asia/Jakarta');
require_once "../includes/registrarfunctions.php";
require_once "../modules/registrars/domainku/domainku.php";

if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
    $config = getregistrarconfigoptions('domainku');
    $adminuser = 'resellerapi';

    if ($_POST['token'] == $config['Token'] && $_POST['authemail'] == $config['AuthEmail'])
    {
        $action = isset($_POST['action']) ? $_POST['action'] : "";

        if ($action == 'UpdateDomainStatus') {
            $domain = isset($_POST['domain']) ? $_POST['domain'] : "";
            $expirydate = isset($_POST['expirydate']) ? $_POST['expirydate'] : "";
            $nextduedate = isset($_POST['nextduedate']) ? $_POST['nextduedate'] : "";
            $status = isset($_POST['status']) ? $_POST['status'] : "Pending";
            $query = full_query("
                SELECT * FROM tbldomains d
                    WHERE domain = '".$domain."' AND (status = 'Active' OR status = 'Pending')
                ");
            $rows = mysql_fetch_array($query);

            $query = update_query( "tbldomains", array('expirydate'=>$expirydate, 'nextduedate'=>$nextduedate, 'nextinvoicedate'=>$nextduedate), array('id'=>$rows['id']) );
            
            # Send successful domain registration notification to customer.
            if ($query && $status == 'Active') {
                $command = "sendemail";
                $values["messagename"] = "Domain Registration Approved";
                $values["id"] = $rows['id'];

                $results = localAPI($command, $values, $adminuser);
            }
        }
    }
}