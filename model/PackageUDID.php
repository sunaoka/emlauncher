<?php

/*! @file
 * iOSアプリに関連付けられているインストール可能な端末のUDID情報.
 * テーブルはpackage_udid.
 * @sa PackageUDID
 */

class PackageUDID extends mfwObject {
	const SET_CLASS = 'PackageUDIDSet';
	const DB_CLASS = 'PackageUDIDDb';

	public function getId() {
		return $this->value('id');
	}

	public function getDeviceUDID() {
		return $this->value('device_udid');
	}

	public function getPackageId() {
		return $this->value('package_id');
	}

	public function delete()
	{
		$sql = 'DELETE FROM package_udid WHERE device_udid = :device_udid AND package_id = :package_id';
		$bind = array(
			':device_udid' => $this->getDeviceUDID(),
			':package_id' => $this->getPackageId(),
			);
		return mfwDBIBase::query($sql, $bind, $con);
	}
}

class PackageUDIDSet extends mfwObjectSet {
	const PRIMARY_KEY = 'id';
	protected $user;
	protected $PackageUDIDs = null;

	protected function selectPackageUDIDs()
	{
		//var_dump_log("this", $this);
		if ( $this->PackageUDIDs === null ) {
			$package_ids = $this->getColumnArray('id');
			//var_dump_log("package_ids", $package_ids);
			$this->PackageUDIDs = PackageUDIDDb::retrieveByPKs($package_ids);
			//var_dump_log("PackageUDIDs", $this->PackageUDIDs);
		}
		return $this->PackageUDIDs;
	}

	public function __construct(Array $rows = array())
	{
		//var_dump_log("rows", $rows);
		parent::__construct($rows);
		//$this->user = $user;
	}
	public static function hypostatize(Array $row = array())
	{
		return new PackageUDID($row);
	}
	protected function unsetCache($id)
	{
		parent::unsetCache($id);
	}

/*
	public function offsetGet($offset)
	{
		//var_dump_log("this", $this);
		$ip = parent::offsetGet($offset);
		//var_dump_log("ip", $ip);
		$package_udids = $this->selectPackageUDIDs();
		//var_dump_log("package_udids", $package_udids);
		if ( isset($package_udids[$ip->getDeviceUDID()]) ) {
			$ip->setPackage($package_udids[$ip->getDeviceUDID()]);
		}
		return $ip;
	}
*/
}

/**
 * database accessor for 'package_udid' table.
 */
class PackageUDIDDb extends mfwObjectDb {
	const TABLE_NAME = 'package_udid';
	const SET_CLASS = 'PackageUDIDSet';

	public static function insertNewPackageUUID($package_id, $device_udid)
	{
		$now = date('Y-m-d H:i:s');
		// insert new application
		$row = array(
			'device_udid' =>  $device_udid,
			'package_id' => $package_id,
			);
		$package_udid = new PackageUDID($row);
		$package_udid->insert();

		return $package_udid;
	}

	public static function selectAll()
	{
		$query = 'ORDER BY id DESC';
		return static::selectSet($query);
	}

	public static function selectCount()
	{
		$table = static::TABLE_NAME;
		$sql = "SELECT count(*) FROM `$table`";
		return mfwDBIBase::getOne($sql);
	}

        public static function selectCountByPackageId($package_id)
        {
                $table = static::TABLE_NAME;
                $sql = "SELECT count(*) FROM `$table` WHERE package_id=?";
                return mfwDBIBase::getOne($sql, array($package_id));
        }

	public static function selectByPackageID($package_id)
	{
                $query = "WHERE package_id = ?";
                return static::selectSet($query, array($package_id));
	}

        public static function selectByDeviceUDID($device_udid)
        {
                $query = "WHERE device_udid = ?";
                return static::selectSet($query, array($device_udid));
        }

	public static function removeFromPackage(Package $pkg, $con = null)
	{
		$sql = 'DELETE FROM package_udid WHERE package_id = ?';
		mfwDBIBase::query($sql, array($pkg->getId()), $con);
	}
/*
	public static function selectOwnUDID($user)
	{
		$ios_udids = IOS_UDIDDb::selectByOwnerMail($user->getMail());
		if ( $aos->count() == 0 ) {
			return new PackagelicationSet();
		}
		$ids = array();
		foreach ( $ios_udids as $udid ) {
			$ids[] = $udid->getId();
		}
		$bind = array();
		$pf = static::makeInPlaceholder($ids, $bind);
		return static::selectSet("WHERE device_udid IN ($pf) ORDER BY id DESC", $bind);
	}
*/
}
