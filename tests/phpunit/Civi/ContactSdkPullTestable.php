<?php

namespace Civi;

use GuzzleHttp\ClientInterface;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;

/**
 * Overrides CRM_Civixero_Contact::getAccountingApiInstance() so the real SDK
 * (real request-building/serialization, real response deserialization) can
 * be exercised against a mocked Guzzle HTTP client instead of the network.
 * pullFromXero() is already public, so no extra wrapper method is needed
 * here (unlike ContactSdkPushTestable's callPushToXero()). See
 * InvoiceMappingTestable for why this lives outside a *Test.php file.
 */
class ContactSdkPullTestable extends \CRM_Civixero_Contact {

  public ClientInterface $mockClient;

  public function getAccountingApiInstance(): AccountingApi {
    $config = Configuration::getDefaultConfiguration()->setAccessToken($this->getAccessToken());
    return new AccountingApi($this->mockClient, $config);
  }

}
