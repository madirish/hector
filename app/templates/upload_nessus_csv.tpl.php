<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>
<h2>Upload a Nessus Scan</h2>
<p id='explaination'>After running a Nessus scan you can export the results as a Comma Separated Value (CSV) file.
HECTOR can import these files, creating host records as necessary, and appending vulnerabilities and descriptions 
to HECTOR records.</p>
<form method="post" action="?action=upload_nessus_csv" enctype="multipart/form-data">
<input type="file" name="nessus_csv"/>
<input type="submit" value="Upload CSV"/>
</form>