<?php

require 'generator_header.php';

echo '<?php';

?>

  /**************************************************************
   !!!                                                        !!!
   !!! WARNING: This file is automatic generated.             !!!
   !!!                                                        !!!
   !!! In order to modify this file change the content of     !!!
   !!!                                                        !!!
   !!!    /module_template/call_report_template               !!!
   !!!                                                        !!!
   !!! and execute                                            !!!
   !!!                                                        !!!
   !!!    sh generate_modules.sh                              !!! 
   !!!                                                        !!!
   **************************************************************/

sfLoader::loadHelpers(array('I18N', 'Debug', 'Asterisell'));
class <?php echo $className; ?> extends <?php echo   $parentClassName; ?> {

  protected $cachedStartDate = NULL;
  protected $cachedEndDate = NULL;
  protected $areDateCached = FALSE;

  public function executeExportToCsv() {
    return $this->redirect('commercial_feature/index');
  }

  public function executeExportToExcel() {
    return $this->redirect('commercial_feature/index');
  }

  public function executeGetSvg() {
    // execute templates/getSvgSuccess.php
    //
    // NOTE: I'm using this method for retrieving files
    // in order to set the http header 
    // "content-type" to "image/svg+xml" as required
    // from the browser for SVG files.
    //
    return sfView::SUCCESS;
  }

  public function executeShowChannelUsage() {
    return $this->redirect('commercial_feature/index');
  }

  public function executeHideChannelUsage() {
    return $this->redirect('commercial_feature/index');
  }

  /**
   * Call this function before call other functions on filters.
   */
  protected function initBeforeCalcCondition() {
    $this->processSort();
    $this->processFilters();
    $this->filters = $this->getUser()->getAttributeHolder()->getAll('sf_admin/cdr/filters');
  }

  /**
   * @pre call first self::initBeforeCalcCondition()
   * @return a Condition 
   */
  protected function calcConditionWithoutJoins() {
    $fullCondition = new Criteria();
    $this->addFiltersCriteria($fullCondition);
    return $fullCondition;
  }

  /**
   * @pre call first self::initBeforeCalcCondition()
   */
  protected function addJoinsToCondition($c) {
    CdrPeer::addAllJoinsExceptVendorCondition($c);
  }

  /**
   * @pre call first self::initBeforeCalcCondition()
   */
  protected function addOrder($c) {
    $this->addSortCriteria($c);
  }

  public function executeList() {
    $this->initBeforeCalcCondition();
    $filterWithJoins = $this->calcConditionWithoutJoins();
    $this->addJoinsToCondition($filterWithJoins);
    $this->updateVariableFrameWithHeaderInfo($filterWithJoins);
  }

  /**
   * Put in VariableFrame information that will be used both
   * from _list_header and _list module.
   * This allows to compute some values only once.
   *
   * @param $c the filter condition used
   */
  protected function updateVariableFrameWithHeaderInfo($c) {

    VariableFrame::$filterCondition = clone($c);

    $filterWithOrder = clone($c);
    $this->addOrder($filterWithOrder);
    
    VariableFrame::$filterConditionWithOrder = $filterWithOrder;

    list($startDate, $endDate) = $this->getAndUpdateTimeFrame();
    VariableFrame::$startFilterDate = $startDate;
    VariableFrame::$endFilterDate = $endDate;

    VariableFrame::$showChannelUsage = $this->getFlash('show_channel_usage');
    if (is_null(VariableFrame::$showChannelUsage)) {
      VariableFrame::$showChannelUsage = FALSE;
    }
    
    // Compute values

    $c2 = clone($c);
    $c2->clearSelectColumns();
    $c2->addSelectColumn('COUNT(' . CdrPeer::ID . ')');     // field 0
    $c2->addSelectColumn('SUM(' . CdrPeer::BILLSEC . ')');  // field 1
    $c2->addSelectColumn('SUM(' . CdrPeer::INCOME . ')');   // field 2
    $c2->addSelectColumn('SUM(' . CdrPeer::COST . ')');     // field 3
    $rs = CdrPeer::useCalldateIndex($c2);
    //
    // NOTE: use a personalized "useCalldateIndex" of "lib/model/CdrPeer.php"
    // in order to create an optimized version of MySQL query associated
    // to the current filter.
    
    $totCalls = 0;
    $totSeconds = 0;
    $totIncomes = 0;
    $totCosts = 0;
    $totEarn = 0;
    
    foreach($rs as $rec) {
      $totCalls += $rec[0];
      $totSeconds += $rec[1];
      $totIncomes += $rec[2];
      $totCosts += $rec[3];
      $totEarn += $totIncomes - $totCosts;
    }
    
    VariableFrame::$countOfRecords = $totCalls;
    VariableFrame::$totSeconds = $totSeconds;
    VariableFrame::$totIncomes = $totIncomes;
    VariableFrame::$totCosts = $totCosts;
    VariableFrame::$totEarn = $totEarn;
  }

  /**
   * Add to $c a filter on curret web accounts CDR
   * in order to display only CDRs viewable from
   * proper accounts.
   */
  protected function addCurrentAccountViewableCdrsCriteria($c) {
    // add filter criteria.
    // NOTE: the joins are already added/processed from
    // "lib/model/CdrPeer::doSelectJoinAllExceptVendor()"
    //
    <?php if ($generateForCustomer || $generateForOffice) { ?>
      $partyId = $this->getUser()->getPartyId();
      $c->add(ArOfficePeer::AR_PARTY_ID, $partyId);
    <?php } else if ($generateForOffice) { ?>
      $officeId = $this->getUser()->getOfficeId();
      $c->add(ArAsteriskAccountPeer::AR_OFFICE_ID, $officeId);
    <?php } ?>
  }

  /**
   * Override addSortCriteris in order to add a more strict filter.
   *
   * POSTCONDITION: the resulting $c does not contain any select field
   * (required from the pager that adds its fields)
   *
   * NOTE: the enabled/disabled filters must the same configured in 
   * generator.yml, filters section. 
   */
  protected function addFiltersCriteria($c) {
    // Process filter_on_party
    //
    $filterOnPartyApplied = false;
    $partyId = null;
<?php if ($generateForAdmin) { ?>
   if (isset($this->filters['filter_on_params'])) {
     $paramId = $this->filters['filter_on_params'];
     if ($paramId == "" || $paramId == -1) {
       $paramId = null;
       unset($this->filters['filter_on_params']);
     } else {
       $c->add(ArPartyPeer::AR_PARAMS_ID, $paramId);
     }
   }

   if (isset($this->filters['filter_on_party'])) {
     $partyId = $this->filters['filter_on_party'];
     $filterOnPartyApplied = true;
     if ($partyId == "" || $partyId == -1) {
       $filterOnPartyApplied = false;
       $partyId = null;
       unset($this->filters['filter_on_party']);
     }
   }
<?php } else { ?>
      // a customer/office has a default filter on party
      //
      $partyId = $this->getUser()->getPartyId();
      $filterOnPartyApplied = true;
<?php } ?>

      // apply the filter
      //
      if ($filterOnPartyApplied) {
        $c->add(ArPartyPeer::ID, $partyId);
	$filterOnPartyApplied = true;
      }

    // Process filter_on_office
    //
    $filterOnOfficeApplied = false;
    $accountId = null;

<?php if ($generateForAdmin || $generateForCustomer) { ?>

    if (isset($this->filters['filter_on_office']) && $filterOnPartyApplied == true) {
      $officeId = $this->filters['filter_on_office'];
      if ($officeId != "" && $officeId != -1) {
        if ($this->getUser()->hasCredentialOnOffice($officeId)) {
          $filterOnOfficeApplied = true;
	} else {
          unset($this->filters['filter_on_account']);
	  $officeId = null;
	}
      }
    }
<?php } else { ?>
  
    // in case of office account this filter is applied by default
    //
    $officeId = $this->getUser()->getOfficeId();
    $filterOnOfficeApplied = true;
<?php } ?>

    // apply the filter
    //
    if ($filterOnOfficeApplied) {
      $c->add(ArOfficePeer::ID, $officeId);
    }

    // Process filter_on_account
    //
    $filterOnAccountApplied = false;
    if (isset($this->filters['filter_on_account']) && $filterOnOfficeApplied == true) {
      $accountId = $this->filters['filter_on_account'];
      if ($accountId != "" && $accountId != -1) {
        $c->add(ArAsteriskAccountPeer::ID, $accountId);
        $filterOnAccountApplied = true;
      }
    }

    // Process filter_on_destination_type
    // 
<?php if ($displayFilterOnCallDirection) { ?>
    $filterOnDestinationTypeApplied = false;
    if (isset($this->filters['filter_on_destination_type'])) {
      $destinationType = $this->filters['filter_on_destination_type'];
      if ($destinationType != "") {
        $c->add(CdrPeer::DESTINATION_TYPE, $destinationType);
	$filterOnDestinationTypeApplied = true;
      }
    }
<?php } else { ?>
    $filterOnDestinationTypeApplied = false;
<?php } ?>

    <?php if ($generateForAdmin) { ?>
      // Admin can view all types of destination types except
      // unprocessed and ignored calls, that are displayed on
      // separate reports.
      //
      if (!$filterOnDestinationTypeApplied) {
	DestinationType::addAdminFiltersAccordingConfiguration($c);
      }
    <?php } else { ?>
      // Normal users do not see unprocessed/ignored calls
      //
      if (!$filterOnDestinationTypeApplied) {
	DestinationType::addCustomerFiltersAccordingConfiguration($c);
      }
    <?php }?>
 
    // NOTE: filter_on_account and filter_on_office are enabled
    // only if it is enabled also filter_on_party 

    // Process filter_on_vendor
    //
<?php if ($generateForAdmin) { ?>
    if (isset($this->filters['filter_on_vendor'])) {
      $vendorId = $this->filters['filter_on_vendor'];
      if ($vendorId != "") {
        $c->add(CdrPeer::VENDOR_ID, $vendorId);
      } else {
        unset($this->filters['filter_on_vendor']);
      }
    }
<?php } ?>

    // Manage time frame
    //
    $this->addFilterOnTimeFrame($c);

    // Filter on type of destination number according prefix table
    //
    if (isset($this->filters['filter_on_dst_operator_type'])) {
      $loc = $this->filters['filter_on_dst_operator_type'];
      if (strlen(trim($loc)) != 0) {
        $c->add(ArTelephonePrefixPeer::OPERATOR_TYPE, $loc);
      }
    }

    if (isset($this->filters['filter_on_dst_geographic_location'])) {
      $loc = $this->filters['filter_on_dst_geographic_location'];
      if (strlen(trim($loc)) != 0) {
        $c->add(ArTelephonePrefixPeer::GEOGRAPHIC_LOCATION, $loc);
      }
    }

    if (isset($this->filters['filter_on_external_telephone_number'])) {
      $loc = $this->filters['filter_on_external_telephone_number'];
      if (strlen(trim($loc)) != 0) {
        $c->add(CdrPeer::CACHED_MASKED_EXTERNAL_TELEPHONE_NUMBER, $loc .'%', Criteria::LIKE);
      }
    }
    
    // Show only proper calls for administrator/party/account
    // in the case no relevant filter on it is applied
    //
    if (!($filterOnAccountApplied || $filterOnPartyApplied)) {
      $this->addCurrentAccountViewableCdrsCriteria($c);
    }
    parent::addFiltersCriteria($c);
  }

<?php if ($generateForAdmin) { ?>

    // CODE SPECIFIC FOR ADMIN //

  /**
   * Set to null all the income fields of selected cdrs.
   */
  public function executeResetCallsCost() {
    try {

      // Reset CDRs in the date range
      //
      $this->initBeforeCalcCondition();
      list($fromDate, $toDate) = $this->getAndUpdateTimeFrame();

      $sql = "UPDATE cdr SET destination_type = ? WHERE calldate >= ?";

      if (! is_null($toDate)) {
	$sql .= " AND calldate < ?";
      }

      $conn = Propel::getConnection();
      $stmt = $conn->prepareStatement($sql);

      $stmt->setInt(1, DestinationType::unprocessed);
      $stmt->setTimestamp(2, $fromDate);

      if (! is_null($toDate)) {
	$stmt->setTimestamp(3, $toDate);
      }

      $stmt->executeUpdate();
    } catch(Exception $e) {
      $p = new ArProblem();
      $p->setDuplicationKey($e->getCode());
      $p->setDescription('Error during reset of Calls Cost ' . $e->getCode() . ': ' . $e->getMessage());
      ArProblemException::addProblemIntoDBOnlyIfNew($p);
    }

    // Rerate calls.
    //
    $re = new JobQueueProcessor();
    $re->process();

    return $this->redirect('admin_tt_call_report/list');
  }

  <?php } ?>


  /**
   * Update also VariableFrame::$startFilterDate, 
   * and VariableFrame::$endFilterDate = $endDate.
   *
   * @return list($startDate, $endDate) in unix timestamp format.
   * @pre call first $this->initBeforeCalcCondition();
   */
  protected function getAndUpdateTimeFrame() {
    if ($this->areDateCached == TRUE) {
      return array($this->cachedStartDate, $this->cachedEndDate);
    }

    $fromDate = null;
    $toDate = null;

    if (isset($this->filters['filter_on_calldate_from']) && trim($this->filters['filter_on_calldate_from']) != '') {
      $fromDate = fromSymfonyDateToUnixTimestamp($this->filters['filter_on_calldate_from']);
    }

    if (isset($this->filters['filter_on_calldate_to']) && trim($this->filters['filter_on_calldate_to'] != '')) {
      $toDate = fromSymfonyDateToUnixTimestamp($this->filters['filter_on_calldate_to']);
    }

    if (isset($this->filters['filter_on_timeframe'])) {
      $frame = $this->filters['filter_on_timeframe'];
    } else {
      $frame = 0;
    }

    switch ($frame) {
      case '0':
	if (is_null($fromDate)) {
	  // set a default timeframe in order to avoid the processing
	  // of all made calls.
	  //
	  if ($this->getUser()->hasCredential('admin')) {
	    // only 2 days for an admin because he sees the calls
	    // of all his customers and so it is a lot of data.
	    //
	    $fromDate = strtotime("-1 day");
	    $this->filters['filter_on_timeframe'] = '2';
	  } else {
	    // a reasonable default for a customer
	    //
	    $fromDate = strtotime("-2 week");
	    $this->filters['filter_on_timeframe'] = '4';
	  }
	}
	break;
      case '1':
        $fromDate = strtotime("today");
      break;
      case '2':
        $fromDate = strtotime("-1 day");
      break;
      case '3':
        $fromDate = strtotime("-1 week");
      break;
      case '4':
        $fromDate = strtotime("-2 week");
      break;
      case '5':
        $fromDate = strtotime("-1 month");
      break;
      case '6':
        $fromDate = strtotime("-2 month");
      break;
      case '7':
        $fromDate = strtotime("-4 month");
      break;
      case '20':
	// this month
	//
	$now = time();
	$mm = date('m', $now);
	$yy = date('Y', $now);
	$dd = date('t', $now);
	$fromDate = strtotime("$yy-$mm-01");
	$toDate = strtotime("$yy-$mm-$dd");
	$toDate = strtotime("+1 day", $toDate);
	break;
      case '21':
	// last month
	//
	$now = time();
	$mm = date('m', $now);
	$yy = date('Y', $now);

	$mm--;
	if ($mm < 1) {
	  $mm = 12;
	  $yy--;
	}
	$fromDate = strtotime("$yy-$mm-01");
	$dd = date('t', $fromDate);
	$toDate = strtotime("$yy-$mm-$dd");
	$toDate = strtotime("+1 day", $toDate);
	break;
      default:
        trigger_error(__("timestamp filter not recognized") . ":" . $frame);
        $this->forward404();
    }

    $this->cachedStartDate = $fromDate;
    $this->cachedEndDate = $toDate;
    $this->areDateCached = TRUE;

    return array($fromDate, $toDate);
  }


  /**
   * Apply a filter on time frame.
   *
   * @param $c the initial condition
   * @return $c the final condition with the new constraints
   */
  protected function addFilterOnTimeFrame($c) {
    list($fromDate, $toDate) = $this->getAndUpdateTimeFrame();

    if (is_null($toDate)) {
      $filterFromDate = fromUnixTimestampToMySQLDate($fromDate);
      $c->add(CdrPeer::CALLDATE, $filterFromDate, Criteria::GREATER_EQUAL);
    } else {
      $filterFromDate = fromUnixTimestampToMySQLDate($fromDate);
      $filterToDate = fromUnixTimestampToMySQLDate($toDate);

      $c2  = $c->getNewCriterion(CdrPeer::CALLDATE, $filterFromDate, Criteria::GREATER_EQUAL);
      $c2->addAnd($c->getNewCriterion(CdrPeer::CALLDATE, $filterToDate, Criteria::LESS_THAN));
      $c->add($c2);
    }

    $this->filters['filter_on_calldate_to'] =  fromUnixTimestampToSymfonyStrDate($toDate);
    $this->filters['filter_on_calldate_from'] = fromUnixTimestampToSymfonyStrDate($fromDate);
  }
}

<?php 
echo '?>' . "\n";
?>
