<?php

/**
 * Class CRM_Civixero_BankTransaction.
 *
 * This class is intended to be used as an alternative to invoice push.
 *
 * It largely inherits the invoice class but creates Bank transaction
 * (payment receipt) records instead of CiviCRM.
 *
 * To choose to push transactions as bank receipts rather than invoices
 * you need to configure the Banktransaction.Push api as a scheduled job
 * rather than an invoice push.
 *
 * This is envisaged as a one way job and a 'pull' is not anticipated.
 *
 * The two actions differ in which Xero entity they map to and the field
 * mappings but are otherwise the same.
 */
class CRM_Civixero_BankTransaction extends CRM_Civixero_Invoice {

  /**
   * Name in Xero of entity being pushed.
   *
   * @var string
   */
  protected $xero_entity = 'BankTransaction';

  /**
   * Push record to Xero.
   *
   * @param array|false $accountsInvoice
   *
   * @param int $connector_id
   *   ID of the connector (0 if nz.co.fuzion.connectors not installed.
   *
   * @return array|false
   */
  protected function pushToXero($accountsInvoice, $connector_id) {
    if ($accountsInvoice === FALSE) {
      return FALSE;
    }
    return $this->getSingleton($connector_id)->BankTransactions($accountsInvoice);
  }

  /**
   * Should transactions be split to go to different accounts based on the line items.
   *
   * Currently we just say 'yes' for bank transactions and 'no' for invoices but
   * in future we may do a setting for this. Although we don't particularly envisage
   * invoices ever being split.
   *
   * Splitting only works if the nz.co.fuzion.connectors extension is installed.
   *
   * @return bool
   */
  protected function isSplitTransactions(): bool {
    return TRUE;
  }

  /**
   * Get a list of responses indicating the transaction cannot be updated.
   *
   * @return array
   */
  protected function getNotUpdateCandidateResponses(): array {
    return [
      'This Bank Transaction cannot be edited as it has been reconciled with a Bank Statement.',
    ];
  }

}
