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
   * @var string
   * Maximum 32 characters, case-sensitive human-readable display name.
   *
   * This should be the name of the website.
   */
  public $metricName;

  /**
   * @var string
   * Reverse-domain-name-style identifier such as com.newrelic.myapp.
   *
   * Can be the reverse-domain of the website.
   */
  public $metricGuid;

  /**
   * @var int
   * The number of seconds over which these data were collected.
   *
   * The end time is automatically implied as the time the request is received
   * by the API.
   */
  public $metricDuration;

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
   * Make sure that internally, the metric data are ready to be used.
   */
  public function verifyMetric() {
    if (
      empty($this->metricName) ||
      empty($this->metricGuid) ||
      empty($this->metricDuration)
    ) {
      return FALSE;
    }

    // We got here, so it's successful.
    return TRUE;
  }

  /**
   * Send the API request to the New Relic API and receive a response.
   */
  public function sendRequest() {
    // Make sure we're in a position to make the request, by checking that all
    // the agent parameters have been set correctly.
    if (!$this->verifyAgent()) {
      throw new BadMethodCallException('Unable to make request: the agent details (host) have not been set correctly.');
    }

    // Now check that all the metric parameters have been set correctly.
    if (!$this->verifyMetric()) {
      throw new BadMethodCallException('Unable to make request: the metric details (duration, name, guid) have not been set correctly.');
    }

    foreach ($this->getComponents() as $component) {
      if (!$component->verify()) {
        throw new BadMethodCallException('Unable to make request: component with name "' . $component->name . '" could not be verified.');
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
    if (!$this->verifyMetric()) {
      throw new BadMethodCallException('Unable to encode JSON: the metric details (duration, name, guid) have not been set correctly.');
    }

    foreach ($this->getComponents() as $component) {
      if (!$component->verify()) {
        throw new BadMethodCallException('Unable to encode JSON: the component with name "' . $component->name . '" failed to verify.');
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

    $metric = array(
      'name'      => $this->metricName,
      'guid'      => $this->metricGuid,
      'duration'  => $this->metricDuration,
    );

    // Set up each componeont to supply the metrics data.
    $components = array();

    foreach ($this->getComponents() as $component) {
      // Explicit cast is needed here otherwise the JSON conversion process
      // unreliably sometimes puts double quotes around numbers. This causes the
      // New Relic plugin API to fail as it REQUIRES a number and not a string.
      $components[$component->__toString()] = (int) $component->value;
    }

    // Put everything together and return the encoded JSON.
    $metric['metrics'] = $components;

    $json = array(
      'agent'       => $agent,

      // Note that in the JSON, "components" is actually an array and can accept
      // multiple components but we're just sending one here.
      'components'  => array($metric),
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
