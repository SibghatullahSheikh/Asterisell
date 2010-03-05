<?php


abstract class BaseArInvoice extends BaseObject  implements Persistent {


	
	protected static $peer;


	
	protected $id;


	
	protected $ar_party_id;


	
	protected $type = 'C';


	
	protected $is_revenue_sharing = false;


	
	protected $nr;


	
	protected $invoice_date;


	
	protected $ar_cdr_from;


	
	protected $ar_cdr_to;


	
	protected $total_without_tax = 0;


	
	protected $vat_perc = 0;


	
	protected $total_vat = 0;


	
	protected $total = 0;


	
	protected $html_details;


	
	protected $pdf_invoice;


	
	protected $email_subject;


	
	protected $email_message;


	
	protected $already_sent;

	
	protected $aArParty;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

	
	public function getId()
	{

		return $this->id;
	}

	
	public function getArPartyId()
	{

		return $this->ar_party_id;
	}

	
	public function getType()
	{

		return $this->type;
	}

	
	public function getIsRevenueSharing()
	{

		return $this->is_revenue_sharing;
	}

	
	public function getNr()
	{

		return $this->nr;
	}

	
	public function getInvoiceDate($format = 'Y-m-d')
	{

		if ($this->invoice_date === null || $this->invoice_date === '') {
			return null;
		} elseif (!is_int($this->invoice_date)) {
						$ts = strtotime($this->invoice_date);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse value of [invoice_date] as date/time value: " . var_export($this->invoice_date, true));
			}
		} else {
			$ts = $this->invoice_date;
		}
		if ($format === null) {
			return $ts;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $ts);
		} else {
			return date($format, $ts);
		}
	}

	
	public function getArCdrFrom($format = 'Y-m-d')
	{

		if ($this->ar_cdr_from === null || $this->ar_cdr_from === '') {
			return null;
		} elseif (!is_int($this->ar_cdr_from)) {
						$ts = strtotime($this->ar_cdr_from);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse value of [ar_cdr_from] as date/time value: " . var_export($this->ar_cdr_from, true));
			}
		} else {
			$ts = $this->ar_cdr_from;
		}
		if ($format === null) {
			return $ts;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $ts);
		} else {
			return date($format, $ts);
		}
	}

	
	public function getArCdrTo($format = 'Y-m-d')
	{

		if ($this->ar_cdr_to === null || $this->ar_cdr_to === '') {
			return null;
		} elseif (!is_int($this->ar_cdr_to)) {
						$ts = strtotime($this->ar_cdr_to);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse value of [ar_cdr_to] as date/time value: " . var_export($this->ar_cdr_to, true));
			}
		} else {
			$ts = $this->ar_cdr_to;
		}
		if ($format === null) {
			return $ts;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $ts);
		} else {
			return date($format, $ts);
		}
	}

	
	public function getTotalWithoutTax()
	{

		return $this->total_without_tax;
	}

	
	public function getVatPerc()
	{

		return $this->vat_perc;
	}

	
	public function getTotalVat()
	{

		return $this->total_vat;
	}

	
	public function getTotal()
	{

		return $this->total;
	}

	
	public function getHtmlDetails()
	{

		return $this->html_details;
	}

	
	public function getPdfInvoice()
	{

		return $this->pdf_invoice;
	}

	
	public function getEmailSubject()
	{

		return $this->email_subject;
	}

	
	public function getEmailMessage()
	{

		return $this->email_message;
	}

	
	public function getAlreadySent()
	{

		return $this->already_sent;
	}

	
	public function setId($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = ArInvoicePeer::ID;
		}

	} 
	
	public function setArPartyId($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->ar_party_id !== $v) {
			$this->ar_party_id = $v;
			$this->modifiedColumns[] = ArInvoicePeer::AR_PARTY_ID;
		}

		if ($this->aArParty !== null && $this->aArParty->getId() !== $v) {
			$this->aArParty = null;
		}

	} 
	
	public function setType($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->type !== $v || $v === 'C') {
			$this->type = $v;
			$this->modifiedColumns[] = ArInvoicePeer::TYPE;
		}

	} 
	
	public function setIsRevenueSharing($v)
	{

		if ($this->is_revenue_sharing !== $v || $v === false) {
			$this->is_revenue_sharing = $v;
			$this->modifiedColumns[] = ArInvoicePeer::IS_REVENUE_SHARING;
		}

	} 
	
	public function setNr($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->nr !== $v) {
			$this->nr = $v;
			$this->modifiedColumns[] = ArInvoicePeer::NR;
		}

	} 
	
	public function setInvoiceDate($v)
	{

		if ($v !== null && !is_int($v)) {
			$ts = strtotime($v);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse date/time value for [invoice_date] from input: " . var_export($v, true));
			}
		} else {
			$ts = $v;
		}
		if ($this->invoice_date !== $ts) {
			$this->invoice_date = $ts;
			$this->modifiedColumns[] = ArInvoicePeer::INVOICE_DATE;
		}

	} 
	
	public function setArCdrFrom($v)
	{

		if ($v !== null && !is_int($v)) {
			$ts = strtotime($v);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse date/time value for [ar_cdr_from] from input: " . var_export($v, true));
			}
		} else {
			$ts = $v;
		}
		if ($this->ar_cdr_from !== $ts) {
			$this->ar_cdr_from = $ts;
			$this->modifiedColumns[] = ArInvoicePeer::AR_CDR_FROM;
		}

	} 
	
	public function setArCdrTo($v)
	{

		if ($v !== null && !is_int($v)) {
			$ts = strtotime($v);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse date/time value for [ar_cdr_to] from input: " . var_export($v, true));
			}
		} else {
			$ts = $v;
		}
		if ($this->ar_cdr_to !== $ts) {
			$this->ar_cdr_to = $ts;
			$this->modifiedColumns[] = ArInvoicePeer::AR_CDR_TO;
		}

	} 
	
	public function setTotalWithoutTax($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->total_without_tax !== $v || $v === 0) {
			$this->total_without_tax = $v;
			$this->modifiedColumns[] = ArInvoicePeer::TOTAL_WITHOUT_TAX;
		}

	} 
	
	public function setVatPerc($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->vat_perc !== $v || $v === 0) {
			$this->vat_perc = $v;
			$this->modifiedColumns[] = ArInvoicePeer::VAT_PERC;
		}

	} 
	
	public function setTotalVat($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->total_vat !== $v || $v === 0) {
			$this->total_vat = $v;
			$this->modifiedColumns[] = ArInvoicePeer::TOTAL_VAT;
		}

	} 
	
	public function setTotal($v)
	{

		
		
		if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->total !== $v || $v === 0) {
			$this->total = $v;
			$this->modifiedColumns[] = ArInvoicePeer::TOTAL;
		}

	} 
	
	public function setHtmlDetails($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->html_details !== $v) {
			$this->html_details = $v;
			$this->modifiedColumns[] = ArInvoicePeer::HTML_DETAILS;
		}

	} 
	
	public function setPdfInvoice($v)
	{

								if ($v instanceof Lob && $v === $this->pdf_invoice) {
			$changed = $v->isModified();
		} else {
			$changed = ($this->pdf_invoice !== $v);
		}
		if ($changed) {
			if ( !($v instanceof Lob) ) {
				$obj = new Blob();
				$obj->setContents($v);
			} else {
				$obj = $v;
			}
			$this->pdf_invoice = $obj;
			$this->modifiedColumns[] = ArInvoicePeer::PDF_INVOICE;
		}

	} 
	
	public function setEmailSubject($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->email_subject !== $v) {
			$this->email_subject = $v;
			$this->modifiedColumns[] = ArInvoicePeer::EMAIL_SUBJECT;
		}

	} 
	
	public function setEmailMessage($v)
	{

		
		
		if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->email_message !== $v) {
			$this->email_message = $v;
			$this->modifiedColumns[] = ArInvoicePeer::EMAIL_MESSAGE;
		}

	} 
	
	public function setAlreadySent($v)
	{

		if ($this->already_sent !== $v) {
			$this->already_sent = $v;
			$this->modifiedColumns[] = ArInvoicePeer::ALREADY_SENT;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->id = $rs->getInt($startcol + 0);

			$this->ar_party_id = $rs->getInt($startcol + 1);

			$this->type = $rs->getString($startcol + 2);

			$this->is_revenue_sharing = $rs->getBoolean($startcol + 3);

			$this->nr = $rs->getString($startcol + 4);

			$this->invoice_date = $rs->getDate($startcol + 5, null);

			$this->ar_cdr_from = $rs->getDate($startcol + 6, null);

			$this->ar_cdr_to = $rs->getDate($startcol + 7, null);

			$this->total_without_tax = $rs->getInt($startcol + 8);

			$this->vat_perc = $rs->getInt($startcol + 9);

			$this->total_vat = $rs->getInt($startcol + 10);

			$this->total = $rs->getInt($startcol + 11);

			$this->html_details = $rs->getString($startcol + 12);

			$this->pdf_invoice = $rs->getBlob($startcol + 13);

			$this->email_subject = $rs->getString($startcol + 14);

			$this->email_message = $rs->getString($startcol + 15);

			$this->already_sent = $rs->getBoolean($startcol + 16);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 17; 
		} catch (Exception $e) {
			throw new PropelException("Error populating ArInvoice object", $e);
		}
	}

	
	public function delete($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(ArInvoicePeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			ArInvoicePeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	public function save($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(ArInvoicePeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			$affectedRows = $this->doSave($con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	protected function doSave($con)
	{
		$affectedRows = 0; 		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;


												
			if ($this->aArParty !== null) {
				if ($this->aArParty->isModified()) {
					$affectedRows += $this->aArParty->save($con);
				}
				$this->setArParty($this->aArParty);
			}


						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = ArInvoicePeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
					$this->setNew(false);
				} else {
					$affectedRows += ArInvoicePeer::doUpdate($this, $con);
				}
				$this->resetModified(); 			}

			$this->alreadyInSave = false;
		}
		return $affectedRows;
	} 
	
	protected $validationFailures = array();

	
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


												
			if ($this->aArParty !== null) {
				if (!$this->aArParty->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aArParty->getValidationFailures());
				}
			}


			if (($retval = ArInvoicePeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}



			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = ArInvoicePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->getByPosition($pos);
	}

	
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getArPartyId();
				break;
			case 2:
				return $this->getType();
				break;
			case 3:
				return $this->getIsRevenueSharing();
				break;
			case 4:
				return $this->getNr();
				break;
			case 5:
				return $this->getInvoiceDate();
				break;
			case 6:
				return $this->getArCdrFrom();
				break;
			case 7:
				return $this->getArCdrTo();
				break;
			case 8:
				return $this->getTotalWithoutTax();
				break;
			case 9:
				return $this->getVatPerc();
				break;
			case 10:
				return $this->getTotalVat();
				break;
			case 11:
				return $this->getTotal();
				break;
			case 12:
				return $this->getHtmlDetails();
				break;
			case 13:
				return $this->getPdfInvoice();
				break;
			case 14:
				return $this->getEmailSubject();
				break;
			case 15:
				return $this->getEmailMessage();
				break;
			case 16:
				return $this->getAlreadySent();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = ArInvoicePeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getArPartyId(),
			$keys[2] => $this->getType(),
			$keys[3] => $this->getIsRevenueSharing(),
			$keys[4] => $this->getNr(),
			$keys[5] => $this->getInvoiceDate(),
			$keys[6] => $this->getArCdrFrom(),
			$keys[7] => $this->getArCdrTo(),
			$keys[8] => $this->getTotalWithoutTax(),
			$keys[9] => $this->getVatPerc(),
			$keys[10] => $this->getTotalVat(),
			$keys[11] => $this->getTotal(),
			$keys[12] => $this->getHtmlDetails(),
			$keys[13] => $this->getPdfInvoice(),
			$keys[14] => $this->getEmailSubject(),
			$keys[15] => $this->getEmailMessage(),
			$keys[16] => $this->getAlreadySent(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = ArInvoicePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setArPartyId($value);
				break;
			case 2:
				$this->setType($value);
				break;
			case 3:
				$this->setIsRevenueSharing($value);
				break;
			case 4:
				$this->setNr($value);
				break;
			case 5:
				$this->setInvoiceDate($value);
				break;
			case 6:
				$this->setArCdrFrom($value);
				break;
			case 7:
				$this->setArCdrTo($value);
				break;
			case 8:
				$this->setTotalWithoutTax($value);
				break;
			case 9:
				$this->setVatPerc($value);
				break;
			case 10:
				$this->setTotalVat($value);
				break;
			case 11:
				$this->setTotal($value);
				break;
			case 12:
				$this->setHtmlDetails($value);
				break;
			case 13:
				$this->setPdfInvoice($value);
				break;
			case 14:
				$this->setEmailSubject($value);
				break;
			case 15:
				$this->setEmailMessage($value);
				break;
			case 16:
				$this->setAlreadySent($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = ArInvoicePeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setArPartyId($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setType($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setIsRevenueSharing($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setNr($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setInvoiceDate($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setArCdrFrom($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setArCdrTo($arr[$keys[7]]);
		if (array_key_exists($keys[8], $arr)) $this->setTotalWithoutTax($arr[$keys[8]]);
		if (array_key_exists($keys[9], $arr)) $this->setVatPerc($arr[$keys[9]]);
		if (array_key_exists($keys[10], $arr)) $this->setTotalVat($arr[$keys[10]]);
		if (array_key_exists($keys[11], $arr)) $this->setTotal($arr[$keys[11]]);
		if (array_key_exists($keys[12], $arr)) $this->setHtmlDetails($arr[$keys[12]]);
		if (array_key_exists($keys[13], $arr)) $this->setPdfInvoice($arr[$keys[13]]);
		if (array_key_exists($keys[14], $arr)) $this->setEmailSubject($arr[$keys[14]]);
		if (array_key_exists($keys[15], $arr)) $this->setEmailMessage($arr[$keys[15]]);
		if (array_key_exists($keys[16], $arr)) $this->setAlreadySent($arr[$keys[16]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(ArInvoicePeer::DATABASE_NAME);

		if ($this->isColumnModified(ArInvoicePeer::ID)) $criteria->add(ArInvoicePeer::ID, $this->id);
		if ($this->isColumnModified(ArInvoicePeer::AR_PARTY_ID)) $criteria->add(ArInvoicePeer::AR_PARTY_ID, $this->ar_party_id);
		if ($this->isColumnModified(ArInvoicePeer::TYPE)) $criteria->add(ArInvoicePeer::TYPE, $this->type);
		if ($this->isColumnModified(ArInvoicePeer::IS_REVENUE_SHARING)) $criteria->add(ArInvoicePeer::IS_REVENUE_SHARING, $this->is_revenue_sharing);
		if ($this->isColumnModified(ArInvoicePeer::NR)) $criteria->add(ArInvoicePeer::NR, $this->nr);
		if ($this->isColumnModified(ArInvoicePeer::INVOICE_DATE)) $criteria->add(ArInvoicePeer::INVOICE_DATE, $this->invoice_date);
		if ($this->isColumnModified(ArInvoicePeer::AR_CDR_FROM)) $criteria->add(ArInvoicePeer::AR_CDR_FROM, $this->ar_cdr_from);
		if ($this->isColumnModified(ArInvoicePeer::AR_CDR_TO)) $criteria->add(ArInvoicePeer::AR_CDR_TO, $this->ar_cdr_to);
		if ($this->isColumnModified(ArInvoicePeer::TOTAL_WITHOUT_TAX)) $criteria->add(ArInvoicePeer::TOTAL_WITHOUT_TAX, $this->total_without_tax);
		if ($this->isColumnModified(ArInvoicePeer::VAT_PERC)) $criteria->add(ArInvoicePeer::VAT_PERC, $this->vat_perc);
		if ($this->isColumnModified(ArInvoicePeer::TOTAL_VAT)) $criteria->add(ArInvoicePeer::TOTAL_VAT, $this->total_vat);
		if ($this->isColumnModified(ArInvoicePeer::TOTAL)) $criteria->add(ArInvoicePeer::TOTAL, $this->total);
		if ($this->isColumnModified(ArInvoicePeer::HTML_DETAILS)) $criteria->add(ArInvoicePeer::HTML_DETAILS, $this->html_details);
		if ($this->isColumnModified(ArInvoicePeer::PDF_INVOICE)) $criteria->add(ArInvoicePeer::PDF_INVOICE, $this->pdf_invoice);
		if ($this->isColumnModified(ArInvoicePeer::EMAIL_SUBJECT)) $criteria->add(ArInvoicePeer::EMAIL_SUBJECT, $this->email_subject);
		if ($this->isColumnModified(ArInvoicePeer::EMAIL_MESSAGE)) $criteria->add(ArInvoicePeer::EMAIL_MESSAGE, $this->email_message);
		if ($this->isColumnModified(ArInvoicePeer::ALREADY_SENT)) $criteria->add(ArInvoicePeer::ALREADY_SENT, $this->already_sent);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(ArInvoicePeer::DATABASE_NAME);

		$criteria->add(ArInvoicePeer::ID, $this->id);

		return $criteria;
	}

	
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setArPartyId($this->ar_party_id);

		$copyObj->setType($this->type);

		$copyObj->setIsRevenueSharing($this->is_revenue_sharing);

		$copyObj->setNr($this->nr);

		$copyObj->setInvoiceDate($this->invoice_date);

		$copyObj->setArCdrFrom($this->ar_cdr_from);

		$copyObj->setArCdrTo($this->ar_cdr_to);

		$copyObj->setTotalWithoutTax($this->total_without_tax);

		$copyObj->setVatPerc($this->vat_perc);

		$copyObj->setTotalVat($this->total_vat);

		$copyObj->setTotal($this->total);

		$copyObj->setHtmlDetails($this->html_details);

		$copyObj->setPdfInvoice($this->pdf_invoice);

		$copyObj->setEmailSubject($this->email_subject);

		$copyObj->setEmailMessage($this->email_message);

		$copyObj->setAlreadySent($this->already_sent);


		$copyObj->setNew(true);

		$copyObj->setId(NULL); 
	}

	
	public function copy($deepCopy = false)
	{
				$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		return $copyObj;
	}

	
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new ArInvoicePeer();
		}
		return self::$peer;
	}

	
	public function setArParty($v)
	{


		if ($v === null) {
			$this->setArPartyId(NULL);
		} else {
			$this->setArPartyId($v->getId());
		}


		$this->aArParty = $v;
	}


	
	public function getArParty($con = null)
	{
		if ($this->aArParty === null && ($this->ar_party_id !== null)) {
						include_once 'lib/model/om/BaseArPartyPeer.php';

			$this->aArParty = ArPartyPeer::retrieveByPK($this->ar_party_id, $con);

			
		}
		return $this->aArParty;
	}

} 