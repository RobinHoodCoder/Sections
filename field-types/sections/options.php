<?
$gridsize = $data["gridsize"] ? intval($data["gridsize"]) : "";

?>

<fieldset>
	<label>Grid size</label>
	<input type="text" name="gridsize" value="<?=$gridsize?>">
</fieldset>
<fieldset class="last">
	<label>Callout groups</label>
	<? foreach ($admin->getCalloutGroups() as $group) { ?>
	<input type="checkbox" name="display_group[]" value="<?=$group['id'] ?>"<? if (is_array($data["display_group"]) && in_array($group['id'], $data["display_group"])) { ?> checked="checked"<? } ?> />
		<label class="for_checkbox"><?=$group['name'] ?></label>
	<? } ?>
</fieldset>