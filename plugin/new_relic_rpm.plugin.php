<?php
/**
 * @file
 * A command-line plugin that can send data to the New Relic API.
 *
 * Full description and setup is contained in README.txt.
 */

require 'NewRelicRpmRequestComponent.php';
require 'NewRelicRpmRequest.php';

// Parse the options from the command line.
$opts = getopt('u:k:h:');

// Grab the data we need from the website.
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $opts['u'] . '/new-relic-rpm-plugin/' . $opts['k']);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

$response = curl_exec($curl);
curl_close($curl);

$json = json_decode($response);

// Create a new request. We'll populate it later once we know if we have any
// components or not.
$request = new NewRelicRpmRequest();

// Set up some components into which we can load data.
$components = array();
$any_components = FALSE;

// These are the things that we want to measure and send to the plugin API. The
// machine-readable is the array key and the value is an array of name and
// units.
$measurements = array(
  'total_users'     => array('name' => 'Total Users', 'units' => 'users'),
  'total_nodes'     => array('name' => 'Total Nodes', 'units' => 'nodes'),
  'total_comments'  => array('name' => 'Total Comments', 'units' => 'comments'),
);

// Collect up all the components we will need to measure metrics against.
foreach ($measurements as $machine_name => $extra_info) {
  if (isset($json->$machine_name)) {
    $component = new NewRelicRpmRequestComponent();
    $component->name = $machine_name;
    $component->units = $extra_info['units'];
    $component->value = $json->$machine_name;

    $request->addComponent($component);

    // Keep track of whether we have any components at all! The user could have
    // disabled ALL components on the site.
    $any_components = TRUE;
  }
}

// Stop here if we don't have any components, because there are no data to send
// to the API.
if (!$any_components) {
  exit;
}

// Finish setting up the request data.
$request->licenseKey = $opts['k'];
$request->host = $opts['h'];

$request->metricName = isset($json->site_name) ? $json->site_name : 'Unknown website';
$request->metricGuid = 'org.Drupal';
$request->metricDuration = 300;

// Send the request and print the response to the stdout.
$request->sendRequest();
print_r($request->response);
