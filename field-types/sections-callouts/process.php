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
		$field['input'][$key]['colsize-css'] = 'col ' . call_user_func(function ($colsize) {
			$return = array();
			if (is_array($colsize)) {
				foreach ($colsize as $key => $value) {
					$return[] = 'col'.($key == '0' ? '' : '-'.$key).'-'.$value;
				}
			}
			return implode(' ', $return);
		}, $value['colsize']);
	}

	// Some backwards compat stuff.
	$upload_service = new BigTreeUploadService;
	$bigtree["callout_field"] = $field;
	$bigtree["saved_entry"] = $bigtree["entry"];
	$bigtree["saved_post_data"] = $bigtree["post_data"];
	$bigtree["saved_file_data"] = $bigtree["file_data"];
	$bigtree["parsed_callouts"] = array();

	if (count($bigtree["callout_field"]["input"])) {

		foreach ($bigtree["callout_field"]["input"] as $number => $data) {
			// Make sure there's a callout here...
			if ($data["type"]) {
				// Setup the new callout for fun-ness.
				$bigtree["entry"] = array("type" => $data["type"],"display_title" => $data["display_title"]);
				$bigtree["callout"] = $admin->getCallout($data["type"]);
				$bigtree["post_data"] = $data;
				$bigtree["file_data"] = $bigtree["callout_field"]["file_input"][$number];

				$bigtree['callout']['resources'][] = array(
					'id' => 'colsize',
					'type' => 'com.terra-it.sections*section-callouts-cols',
					'title' => '',
					'options' => array()
				);

				$bigtree['callout']['resources'][] = array(
					'id' => 'colsize-css',
					'type' => 'text',
					'title' => '',
					'options' => array()
				);
				
				foreach ($bigtree["callout"]["resources"] as $resource) {
					$field = array(
						"type" => $resource["type"],
						"title" => $resource["title"],
						"key" => $resource["id"],
						"options" => $resource["options"],
						"ignore" => false,
						"input" => $bigtree["post_data"][$resource["id"]],
						"file_input" => $bigtree["file_data"][$resource["id"]]
					);
					if (empty($field["options"]["directory"])) {
						$field["options"]["directory"] = "files/pages/";
					}
					
					// If we JSON encoded this data and it hasn't changed we need to decode it or the parser will fail.
					if (is_string($field["input"]) && is_array(json_decode($field["input"],true))) {
						$field["input"] = json_decode($field["input"],true);
					}

					$output = BigTreeAdmin::processField($field);
					if (!is_null($output)) {
						$bigtree["entry"][$field["key"]] = $output;
					}
				}
				$bigtree["parsed_callouts"][] = $bigtree["entry"];
			}
		}
	}
	
	$bigtree["entry"] = $bigtree["saved_entry"];
	$bigtree["post_data"] = $bigtree["saved_post_data"];
	$bigtree["file_data"] = $bigtree["saved_file_data"];
	$field = $bigtree["callout_field"];
	$field["output"] = $bigtree["parsed_callouts"];
?>