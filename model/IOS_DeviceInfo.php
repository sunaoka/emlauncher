<?php

/*! @file
 * あるユーザが利用しているiOSテスト端末の情報.
 * テーブルはios_device_info.
 * @sa IOS_DeviceInfo
 */

class IOS_DeviceInfo extends mfwObject {
	const SET_CLASS = 'IOS_DeviceInfoSet';
	const DB_CLASS = 'IOS_DeviceInfoDb';

        public function getId() {
                return $this->value('id');
        }

	public function getDeviceUDID() {
		return $this->value('device_udid');
	}

	public function getMail() {
		return $this->value('mail');
	}

	public function getDeviceUUID() {
		return $this->value('device_uuid');
	}

        public function getDeviceName() {
                return $this->value('device_name');
        }

        public function getDeviceVersion() {
                return $this->value('device_version');
        }

        public function getDeviceProduct() {
                return $this->value('device_product');
        }

	public function delete()
	{
		$sql = 'DELETE FROM ios_device_info WHERE device_udid = :device_udid AND mail = :mail';
		$bind = array(
			':device_udid' => $this->getDeviceUDID(),
			':mail' => $this->getMail(),
			);
		return mfwDBIBase::query($sql, $bind, $con);
	}
}

class IOS_DeviceInfoSet extends mfwObjectSet {
	const PRIMARY_KEY = 'device_udid';
	protected $user;
	protected $DeviceInfoList = null;

	protected function selectDeviceInfoList()
	{
		if ( $this->UDIDs === null ) {
			$device_udids = $this->getColumnArray('device_udid');
			$this->DeviceInfoList = IOS_UDIDDb::retrieveByPKs($device_udids);
		}
		return $this->DeviceInfoList;
	}

	public function __construct(Array $rows = array())
	{
		parent::__construct($rows);
		//$this->user = $user;
	}
	public static function hypostatize(Array $row = array())
	{
		return new IOS_DeviceInfo($row);
	}
	protected function unsetCache($id)
	{
		parent::unsetCache($id);
	}

	public function offsetGet($offset)
	{
		$di = parent::offsetGet($offset);
		$device_info_list = $this->selectUDIDs();
		if ( isset($device_info_list[$di->getId()]) ) {
			$di->setApp($apps[$di->getId()]);
		}
		return $di;
	}
}

/**
 * database accessor for 'ios_udid' table.
 */
class IOS_DeviceInfoDb extends mfwObjectDb {
	const TABLE_NAME = 'ios_device_info';
	const SET_CLASS = 'IOS_DeviceInfoSet';

	public static function insertNewIOS_DeviceInfo($owner, $device_uuid, $device_name = NULL, $device_version = NULL, $device_product = NULL)
	{
		$now = date('Y-m-d H:i:s');
		// insert new application
		$row = array(
			'device_uuid' =>  $device_uuid,
			'mail' => $owner->getMail(),
			);
		$ios_device_info = new IOS_DeviceInfo($row);
		$ios_device_info->insert();

		return $ios_device_info;
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

	public static function selectByDeviceInfoId($device_info_id)
	{
		$query = "WHERE id = ?";
		return static::selectOne($query, array($device_info_id));
	}

	public static function selectOwnDeviceInfo($user)
	{
		$ios_device_info_list = IOS_DeviceInfoDb::selectByOwnerMail($user->getMail());
		if ( $ios_device_info_list->count() == 0 ) {
			return new IOS_DeviceInfoSet();
		}
		$ids = array();
		foreach ( $ios_device_info_list as $ios_device_info ) {
			$ids[] = $ios_device_info->getId();
		}
		$bind = array();
		$pf = static::makeInPlaceholder($ids, $bind);
		return static::selectSet("WHERE device_udid IN ($pf) ORDER BY id DESC", $bind);
	}
}
