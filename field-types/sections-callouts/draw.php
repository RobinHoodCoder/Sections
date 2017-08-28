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

echo('<div id="section_resources"></div>');
$bigtree["callout_count"] = intval($_POST["count"]);
$bigtree["callout_key"] = htmlspecialchars($_POST["key"]);

// Haalde eerst setting op, nu niet meer bruh
//$mediaqueries = $admin->getSetting('mediaqueries');
//$mediaqueries = array_merge(array(array('id'=>'0', 'name'=>'Standaard')), (isset($mediaqueries['value']) && is_array($mediaqueries['value']) ? $mediaqueries['value'] : array()));

$mediaqueries = array(
        array(
            'id' => '0',
            'name' => 'Standaard'
        ),
        array(
            'id' => 'medium',
            'name' => 'Medium'
        ),
        array(
            'id' => 'small',
            'name' => 'Small'
        ),
        array(
            'id' => 'mobile',
            'name' => 'Mobile'
        )
);

foreach ($mediaqueries as $key => $value) {
	$mediaqueries[$key]['options'] = array(
		'hide' => 'Verbergen'
	);
}

?>

<style>
	#callout_type.sections-callout-cols fieldset:first-child,
	.callout_type.sections-callout-cols fieldset:first-child{
		margin-bottom: 15px;
	}
	#callout_type.sections-callout-cols .sections-callout-col,
	.callout_type.sections-callout-cols .sections-callout-col{
		float: left;
		padding-right: 10px;
	}
	#callout_type.sections-callout-cols .sections-callout-col:after,
	.callout_type.sections-callout-cols .sections-callout-col:after{
		content: '';
		display: block;
		clear:both;
	}
	#section_resources .form_fields>p {
		display: none;
	}
</style>

<script type="text/javascript">

$('fieldset select').each(function () {
	if (this.name == '<?=$bigtree['matrix_key'] ?>[<?=$bigtree['matrix_count'] ?>][section_type]') {

		var loadSectionResources = function (event,data) {
			$('#section_resources').load(
				"<?=ADMIN_ROOT?>ajax/callouts/resources/", 
				{
					count: <?=$bigtree["callout_count"]?>, 
					key: "<?=$bigtree["callout_key"]?>", 
					resources: "<?=htmlspecialchars($_POST["data"])?>", 
					type: data.value
				}, 
				function () {
					BigTreeCustomControls();
				}
			).scrollTop(0);
		}

		loadSectionResources(undefined, {value: this.value})

		$(this).change(loadSectionResources);
	};
});

function getQueryVariables(uri) {
	var returnval = {};
    var re = /([^&]*)=([^&]*)/g;
	var m;
	 
	while ((m = re.exec(uri)) !== null) {
	    if (m.index === re.lastIndex) {
	        re.lastIndex++;
	    }
        returnval[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
	}
    return returnval;
}

var BigTreeCalloutsIntercept = function (settings) {

	var colsizes = {};

	console.log("test2");

	var appendButton = function () {
		var button = function (count, set) {
			return $('<fieldset>' + 
				<?
					foreach ((isset($mediaqueries) ? $mediaqueries : array()) as $mediaquery) {
				?>
					'<div class="sections-callout-col"><label>Formaat: <?=$mediaquery['name'] ?></label><select name="<?=$field['key']?>['+ count +'][colsize][<?=$mediaquery['id'] ?>]">' + <?

					for ($i = $field['options']['gridsize'] - 1; $i >= 0; $i--) {
						echo('\'<option value="'.($i+1).'"\' + (set[\''. $mediaquery['id'] .'\'] == '.($i+1).' ? \' selected\' : \'\') + \'>'.($i+1).'/'.$field['options']['gridsize'].'</option>\' + ');
					}
					if (isset($mediaquery['options'])) {
						foreach ($mediaquery['options'] as $key => $value) {
							echo('\'<option value="'.$key.'"\' + (set[\''. $mediaquery['id'] .'\'] == \''.($key).'\' ? \' selected\' : \'\') + \'>'.$value.'</option>\' + ');
						}
					};

					?>'</select></div>' + 
				<?
					}
				?>
				'</fieldset>');
		}
		$(document).ajaxComplete(function(event, xhr, settings) {

			var containers = [
				$('#callout_type'),
				$('.callout_type')
			]
			var container;

			for (var i = 0; i < containers.length; i++) {
				if(containers[i].length > 0) {
					container = containers[i];
					break;
				};
			}

			if ((settings.url == '<?=ADMIN_ROOT ?>ajax/callouts/add/' || settings.url == '<?=ADMIN_ROOT ?>ajax/callouts/edit/') && !container.hasClass('sections-callout-cols')) {
				settings.data = getQueryVariables(settings.data);
				container.append(
					button(
						settings.data.count, 
						(settings.data.data !== undefined ? (function(data) {
							return JSON.parse(atob(data));
						} (settings.data.data)).colsize : {})
					)
				).addClass('sections-callout-cols');
				BigTreeCustomControls();
			};
		});
	};

	$(settings.selector)
		.on("click",".add_callout",function () {
			appendButton();
		})
		.on("click",".icon_edit",function () {
			appendButton();
		});
};

if(BigTreeCalloutsFunction == undefined) {

	var BigTreeCalloutsFunction = BigTreeCallouts;
	var BigTreeCallouts = function() {        
	    BigTreeCalloutsIntercept(arguments[0]);
	    return BigTreeCalloutsFunction.apply(this, arguments);
	}
}

</script>
<?
    //Normaal deze, maar wij maken een custom callouts
	//include BigTree::path("admin/form-field-types/draw/callouts.php");
?>
<?
if (!is_array($field["value"])) {
    $field["value"] = array();
}

$noun = $field["options"]["noun"] ? htmlspecialchars($field["options"]["noun"]) : "Element";

$max = !empty($field["options"]["max"]) ? $field["options"]["max"] : 0;


// Work with older group info from 4.1 and lower
if (!is_array($field["options"]["groups"]) && $field["options"]["group"]) {
    $field["options"]["groups"] = array($field["options"]["group"]);
}
?>

<fieldset class="callouts<? if ($bigtree["last_resource_type"] == "callouts") { ?> callouts_no_margin<? } ?>" id="<?=$field["id"]?>">
    <label>Elementen</label>
    <label<?=$label_validation_class?>><?=$field["title"]?><? if ($field["subtitle"]) { ?> <small><?=$field["subtitle"]?></small><? } ?></label>
    <div class="contain">
        <?
        $x = 0;
        foreach ($field["value"] as $callout) {
            $type = $admin->getCallout($callout["type"]);
            $calloutsize = str_replace('col-','',$callout['colsize-css']);
            $calloutsize = str_replace('-','',$calloutsize);
            $calloutsize = str_replace('col ','Normal',$calloutsize);
            $calloutsize = str_replace(' ',', ',$calloutsize);


            ?>
            <article>
                <input type="hidden" class="callout_data" value="<?=base64_encode(json_encode($callout))?>" />
                <? BigTreeAdmin::drawArrayLevel(array($x),$callout,$field) ?>
                <h4>
                    <?
                    //Maak mooie titels en zet callout type (ofterwijl naam) achter de label data
                    if(BigTree::safeEncode($callout["display_title"]) != ''){
                        $display_title = mb_strimwidth(BigTree::safeEncode($callout["display_title"]), 0, 40, "...");
                        $display_title = $type['name']. ': '.'<em>'.$display_title.'</em>';
                    }
                    else{
                        $display_title =$type['name'].': '. '<em>[geen label data]</em>';
                    }
                    ?>


                    <?=$display_title?>
                    <input type="hidden" name="<?=$field["key"]?>[<?=$x?>][display_title]" value="<?=BigTree::safeEncode($callout["display_title"])?>" />
                </h4>
                <p>Grootte: <?=$calloutsize?></p>
                <div class="bottom">
                    <span class="icon_drag"></span>
                    <? if ($type["level"] > $admin->Level) { ?>
                        <span class="icon_disabled has_tooltip" data-tooltip="<p>This callout requires a higher user level to edit.</p>"></span>
                    <? } else { ?>
                        <a href="#" class="icon_edit"></a>
                        <a href="#" class="icon_delete"></a>
                    <? } ?>
                </div>
            </article>
            <?
            $x++;
        }
        ?>
    </div>
    <a href="#" class="add_callout button"><span class="icon_small icon_small_add"></span><?=$noun?> Toevoegen</a>
    <? if ($max) { ?>
        <small class="max">LIMIT <?=$max?></small>
    <? } ?>
    <script>
        BigTreeCallouts({
            selector: "#<?=$field["id"]?>",
            key: "<?=$field["key"]?>",
            noun: "<?=$noun?>",
            groups: <?=json_encode($field["options"]["groups"])?>,
            max: <?=$max?>
        });
    </script>
</fieldset>
