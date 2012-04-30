<?php

/* $LICENSE 2009, 2010, 2011:
 *
 * Copyright (C) 2009, 2010 Massimo Zaniboni <massimo.zaniboni@profitoss.com>
 *
 * This file is part of Asterisell.
 *
 * Asterisell is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Asterisell is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Asterisell. If not, see <http://www.gnu.org/licenses/>.
 * $
 */

sfLoader::loadHelpers(array('I18N', 'Debug', 'Date', 'Asterisell'));

/**
 * Rate all unrated CDR records.
 *
 * This can be a time-consuming, and resource intensive job.
 * It is typically invoked from the cron-processor, so there are no resouce constraints.
 *
 * But It can be executed also inside a web-enviroment, when invoked from the user.
 * In this case there can be interruptions due to resource constraints.
 * The algorithm choosen is robust respect these interruptions.
 *
 * There can be also interruptions due to database errors or missing rates.
 *
 * All errors are signaled in the error table, and CDR with problems will be not rated
 * until all the problems are fixed.
 *
 * The rates are of two types:
 *  - simple rate working on single CDR;
 *  - bundle / incremental rates preserving a state during CDR processing;
 */
class RateCalls extends FixedJobProcessor {

    /**
     * Used to cache the PhpRate,
     * otherwise they must be unserialized from database every time.
     *
     * It contains values of the type:
     * 
     * > (DestinationType) => (ArRate.getRateType()) => (ArRate.Id) => (ArRate, PhpRate)
     *
     */
    protected $rateCache = NULL;
    protected $bundleRateInfoCache = null;
    
    /**
     * Contains a list of bundle rate periods to recalculate,
     * because the bundle incremental info was not updated.
     *
     * Structure: { $arPartyId => { $isCustomer, $startDate, $endDate } }
     *
     * where startDate and endDate represent the interval of CDR
     * to evaluate again, in order to update the incremental info,
     * $isCustomer is TRUE if it represent a Customer, FALSE if it represent a Vendor.
     */
    protected $bundleRatePeriodsToRecalculate = array();
    /**
     * An association list of the type
     *
     * { ar_rate_incremental_info.id  => 1 }
     *
     * containins all the ar_rate_incremental_info that are corrupted, and must be recalculated.
     */
    protected $bundleRateIncrementalInfoToReset = array();

    /**
     * Add the period of a bundle rate in a queue, and mark it has corrupted.
     * It will be recalculated at next processing steps because
     * incremental info was not updated.
     *
     * @param  $isParty
     * @param  $partyId
     * @param  $startDate
     * @param  $endDate
     * @param  $id
     * @return void
     */
    protected function addPeriodToRecalculate($isParty, $partyId, $startDate, $endDate, $arRateIncrementalInfoId) {
        if (array_key_exists($partyId, $this->bundleRatePeriodsToRecalculate)) {

            // expand the deletion period if needed
            if ($startDate < $this->bundleRatePeriodsToRecalculate[$partyId][1]) {
                $this->bundleRatePeriodsToRecalculate[$partyId][1] = $startDate;
            }

            if ($endDate > $this->bundleRatePeriodsToRecalculate[$partyId][2]) {
                $this->bundleRatePeriodsToRecalculate[$partyId][2] = $endDate;
            }
        } else {
            $this->bundleRatePeriodsToRecalculate[$partyId] = array($isParty, $startDate, $endDate);
        }

        $this->bundleRateIncrementalInfoToReset[$arRateIncrementalInfoId] = 1;
    }

    /**
     * Reset (set as to recalculate) the CDRs with invalidated incremental info.
     * Reset the corresponding Bundle Rate Incremental Info,
     * before starting with a new rating process from scratch.
     *
     * @return void
     */
    protected function resetCDRPeriodsToRecalculate() {
        try {
            $conn = Propel::getConnection();

            // Set all CDRs in the time period of the specified party/vendor as to rate...
            foreach ($this->bundleRatePeriodsToRecalculate as $partyId => $period) {
                $isCustomer = $period[0];
                $fromDate = $period[1];
                $toDate = $period[2];

                $sql = "UPDATE cdr SET destination_type = " . DestinationType::unprocessed
                        . " WHERE cdr.calldate >= \"" . fromUnixTimestampToMySQLDate($fromDate) . "\""
                        . "  AND cdr.calldate < \"" . fromUnixTimestampToMySQLDate($toDate) . "\"";

                if ($isCustomer) {
                    // Delete each asterisk account id using a separate query
                    $c = new Criteria();
                    $c->addJoin(ArAsteriskAccountPeer::AR_OFFICE_ID, ArOfficePeer::ID);
                    $c->add(ArOfficePeer::AR_PARTY_ID, $partyId);
                    $c->clearSelectColumns();
                    $c->addSelectColumn(ArAsteriskAccountPeer::ID);
                    $rs = ArAsteriskAccountPeer::doSelectRS($c);
                    while ($rs->next()) {
                        $accountdId = $rs->getInt(1);

                        $sql .= " AND cdr.ar_asterisk_account_id = $accountdId ";
                        $conn->executeUpdate($sql);
                    }
                } else {
                    $sql .= " AND cdr.vendor_id = $partyId;";
                    $conn->executeUpdate($sql);
                }

                $conn->executeUpdate($sql);
            }

            // Delete incremental info.
            // It will be restored with default values during next rating passage.
            // NOTE: incremental info can be safely deleted because it is reconstructed
            // on needs. There is no loss of data.
            foreach ($this->bundleRateIncrementalInfoToReset as $id => $ignore) {
                $sql = "DELETE FROM  ar_rate_incremental_info WHERE id = " . $id . ";";
                $conn->executeUpdate($sql);
            }


            // Reset info about CDR to recalculate
            $this->bundleRateIncrementalInfoToReset = array();
            $this->bundleRatePeriodsToRecalculate = array();
            $this->bundleRateInfoCache = null;
        } catch (Exception $e) {
            $p = new ArProblem();
            $p->setDuplicationKey("resetCDRPeriodsToRecalculate " . $e->getCode());
            $p->setDescription('Error during reset of Calls Cost (for updating incremental bundle rate information) using the query "' . $sql . '". Error messages is: ' . $e->getMessage());
            $p->setEffect("If the error is a not repeating error, there is no effect. At the next run of the job processor the remaining CDRs will be rated. If this is a recurring error, then there is an error inside the code and the affected CDRs will be not rated.");
            $p->setProposedSolution("Delete the error table. If the error is recuring, contact the developer, because this is a code error.");
            ArProblemException::addProblemIntoDBOnlyIfNew($p);
        }
    }

    /**
     * Rate all pending CDRs, and force revaluation of CDR associated to incremental bundle rate
     * with corrupted incremental info.
     *
     * So this a two stage process. In the first stage, the unrated CDRs are rated.
     * In the next stage, if there were corrupted incremental info, then the period
     * is re-valuated.
     *
     * The second stage occurs only if there are CDR with problems or an explicit rerating of old CDRs were
     * requested from the user.
     *
     * @return always TRUE. Errors are reported on the error table.
     */
    public function process() {
        // first rating pass
        $msg1 = $this->process1();

        $msg2 = "";
        if (count($this->bundleRateIncrementalInfoToReset) > 0) {
            // there are periods to recalculate

            $this->resetCDRPeriodsToRecalculate();
            $msg2 = "\nThere were bundle rate periods to fix. CDRs inside the period will be rated from scratch. \n" . $this->process1();

            if (count($this->bundleRateIncrementalInfoToReset) > 0) {
                $p = new ArProblem();
                $p->setDuplicationKey("Still broken bundle rate incremental info");
                $p->setDescription('Error during rating of CDR. There are bundle rates, that have still broken incremental info, also after a recalculation pass.');
                $p->setEffect("Not all CDRs are rated.");
                $p->setProposedSolution("This is an error inside code, because this condition should not occur. Contact the developer/support team.");
                ArProblemException::addProblemIntoDBOnlyIfNew($p);
            }
        }

        return ($msg1 . $msg2);
    }

    public function process1() {
        
        // Profiling
        $time1 = microtime_float();
        $nrOfRates = 0;

        // start with an empty cache
        $this->bundleRateInfoCache = new RateIncrementalInfoCache();

        $chunk = 0;
        if (JobQueueProcessor::$IS_WEB_PROCESS) {
            $chunk = 500;
            // in web-processing there are few resources, 
            // so reduce the usage of RAM using smaller chunks
        } else {
            $chunk = 10000;
        }

        // Execute the work in chucks because there is a memory leak
        // in Propel, and record-set and other resources are not released.
        $maxDate = NULL;
        do {
            list($newMaxRate, $nr) = $this->process2($maxDate, $chunk);
            $nrOfRates += $nr;
            $maxDate = $newMaxRate;
        } while ($nr >= $chunk && JobQueueProcessor::isThereTimeForOtherJobs());

        // Store incremental data on the database
        // NOTE: also if this operation fails, after CDR rating,
        // it is not a consistency problem, because incremental-info
        // will be calculated the next-time, again. So it is only
        // a performance problem.
        if (!is_null($this->bundleRateInfoCache) && JobQueueProcessor::isThereTimeForOtherJobs()) {
            $this->bundleRateInfoCache->closeAndUpdateDatabase();
        }

        // Profiling
        $time2 = microtime_float();
        $totTime = $time2 - $time1;
        $meanTime = "n.c.";
        $rateForSecond = "n.c.";
        if ($nrOfRates > 0) {
            $meanTime = $totTime / $nrOfRates;
            if ($meanTime > 0) {
                $rateForSecond = 1 / $meanTime;
            }
        }

        return "$nrOfRates calls rated, $rateForSecond calls rated for second, $totTime seconds as total execution time";
    }
    
    /**
     * Rate a chunk of pending CDRs.
     * Errors are reported on the error table.
     * 
     * @param $minCallDate NULL for lower date, a value in MySQL format otherwise
     * @param $limit the max number of CDR to process
     * @return array(the last processed callDate in MySQL format, the number of processed records)
     */
    public function process2($minCallDate, $limit) {

        $telephoneNumbersConfig = sfConfig::get('app_internal_external_telephone_numbers');

        // Retrieve the CDR to process
        $cdrCondition = new Criteria();

        CdrPeer::addSelectColumns($cdrCondition);

        $cdrCondition->addAscendingOrderByColumn(CdrPeer::CALLDATE);
        $cdrCondition->addAscendingOrderByColumn(CdrPeer::ID);
        // NOTE: these ascending orders are very important because the
        // algorithm access the rate cache according the order of calls,
        // and because bundle rate requires an incremental update of their state

        $cdrCondition->add(CdrPeer::DESTINATION_TYPE, DestinationType::unprocessed, Criteria::EQUAL);
        
        if (!is_null($minCallDate)) {
          $cdrCondition->add(CdrPeer::CALLDATE, $minCallDate, Criteria::GREATER_EQUAL);
        }

        $cdrCondition->setLimit($limit);

        $rs = CdrPeer::doSelectRS($cdrCondition);
        
        // Process every $cdr using doSelectRS that fetch only one object at once from DB,
        // this is a *must* because there can be many CDRs records to process.
        $count = 0;
        $maxCallDate = NULL;
        while ($rs->next() && JobQueueProcessor::isThereTimeForOtherJobs()) {
            $count++;
            $cdr = new Cdr();
            $cdr->hydrate($rs);

            $maxCallDate = $cdr->getCalldate();
            
            // Reset some fields of the CDR that can be contain incosistent values from previous rating stage.
            // Now all fields that are not setted from system-rates, will be setted from this method.
            $cdr->resetAll();

            // Initialize the cache of rates
            // NOTE: cache is initialized now because only now we know the starting date
            // and because in this way we can init it only when it is really necessary.
            // NOTE: it is initializated outside of `try`,
            //  because errors on load-cache must suspend completely the rating process
            if (is_null($this->rateCache)) {
              $this->rateCache_loadAll($cdr->getCalldate());
            }
            
            // Try to process the $cdr.
            // NOTE: the contracts is that any problem of called methods
            // is thrown by an exception and signaled to the user in this catch part.
            // Then the processing continue with another $cdr.
            // In this way an error does not block all CDR ratings, but only one CDR record.
            try {

                // Test for malformed cdr
                $cdrProblem = $cdr->isConsistent();
                if (!is_null($cdrProblem)) {
                    $p = new ArProblem();
                    $p->setDuplicationKey("CDR - " . $cdr->getId());
                    $p->setDescription("CDR with id " . $cdr->getId() . " has problem: " . $cdrProblem . $this->showCDRDebugInfo($cdr));
                    $p->setCreatedAt(date("c"));
                    $p->setEffect("The CDR will not be rated.");
                    $p->setProposedSolution("Inspect the CDR and solve the problem. Wait for the next rate pass.");
                    throw (new ArProblemException($p));
                }

                // First apply a rate of type "isForUnprocessedCDR"
                list($rate, $phpRate, $bundleRateInfo, $skip) = $this->getPhpRate($cdr, null, null, "S");
                if ($skip) {
                    // This CDR can not be rated because the bundle rate has not valid incremental info.
                    // It will be processed at the next step.
                    continue;
                }
                $phpRate->processCDR($cdr, $rate, $phpRate, null, $bundleRateInfo, false);

                // Signal a problem if $cdr is again in unprocessed state, and it was not due to invalid incremental info
                if ($cdr->getDestinationType() == DestinationType::unprocessed) {
                    $p = new ArProblem();
                    $p->setDuplicationKey("CDR - " . $cdr->getId());
                    $p->setDescription("CDR with id " . $cdr->getId() . " was processed from rate with id " . $rate->getId() . " but CDR.destination_type is still \"unprocessed\"." . $this->showCDRDebugInfo($cdr));
                    $p->setCreatedAt(date("c"));
                    $p->setEffect("The CDR will not be rated.");
                    $p->setProposedSolution("Inspect the rates configurations, and solve the problem. Wait for the next rate pass.");
                    throw (new ArProblemException($p));
                }  else if ($cdr->getDestinationType() == DestinationType::known_error) {
                    // continue with the next CDR, because the problem was already signaled.
                    // NOTE: The CDR is not saved so it remains to unprocessed state at the next rating process.
                    continue;
                }

                // This CDR is classified from the CDR processing as TO IGNORE.
                // No more work to do on it.
                if ($cdr->getDestinationType() == DestinationType::ignored) {
                    $cdr->setCost(0);
                    $cdr->setIncome(0);
                    $cdr->save();

                    continue;
                }

                // Calculate the account, that is the VoIP number associated to the CDR
                $account = null;
                $accountId = $cdr->getArAsteriskAccountId();
                if (is_null($accountId)) {
                      $accountcode = $cdr->getAccountcode();
                      $account = VariableFrame::getArAsteriskAccountByCodeCache()->getArAsteriskAccountByCode($accountcode);
                      if (is_null($account)) {
                        // exit with an error
                        $p = new ArProblem();
                        $p->setDuplicationKey("unknown ArAsteriskAccount $accountcode");
                        $p->setCreatedAt(date("c"));
                        $p->setDescription("\"$accountcode\" Asterisk account code is used in CDR with id \"" . $cdr->getId() . "\", but it is not defined in ArAsteriskAccount table (VoIP Accounts)." . $this->showCDRDebugInfo($cdr));
                        $p->setEffect("All CDRs with this account will not rated.");
                        $p->setProposedSolution("Complete the Asterisk Account table (VoIP accounts). The CDRs will be rated automatically at the next execution pass of Jobs.");
                        throw (new ArProblemException($p));
                     }
                     $accountId = $account->getId();
                } else {
                    $account = VariableFrame::getArAsteriskAccountByIdCache()->getArAsteriskAccountById($accountId);
                    if (is_null($account)) {
                        // exit with an error
                        $p = new ArProblem();
                        $p->setDuplicationKey("unknown ArAsteriskAccountId $accountId");
                        $p->setCreatedAt(date("c"));
                        $p->setDescription("\"$accountId\" Asterisk AccountId is used in CDR with id \"" . $cdr->getId() . "\", but it is not defined in ArAsteriskAccount table (VoIP Accounts)." . $this->showCDRDebugInfo($cdr));
                        $p->setEffect("All CDRs with this account will not rated.");
                        $p->setProposedSolution("It is an error inside some system processing rate, because if an AccountId it is setted, then it must exist for sure. Contact the assistance.");
                        throw (new ArProblemException($p));
                     }
                }
                
                $cdr->setArAsteriskAccountId($accountId);

                // Given an account, office and party can be derived easily
                $office = VariableFrame::getOfficeCache()->getArOffice($account->getArOfficeId());
                $party = VariableFrame::getVendorCache()->getArParty($office->getArPartyId());

                // The more specific costumer category ID.
                $customerCategoryId = NULL;
                $t = $account->getArRateCategoryId();
                if (!is_null($t)) {
                    $customerCategoryId = $t;
                } else {
                    $t = $office->getArRateCategoryId();
                    if (!is_null($t)) {
                        $customerCategoryId = $t;
                    } else {
                        $t = $party->getArRateCategoryId();
                        if (!is_null($t)) {
                            $customerCategoryId = $t;
                        }
                    }
                }

                // Assign internal and external telephone numbers
                // the external number with applied number portability
                $dstNumber = null;

                if ($telephoneNumbersConfig == 3) {
                    // nothing to do, already configured from CDR processing
                    $dstNumber = $cdr->getExternalTelephoneNumberWithAppliedPortability();
                } else {
                    if (is_null($cdr->getCachedInternalTelephoneNumber())) {
                      $cdr->setCachedInternalTelephoneNumber($cdr->calcInternalTelephoneNumber());
                    }
                    
                    if (is_null($cdr->getCachedExternalTelephoneNumber())) {
                      $cdr->setCachedExternalTelephoneNumber($cdr->calcExternalTelephoneNumber());
                    }

                    if (is_null($cdr->getCachedExternalTelephoneNumber())) {
                        $p = new ArProblem();
                        $p->setDuplicationKey("CDR without external telephone number " . $cdr->getId());
                        $p->setCreatedAt(date("c"));
                        $p->setDescription("The CDR record with id " . $cdr->getId() . " has no external telephone number." . $this->showCDRDebugInfo($cdr));
                        $p->setEffect("The CDR is not rated.");
                        $p->setProposedSolution("The problem can reside in the CDR record or in the application configuration. If you change a lot the configuration, you must force a reset and rerate of old calls in order to propagate the effects.");
                        throw (new ArProblemException($p));
                    }

                    // Apply number portability.
                    // Number portability is used for rating the call, but not for displaying it.
                    $dstNumber = $cdr->getExternalTelephoneNumberWithAppliedPortability();
                    if (is_null($dstNumber)) {
                      $dstNumber = $cdr->getCachedExternalTelephoneNumber();
                      if (VariableFrame::getNumberPortabilityCache()->isUnderNumberPortability($dstNumber)) {
                          $dstNumber = ArNumberPortability::checkPortability($dstNumber, $cdr->getCalldate());
                      }
                      $cdr->setExternalTelephoneNumberWithAppliedPortability($dstNumber);
                    }
                }

                // Associate telephone operator prefix, using the ported telephone number.
                // It can be already configured from the CDR processor.
                $telephonePrefixId = $cdr->getArTelephonePrefixId();
                if (is_null($telephonePrefixId)) {
                    $telephonePrefixId = VariableFrame::getTelephonePrefixCache()->getTelephonePrefixId($dstNumber);
                    if (is_null($telephonePrefixId)) {
                        // prepare a missing prefix key: it is a balance between displaying many errors for missing number prefix,
                        // and avoid displaying all missing telephone numbers.
                        $maxLen = strlen($dstNumber);
                        if ($maxLen >= 4) {
                            $maxLen = 4;
                        }
                        $missingPrefix = substr($dstNumber, 0, $maxLen);

                        $p = new ArProblem();
                        $p->setDuplicationKey("no telephone operator prefix " . $missingPrefix);
                        $p->setCreatedAt(date("c"));
                        $p->setDescription('There is no a telephone operator prefix entry associated to the destination number ' . $dstNumber . $this->showCDRDebugInfo($cdr));
                        $p->setEffect("CDRs with destination number of the same type will not be rated.");
                        $p->setProposedSolution("Complete the Telephone Prefixes Table. If you are not interested to classification of calls according their operator, then you can also add an Empty Prefix matching all destination numbers and calling it None.");
                        throw (new ArProblemException($p));
                    }

                    $cdr->setArTelephonePrefixId($telephonePrefixId);
                }

                // Associate masked external telephone number
                if (is_null($cdr->getCachedMaskedExternalTelephoneNumber())) {
                    $nonMasked = $cdr->getCachedExternalTelephoneNumber();
                    $cdr->setCachedMaskedExternalTelephoneNumber($cdr->calcMaskedTelephoneNumber($nonMasked, VariableFrame::getTelephonePrefixCache()->isIdToNeverMask($telephonePrefixId)));
                }

                // calc cost
                list($rate, $phpRate, $bundleRateInfo, $skip) = $this->getPhpRate($cdr, null, $party->getId(), "V");
                if ($skip) {
                    // This CDR can not be rated because the bundle rate has not valid incremental info.
                    // It will be processed at the next step.
                    continue;
                }

                $vendorId = $rate->getArPartyId();
                $phpRate->processCDR($cdr, $rate, $phpRate, $vendorId, $bundleRateInfo, true);

                // calc income
                list($rate, $phpRate, $bundleRateInfo, $skip) = $this->getPhpRate($cdr, $customerCategoryId, $party->getId(), "C");
                if ($skip) {
                    // This CDR can not be rated because the bundle rate has not valid incremental info.
                    // It will be processed at the next step.
                    continue;
                }
                $phpRate->processCDR($cdr, $rate, $phpRate, $party->getId(), $bundleRateInfo, false);

                // Save the CDR if is completely processed.
                // Otherwise it is not saved and it remains in unprocessed state.
                $cdr->save();
                unset($cdr);
            } catch (ArProblemException $e) {
                $e->addThisProblemIntoDBOnlyIfNew();
            } catch (Exception $e) {
                $p = new ArProblem();
                $p->setDuplicationKey($e->getCode());
                $p->setDescription($e->getCode() . ': ' . $e->getMessage());
                ArProblemException::addProblemIntoDBOnlyIfNew($p);
            }
        }

        return array($maxCallDate, $count);
    }

    /**
     * Put in rate cache all the rates applicable to cdrs tarting from calldate $firstDate.
     *
     * Return an exception in case of problems.
     *
     * Note: this method is "efficent" because only valid rates
     * at $cdr->getCalldate must be loaded in the cache.
     */
    protected function rateCache_loadAll($firstDate) {
        // Select all rates active at the moment of $firstDate and
        // that will be active for dates greather than $firstDate,
        // that will be the next CDRs.

        $this->rateCache = array();
        
        $rateCondition = new Criteria();
        $rateCondition1 = $rateCondition->getNewCriterion(ArRatePeer::END_TIME, null, Criteria::EQUAL);
        $rateCondition1->addOr($rateCondition->getNewCriterion(ArRatePeer::END_TIME, $firstDate, Criteria::GREATER_EQUAL));
        $rateCondition->add($rateCondition1);
        $rates = ArRatePeer::doSelect($rateCondition);
        foreach ($rates as $rate) {
            $this->checkRateConstraints($rate);

            $destinationType = $rate->getDestinationType();
            $phpRate = $rate->unserializePhpRateMethod();
            $rateType = $rate->getRateType();

            // > (DestinationType) => (ArRate.getRateType()) => (ArRate.Id) => (ArRate, PhpRate)
            $this->rateCache[$destinationType][$rateType][$rate->getId()] = array($rate, $phpRate);
        }
    }
    
    /**
     * Search in RateCache the correct rate that can be applied to $cdr.
     *
     * Call addPeriodToRecalculate in case of problems with bundle incremental info.
     *
     * @precondition: the rateCache is updated with current (respect $cdr) rates
     * @precondition: $cdr are rated in ascending order respect Calldate
     * @precondition: $categoryIndex is NULL for system-rates and vendor-rates
     * @precondition: $categoryIndex is the category id of the party associated to the $cdr,
     *                if it is not a system rate or a vendor-rate
     *
     * @param $cdr the $cdr to wich the rate must be applied
     * @param $categoryIndex null for a system rate or vendor-rate, the ArRateCategory.id of the party associated to the CDR for a customer-party
     * @param $arPartyId the ar_party.id of the customer to wich is associated the $cdr, null if it is a system rate
     * @param $rateType "C" for customer rate, "V" for vendor rate, "S" for system rate
     *
     * @return an array (ArRate, PhpRate, BundleRateIncrementalInfo, $skip) with the applicable rate.
     * $skip to True if the CDR can not be rated because it is a Bundle with a broken incremental info cache.
     * In this case the period to recalculate is put on the proper list of periods to recalculate, and these CDRs
     * must be completed in a second calculation passage.
     *
     * @throw ArProblemException in case of missing or not unique rates to apply.
     *
     */
    protected function getPhpRate($cdr, $categoryIndex, $arPartyId, $requestedRateType) {
        $destinationType = $cdr->getDestinationType();
        $cdrDate = $cdr->getCalldate();

        // At the end of the process these will be setted to the best found values,
        // if $thereIsConflict == false.
        $thereIsConflict = false;
        $thereIsFitnessMethodConflict = false;
        $resultRate = null;
        $resultPhpRate = null;
        $bestBundleRateInfo = null;
        $bestFitness = 0;
        $bestRateFitnessMethod = null;
        $bestRatePriority = 0;

        // Search the rate in the cache with format
        //
        // > (DestinationType) => (ArRate.getRateType()) => (ArRate.Id) => (ArRate, PhpRate)
        $ratesToTest = FALSE;
        if (array_key_exists($destinationType, $this->rateCache)) {
            if (array_key_exists($requestedRateType, $this->rateCache[$destinationType])) {
                $ratesToTest = TRUE;
            }
        }

        if ($ratesToTest) {
            // compare each rate. In case of permanent conflict, 
            // try other rates because there can be a high-priority rate
            // overriding all other conflicts.
            //
            foreach ($this->rateCache[$destinationType][$requestedRateType] as $index => $rateAndPhpRate) {
                $rate = $rateAndPhpRate[0];
                $phpRate = $rateAndPhpRate[1];
                $rateFitnessMethod = $phpRate->getPriorityMethod();
                $isException = $rate->getIsException();
                $isBundleRate = false;
                $rateInfoId = null;
                $rateInfo = null;
                $rateType = $requestedRateType;
                assert($rate->getRateType() === $requestedRateType);

                if ($cdrDate >= $rate->getStartTime() && (is_null($rate->getEndTime()) || $cdrDate < $rate->getEndTime())
                        && $categoryIndex === $rate->getArRateCategoryId()) {

                    $rateInfo = null;
                    if ($phpRate instanceof BundleRate) {
                        $isBundleRate = true;

                        $period = $phpRate->getPeriod(strtotime($cdrDate));
                        $subjectId = 0;
                        if ($rateType === "C") {
                            // bundle-rate incremental-info is associated to the customer
                            $subjectId = $arPartyId;
                        } else if ($rateType === "V") {
                            // bundle-rate incremental-info is associated to the vendor
                            $subjectId = $rate->getArPartyId();
                        } else {
                            $p = new ArProblem();
                            $p->setDuplicationKey("system-rate of type bundle " . $rate->getId());
                            $p->setDescription("Rate with id=" . $rate->getId() . " is a system-rate of type bundle.");
                            $p->setCreatedAt(date("c"));
                            $p->setEffect("CDRs will not be rated, because there is an error in the specification of rates.");
                            $p->setProposedSolution("Bundle rates can only of type vendor or customer. Fix the corresponding rate, and wait for the next rate pass.");
                            throw (new ArProblemException($p));
                        }
                        list($rateInfoId, $rateInfo, $isCorrupted) = $this->bundleRateInfoCache->get($phpRate, $subjectId, $rate->getId(), $cdr, $period);

                        if ($isCorrupted) {
                            // in this case the bundle rate contains an invalidated incremental info
                            // and the info must be recalculated, and skip all the rest...
                            //
                            // NOTE: and invalid rate is not good also for determining fitness of a bundle rate,
                            // and so it must stop immediately.
                            $isCustomerRate = ($rateType === "C");
                            $this->addPeriodToRecalculate($isCustomerRate, $subjectId, $phpRate->getPeriodStartDate($period), $phpRate->getPeriodEndDate($period), $rateInfoId);
                            return array(null, null, null, true);
                        }
                    }
                    $fitness = $phpRate->isApplicable($cdr, $rateInfo);

                    // calculate the priority (except $fitness) in order to make a distinction between exception/bundle rates
                    // and normal rates.
                    // First rates are ordered on priority, then on fitness.
                    $ratePriority = 0;
                    if ($isException) {
                        $ratePriority += 10;
                    }
                    if ($isBundleRate) {
                        $ratePriority += 5;
                    }

                    $isBestFit = false; // starts pessimistic
                    if ($fitness != 0) {
                        //
                        // This rate is applicable.
                        if ($ratePriority > $bestRatePriority) {
                            // An higher priority rate.
                            // Its removes also conflicts caused by other lower priority rates,
                            // also if they are based on different fitness methods.
                            $isBestFit = true;
                            $thereIsConflict = false;
                            $thereIsFitnessMethodConflict = false;
                        } else if ($ratePriority < $bestRatePriority) {
                            // a low priority rate is never a bestFit
                            $isBestFit = false;
                        } else if ($ratePriority === $bestRatePriority) {
                            if (is_null($bestRateFitnessMethod)) {
                                // this is the first applicable rate
                                $isBestFit = true;
                            } else {
                                if (strcmp($rateFitnessMethod, $bestRateFitnessMethod) == 0) {
                                    // The two fitness methods are the same, so fitness are comparable...
                                    if ($bestFitness === $fitness) {
                                        // it is not admissible to have two rates with the same fitness
                                        $thereIsConflict = true;
                                    } else if ($fitness > $bestFitness) {
                                        $isBestFit = true;
                                    } else {
                                        $isBestFit = false;
                                    }
                                } else {
                                    // It is not admissible to have two different rates with different fitness methods,
                                    // because their fitness values are not comparable...
                                    $thereIsConflict = true;
                                    $thereIsFitnessMethodConflict = true;
                                }
                            }
                        }
                    }

                    // update bestFitness rate
                    if ($isBestFit) {
                        $bestFitness = $fitness;
                        $bestRatePriority = $ratePriority;
                        $bestRateFitnessMethod = $rateFitnessMethod;
                        $bestBundleRateInfo = $rateInfo;
                        $resultPhpRate = $phpRate;
                        $resultRate = $rate;
                    }
                }
            }
        }

        if ($thereIsFitnessMethodConflict) {
            $thereIsConflict = true;
        }

        // Analyze the candidate results...
        if ($thereIsConflict || is_null($resultRate)) {
            $p = new ArProblem();

            // These variables are used in order to set duplication-key.
            $startOfDescr = '';
            $categoryName = '';
            $dateStr = date("Y-m-d", strtotime($cdr->getCalldate()));

            // Create the error descritpion and initialize duplication-key
            // variables.

            if ($thereIsConflict) {
                $descr = "Too many Rate to apply";
            } else {
                $descr = "No Rate to apply";
            }
            $descr .= " at date " . $dateStr;
            $descr .= " of type ";
            if ($requestedRateType === "S") {
                $descr .= " system (initial classification) ";
            } else if ($requestedRateType === "V") {
                $descr .= " vendor (calculating a cost) ";
            } else {
                $descr .= " customer (calculating an income) ";
            }

            $descr .= ' on CDR with id "' . $cdr->getId() . '", and destination_type "' . DestinationType::getUntraslatedName($cdr->getDestinationType()) . '" ';
            if ($requestedRateType === "C") {
                $categoryR = ArRateCategoryPeer::retrieveByPK($categoryIndex);
                $categoryName = $categoryR->getName();
                $descr .= ' for customer of price category "' . $categoryName . '"';
            }

            if (!is_null($cdr->getCachedExternalTelephoneNumber())) {
                // in case of system-rates these fields are not already computed...
                $descr .= ' for external telephone number "' . $cdr->getCachedExternalTelephoneNumber() . '" (with number portability applied it is "' . $cdr->getCachedExternalTelephoneNumberWithAppliedPortability() . '"), ';
            }
            $descr .= 'and for dstchannel "' . $cdr->getDstchannel() . '"' . ' and amaflags "' . $cdr->getAmaflags() . '"';

            $p->setDescription($descr . $this->showCDRDebugInfo($cdr));
            $p->setDuplicationKey($startOfDescr . " - " . $dateStr . " - " . $categoryName . " - " . $cdr->getDstchannel());
            $p->setCreatedAt(date("c"));
            $p->setEffect("CDRs in the given rate interval will not be rated.");
            $p->setProposedSolution("Complete the rate table and wait for the next rate pass. Use menu entry Calls/Unprocessed Calls, for inspecting all details of unprocessed calls.");
            throw (new ArProblemException($p));
        } else {
            return array($resultRate, $resultPhpRate, $bestBundleRateInfo, false);
        }
    }

    /**
     * @throw ArProblemException
     */
    protected function isCdrToRate($cdr) {
        // Malformed cdr
        $cdrProblem = $cdr->isConsistent();
        if (!is_null($cdrProblem)) {
            $p = new ArProblem();
            $p->setDuplicationKey("CDR - " . $cdr->getId());
            $p->setDescription("CDR with id " . $cdr->getId() . " has problem: " . $cdrProblem . $this->showCDRDebugInfo($cdr));
            $p->setCreatedAt(date("c"));
            $p->setEffect("The CDR will not be rated.");
            $p->setProposedSolution("Inspect the CDR and solve the problem. Wait for the next rate pass.");
            throw (new ArProblemException($p));
        }
        return true;
    }

    /**
     * @throw ArProblemException if the ArRate does not respect constraints
     */
    protected function checkRateConstraints($rate) {
        if (is_null($rate->getPhpClassSerialization())) {
            $p = new ArProblem();
            $p->setDuplicationKey("Rate - " . $rate->getId());
            $p->setDescription("Rate with id " . $rate->getId() . " has NULL internal phpRate.");
            $p->setCreatedAt(date("c"));
            $p->setEffect("Rating process will not start until the problem is fixed.");
            $p->setProposedSolution("Fix the rate and wait the next rate process or force a rerate of calls.");
            ArProblemException::addProblemIntoDBOnlyIfNew($p);
            // signal the problem in the usual way...
            
            throw (new ArProblemException($p));
            // ... and stop immediately
        }

        if ($rate->getRateType() === "X") {
            $p = new ArProblem();
            $p->setDuplicationKey("Rate - " . $rate->getId());
            $p->setDescription("Rate with id " . $rate->getId() . " has a bad/error-prone format.");
            $p->setCreatedAt(date("c"));
            $p->setEffect("Rating process will not start until the problem is fixed.");
            $p->setProposedSolution("Fix the rate and wait the next rate process or force a rerate of calls. Customer Rates must have only price-category field. Vendor Rates must have only vendor field. System rates can not have price-category and vendor field. Bundle rates can be Customer Rates, or Vendor Rates, but not System Rates.");
            ArProblemException::addProblemIntoDBOnlyIfNew($p);
            // signal the problem in the usual way...

            throw (new ArProblemException($p));
            // ... and stop immediately
        }
    }

    protected function showCDRDebugInfo($cdr) {
      return "\nCDR content is " . $cdr->getDebugDescription() . "\n";
    }
}

?>