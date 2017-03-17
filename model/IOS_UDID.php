<?php

/*! @file
 * あるユーザが利用しているiOSテスト端末の情報.
 * テーブルはios_udid.
 * @sa IOS_UDID
 */

class IOS_UDID extends mfwObject {
	const SET_CLASS = 'IOS_UDIDSet';
	const DB_CLASS = 'IOS_UDIDDb';

	public function getDeviceUDID() {
		return $this->value('device_udid');
	}

	public function getMail() {
		return $this->value('mail');
	}

	public function getDeviceUUID() {
		return $this->value('device_uuid');
	}

	public function delete()
	{
		$sql = 'DELETE FROM ios_udid WHERE device_udid = :device_udid AND mail = :mail';
		$bind = array(
			':device_udid' => $this->getDeviceUDID(),
			':mail' => $this->getMail(),
			);
		return mfwDBIBase::query($sql, $bind, $con);
	}
}

class IOS_UDIDSet extends mfwObjectSet {
	const PRIMARY_KEY = 'device_udid';
	protected $user;
	protected $UDIDs = null;

	protected function selectUDIDs()
	{
		if ( $this->UDIDs === null ) {
			$device_udids = $this->getColumnArray('device_udid');
			$this->UDIDs = IOS_UDIDDb::retrieveByPKs($device_udids);
		}
		return $this->UDIDs;
	}

	public function __construct(Array $rows = array())
	{
		parent::__construct($rows);
		//$this->user = $user;
	}
	public static function hypostatize(Array $row = array())
	{
		return new IOS_UDID($row);
	}
	protected function unsetCache($id)
	{
		parent::unsetCache($id);
	}

	public function offsetGet($offset)
	{
		$ia = parent::offsetGet($offset);
		$device_udids = $this->selectUDIDs();
		if ( isset($device_udids[$ia->getAppId()]) ) {
			$ia->setApp($apps[$ia->getAppId()]);
		}
		return $ia;
	}
}

/**
 * database accessor for 'ios_udid' table.
 */
class IOS_UDIDDb extends mfwObjectDb {
	const TABLE_NAME = 'ios_udid';
	const SET_CLASS = 'IOS_UDIDSet';

	public static function insertNewIOS_UUID($owner, $device_uuid)
	{
		$now = date('Y-m-d H:i:s');
		// insert new application
		$row = array(
			'device_uuid' =>  $device_uuid,
			'mail' => $owner->getMail(),
			);
		$ios_udid = new IOS_UDID($row);
		$ios_udid->insert();

		return $ios_udid;
	}

	public static function selectAll()
	{
		$query = 'ORDER BY date_to_sort DESC';
		return static::selectSet($query);
	}

	public static function selectCount()
	{
		$table = static::TABLE_NAME;
		$sql = "SELECT count(*) FROM `$table`";
		return mfwDBIBase::getOne($sql);
	}

	public static function selectByOwnerMail($ownerMail)
	{
                $query = "WHERE mail = ?";
                return static::selectSet($query, array($mail));
	}

        public static function selectByDeviceUUID($device_uuid)
        {
                $query = "WHERE device_uuid = ?";
                return static::selectOne($query, array($device_uuid));
        }

	public static function selectOwnUDID($user)
	{
		$ios_udids = IOS_UDIDDb::selectByOwnerMail($user->getMail());
		if ( $aos->count() == 0 ) {
			return new ApplicationSet();
		}
		$ids = array();
		foreach ( $ios_udids as $udid ) {
			$ids[] = $udid->getId();
		}
		$bind = array();
		$pf = static::makeInPlaceholder($ids, $bind);
		return static::selectSet("WHERE device_udid IN ($pf) ORDER BY id DESC", $bind);
	}
}
