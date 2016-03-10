<div class="wrap">

<?php

/*
if ($_POST['submit']) {
	$options['steps'] = $_POST['steps'];
	$options['current'] = (is_numeric($_POST['current'])) ? $_POST['current'] : "0";
	update_option("wp-thermometer",$options);
	echo "<div class=\"updated\"><p><strong>Settings saved!</strong></p></div>";
}
?>
<form name="thermometer_form" method="post" action="">
<?php settings_fields("wp-thermometer"); ?>
<?php $options = get_option("wp-thermometer"); ?>
<table class="form-table">
<tr valign="top">
<th scope="row">Add steps (one per line)</th>
<td><textarea name="steps" id="steps" rows="10" cols="50"><?php echo $options['steps']; ?></textarea></td>
</tr>
<tr valign="top">
<th scope="row">Current step</th>
<td><select name="current" id="current">
<?php
foreach (explode("\r",$options['steps']) as $id=>$step) {
	echo "<option value=\"" . ($id-1) . "\"";
	if (($id-1) == $options['current']) echo " selected=\"selected\"";
	echo ">" . $step . "</option>\r\n";
}
echo "<option value=\"" . $id . "\"";
if ($id == $options['current']) echo " selected=\"selected\"";
echo ">Finished!</option>\r\n";
?>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>

</div>
