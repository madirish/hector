<?php // Ensure that the collapse controls only show on results page ?>
<?php if ((isset($_POST['form_name']) && ($_POST['form_name']=='search_form' || $_POST['form_name']=='search_ip_form')) || isset($_GET['os']) ) { ?>
<div class="accordion" id="search-accordion" style="padding: 2px;">
	<div class="accordion-group">
		<div calss="accordion-heading">
		<a class="btn btn-small accordion-toggle" data-toggle="collapse" data-parent="#search-accordion" href="#collapseOne">
		<i class="icon-resize-vertical"></i> Search again... </a>
		
		</a>
		</div>
		<div id="collapseOne" class="accordion-body collapse">
			<div class="accodion-inner">
<?php } ?> 
				<fieldset>
				<legend>Search</legend>
				<p>Search is <em>inclusive</em> meaning results are drawn from machines 
				that match any of the specified criteria.</p>
				<form method="post" name="<?php echo $formname;?>" id="<?php echo $formname;?>">
				<table>
					<tr><td>Hostname:</td><td><input type="text" name="hostname"/></td></tr>
				  <tr><td>IP:</td><td><input type="text" name="ip"/></td></tr>
				  <tr><td>Service version (e.x. "OpenSSH"):</td><td><input type="text" name="version"/></td></tr>
				  <tr><td>Operating System (e.x. "Linux"):</td><td><input type="text" name="version"/></td></tr>
					<tr><td>&nbsp;</td><td><input type="submit" value="Search"/></td></tr>
				</table>
				<input type="hidden" name="token" value="<?php echo $token;?>"/>
				<input type="hidden" name="form_name" value="<?php echo $formname;?>"/>
				</form>
				</fieldset>
<?php if ((isset($_POST['form_name']) && ($_POST['form_name']=='search_form' || $_POST['form_name']=='search_ip_form'))  || isset($_GET['os'])) { ?>
			</div>
		</div>
	</div>
</div>
<?php } ?> 
