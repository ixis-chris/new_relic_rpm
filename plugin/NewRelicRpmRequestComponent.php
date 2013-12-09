<?php
/**
 * @file
 * A single component that makes up an API request to the New Relic plugin API.
 *
 * A component is essentially one "metric" such as the number of users at the
 * current time, or the number of comments, or page requests, etc.
 */

class NewRelicRpmRequestComponent {
  /**
   * @var string
   * Maximum 32 characters, case-sensitive human-readable display name.
   */
  public $metricName;

  /**
   * @var string
   * Reverse-domain-name-style identifier such as com.newrelic.myapp.
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
   * @var int
   * A single number representing the value of the metric being recorded.
   *
   * NB: The API supports more complicated metrics than this but the more
   * complicated form is not yet implemented here.
   */
  public $metricData;

  /**
   * Make sure that internally, the metrics data are ready to be used.
   */
  public function verifyMetrics() {
    if (
      empty($this->metricName) ||
      empty($this->metricGuid) ||
      empty($this->metricDuration) ||
      empty($this->metricData)
    ) {
      return FALSE;
    }

    // We got here, so it's successful.
    return TRUE;
  }
}
