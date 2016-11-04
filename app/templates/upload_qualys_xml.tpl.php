<?php if (isset($message)) {?>
<div id="message" class="alert"><?php echo $message;?></div>
<?php } ?>
<h2>Upload a Qualys Scan Results</h2>
<p id='explaination'>After running a Qualys scan you can export the results as an XML file.
HECTOR can import these files, creating host records as necessary, and appending vulnerabilities and descriptions 
to HECTOR records.</p>
<form method="post" action="?action=upload_qualys_xml&upload=1" enctype="multipart/form-data" name="upload_qualys_xml" class="form-horizontal">
	<div class="control-group">
		<label class="control-label" for="file-name-field">Qualys XML File:</label>
		<div class="controls">
			<input type="file" name="qualys_xml" id="file-name-field"/>
		</div>
	</div>
	<div class="control-group">
		<div class="controls"
	</div>
<input type="submit" value="Upload XML"/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>