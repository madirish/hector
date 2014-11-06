<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>
<h2>Upload a Nessus Scan</h2>
<p id='explaination'>After running a Nessus scan you can export the results as a Comma Separated Value (CSV) file.
HECTOR can import these files, creating host records as necessary, and appending vulnerabilities and descriptions 
to HECTOR records.</p>
<form method="post" action="?action=upload_nessus_csv" enctype="multipart/form-data" name="upload_nessus_csv">
File: <input type="file" name="nessus_csv"/><br/>
Date: <div class="input-append date" id="dp" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd">
		<input name='scan_date' data-format="yyyy-MM-dd hh:mm:ss" id='scan_date' type="text" value=""/>
		<span class="add-on"><i class="icon-calendar"></i></span>
		</div><br/>
<input type="submit" value="Upload CSV"/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>
<script type="text/javascript">
	var ddatetime = new Date(<?php echo strtotime($vuln_detail->get_datetime());?> *1000);
	var defaulttime = '<?php echo date(' H:i:s');?>';
    $('#dp').datepicker().on('changeDate', function(ev){
    	if (ev.date.valueOf() > ddatetime) {
				document.getElementById('scan_date').value += defaulttime;
    	}
    	else {
    		document.getElementById('scan_date').value='0000-00-00 00:00:00';
    		alert('Invalid Scan Time');
    	}
    });
</script>