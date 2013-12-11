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
   * @var int
   * The name of the component, such as "users" or "user_registrations".
   *
   * Stick with machine names rather than 
   */
  public $name;


}
