<?php

use Civi\ContactSdkPullTestable;
use Civi\MockConnector;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\GuzzleTestTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises CRM_Civixero_Contact::pullFromXero() against the real
 * xeroapi/xero-php-oauth2 AccountingApi with a mocked Guzzle HTTP client
 * (Civi\Test\GuzzleTestTrait, the same pattern used for ContactSdkPushTest)
 * - so the SDK's own request building/serialization and response
 * deserialization run for real, only the network call itself is faked.
 *
 * @group headless
 */
class ContactSdkPullTest extends TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use GuzzleTestTrait;

  public function setUpHeadless(): CiviEnvBuilder {
    return \Civi\Test::headless()
      ->install('org.civicrm.search_kit')
      ->install('nz.co.fuzion.accountsync')
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    Civi::$statics['civixero_connector'] = new MockConnector();
    parent::setUp();
  }

  private function getContactWithMockClient(): ContactSdkPullTestable {
    $this->setUpClientWithHistoryContainer();
    $contact = new ContactSdkPullTestable([]);
    $contact->mockClient = $this->getGuzzleClient();
    return $contact;
  }

  private function pull(ContactSdkPullTestable $contact): array {
    return $contact->pullFromXero([], FALSE, FALSE, '', 1, 100, '-1 week');
  }

  public function testPullFromXeroMapsResponseIntoFlatArrayKeyedByContactId(): void {
    $this->createMockHandler([
      json_encode([
        'Contacts' => [
          [
            'ContactID' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'Name' => 'Jane Doe',
            'ContactNumber' => '123',
            'UpdatedDateUTC' => '2024-03-15T10:00:00',
          ],
        ],
      ]),
    ]);
    $contact = $this->getContactWithMockClient();

    $result = $this->pull($contact);

    $this->assertArrayHasKey('aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', $result);
    $mapped = $result['aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'];
    $this->assertEquals('Jane Doe', $mapped['name']);
    $this->assertEquals('123', $mapped['contact_number']);
    $this->assertEquals('2024-03-15 10:00:00', $mapped['updated_date_utc']);
  }

  public function testPullFromXeroReturnsEmptyArrayWhenXeroHasNoContacts(): void {
    $this->createMockHandler([
      json_encode(['Contacts' => []]),
    ]);
    $contact = $this->getContactWithMockClient();

    $result = $this->pull($contact);

    $this->assertEquals([], $result);
  }

  public function testPullFromXeroLogsAndRethrowsOnApiError(): void {
    $this->createMockHandler([]);
    $this->getMockHandler()->append(new \GuzzleHttp\Psr7\Response(500, [], json_encode(['Message' => 'Internal error'])));
    $contact = $this->getContactWithMockClient();

    $this->expectException(\XeroAPI\XeroPHP\ApiException::class);
    $this->pull($contact);
  }

}
