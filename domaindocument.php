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
 *
 * @package    Infinys - Document Management (Clientarea) for Reseller
 * @copyright  Copyright (c) PT Infinys System Indonesia 2015
 **/

define("CLIENTAREA", true);
require_once "init.php";
require_once "includes/domainfunctions.php";
require_once "includes/whoisfunctions.php";
require_once "includes/registrarfunctions.php";
require_once "dcconfig.php";

$capatacha = clientAreaInitCaptcha();
$pagetitle = $_LANG['domaintitle'];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG['globalsystemname'] . "</a> > <a href=\"domainchecker.php\">" . $_LANG['domaintitle'] . "</a>";
$templatefile = "domaindocument";
$pageicon = "images/domains_big.gif";
initialiseClientArea($pagetitle, $pageicon, $breadcrumbnav);

$search = $whmcs->get_req_var("search");
$domain = $whmcs->get_req_var("domain");
$bulkdomains = $whmcs->get_req_var("bulkdomains");
$tld = $whmcs->get_req_var("tld");
$tlds = $whmcs->get_req_var("tlds");
$ext = $whmcs->get_req_var("ext");
$direct = $whmcs->get_req_var("direct");

$sld = "";
$invalidtld = "";
$availabilityresults = array();
$search_tlds = array();
$tldslist = array();

$client = new WHMCS_ClientArea();
$uid = $client->getUserID();
$currencyid = (isset($_SESSION['currency']) ? $_SESSION['currency'] : "");
$currency = getCurrency($uid, $currencyid);
$smartyvalues['currency'] = $currency;

$action = (isset( $_REQUEST['a'] ) ? $_REQUEST['a'] : "");
$domain = (isset( $_REQUEST['domain'] ) ? $_REQUEST['domain'] : "");
$document_download = (isset( $_REQUEST['dl'] ) ? $_REQUEST['dl'] : "");
$search_domain = $_POST['search_domain'] != "Enter Domain to Find" ? $_POST['search_domain'] : "";
$current_date = date('Y-m-d');

$where = array("registrar"=>"domainku");
if ($domain || $search_domain) {
    $where["domain"] = ($domain ? $domain : $search_domain);
    $where_document = array("domain"=>$domain);
}

$query = full_query( "
    SELECT  t.id, t.userid, t.type, t.domain, t.registrar, t.registrationdate, t.registrationperiod, t.status AS domstatus, i.status, o.nameservers, o.transfersecret,
                m.domain AS coza_domain, m.domainid AS coza_domainid, m.userid AS coza_userid, m.id_doc_storage_name, m.id_doc_type, m.le_doc_storage_name, 
                m.le_doc_type, m.su_doc_storage_name, m.su_doc_type, m.domain_approval_date, m.domain_status,
                c.firstname, c.lastname, c.companyname, c.email, c.address1, c.address2, c.city, c.state, c.postcode, c.country, c.phonenumber
        FROM tbldomains t 
        LEFT JOIN mod_domaincloudregistrar m ON t.domain = m.domain 
        LEFT JOIN tblorders o ON t.orderid = o.id
        LEFT JOIN tblinvoices i ON o.invoiceid = i.id
        LEFT JOIN tblclients c ON t.userid = c.id
        WHERE t.userid = " . $uid . " AND t.status <> 'Cancelled' AND t.status <> 'Expired' AND ". (!empty($domain) || !empty($search_domain) ? "t.domain LIKE '" . (!empty($domain) ? $domain : $search_domain) . "%'" : "t.domain LIKE '%.id'") .";
    ");
$rows = array();

while ($row = mysql_fetch_array($query)) {
    $rows[] = $row;
}

if ($action && $domain) {
    if ($_FILES["file"]["error"] > 0) {
        echo "Error: " . $_FILES["file"]["error"] . "<br>";
    } else {
        if ($_FILES["file"]["name"] != null) {
            $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            $filename = md5($rows[0]['userid'] . $domain . $action) . "." . $ext;
            move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $filename);

            $domainparts = explode(".", $domain, 2);

            $config = getregistrarconfigoptions('domainku');
            $data = array(
                "action"            => 'UploadFile',
                "token"             => $config['Token'],
                "authemail"         => $config['AuthEmail'],
                "sld"               => $domainparts[0],
				"tld"               => $domainparts[1],
                "file"              => '@' . $upload_path . $filename . ';filename=' . $filename . ';type=' . $_FILES['file']['type'],
                "user_action"       => $action,
                "doc_type"          => $_POST['doc_type']
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_endpoint);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $output = curl_exec($ch);
            if ($output == false) {
                $res = array("error"=>curl_error($ch));
            } else {
                $res = json_decode($output, true);
            }
            curl_close($ch);

            if (empty($res['error'])) {
            	$values = array("userid"=>$uid,"domain"=>$domain);
	            if ($action == "upload_1") { 
	            	$values["id_doc_storage_name"] = $filename;
	            	$values["id_doc_type"] = $_POST["doc_type"];
				}
				if ($action == "upload_2") { 
	            	$values["le_doc_storage_name"] = $filename;
	            	$values["le_doc_type"] = $_POST["doc_type"];
				}
				if ($action == "upload_3") { 
	            	$values["su_doc_storage_name"] = $filename;
	            	$values["su_doc_type"] = $_POST["doc_type"];
				}

				if ($rows[0]["coza_domain"] == $domain && $filename) {
					$values['userid'] = $rows[0]['userid'];

					/* Revalidate domainid value */
					if ($rows[0]['coza_domainid'] != $rows['id']) {
						$values['domainid'] = $rows[0]['id'];
					}
					if ($rows[0]['coza_userid'] != $rows['userid']) {
						$values['userid'] = $rows[0]['userid'];
					}
					/* End of revalidation */

	        		$query = update_query( "mod_domaincloudregistrar", $values, $where_document);
	        	}
	        	else {
	        		$values['userid'] = $rows[0]['userid'];
	        		$values['domainid'] = $rows[0]['id'];
	        		$values['domain_registration_date'] = $rows[0]['registrationdate'];
	        		$values['domain_status'] = "2";
	        		$query = insert_query( "mod_domaincloudregistrar", $values );
	        	}
	        	$query = update_query( "tbldomains", array("registrar"=>"domainku"), array("id"=>$rows[0]['id']) );
	        	redir();
            }
        }
    }

    if (strpos($action, 'download') !== false) {
		/* START OF Document Download Handler */
	    if ($action == "download_1") {
	    	$file = $upload_path . $rows[0]['id_doc_storage_name'];
		}
		if ($action == "download_2") {
	    	$file = $upload_path . $rows[0]['le_doc_storage_name'];
		}
		if ($action == "download_3") {
	    	$file = $upload_path . $rows[0]['su_doc_storage_name'];
		}

		if (file_exists($file)) {
			header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename=' . basename($file));
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($file));
		    ob_clean();
		    flush();
		    readfile($file);
		    exit;
		}
		/* END OF Document Download Handler */
    }
}

$smartyvalues['userid'] = $uid;
$smartyvalues['action'] = $action;
$smartyvalues['domain'] = $domain;
$smartyvalues['domains'] = $rows;
outputClientArea($templatefile);