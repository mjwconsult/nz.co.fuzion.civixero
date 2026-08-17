<?php

namespace Civi;

/**
 * Exposes CRM_Civixero_Contact's protected mapToAccounts() for direct
 * testing.
 *
 * Lives outside any *Test.php file (like MockConnector) so PHPUnit's eager
 * file-based test discovery doesn't include_once it - and thus resolve its
 * `extends` clause - before CiviCRM's headless install has registered the
 * civixero extension's autoloading.
 */
class ContactMappingTestable extends \CRM_Civixero_Contact {

  /**
   * @return array|bool
   */
  public function callMapToAccounts(array $contact, ?string $xeroContactUUID) {
    return $this->mapToAccounts($contact, $xeroContactUUID);
  }

}
