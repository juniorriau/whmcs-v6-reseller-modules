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

class WHMCS_DomainCloudFunctions {
	private $rows;

	public function __construct($domainid = "") {
		if (!empty($domainid)) {
			$query = full_query( "
            SELECT t.*, m.domain AS coza_domain, m.id_doc_storage_name, m.le_doc_storage_name, m.su_doc_storage_name, m.domain_approval_date, m.domain_status
                FROM tbldomains t
                LEFT JOIN mod_domaincloudregistrar m ON t.id = m.domainid
                WHERE t.id = " . $domainid ."
            ");
        	$this->rows = mysql_fetch_array($query);
		}
	}

	public function outputUploadSection($domain, $action) {
		$cmd = substr($action, -1);

		$content = "
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div style=\"border: 1px solid #ccc; background-color: #f0f0f0; border-radius: 5px; padding-bottom: 15px; padding-left: 15px; padding-right: 15px;\">
                    <form method=\"post\" enctype=\"multipart/form-data\">
                        <table width=\"100%\">
                            <tbody>
                                <tr>
                                    <td width=\"10%\"><b>Domain</b>:</td>
                                    <td>" . $domain . "</td>
                                </tr>
                                <tr>
                                	<td width=\"10%\"><b>Jenis Dokumen</b>:</td>
                                	<td>";

		switch ($cmd) {
			case '1':
				$content .= "
				<select name=\"doc_type\">
	                <option value=\"KTP\">KTP</option>
	                <option value=\"SIM\">SIM</option>
	                <option value=\"PASSPORT\">PASSPORT</option>
	            </select>";
				break;
			case '2':
				$content .= "
				<select name=\"doc_type\">
	                <option value=\"NPWP\">NPWP</option>
	                <option value=\"SIUP\">SIUP</option>
	                <option value=\"BKPM\">BKPM</option>
	            </select>";
				break;
			case '3':
				$content .= "
				<select name=\"doc_type\">
	                <option value=\"Surat Pernyataan\">Surat Pernyataan</option>
	                <option value=\"Lainnya\">Lainnya</option>
	            </select>";
				break;
		}

		$content .= "
                                	</td>
                                </tr>
                                <tr>
                                	<td width=\"10%\"><b>Dokumen</b>:</td>
                                	<td><input type=\"file\" name=\"file\" id=\"file\" size=\"30\"><br /></td>
                                </tr>
                                <tr>
                                    <td colspan=\"2\">
                                        <input class=\"btn btn-success\" type=\"submit\" name=\"ul\" value=\"Upload\" onclick=\"check_file()\">
                                        <a href=\"addonmodules.php?module=domainku\">
			                                <input class=\"btn btn-default\" type=\"button\" name=\"cancel\" value=\"Cancel\">
			                            </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>";

        # Add literal javascript functions
        $content .= "
		<script type=\"text/javascript\"> 
        function check_file() {
            var file = document.getElementById(\"file\").files[0];
            var file_name = file.name;
            var file_ext = file_name.split('.')[file_name.split('.').length - 1]);
            var fe = file_ext.toLowerCase();

            if (fe != \"pdf\" || fe != \"jpeg\" || fe != \"jpg\" || fe != \"png\") {
                alert(\"File type is not allowed!\");
                event.returnValue = false;
            }
            else {
                event.returnValue = true;
            }
        }
        </script>";
        return $content;
	}

	public function outputDownloadSection($domain, $domainid, $userid, $document_name, $action, $domain_status) {
        switch (substr($action, -1)) {
            case 1:
                $doctype = "Identity Document";
                break;
            case 2:
                $doctype = "Legality Document";
                break;
            case 3:
                $doctype = "Other Document";
                break;
            default:
                $doctype = "Identity Document";
                break;
        }
		$content = "
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div style=\"border: 1px solid #ccc; background-color: #f0f0f0; border-radius: 5px; padding-bottom: 15px; padding-left: 15px; padding-right: 15px;\">
                    <form method=\"post\">
                        <table width=\"100%\">
                            <tbody>
                                <tr>
                                    <td width=\"5%\"><b>Domain</b>:</td>
                                    <td>" . $domain . "</td>
                                </tr>
                                <tr>
                                    <td width=\"5%\"><b>Document</b>:</td>
                                    <td>" . $doctype . " &rarr; <a href=\"addonmodules.php?module=domainku&amp;userid=". $userid ."&amp;a=dl&amp;domainid=" . $domainid . "&amp;doc_name=" . $document_name . "\">Download</a></td>
                                </tr>
                                <tr>
                                    <td width=\"5%\"><b>Domain Status</b>:</td>
                                    <td>
                                        <select name=\"domain_status\">
                                            <option value=\"2\"". ($this->rows['domain_status'] == 2 ? "selected" : "") .">Review</option>
                                            <option value=\"3\"". ($this->rows['domain_status'] == 3 ? "selected" : "") .">Approve</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input class=\"btn btn-success\" type=\"submit\" name=\"save_domain_status\" value=\"Update Status\">
                                    </td>
                                    <td>
                                        <a href=\"addonmodules.php?module=domainku\">
                                            <input type=\"button\" value=\"Cancel\" />
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>";
        
        return $content;
	}
}