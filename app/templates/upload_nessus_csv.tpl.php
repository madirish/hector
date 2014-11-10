<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>
<h2>Upload a Nessus Scan</h2>
<p id='explaination'>After running a Nessus scan you can export the results as a Comma Separated Value (CSV) file.
HECTOR can import these files, creating host records as necessary, and appending vulnerabilities and descriptions 
to HECTOR records.</p>
<form method="post" action="?action=upload_nessus_csv" enctype="multipart/form-data" name="upload_nessus_csv" class="form-horizontal">
	<div class="control-group">
		<label class="control-label" for="file-name-field">File:</label>
		<div class="controls">
			<input type="file" name="nessus_csv" id="file-name-field"/>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="scan-date">Date:</label>
		<div class="controls"">
			<div class="input-append date" id="dp" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd">
				<input name="scan-date" id='scan-date' type="text" value="<?php echo date('Y-m-d');?>"/>
				<span class="add-on"><i class="icon-calendar"></i></span>
			</div>
		</div>
	</div>
	<div class="control-group">
		<div class="controls"
	</div>
<input type="submit" value="Upload CSV"/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>
<script type="text/javascript">
	var dpdatetime = new Date(<?php echo strtotime("now");?> *1000);
	var defaulttime = '<?php echo date(' H:i:s');?>';
	$(document).ready(function() {
	    $('#dp').datepicker().on('changeDate', function(ev){
	    	console.log(ev.prototype.getFullYear() + '-' + ev.getMonth() + '-' + ev.getDate());
	    	document.getElementById('scan-date').value += ev;
	    });
    });
</script>