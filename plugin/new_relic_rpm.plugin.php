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
$components = array();
$request = new NewRelicRpmRequest();
$any_components = FALSE;

// Collect up all the components we will need to measure metrics against.
foreach (array('users', 'nodes', 'comments') as $thing) {
  if (isset($json->$thing)) {
    $component = new NewRelicRpmRequestComponent();
    $component->metricName = ucfirst($thing);
    $component->metricDuration = 1;
    $component->metricGuid = 'uk.co.ixis.newrelic.' . $thing;
    $component->metricData = $json->$thing;

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

$request->sendRequest();
print_r($request->response);
