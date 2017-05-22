<?php
require_once APP_ROOT.'/model/InstallLog.php';
require_once APP_ROOT.'/model/IOS_DeviceInfo.php';
require_once APP_ROOT.'/model/UserPass.php';

class User
{
	const SESKEY = 'login_user';

	protected $mail;
	protected $device_uuid;
	protected $device_udid;
	protected $device_info_id;
	protected $as_dmin;
	protected $pkg_install_dates = array();
	protected $install_apps = null;
	protected $pkg_installables = array();

	public function __construct($mail, $as_admin = 0, $device_uuid = null, $device_info_id = 0, $device_udid = null)
	{
		$this->mail = $mail;
		$this->as_admin = $as_admin;
		$this->device_uuid = $device_uuid;
		$this->device_info_id = $device_info_id;
		//var_dump_log("device_uuid", $device_uuid);
		//var_dump_log("device_info_id", $device_info_id);
		//var_dump_log("device_udid", $device_udid);
		if ( ( $device_info_id != 0 || !empty($device_uuid) ) && empty($device_udid) ) {
			if ( !empty($device_uuid) ) {
				$ios_device_info = IOS_DeviceInfoDb::selectByDeviceUUID($device_uuid);
			}
			else {
				$ios_device_info = IOS_DeviceInfoDb::selectByDeviceInfoId($device_info_id);
			}
			//var_dump_log("ios_device_info", $ios_device_info);
			if ( !empty($ios_device_info) ) {
				$this->device_udid = $ios_device_info->getDeviceUDID();
				$this->device_info_id = $ios_device_info->getId();
			}
			//var_dump_log("device_info_id", $this->device_info_id);
			//var_dump_log("device_udid", $this->device_udid);
		}
	}

	public function getMail()
	{
		return $this->mail;
	}


	public static function getLoginUser()
	{
		$session = mfwSession::get(self::SESKEY);
		if(!isset($session['mail'])){
			return null;
		}
		return new self($session['mail'], $session['as_admin'], $session['device_uuid'], $session['device_info_id'], $session['device_udid']);
	}

	public static function login($mail, $as_admin = 0, $device_uuid = null, $device_info_id = 0, $device_udid = null)
	{
		$data = array(
			'mail' => $mail,
			'as_admin' => $as_admin,
			'device_uuid' => $device_uuid,
			'device_info_id' => $device_info_id,
			'device_udid' => $device_udid,
			);
		mfwSession::set(self::SESKEY, $data);
		return new self($mail, $as_admin, $device_uuid, $device_info_id, $device_udid);
	}

	public static function loginWithUUID($device_uuid)
	{
                $ios_device_info = IOS_DeviceInfoDb::selectByDeviceUUID($device_uuid);
               	if ( empty($ios_device_info) ) {
                       	return null;
               	}
		$device_udid = $ios_device_info->getDeviceUDID();
		$device_info_id = $ios_device_info->getId();
		$mail = $ios_device_info->getMail();
               	$user_pass = UserPassDb::selectByEmail($mail);
               	if ( !$user_pass ) {
                       	return null;
               	}
		$mail = $user_pass->getMail();
               	$as_admin = $user_pass->getAsAdmin();
               	return User::login($mail, $as_admin, $device_uuid, $device_info_id);
	}

	public static function logout()
	{
		mfwSession::clear(self::SESKEY);
	}

	public function getPackageInstalledDate(Package $pkg,$format=null)
	{
		$appid = $pkg->getAppId();
		if(!isset($this->pkg_install_dates[$appid])){
			$this->pkg_install_dates[$appid] = InstallLog::packageInstalledDates($this,$appid);
		}
		if(!isset($this->pkg_install_dates[$appid][$pkg->getId()])){
			return null;
		}
		$date = $this->pkg_install_dates[$appid][$pkg->getId()];
		if($format){
			$date = date($format,strtotime($date));
		}
		return $date;
	}

	public function getPackageInstalleable(Package $pkg)
	{
		$appid = $pkg->getAppId();
		//var_dump_log("app_id", $pkg->getAppId());
		$package_id = $pkg->getId();
		//var_dump_log("package_id", $package_id);
                if ( !isset($this->pkg_installables[$appid]) ) {
			$this->pkg_installables[$appid] = $pkg->getInstallablePackageIds($this->getDeviceUDID());
                }
		//var_dump_log("pkg_installables", $this->pkg_installables[$appid]);
		if ( $this->pkg_installables[$appid] == null ) {
			return true;
		}
                if ( !in_array($package_id, $this->pkg_installables[$appid]) ) {
                        return false;
                }
                return true;
	}

	public function getInstallApps()
	{
		if($this->install_apps===null){
			$this->install_apps = InstallLog::getInstallApps($this);
		}
		return $this->install_apps;
	}

	public function getAppInstallDate(Application $app,$format=null)
	{
		$install_apps = $this->getInstallApps();

		if(isset($install_apps[$app->getId()])){
			return $install_apps[$app->getId()]->getLastInstalled();
		}
		return null;
	}

	public function getInstallPackages($app_id)
	{
		$pkg_ids = InstallLog::getInstallPackageIds($this,$app_id);
		return PackageDb::retrieveByPKs($pkg_ids);
	}

	public function isAdmin()
	{
		return ( ( $this->as_admin != 0 ) );
	}

	public function getDeviceUUID()
	{
		return ( $this->device_uuid );
	}

	public function getDeviceUDID()
	{
		return ( $this->device_udid );
	}

	public function getDeviceInfoId()
	{
		return ( $this->device_info_id );
	}

	/**
	 * @return GuestPass[]
	 */
	public function getGuestpasses()
	{
		return GuestPassDB::selectByOwnerMail($this->mail);
	}
}

