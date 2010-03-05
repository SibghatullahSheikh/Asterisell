<?php


abstract class BaseArInvoiceCreationPeer {

	
	const DATABASE_NAME = 'propel';

	
	const TABLE_NAME = 'ar_invoice_creation';

	
	const CLASS_DEFAULT = 'lib.model.ArInvoiceCreation';

	
	const NUM_COLUMNS = 7;

	
	const NUM_LAZY_LOAD_COLUMNS = 0;


	
	const ID = 'ar_invoice_creation.ID';

	
	const TYPE = 'ar_invoice_creation.TYPE';

	
	const IS_REVENUE_SHARING = 'ar_invoice_creation.IS_REVENUE_SHARING';

	
	const FIRST_NR = 'ar_invoice_creation.FIRST_NR';

	
	const INVOICE_DATE = 'ar_invoice_creation.INVOICE_DATE';

	
	const AR_CDR_FROM = 'ar_invoice_creation.AR_CDR_FROM';

	
	const AR_CDR_TO = 'ar_invoice_creation.AR_CDR_TO';

	
	private static $phpNameMap = null;


	
	private static $fieldNames = array (
		BasePeer::TYPE_PHPNAME => array ('Id', 'Type', 'IsRevenueSharing', 'FirstNr', 'InvoiceDate', 'ArCdrFrom', 'ArCdrTo', ),
		BasePeer::TYPE_COLNAME => array (ArInvoiceCreationPeer::ID, ArInvoiceCreationPeer::TYPE, ArInvoiceCreationPeer::IS_REVENUE_SHARING, ArInvoiceCreationPeer::FIRST_NR, ArInvoiceCreationPeer::INVOICE_DATE, ArInvoiceCreationPeer::AR_CDR_FROM, ArInvoiceCreationPeer::AR_CDR_TO, ),
		BasePeer::TYPE_FIELDNAME => array ('id', 'type', 'is_revenue_sharing', 'first_nr', 'invoice_date', 'ar_cdr_from', 'ar_cdr_to', ),
		BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, )
	);

	
	private static $fieldKeys = array (
		BasePeer::TYPE_PHPNAME => array ('Id' => 0, 'Type' => 1, 'IsRevenueSharing' => 2, 'FirstNr' => 3, 'InvoiceDate' => 4, 'ArCdrFrom' => 5, 'ArCdrTo' => 6, ),
		BasePeer::TYPE_COLNAME => array (ArInvoiceCreationPeer::ID => 0, ArInvoiceCreationPeer::TYPE => 1, ArInvoiceCreationPeer::IS_REVENUE_SHARING => 2, ArInvoiceCreationPeer::FIRST_NR => 3, ArInvoiceCreationPeer::INVOICE_DATE => 4, ArInvoiceCreationPeer::AR_CDR_FROM => 5, ArInvoiceCreationPeer::AR_CDR_TO => 6, ),
		BasePeer::TYPE_FIELDNAME => array ('id' => 0, 'type' => 1, 'is_revenue_sharing' => 2, 'first_nr' => 3, 'invoice_date' => 4, 'ar_cdr_from' => 5, 'ar_cdr_to' => 6, ),
		BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, )
	);

	
	public static function getMapBuilder()
	{
		include_once 'lib/model/map/ArInvoiceCreationMapBuilder.php';
		return BasePeer::getMapBuilder('lib.model.map.ArInvoiceCreationMapBuilder');
	}
	
	public static function getPhpNameMap()
	{
		if (self::$phpNameMap === null) {
			$map = ArInvoiceCreationPeer::getTableMap();
			$columns = $map->getColumns();
			$nameMap = array();
			foreach ($columns as $column) {
				$nameMap[$column->getPhpName()] = $column->getColumnName();
			}
			self::$phpNameMap = $nameMap;
		}
		return self::$phpNameMap;
	}
	
	static public function translateFieldName($name, $fromType, $toType)
	{
		$toNames = self::getFieldNames($toType);
		$key = isset(self::$fieldKeys[$fromType][$name]) ? self::$fieldKeys[$fromType][$name] : null;
		if ($key === null) {
			throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(self::$fieldKeys[$fromType], true));
		}
		return $toNames[$key];
	}

	

	static public function getFieldNames($type = BasePeer::TYPE_PHPNAME)
	{
		if (!array_key_exists($type, self::$fieldNames)) {
			throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants TYPE_PHPNAME, TYPE_COLNAME, TYPE_FIELDNAME, TYPE_NUM. ' . $type . ' was given.');
		}
		return self::$fieldNames[$type];
	}

	
	public static function alias($alias, $column)
	{
		return str_replace(ArInvoiceCreationPeer::TABLE_NAME.'.', $alias.'.', $column);
	}

	
	public static function addSelectColumns(Criteria $criteria)
	{

		$criteria->addSelectColumn(ArInvoiceCreationPeer::ID);

		$criteria->addSelectColumn(ArInvoiceCreationPeer::TYPE);

		$criteria->addSelectColumn(ArInvoiceCreationPeer::IS_REVENUE_SHARING);

		$criteria->addSelectColumn(ArInvoiceCreationPeer::FIRST_NR);

		$criteria->addSelectColumn(ArInvoiceCreationPeer::INVOICE_DATE);

		$criteria->addSelectColumn(ArInvoiceCreationPeer::AR_CDR_FROM);

		$criteria->addSelectColumn(ArInvoiceCreationPeer::AR_CDR_TO);

	}

	const COUNT = 'COUNT(ar_invoice_creation.ID)';
	const COUNT_DISTINCT = 'COUNT(DISTINCT ar_invoice_creation.ID)';

	
	public static function doCount(Criteria $criteria, $distinct = false, $con = null)
	{
				$criteria = clone $criteria;

				$criteria->clearSelectColumns()->clearOrderByColumns();
		if ($distinct || in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->addSelectColumn(ArInvoiceCreationPeer::COUNT_DISTINCT);
		} else {
			$criteria->addSelectColumn(ArInvoiceCreationPeer::COUNT);
		}

				foreach($criteria->getGroupByColumns() as $column)
		{
			$criteria->addSelectColumn($column);
		}

		$rs = ArInvoiceCreationPeer::doSelectRS($criteria, $con);
		if ($rs->next()) {
			return $rs->getInt(1);
		} else {
						return 0;
		}
	}
	
	public static function doSelectOne(Criteria $criteria, $con = null)
	{
		$critcopy = clone $criteria;
		$critcopy->setLimit(1);
		$objects = ArInvoiceCreationPeer::doSelect($critcopy, $con);
		if ($objects) {
			return $objects[0];
		}
		return null;
	}
	
	public static function doSelect(Criteria $criteria, $con = null)
	{
		return ArInvoiceCreationPeer::populateObjects(ArInvoiceCreationPeer::doSelectRS($criteria, $con));
	}
	
	public static function doSelectRS(Criteria $criteria, $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		if (!$criteria->getSelectColumns()) {
			$criteria = clone $criteria;
			ArInvoiceCreationPeer::addSelectColumns($criteria);
		}

				$criteria->setDbName(self::DATABASE_NAME);

						return BasePeer::doSelect($criteria, $con);
	}
	
	public static function populateObjects(ResultSet $rs)
	{
		$results = array();
	
				$cls = ArInvoiceCreationPeer::getOMClass();
		$cls = Propel::import($cls);
				while($rs->next()) {
		
			$obj = new $cls();
			$obj->hydrate($rs);
			$results[] = $obj;
			
		}
		return $results;
	}
	
	public static function getTableMap()
	{
		return Propel::getDatabaseMap(self::DATABASE_NAME)->getTable(self::TABLE_NAME);
	}

	
	public static function getOMClass()
	{
		return ArInvoiceCreationPeer::CLASS_DEFAULT;
	}

	
	public static function doInsert($values, $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		if ($values instanceof Criteria) {
			$criteria = clone $values; 		} else {
			$criteria = $values->buildCriteria(); 		}

		$criteria->remove(ArInvoiceCreationPeer::ID); 

				$criteria->setDbName(self::DATABASE_NAME);

		try {
									$con->begin();
			$pk = BasePeer::doInsert($criteria, $con);
			$con->commit();
		} catch(PropelException $e) {
			$con->rollback();
			throw $e;
		}

		return $pk;
	}

	
	public static function doUpdate($values, $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		$selectCriteria = new Criteria(self::DATABASE_NAME);

		if ($values instanceof Criteria) {
			$criteria = clone $values; 
			$comparison = $criteria->getComparison(ArInvoiceCreationPeer::ID);
			$selectCriteria->add(ArInvoiceCreationPeer::ID, $criteria->remove(ArInvoiceCreationPeer::ID), $comparison);

		} else { 			$criteria = $values->buildCriteria(); 			$selectCriteria = $values->buildPkeyCriteria(); 		}

				$criteria->setDbName(self::DATABASE_NAME);

		return BasePeer::doUpdate($selectCriteria, $criteria, $con);
	}

	
	public static function doDeleteAll($con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}
		$affectedRows = 0; 		try {
									$con->begin();
			$affectedRows += BasePeer::doDeleteAll(ArInvoiceCreationPeer::TABLE_NAME, $con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	 public static function doDelete($values, $con = null)
	 {
		if ($con === null) {
			$con = Propel::getConnection(ArInvoiceCreationPeer::DATABASE_NAME);
		}

		if ($values instanceof Criteria) {
			$criteria = clone $values; 		} elseif ($values instanceof ArInvoiceCreation) {

			$criteria = $values->buildPkeyCriteria();
		} else {
						$criteria = new Criteria(self::DATABASE_NAME);
			$criteria->add(ArInvoiceCreationPeer::ID, (array) $values, Criteria::IN);
		}

				$criteria->setDbName(self::DATABASE_NAME);

		$affectedRows = 0; 
		try {
									$con->begin();
			
			$affectedRows += BasePeer::doDelete($criteria, $con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	public static function doValidate(ArInvoiceCreation $obj, $cols = null)
	{
		$columns = array();

		if ($cols) {
			$dbMap = Propel::getDatabaseMap(ArInvoiceCreationPeer::DATABASE_NAME);
			$tableMap = $dbMap->getTable(ArInvoiceCreationPeer::TABLE_NAME);

			if (! is_array($cols)) {
				$cols = array($cols);
			}

			foreach($cols as $colName) {
				if ($tableMap->containsColumn($colName)) {
					$get = 'get' . $tableMap->getColumn($colName)->getPhpName();
					$columns[$colName] = $obj->$get();
				}
			}
		} else {

		}

		$res =  BasePeer::doValidate(ArInvoiceCreationPeer::DATABASE_NAME, ArInvoiceCreationPeer::TABLE_NAME, $columns);
    if ($res !== true) {
        $request = sfContext::getInstance()->getRequest();
        foreach ($res as $failed) {
            $col = ArInvoiceCreationPeer::translateFieldname($failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
            $request->setError($col, $failed->getMessage());
        }
    }

    return $res;
	}

	
	public static function retrieveByPK($pk, $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		$criteria = new Criteria(ArInvoiceCreationPeer::DATABASE_NAME);

		$criteria->add(ArInvoiceCreationPeer::ID, $pk);


		$v = ArInvoiceCreationPeer::doSelect($criteria, $con);

		return !empty($v) > 0 ? $v[0] : null;
	}

	
	public static function retrieveByPKs($pks, $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(self::DATABASE_NAME);
		}

		$objs = null;
		if (empty($pks)) {
			$objs = array();
		} else {
			$criteria = new Criteria();
			$criteria->add(ArInvoiceCreationPeer::ID, $pks, Criteria::IN);
			$objs = ArInvoiceCreationPeer::doSelect($criteria, $con);
		}
		return $objs;
	}

} 
if (Propel::isInit()) {
			try {
		BaseArInvoiceCreationPeer::getMapBuilder();
	} catch (Exception $e) {
		Propel::log('Could not initialize Peer: ' . $e->getMessage(), Propel::LOG_ERR);
	}
} else {
			require_once 'lib/model/map/ArInvoiceCreationMapBuilder.php';
	Propel::registerMapBuilder('lib.model.map.ArInvoiceCreationMapBuilder');
}
