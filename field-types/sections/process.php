<?
	/*
		When processing a field type you are provided with the $field array with the following keys:
			"key" — The key of the field (this could be the database column for a module or the ID of the template or callout resource)
			"options" — An array of options provided by the developer
			"input" — The end user's $_POST data input for this field
			"file_input" — The end user's uploaded files for this field in a normalized entry from the $_FILES array in the same formatting you'd expect from "input"

		BigTree expects you to set $field["output"] to the value you wish to store. If you want to ignore this field, set $field["ignore"] to true.
		Almost all text that is meant for drawing on the front end is expected to be run through PHP's htmlspecialchars function as seen in the example below.
		If you intend to allow HTML tags you will want to run htmlspecialchars in your drawing file on your value and leave it off in the process file.
	*/

	foreach ($field['input'] as $key => $value) {
		$field['input'][$key]["__section_processing_id"] = $key;
		$field['input'][$key]["__internal-title"] = array();
		if (isset($value['callouts']) && is_array($value['callouts'])) {
			foreach ($value['callouts'] as $subkey => $subvalue) {

				if (!empty($subvalue['display_title'])) {
					$field['input'][$key]["__internal-title"][] = $subvalue['display_title'];
				} else {
					$field['input'][$key]["__internal-title"][] = call_user_func(function ($admin, $subvalue) {
						$callout = $admin->getCallout($subvalue['type']);
						return $callout['name'];
					}, $admin, $subvalue);

				}
			}
		}
		$field['input'][$key]["__internal-title"] = implode(', ', $field['input'][$key]["__internal-title"]) . ' - (' . call_user_func(function ($admin, $value) {
			$section = $admin->getCallout($value['section_type']);
			return $section['name'];
		}, $admin, $value) .')';
	}

	$field['options']['columns'][] = array(
		'type' => 'text',
		'id' => '__section_processing_id',
		'title' => '',
		'subtitle' => ''
	);

	$section_type_list = array(
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

	$field['options']['columns'][] = array(
		'type' => 'checkbox',
		'id' => 'reverse_render_direction',
		'title' => 'Weergave richting omdraaien',
		'subtitle' => '',
		'options' => NULL
	);

    $field['options']['columns'][] = array(
        'type' => 'text',
        'id' => 'noun',
        'title' => 'Titel',
        'subtitle' => 'Sectie titel',
        'options' => NULL
    );



	$CalloutsGroups = $admin->getSetting('sections-settings-calloutgroup');

	$section_types = $admin->getCalloutsInGroups(array($CalloutsGroups['value']));

	foreach ($section_types as $value) {
		$section_type_list['options']['list'][] = array(
			'value'=> $value['id'], 
		   	'description' => $value['name']
		);
	}

	$section_type_list['options'] = json_encode($section_type_list['options']);

	$field['options']['columns'][] = $section_type_list;

	$field['options']['columns'][] = array(
		'type' => 'com.terra-it.sections*sections-callouts',
		'id' => 'callouts',
		'title' => 'Callouts',
		'subtitle' => '',
		'options' => json_encode(array(
			'gridsize' => $field['options']['gridsize'],
			'groups' => array($field['options']['display_group']),
			'noun' => '',
			'max' => ''
		))
	);


	include BigTree::path("admin/form-field-types/process/matrix.php");

	foreach ($field['input'] as $sectionkey => $section) {
		foreach ($section_types as $section_type) {
			if ($section_type['id'] === $section['section_type']) {

				if (isset($section_type['resources'])) {
					foreach ($section_type['resources'] as $key => $resource) {
						if (array_key_exists($resource['id'], $section)) {

							$thisfield = array(
								"type" => $resource["type"],
								"title" => $resource["title"],
								"key" => $resource["id"],
								"options" => $resource['options'],
								"ignore" => false,
								"input" => $section[$resource['id']],
								"file_input" => $bigtree["file_data"][$resource["id"]]
							);

							$output = BigTreeAdmin::processField($thisfield);
							if (!is_null($output)) {
								foreach ($field["output"] as $outputkey => $outputvalue) {
									if ($field["output"][$outputkey]['__section_processing_id'] == $section['__section_processing_id']) {
										$field["output"][$outputkey][$resource["id"]] = $output;
									}
								}
							}
						}
					}
				}

				break;
			}
		}
	}

	foreach ($field['output'] as $key => $value) {
		unset($field['output'][$key]['__section_processing_id']);
	}
?>