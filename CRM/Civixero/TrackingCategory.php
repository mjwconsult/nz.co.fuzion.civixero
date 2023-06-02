<?php

class CRM_Civixero_TrackingCategory extends CRM_Civixero_Base {

  /**
   * Pull TrackingCategories from Xero and temporarily stash them in a static variable.
   *
   * we don't want to keep stale ones in our DB - we'll check each time
   * We call the civicrm_accountPullPreSave hook so other modules can alter if required
   *  - I can't think of a reason why they would but it seems consistent
   *
   * @param array $params
   */
  function pull($params) {
    CRM_Civixero_Base::isApiRateLimitExceeded(TRUE);

    static $trackingOptions = [];
    if (empty($trackingOptions)) {
      $tc = $this->getSingleton($this->connector_id)->TrackingCategories();
      foreach ($tc['TrackingCategories'] as $trackingCategory) {
        $trackingOptions[$trackingCategory['Name']]['Name'] = $trackingCategory['Name'];
        $trackingOptions[$trackingCategory['Name']]['Status'] = $trackingCategory['Status'];
        $trackingOptions[$trackingCategory['Name']]['TrackingCategoryID'] = $trackingCategory['TrackingCategoryID'];
        foreach ($trackingCategory['Options']['Option'] as $key => $value) {
          $trackingOptions[$trackingCategory['Name']]['Options'][$value['TrackingOptionID']] = $value['Name'];
        }
      }
    }
    return $trackingOptions;
  }
}
