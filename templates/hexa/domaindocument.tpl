{ literal }
<script type="text/javascript">
$(document).ready(function(){
	$('#checkall0').click(function (event) {
	    $(event.target).parents('.datatable').find('input').attr('checked',this.checked);
	});
});
</script>
{ /literal }

<div class="row">
	<div class="col-md-12">
		<a href="/domaindocument.php" title="Kembali ke depan">
			<h3 class="page-header">
				<span aria-hidden="true"></span><i class="fa fa-cloud"></i> Registrasi Dokumen
			</h3>
		</a>

		<div class="row">
			<form method="post">
				<div class="col-lg-4">
					<div class="input-group">
						<input type="text" class="form-control" name="search_domain" value="Enter Domain to Find" onfocus="if(this.value=='Enter Domain to Find')this.value=''">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
						</span>
					</div>
					<span class="help-block"><small>{ $domains|@count } Rekaman Ditemukan, Halaman 1 dari 1</small></span>
				</div>
			</form>
		</div>

		<div class="tablebg">
			<table class="table table-striped table-framed">
				<thead>
					<tr>
						<th width="20">
							<input type="checkbox" onclick="toggleCheckboxes('domids')">
						</th>
						<th style="text-align: center;"><a href="">Domain</a></th>
						<th style="text-align: center;"><a href="">Dok. Identitas</a></th>
						<th style="text-align: center;"><a href="">Dok. Legalitas</a></th>
						<th style="text-align: center;"><a href="">Dok. Penunjang</a></th>
						<th style="text-align: center;"><a href="">Tanggal Registrasi</a></th>
						<th style="text-align: center;"><a href="">Status Domain</a></th>
					</tr>
				</thead>
				<tbody>
					{ foreach name=outer item=data from=$domains }
						<tr>
							<td>
								<input type="checkbox" name="domids[]" class="domids" value="2">
							</td>
							<td >
								{ $data.domain }
							</td>
							<td style="text-align: center;">
								{ if $data.id_doc_storage_name }
									<a href="domaindocument.php?userid={ $data.userid }&amp;a=download_1&amp;domain={ $data.domain }" style="text-decoration: none;">
										<span class="label suspended">Check <i class="fa fa-download"></i></span>
									</a> &#124; 
								{ /if }
								<a href="domaindocument.php?userid={ $data.userid }&amp;a=upload_1&amp;domain={ $data.domain }" style="text-decoration: none;">
									<span class="label refunded">Upload <i class="fa fa-upload"></i></span>
								</a> 
							</td>
							<td style="text-align: center;">
								{ if $data.le_doc_storage_name }
									<a href="domaindocument.php?userid={ $data.userid }&amp;a=download_2&amp;domain={ $data.domain }" style="text-decoration: none;">
										<span class="label suspended">Check <i class="fa fa-download"></i></span>
									</a> &#124; 
								{ /if }
								<a href="domaindocument.php?userid={ $data.userid }&amp;a=upload_2&amp;domain={ $data.domain }" style="text-decoration: none;">
									<span class="label refunded">Upload <i class="fa fa-upload"></i></span>
								</a>
							</td>
							<td style="text-align: center;">
								{ if $data.su_doc_storage_name }
									<a href="domaindocument.php?userid={ $data.userid }&amp;a=download_3&amp;domain={ $data.domain }" style="text-decoration: none;">
										<span class="label suspended">Check <i class="fa fa-download"></i></span>
									</a> &#124; 
								{ /if }
								<a href="domaindocument.php?userid={ $data.userid }&amp;a=upload_3&amp;domain={ $data.domain }" style="text-decoration: none;">
									<span class="label refunded">Upload <i class="fa fa-upload"></i></span>
								</a>
							</td>
							<td style="text-align: center;">
								{ $data.registrationdate }
							</td>
							<td style="text-align: center;">
								{ if $data.domain_status == "3" }
									<span class="label active">Approved</span>
								{ elseif $data.domain_status == "2" }
									<span class="label pending">Review</span>
								{ /if }
							</td>
						</tr>
					{ /foreach }
				</tbody>
			</table>
		</div>
	</div>
</div>

{ if (strpos($action, 'upload') !== false) }
<div class="row">
	<div class="col-md-12">
	    <div style="border: 1px solid #ccc; background-color: #f0f0f0; border-radius: 5px; padding-bottom: 15px; padding-left: 15px; padding-right: 15px;">
	        <form method="POST" action="domaindocument.php?a={ $action }&amp;domain={ $domain }" enctype="multipart/form-data">
	            <center>
					<h3>Upload Dokumen</h3>
	            </center>
	            <b>Domain</b>: { $domain } <br />
	            <b>Jenis Dokumen</b>:
	            <select name="doc_type">
	                { if $action == "upload_1" }
	                    <option value="KTP">KTP</option>
	                    <option value="SIM">SIM</option>
	                    <option value="PASSPORT">PASSPORT</option>
	                { elseif $action == "upload_2" }
	                    <option value="NPWP">NPWP</option>
	                    <option value="SIUP">SIUP</option>
	                    <option value="BKPM">BKPM</option>
	                { else }
	                    <option value="Surat Pernyataan">Surat Pernyataan</option>
	                    <option value="Lainnya">Lainnya</option>
	                { /if }
	            </select><br />
	            <b>Dokumen</b>:
	            <input type="file" name="file" id="file" size="30"><br />
	            <input class="btn btn-success" type="submit" name="submit" value="Upload" onclick="check_file()">
	            <a href="domaindocument.php">
	                <input class="btn btn-default" type="button" name="cancel" value="Cancel">
	            </a>
	        </form>
	    </div>
	</div>
</div>
{ /if }
<br />
<div class="row">
	<div class="col-md-12">
		<table>
			<tbody>
				<tr>
					<td style="text-align:left"><b>Notes : </b></td>
				</tr>
				<tr>
					<td style="text-align:left">Allowed filetype : JPG, JPEG, PNG & PDF</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<hr />