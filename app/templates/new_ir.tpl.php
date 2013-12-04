<h1>New Incident Report</h1>
<form class="form-horizontal" method="post" name="<?php echo $ir_form_name;?>" id="<?php echo $ir_form_name;?>" action="?action=new_ir_scr">
<fieldset>
	<legend>Metadata</legend>
	<div class="control-group">
		<label class="control-label" for="incidentTitle">Title</label>
		<div class="controls">
			<input type="text" id="incidentTitle" placeholder="Incident Title">
		</div>
		<label class="control-label" for="incidentMonth">Month</label>
		<div class="controls">
			<select id="incidentMonth" class="input-mini">
				<option value="1">Jan</option>
				<option value="2">Feb</option>
				<option value="3">Mar</option>
				<option value="4">Apr</option>
				<option value="5">May</option>
				<option value="6">Jun</option>
				<option value="7">July</option>
				<option value="8">Aug</option>
				<option value="9">Sep</option>
				<option value="10">Oct</option>
				<option value="11">Nov</option>
				<option value="12">Dec</option>
			</select>
			<select id="incidentYear" class="input-small">
			<?php
				for ($i=$cur_year;$i>$cur_year-10;$i--) {?>
				<option value="<?php echo $i;?>"><?php echo $i;?></option>
			<?php } ?>
			</select>
		</div>
	</div>
	
	<legend>Details</legend>
	<label class="control-label" for="incidentAgent">Agent causing incident</label>
		<div class="controls">
			<select id="incidentAgent">
				<?php foreach ($agents as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
	<label class="control-label" for="incidentAction">Source of incident</label>
		<div class="controls">
			<select id="incidentAction">
				<?php foreach ($actions as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
	<label class="control-label" for="incidentAsset">Assets affected</label>
		<div class="controls">
			<select id="incidentAsset">
				<?php foreach ($assets as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
		
	<legend>Loss</legend>
	<label class="control-label" for="incidentPII">Data exposure</label>
		<div class="controls">
			<label class="radio">
			<input type="radio" name="incidentPII" id="incidentPII" value="0" checked>No confidential data
			</label>
			<label class="radio">
			<input type="radio" name="incidentPII" id="incidentPII" value="1">Confidential data exposed
			</label>
		</div>
		
	<label class="control-label" for="integrityloss">Integrity loss</label>
		<div class="controls">
			<textarea name="integrityloss" rows="3"></textarea>
		</div>
	<label class="control-label" for="authenloss">Authenticity loss</label>
		<div class="controls">
			<textarea name="authenloss" rows="3"></textarea>
		</div>
	<label class="control-label" for="availloss">Availability loss</label>
		<div class="controls">
			<textarea name="availloss" rows="3"></textarea>
		</div>
	<label class="control-label" for="utilityloss">Utility loss</label>
		<div class="controls">
			<textarea name="utilityloss" rows="3"></textarea>
		</div>
		
	<legend>Timeframes</legend>
	<label class="control-label" for="incidentAtoD">Action to discovery</label>
		<div class="controls">
			<select id="incidentAtoD">
				<?php foreach ($timeframes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
	<label class="control-label" for="incidentDtoC">Discovery to containment</label>
		<div class="controls">
			<select id="incidentDtoC">
				<?php foreach ($timeframes as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
		
	<legend>Discovery</legend>
	<label class="control-label" for="incidentDisco">Method</label>
		<div class="controls">
			<select id="incidentDisco">
				<?php foreach ($discovery as $key=>$val) {?>
					<option value="<?php echo $key;?>"><?php echo $val; ?></option>
				<?php } ?>
			</select>
		</div>
		
</fieldset>
<input type="hidden" name="token" value="<?php echo $ir_form_token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $ir_form_name;?>"/>
</form>