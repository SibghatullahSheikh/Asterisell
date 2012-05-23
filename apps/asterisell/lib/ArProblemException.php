<?php

/* $LICENSE 2009, 2010:
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

/**
 * A problem to signal into the ArProblem table
 * and to signal as Exception in the PhpCode.
 *
 * The policy is to add English error message with date in universal format (yyyy/mm/dd).
 */
class ArProblemException extends Exception {

  static public $disableNotifications = 0;

  /**
   * Call this method if the notification to admin must be temporary
   * disabled. In this case all new problems will be considered
   * as already sent. This allows to work-online with the program,
   * without sending many warning emails.
   */
  public static function disableNotificationsToAdmin() {
    ArProblemException::$disableNotifications = 1;
  }

  protected $arProblem = null;


  public function __construct(ArProblem $p) {
    $this->arProblem = $p;
    parent::__construct($p->getDescription(), 0);
  }
  public function getArProblem() {
    return $this->arProblem;
  }
  /**
   * Add the problem into the table without generating an Exception.
   */
  static public function signalProblemWithoutException(ArProblem $problem) {
    $exc = new ArProblemException($problem);
    $exc->addThisProblemIntoDBOnlyIfNew();
  }
  /**
   * Add the problem into the table and throw an Exception.
   */
  static public function signalProblemWithException(ArProblem $problem) {
    $exc = new ArProblemException($problem);
    $exc->addThisProblemIntoDBOnlyIfNew();
    throw ($exc);
  }
  /**
   * Add the $this->$arProblem to ar_problem table only if not already present.
   */
  public function addThisProblemIntoDBOnlyIfNew() {
    ArProblemException::addProblemIntoDBOnlyIfNew($this->arProblem);
  }
  /**
   * Add the $p ar_problem only if not already present.
   */
  static public function addProblemIntoDBOnlyIfNew(ArProblem $p) {

      if (ArProblemException::$disableNotifications == 1) {
          // add the problem, but does not advise admin of the problem via mail
          //
          $p->setSignaledToAdmin(1);
      }

      $c = new Criteria();
      $c->add(ArProblemPeer::DUPLICATION_KEY, $p->getDuplicationKey());
      $oldProblem = ArProblemPeer::doSelectOne($c);

      // save the problem only if not present.
      //
      if (is_null($oldProblem)) {
          if (!$p->isNew()) {
              // NOTE: after a rollback a problem is not saved anymore in the database,
              // so force its saving status again to true

              $n = $p->copy();
              $n->save();
          } else {
              $p->save();
          }
      }
  }
}
