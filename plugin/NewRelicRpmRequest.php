<?php
/**
 * @file
 * Represents a cURL request to the New Relic plugin API.
 */

class NewRelicRpmRequest {
  const VERSION = '1.0.0';

  /**
   * @var string
   * The URL to which the cURL request should be made.
   */
  public $url = 'http://platform-api.newrelic.com/platform/v1/metrics';

  /**
   * @var string
   * The license key used to access the New Relic account.
   *
   * This could be one license for the account itself or it could be the license
   * key of a user who is authorised to access the account.
   *
   * It can be found by navigating to "Account Settings" from the drop-down
   * menu in the top-right of the New Relic web interface.
   */
  public $licenseKey;

  /**
   * @var string
   * The FQDN of the MACHINE that is making the requests, not the site.
   */
  public $host;

  /**
   * @var int
   * The process identifier of the agent. Can be used to distinguish between
   * different agents, if necessary. This is optional.
   */
  public $pid = 0;

  /**
   * @var NewRelicRpmRequestComponent[]
   * Array of the metric components that make up this request.
   */
  protected $components;

  /**
   * @var mixed
   * The response to the API call, sent back from New Relic.
   */
  public $response = NULL;

  /**
   * Make sure that internally, the agent data are ready to be used.
   *
   * @return bool
   *   TRUE if the agent (host, version, pid) is ready to use. FALSE otherwise.
   */
  protected function verifyAgent() {
    // Fail this check if the host has been left empty.
    return !empty($this->host);
  }

  /**
   * Send the API request to the New Relic API and receive a response.
   */
  public function sendRequest() {
    // Make sure we're in a position to make the request, by checking that all
    // the parameters have been set correctly.
    if (!$this->verifyAgent()) {
      throw new BadMethodCallException('Unable to make request: the agent details (host) have not been set correctly.');
    }

    foreach ($this->getComponents() as $component) {
      if (!$component->verifyMetrics()) {
        throw new BadMethodCallException('Unable to make request: the metrics details for one or more components (data, duration, guid, name) have not been set correctly.');
      }
    }

    $curl = curl_init();

    // Set the URL where the API call will be made.
    curl_setopt($curl, CURLOPT_URL, $this->url);

    // Turn the headers into an array and let cURL have them.
    $headers = array(
      'X-License-Key: ' . $this->licenseKey,
      'Content-Type: application/json',
      'Accept: application/json',
    );

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    // Create the JSON to be sent to the API and provide it to cURL.
    $json = $this->createJson();

    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

    // Make the cURL request and record the response.
    $this->response = curl_exec($curl);
  }

  /**
   * Turn the metrics data properties into JSON ready to be sent to the API.
   *
   * The metrics must be properly populated before this is called.
   *
   * @return string
   *   A JSON-encoded version of the metrics data.
   */
  protected function createJson() {
    // We need the agent data and the metrics data to be able to produce JSON.
    if (!$this->verifyAgent()) {
      throw new BadMethodCallException('Unable to encode JSON: the agent details (host) have not been set correctly.');
    }

    foreach ($this->getComponents() as $component) {
      if (!$component->verifyMetrics()) {
        throw new BadMethodCallException('Unable to encode JSON: the metrics details for one or more components (data, duration, guid, name) have not been set correctly.');
      }
    }

    // Set up the data array for the agent.
    $agent = array(
      'host'    => $this->host,
      'version' => self::VERSION,
    );

    if (!empty($this->pid)) {
      $agent['pid'] = $this->pid;
    }

    // Set up each componeont to supply the metrics data.
    $components = array();

    foreach ($this->getComponents() as $component) {
      $json_component = array(
        'name'      => $component->metricName,
        'guid'      => $component->metricGuid,
        'duration'  => $component->metricDuration,
        'metrics'   => $component->metricData,
      );

      $components[] = $json_component;
    }

    // Put everything together and return the encoded JSON.
    $json = array(
      'agent'       => $agent,
      'components'  => $components,
    );

    return json_encode($json);
  }

  /**
   * Get all components currently set.
   *
   * @return NewRelicRpmRequestComponent[]
   *   The array of NewRelicRpmRequestComponents.
   */
  public function getComponents() {
    return $this->components;
  }

  /**
   * Set the components to be used in this request.
   *
   * @param NewRelicRpmRequestComponent[] $components
   *   An array of NewRelicRpmRequestComponents.
   */
  public function setComponents(array $components) {
    $this->components = $components;
  }

  /**
   * Add a new component to the list of components to be used in the request.
   *
   * @param NewRelicRpmRequestComponent $component
   *   A NewRelicRpmRequestComponent.
   */
  public function addComponent(NewRelicRpmRequestComponent $component) {
    $this->components[] = $component;
  }
}
