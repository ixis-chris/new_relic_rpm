<?php
/**
 * @file
 * A single component that makes up an API request to the New Relic plugin API.
 *
 * A component is essentially one "metric" such as the number of users at the
 * current time, or the number of comments, or page requests, etc.
 *
 * Components are made up of a name (such as 'user_registrations'), a value
 * (such as '1337') and a unit (such as 'users/hour').
 */

class NewRelicRpmRequestComponent {
  /**
   * @var string
   * The name of the component, such as "users" or "user_registrations".
   *
   * Stick with machine names rather than human-readable names that might
   * contain spaces or other non-alphanumerics because the API needs a format
   * that might not cope with these.
   */
  public $name;

  /**
   * The category or group that this component is in.
   *
   * For example, a user_registrations component might be in the "Users"
   * category.
   *
   * @var string
   */
  public $category;

  /**
   * The units used by the measurement.
   *
   * For example, user_registrations might have "users/hour" as a unit.
   * Something like total_users might just have "users" as a unit because it's
   * not "per" anything.
   *
   * @var string
   */
  public $units;

  /**
   * The value of the component itself.
   *
   * @var int
   */
  public $value;

  /**
   * Return the name of the componeont in full.
   *
   * @return string
   *   The component name formatted as the New Relic plugin API wants it. This
   *   is in the form "Component/Category/metric[units]". The Category part is
   *   optional.
   */
  public function __toString() {
    $pieces = array('Component');

    if (!empty($this->category)) {
      $pieces[] = $this->category;
    }

    $pieces[] = $this->name;

    return implode('/', $pieces) . '[' . $this->units . ']';
  }

  /**
   * Verify that all the necessary data are filled out.
   *
   * @return bool
   *   TRUE if this instance contains all the necessary data. FALSE otherwise.
   */
  public function verify() {
    if (
      empty($this->name) ||
      empty($this->units)
    ) {
      return FALSE;
    }

    // We got here so we must be able to verify!
    return TRUE;
  }
}
