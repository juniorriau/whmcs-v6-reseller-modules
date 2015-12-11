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
 * This is document management module for DomainCloud Reseller. 
 *
 * @package    DomainCloud Document Management
 * @author     Infinys System Indonesia
 * @copyright  Copyright (c) Infinys System Indonesia. 2015
 * @license    http://www.isi.co.id/
 * @version    $Id$
 * @link       http://www.isi.co.id/
 */

date_default_timezone_set('Asia/Jakarta');
require_once ROOTDIR . "/includes/registrarfunctions.php";
require_once ROOTDIR . "/includes/classes/WHMCS/DomainDocuments.php";
require_once ROOTDIR . "/includes/classes/WHMCS/AddonListTable.php";
require_once "functions.php";

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function domaincloud_config() {

    $configarray = array(
    "name" => "DomainCloud Docma",
    "description" => "Document Management",
    "version" => "0.9.0",
    "author" => "Infinys System Indonesia",
    "language" => "english",
    "fields" => array());

    return $configarray;
    
}

function domaincloud_activate() {

    $query = "CREATE TABLE `mod_domaincloudregistrar` 
                ( 
                    `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                    `userid` INT(10) NOT NULL,
                    `domainid` INT(10) NOT NULL,
                    `domain` TEXT(50) NOT NULL,
                    `id_doc_storage_name` VARCHAR(50) NOT NULL,
                    `id_doc_type` VARCHAR(50) NULL,
                    `le_doc_storage_name` VARCHAR(50) NOT NULL,
                    `le_doc_type` VARCHAR(50) NULL,
                    `su_doc_storage_name` VARCHAR(50) NOT NULL,
                    `su_doc_type` VARCHAR(50) NULL,
                    `domain_registration_date` DATE NULL,
                    `domain_approval_date` DATE NULL,
                    `reason` VARCHAR(100) NULL,
                    `domain_status` INT(1) NOT NULL
                )";
    $result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'DomainCloud module has been added successfully');
    return array('status'=>'error','description'=>'Failed to activate DomainCloud module');

}

function domaincloud_deactivate() {

    $query = "DROP TABLE `mod_domaincloudregistrar`";
    $result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'DomainCloud module has been removed successfully');
    return array('status'=>'error','description'=>'Failed to remove DomainCloud module');

}

function domaincloud_output($vars) {
    
    require_once "config.php";

    $uid = (isset($_REQUEST['userid']) ? $_REQUEST['userid'] : "");
    $action = (isset($_REQUEST['a']) ? $_REQUEST['a'] : "");
    $domainid = (isset($_REQUEST['domainid']) ? $_REQUEST['domainid'] : "");
    $document_download = (isset($_REQUEST['dl']) ? $_REQUEST['dl'] : "");
    $document_name = (isset($_REQUEST['doc_name']) ? $_REQUEST['doc_name'] : "");
    $domainname = (isset($_POST["domainname"]) ? $_POST["domainname"] : "");
    $domain_status = (isset($_POST["domain_status"]) ? $_POST["domain_status"] : "");
    $transfersecret = (isset($_POST["transfersecret"]) ? $_POST["transfersecret"] : "");
    $current_date = date('Y-m-d');

    $aInt = new WHMCS\Admin();

    $name = "";
    $orderby = "id";
    $sort = "DESC";
    $pageObj = new WHMCS\Pagination($name, $orderby, $sort);
    $pageObj->digestCookieData();

    $domainsModel = new WHMCS_DomainDocuments($pageObj);

    ob_start();
    echo "
    <a href=\"addonmodules.php?module=domaincloud\" style=\"text-decoration: none\">
        <span style=\"background-color: #84B429; padding: 5px; color: #fff;\">
            &larr;<i class=\"fa fa-home\"></i> Go to Dashboard
        </span>
    </a><br /><br />";

    echo $aInt->beginAdminTabs(array($aInt->lang("global", "searchfilter"))); 
    echo "
            <form action=\"addonmodules.php?module=domaincloud\" method=\"post\">
                <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">
                    <tbody>
                        <tr>
                            <td width=\"15%\" class=\"fieldlabel\">Domain Name</td>
                            <td class=\"fieldarea\"><input type=\"text\" name=\"domainname\" size=\"30\" value=\"\"></td>
                        </tr>
                    </tbody>
                </table>
                <p align=\"center\"><input type=\"submit\" id=\"search-clients\" value=\"Search\" class=\"button\"></p>
            </form>
        </div>
    </div>
    <br />
    <script type=\"text/javascript\">
        $( \"a[href^='#tab']\" ).click( function() {
            var tabID = $(this).attr('href').substr(4);
            var tabToHide = $(\"#tab\" + tabID);
            if(tabToHide.hasClass('active')) {
                tabToHide.removeClass('active');
            }  else {
                tabToHide.addClass('active')
            }
        });
    </script>
    ";

    $criteria = array("domainname" => $domainname, "domainid" => $domainid);

    $section = new WHMCS_DomainCloudFunctions($domainid);
    $tbl = new WHMCS_AddonListTable($pageObj);
    $tbl->setColumns(array("checkall", "Domain", "Identity Document", "Legality Document", "Other Document", "Registration Date", "Special Action", "Domain Status", "Payment"));
    
    $domainsModel->execute($criteria);
    $numresults = $pageObj->getNumResults();

    $domainlist = $pageObj->getData();
    foreach ($domainlist as $dom) {
        $linkopen = "<a href=\"clientsdomains.php?userid=" . $dom['userid'] . "&id=" . $dom['id'] . "\">";
        $linkclose = "</a>";
        $actionlink = "<a href=\"addonmodules.php?module=domaincloudReseller&amp;userid=" . $dom['id'] . "&amp;action=generate_key\" style=\"text-decoration: none;\"><span class=\"label active\">Generate New Key <i class=\"fa fa-comment-o\"></i></span></a> " . 
            ($dom['disabled'] ? "<a href=\"addonmodules.php?module=domaincloudReseller&amp;userid=" . $dom['id'] . "&amp;action=enable_api\" style=\"text-decoration: none;\"><span class=\"label upload\">Enable API <i class=\"fa fa-comment-o\"></i></span></a>" : "<a href=\"addonmodules.php?module=domaincloudReseller&amp;userid=" . $dom['id'] . "&amp;action=disable_api\" style=\"text-decoration: none;\"><span class=\"label closed\">Disable API <i class=\"fa fa-comment-o\"></i></span></a>");
        $tbl->addRow(array(
            "<input type=\"checkbox\" name=\"selecteddomains[]\" value=\"" . $dom['id'] . "\" class=\"checkall\">", 
            $linkopen . $dom['domain'] . $linkclose,
            ($dom['id_doc_storage_name'] ? "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=download_1&amp;domainid=" . $dom['id'] . "&amp;doc_name=" . $dom['id_doc_storage_name'] . "\" style=\"text-decoration: none;\"><span class=\"label check\">Manage <i class=\"fa fa-comment-o\"></i></span></a> &#124; " : "") . "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=upload_1&amp;domainid=" . $dom['id'] . "\" style=\"text-decoration: none;\"><span class=\"label upload\">Upload <i class=\"fa fa-upload\"></i></span></a>", 
            ($dom['le_doc_storage_name'] ? "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=download_2&amp;domainid=" . $dom['id'] . "&amp;doc_name=" . $dom['le_doc_storage_name'] . "\" style=\"text-decoration: none;\"><span class=\"label check\">Manage <i class=\"fa fa-comment-o\"></i></span></a> &#124; " : "") . "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=upload_2&amp;domainid=" . $dom['id'] . "\" style=\"text-decoration: none;\"><span class=\"label upload\">Upload <i class=\"fa fa-upload\"></i></span></a>", 
            ($dom['su_doc_storage_name'] ? "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=download_3&amp;domainid=" . $dom['id'] . "&amp;doc_name=" . $dom['su_doc_storage_name'] . "\" style=\"text-decoration: none;\"><span class=\"label check\">Manage <i class=\"fa fa-comment-o\"></i></span></a> &#124; " : "") . "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=upload_3&amp;domainid=" . $dom['id'] . "\" style=\"text-decoration: none;\"><span class=\"label upload\">Upload <i class=\"fa fa-upload\"></i></span></a>", 
            $dom['registrationdate'],
            "<a href=\"addonmodules.php?module=domaincloud&amp;userid=" . $dom['userid'] . "&amp;a=transfer&amp;domainid=" . $dom['id'] . "\" style=\"text-decoration: none;\"><span class=\"label check\">Renew via Transfer</span></a>",
            $dom['domain_status'] == 3 ? "<span class=\"label active\">Approved</span>" : ($dom['domain_status'] == 2 ? "<span class=\"label pending\">Review</span>" : ($dom['domain_status'] == 1 ? "<span class=\"label closed\">Rejected</span>" : "")),
            $dom['status'] == 'Paid' ? "<span class=\"label active\">".$dom['status']."</span>" : "<span class=\"label cancelled\">".$dom['status']."</span>"
        ));
    }
    echo $tbl->output("domaincloud");

    $output = ob_get_contents();
    ob_end_clean();
    echo $output;

    if ($uid && $action && $domainid) {
        $query = full_query( "
            SELECT t.*, m.domain AS coza_domain, m.id_doc_storage_name, m.le_doc_storage_name, m.su_doc_storage_name, m.domain_approval_date, m.domain_status
                FROM tbldomains t
                LEFT JOIN mod_domaincloudregistrar m ON t.id = m.domainid
                WHERE t.id = " . $domainid ."
            ");
        $rows = mysql_fetch_array($query);
        $domain = $rows['domain'];

	    if ($_FILES["file"]["error"] > 0) {
	        echo "Error: " . $_FILES["file"]["error"] . "<br>";
	    } else {
	        if ($_FILES["file"]["name"] != null) {
	            $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
	            $filename = md5($uid . $domain . $action) . "." . $ext;
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
                curl_setopt($ch, CURLOPT_TIMEOUT, 0);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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

                    if ($rows["coza_domain"] == $domain && $filename) {
                        $query = update_query( "mod_domaincloudregistrar", $values, array("domainid"=>$domainid));
                    } else {
                        $values['domainid'] = $domainid;
                        $values['domain_registration_date'] = $rows['registrationdate'];
                        $values['domain_status'] = "2";
                        $query = insert_query( "mod_domaincloudregistrar", $values );
                    }
                    $query = update_query( "tbldomains", array("registrar"=>"domainku"), array("id"=>$domainid) );
                    redir("module=domaincloud");
                }
	        }
	    }

        if (strpos($action, 'upload') !== false) {
            echo $section->outputUploadSection($domain, $action);
        } elseif (strpos($action, 'dl') !== false) {
            $file = $upload_path . $document_name;

            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($file));
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
        } elseif (strpos($action, 'download') !== false) {
            echo $section->outputDownloadSection($domain, $domainid, $uid, $document_name, $action, $domain_status);

    		$file = $upload_path . $document_name;

            if ($rows["coza_domain"] == $domain && $domain_status != "") {
    			$mvalues = array("domain_status"=>$domain_status);
                
                $params = array();
                $params['userid'] = $uid;
                $params['domainid'] = $domainid;
                $domainparts = explode(".", $domain, 2);
                $params['sld'] = $domainparts[0];
                $params['tld'] = $domainparts[1];
                $params['regperiod'] = $rows['registrationperiod'];
                $params['registrar'] = $rows['registrar'];
                $params['regtype'] = $rows['type'];

                if ($domain_status == 3) {
                    if ($rows['type'] == 'Register') {
                        $result_epp = RegRegisterDomain($params);
                    } elseif ($rows['type'] == 'Transfer') {
                        $params['transfersecret'] = $rows['transfersecret'];
                        $result_epp = RegTransferDomain($params);
                    }

                    if (!$result_epp['error']) {
                        $mvalues['domain_approval_date'] = $current_date;
                        echo "
                        <div class=\"infobox\">
                            <strong><span class=\"title\">Registrar Status</span></strong><br />" . $result_epp['status'] . "
                        </div>
                        ";

        			} else {
                        $mvalues['domain_status'] = $rows['domain_status'];
        				echo "
        				<div class=\"infobox\">
        					<strong><span class=\"title\">Registrar Error</span></strong><br>".$result_epp['error']."
        				</div>
        				";
        			}
        		}
                $query = update_query( "mod_domaincloudregistrar", $mvalues, array("domainid"=>$domainid));
        	}
    	} elseif (strpos($action, 'transfer') !== false) {
            echo "
            <form method=\"post\">
                EPP Code: <input type=\"textbox\" name=\"transfersecret\" id=\"transfersecret\" value=\"\">
                <input type=\"submit\" value=\"Submit Domain Renewal via Transfer\">
            </form>";

            if (!empty($transfersecret)) {
                $params = array();
                $params['userid'] = $uid;
                $params['domainid'] = $rows['id'];
                $domainparts = explode(".", $domain, 2);
                $params['sld'] = $domainparts[0];
                $params['tld'] = $domainparts[1];
                $params['regperiod'] = $rows['registrationperiod'];
                $params['registrar'] = $rows['registrar'];
                $params['regtype'] = 'transfer';
                $params['transfersecret'] = $transfersecret;
                $result_epp = RegTransferDomain($params);

                if (!$result_epp['error']) {
                	# Set domain approval to 'Approved'.
                	$query = update_query( "mod_domaincloudregistrar", array("domain_approval_date"=>$current_date,"domain_status"=>3), array("domainid"=>$rows['id']) );
                	
                	# Check domain status, if 'Pending Transfer' set it to 'Active'.
                	$query = update_query( "tbldomains", array("status"=>"Active"), array("domainid"=>$rows['id'], "status"=>"Pending Transfer") );

                    echo "
                    <div class=\"infobox\">
                        <strong><span class=\"title\">Registrar Status</span></strong><br />Command completed successfully.
                    </div>
                    ";
                } else {
                	echo "
    				<div class=\"infobox\">
    					<strong><span class=\"title\">Registrar Error</span></strong><br>".$result_epp['error']."
    				</div>
    				";
                }
            }
        }
    }

}