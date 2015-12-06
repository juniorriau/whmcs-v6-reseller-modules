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

class WHMCS_DomainDocuments extends WHMCS\TableModel {

	public function _execute($criteria = null) {
		return $this->getClients($criteria);
	}

	public function getClients($criteria = array()) {
		$filters = $this->buildCriteria($criteria);
		$where = (count($filters) ? " WHERE " . implode(" AND ", $filters) : "");
		$result = full_query("SELECT COUNT(*) FROM tbldomains t " . $where);
		$data = mysql_fetch_array($result);
		$this->getPageObj()->setNumResults($data[0]);
		$clients = array();
		$query = "
			SELECT 	t.*, i.subtotal, i.tax, i.status, o.nameservers, o.transfersecret,
    			m.domain AS coza_domain, m.id_doc_storage_name, m.id_doc_type, m.le_doc_storage_name, 
    			m.le_doc_type, m.su_doc_storage_name, m.su_doc_type, m.domain_approval_date, m.domain_status
    		FROM tbldomains t 
    		LEFT JOIN mod_domaincloudregistrar m ON t.domain = m.domain 
    		LEFT JOIN tblorders o ON t.orderid = o.id
            LEFT JOIN tblinvoices i ON o.invoiceid = i.id" . $where . " ORDER BY " . $this->getPageObj()->getOrderBy() . " " . $this->getPageObj()->getSortDirection() . " LIMIT " . $this->getQueryLimit();
		$result = full_query($query);

		while ($data = mysql_fetch_array($result)) {
			$id = $data['id'];
			$userid = $data['userid'];
			$domain = $data['domain'];
			$id_doc_storage_name = $data['id_doc_storage_name'];
			$le_doc_storage_name = $data['le_doc_storage_name'];
			$su_doc_storage_name = $data['su_doc_storage_name'];
			$registrationdate = $data['registrationdate'];
			$domain_approval_date = $data['domain_approval_date'];
			$status = $data['status'];
			$domain_status = $data['domain_status'];

			$clients[] = array("id" => $id, "userid" => $userid, "domain" => $domain, "id_doc_storage_name" => $id_doc_storage_name, "le_doc_storage_name" => $le_doc_storage_name, "su_doc_storage_name" => $su_doc_storage_name, 
				"registrationdate" => $registrationdate, "domain_approval_date" => $domain_approval_date, "domain_status" => $domain_status, "status" => $status);
		}

		return $clients;
	}

	private function buildCriteria($criteria) {
		$filters = array();

		if ($criteria['domainid']) {
			$filters[] = "t.id = " . db_escape_string($criteria['domainid']) . "";
		}

		if ($criteria['domainname']) {
			$filters[] = "t.domain LIKE '%" . db_escape_string($criteria['domainname']) . "%'";
		}

		$filters[] = "t.domain LIKE '%.id'";
		$filters[] = "t.status <> 'Expired'";
		$filters[] = "t.status <> 'Cancelled'";
		$filters[] = "t.status <> 'Fraud'";

		return $filters;
	}
}

?>