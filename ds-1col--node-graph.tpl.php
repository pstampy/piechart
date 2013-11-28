<?php

/**
 * @file
 * Display Suite 1 column template. For displaying graph content.
 */

$ds_content_wrapper;
$layout_attributes;
$classes;


drupal_add_js(drupal_get_path('module', 'raphael') .'/js/raphael.js');
drupal_add_js(drupal_get_path('module', 'raphael') .'/js/raphael-min.js');
drupal_add_js(path_to_theme().'/g.raphael.js');
drupal_add_js(path_to_theme().'/g.pie.js');

// Some inital options to use.
$data_key = 0; // First column is the key
$data_value = 3; // Fourth column is the data
$data_limit = 10; //Number of items to show on chart.

// Load the CSV
#ini_set('auto_detect_line_endings',TRUE);
#if ($csv_field = field_get_items('node', $node, 'field_file')) {
 $csv_url = file_create_url($content['field_file'][0]['#markup']);
  $csv_source = fopen($csv_url, 'r');
#} else {
 # drupal_set_message(t('No valid file was found.'), 'warning');
#}


//Convert CSV data into array
if ($csv_source) {
  $key = 0;
  while (($data = fgetcsv($csv_source, 0, ",")) !== FALSE) {
    // Count the total keys in the row.
    $count = count($data);
    // Populate the multidimensional array.
    for ($x = 0; $x < $count; $x++) {
      $csv_array[$key][$x] = $data[$x];
    }
    $key++;
  }
  // Close the file.
  fclose($csv_source);

  // take the row'ified data and columnize the array
  function columnizeArray($csvarray) {
    $array = array();
    foreach($csvarray as $key=>$value) {
      // reparse into useful array data.
      if ($key == 0) {
        foreach ($value AS $key2=>$value2) {
          $array[$key2] = array();
          $array[$key2][] = $value2;
        }
      } else if ($key > 0) {
        foreach ($value as $key3=>$value3) {
          $array[$key3][] = $value3;
        }
      } else {
      }
    }
    return $array;
  }

  function groupColumns($array = null) {
    $array2 = array();
    foreach ($array as $key => $value) {
      // process each column
      // $key = column number
      // $value = array of rows
      $array2[$value[0]] = array();
      foreach ($array[0] as $key1 => $value1) {
        if ($key1 > 0) {
          // ignore the column heading
          // store the first column variable in as the key.
          // Store the value associated with this item as the value.
          $array2[$value[0]][$value1] = $value[$key1];
          }
        }
      }
    return $array2;
  }

  $csv_array_cols = groupColumns(columnizeArray($csv_array));

  // Get list of column names to pick columns from the main array.
  $col_headers = array();
  foreach ($csv_array_cols as $key => $value) {
    $col_headers[] = $key;
  }
  array_multisort($csv_array_cols[$col_headers[$data_value]], SORT_NUMERIC, SORT_DESC);

  //Display the raw CSV data in a table.
  echo t('<h1>Raw data as table</h1>');

  echo '<table>';
    // Iterate over array
    foreach ($csv_array as $key => $value) {
      echo '<tr>';
      //Build header row (assumes CSV has headers)
      if ($key == 0) {
        foreach ($value as $key => $value) {
          echo '<th>' . $value . '</th>';   
        }
      } else {
        //Build body cells
        foreach ($value as $key => $value) {
          echo '<td>' . $value . '</td>';   
        }
      }
      echo '</tr>';
    }
  echo '</table>';
  
  unset($key, $value);

  //Output the data as JSON

  echo t('<h1>Raw data as JSON</h1>');

  print_r($csv_json = json_encode($csv_array_cols, JSON_HEX_APOS));
  $data_json =  json_encode(array_slice($csv_array_cols[$col_headers[$data_value]], 0, $data_limit), JSON_HEX_APOS);
}
?>

<script type='text/javascript'>

// Create a JavaScript object from the JSON string

var data_json = jQuery.parseJSON('<?php echo $data_json;?>');


//Convert the object into arrays of legend and associated value

var legend = [];
var values = [];

for (var key in data_json) {
  legend.push(key);
}

//Ensure values are stored in array as numbers
for (var key in data_json) {
  values.push(parseInt(data_json[key], 10));
}
window.onload = function () {
  var r = Raphael("block-system-main");
  var pie = r.g.piechart(320, 240, 100, values, { legend: legend, legendpos: "east"});

  r.text(320, 100, "I'm a rad pie chart").attr({ font: "20px sans-serif" });
    pie.hover(function () {
                    this.sector.stop();
                    this.sector.scale(1.1, 1.1, this.cx, this.cy);
                    if (this.label) {
                        this.label[0].stop();
                        this.label[0].scale(1.5);
                        this.label[1].attr({"font-weight": 800});
                    }
                }, function () {
                    this.sector.animate({scale: [1, 1, this.cx, this.cy]}, 500, "bounce");
                    if (this.label) {
                        this.label[0].animate({scale: 1}, 500, "bounce");
                        this.label[1].attr({"font-weight": 400});
                    }
                });
    };


</script>

<?php



if (isset($title_suffix['contextual_links'])) {
  render($title_suffix['contextual_links']);
}

print $ds_content;
  
$ds_content_wrapper;

if (!empty($drupal_render_children)) {
  $drupal_render_children;
}
