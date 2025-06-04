<?php

namespace Google\Service\AnalyticsReporting;

/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();
class TransactionData extends \Google\Model {
	public $transactionId;
	public $transactionRevenue;
	public $transactionShipping;
	public $transactionTax;
	public function setTransactionId($transactionId) {
		$this->transactionId = $transactionId;
	}
	public function getTransactionId() {
		return $this->transactionId;
	}
	public function setTransactionRevenue($transactionRevenue) {
		$this->transactionRevenue = $transactionRevenue;
	}
	public function getTransactionRevenue() {
		return $this->transactionRevenue;
	}
	public function setTransactionShipping($transactionShipping) {
		$this->transactionShipping = $transactionShipping;
	}
	public function getTransactionShipping() {
		return $this->transactionShipping;
	}
	public function setTransactionTax($transactionTax) {
		$this->transactionTax = $transactionTax;
	}
	public function getTransactionTax() {
		return $this->transactionTax;
	}
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias ( TransactionData::class, 'Google_Service_AnalyticsReporting_TransactionData' );
