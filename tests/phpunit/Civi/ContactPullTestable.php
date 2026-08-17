<?php

namespace Civi;

/**
 * Exposes CRM_Civixero_Contact's private processPull() for direct testing.
 *
 * Lives outside any *Test.php file (like MockConnector) so PHPUnit's eager
 * file-based test discovery doesn't include_once it - and thus resolve its
 * `extends` clause - before CiviCRM's headless install has registered the
 * civixero extension's autoloading.
 */
class ContactPullTestable extends \CRM_Civixero_Contact {

  public function callProcessPull($contacts, int $connectorID) {
    return $this->processPull($contacts, $connectorID);
  }

}
