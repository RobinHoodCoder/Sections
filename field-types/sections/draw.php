<?
	/*
		When drawing a field type you are provided with the $field array with the following keys:
			"title" — The title given by the developer to draw as the label (drawn automatically)
			"subtitle" — The subtitle given by the developer to draw as the smaller part of the label (drawn automatically)
			"key" — The value you should use for the "name" attribute of your form field
			"value" — The existing value for this form field
			"id" — A unique ID you can assign to your form field for use in JavaScript
			"tabindex" — The current tab index you can use for the "tabindex" attribute of your form field
			"options" — An array of options provided by the developer
			"required" — A boolean value of whether this form field is required or not
	*/

	$section_types = array(
		'type' => 'list',
		'id' => 'section_type',
		'title' => 'Sectie type',
		'subtitle' => '',
		'options' => array(
			'validation' => 'required',
		    'list_type' => 'static',
		    'allow-empty' => 'No',
		    'list' => array(),
	    	'pop-table' => ''
		)
	);

	$CalloutsGroups = $admin->getSetting('sections-settings-calloutgroup');

	foreach ($admin->getCalloutsInGroups(array($CalloutsGroups['value'])) as $value) {
		$section_types['options']['list'][] = array(
			'value'=> $value['id'], 
		   	'description' => $value['name']
		);
	}

	$section_types['options'] = json_encode($section_types['options']);

	$field['options']['columns'][] = $section_types;

    $field['options']['columns'][] = array(
        'type' => 'text',
        'id' => 'noun',
        'title' => 'Titel',
        'subtitle' => 'sectie titel',
        'options' => NULL
    );

	$field['options']['columns'][] = array(
		'type' => 'com.terra-it.sections*sections-callouts',
		'id' => 'callouts',
		'title' => '',
		'subtitle' => '',
		'options' => json_encode(array(
			'gridsize' => $field['options']['gridsize'],
			'groups' => $field['options']['display_group'],
			'max' => ''
		))
	);

	$field['options']['columns'][] = array(
		'type' => 'checkbox',
		'id' => 'reverse_render_direction',
		'title' => 'Weergave richting omdraaien',
		'subtitle' => '',
		'options' => NULL
	);

    //	Normaal zoals hier beneden. Wij passen hem wat aan.
	//include BigTree::path("admin/form-field-types/draw/matrix.php");
    // Eind
?>
<?
//Aangepaste versie van matrix.
// Aanpassing
// 1: Ander titel label bij sectietitel. Nu kan je hem zelf een naam geven.
// 2: Andere subtitle in het label. Nu zijn dat de elementen die in de sectie zitten
// 3: "Sectie Toevoegen" in plaats van "add item" in de button



if (!is_array($field["value"])) {
    $field["value"] = array();
}
$max = !empty($field["options"]["max"]) ? $field["options"]["max"] : 0;

// Callout style
if ($field["options"]["style"] == "callout") {
    $field["type"] = "callouts"; // Pretend to be callouts to work back-to-back
    ?>
    <fieldset class="callouts<? if ($bigtree["last_resource_type"] == "callouts") { ?> callouts_no_margin<? } ?>" id="<?=$field["id"]?>">
        <label<?=$label_validation_class?>><?=$field["title"]?><? if ($field["subtitle"]) { ?> <small><?=$field["subtitle"]?></small><? } ?></label>
        <div class="contain">
            <?
            $x = 0;
            foreach ($field["value"] as $item) {
                ?>
                <article>
                    <input type="hidden" class="bigtree_matrix_data" value="<?=base64_encode(json_encode($item))?>" />
                    <? BigTreeAdmin::drawArrayLevel(array($x),$item,$field) ?>
                    <h4>
                        <?=BigTree::safeEncode($item["__internal-title"])?>
                        <input type="hidden" name="<?=$field["key"]?>[<?=$x?>][__internal-title]" value="<?=BigTree::safeEncode($item["__internal-title"])?>" />
                    </h4>
                    <p>
                        <?=BigTree::safeEncode($item["__internal-subtitle"])?>
                        <input type="hidden" name="<?=$field["key"]?>[<?=$x?>][__internal-subtitle]" value="<?=BigTree::safeEncode($item["__internal-subtitle"])?>" />
                    </p>
                    <div class="bottom">
                        <span class="icon_drag"></span>
                        <a href="#" class="icon_edit"></a>
                        <a href="#" class="icon_delete"></a>
                    </div>
                </article>
                <?
                $x++;
            }
            ?>
        </div>
        <a href="#" class="add_item button"><span class="icon_small icon_small_add"></span>Add Item</a>
        <? if ($max) { ?>
            <small class="max">LIMIT <?=$max?></small>
        <? } ?>
        <script>
            BigTreeMatrix({
                selector: "#<?=$field["id"]?>",
                key: "<?=$field["key"]?>",
                columns: <?=json_encode($field["options"]["columns"])?>,
                max: <?=$max?>,
                style: "callout"
            });
        </script>
    </fieldset>
    <?
} else {
    ?>
    <fieldset>
        <label<?=$label_validation_class?>><?=$field["title"]?><? if ($field["subtitle"]) { ?> <small><?=$field["subtitle"]?></small><? } ?></label>
        <div class="multi_widget matrix_list" id="<?=$field["id"]?>">
            <section<? if (count($field["value"])) { ?> style="display: none;"<? } ?>>
                <p>Click "Add Item" to add an item to this list.</p>
            </section>
            <ul>
                <?
                $x = 0;
                foreach ($field["value"] as $item) {
                    ?>
                    <li>
                        <input type="hidden" class="bigtree_matrix_data" value="<?=base64_encode(json_encode($item))?>" />
                        <? BigTreeAdmin::drawArrayLevel(array($x),$item,$field) ?>
                        <input type="hidden" name="<?=$field["key"]?>[<?=$x?>][__internal-title]" value="<?=BigTree::safeEncode($item["__internal-title"])?>" />
                        <input type="hidden" name="<?=$field["key"]?>[<?=$x?>][__internal-subtitle]" value="<?=BigTree::safeEncode($item["__internal-subtitle"])?>" />
                        <span class="icon_sort"></span>
                        <p>
                            <?=$item['noun']?>
                            <small><?=BigTree::trimLength(BigTree::safeEncode($item["__internal-title"]),100)?></small>
                        </p>
                        <a href="#" class="icon_delete"></a>
                        <a href="#" class="icon_edit"></a>
                    </li>
                    <?
                    $x++;
                }
                ?>
            </ul>
            <footer>
                <a href="#" class="add_item button"><span class="icon_small icon_small_add"></span>Sectie Toevoegen</a>
                <? if ($max) { ?>
                    <small class="max">LIMIT <?=$max?></small>
                <? } ?>
            </footer>
            <script>
                BigTreeMatrix({
                    selector: "#<?=$field["id"]?>",
                    key: "<?=$field["key"]?>",
                    columns: <?=json_encode($field["options"]["columns"])?>,
                    max: <?=$max?>,
                    style: "list"
                });
            </script>
        </div>
    </fieldset>
    <?
}
?>
