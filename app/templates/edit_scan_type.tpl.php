<form name="add_scan_type_form" id="add_scan_type_form" method="POST" action="?action=add_edit_scr&object=<?php echo $object;?>&id=<?php echo $id;?>">
<div id="configform">
<?php 
include_once($approot . 'scripts/' . $scandir . '/form.php');
?>
</div>
<input type="hidden" name="script" id="script"/>
<input type="hidden" id="flags" name="flags"/>
<input type="hidden" name="token" value="<?php echo $token;?>"/>
<input type="hidden" name="form_name" value="<?php echo $form_name;?>"/>
</form>
<!-- JavaScripts have to go here so they can reach the parts of the DOM (above) they maniuplate -->
<?php 
global $javascripts;
if(isset($javascripts)  && is_array($javascripts)) foreach($javascripts as $script) echo $script;
?>