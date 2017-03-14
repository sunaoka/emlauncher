<?php
require_once APP_ROOT.'/model/InstallLog.php';

class User
{
	const SESKEY = 'login_user';

	protected $mail;
	protected $as_dmin;
	protected $pkg_install_dates = array();
	protected $install_apps = null;

	public function __construct($mail, $as_admin = 0)
	{
		$this->mail = $mail;
		$this->as_admin = $as_admin;
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
		return new self($session['mail'], $session['as_admin']);
	}

	public static function login($mail, $as_admin = 0)
	{
		$data = array(
			'mail' => $mail,
			'as_admin' => $as_admin,
			);
		mfwSession::set(self::SESKEY,$data);
		return new self($mail);
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
		return ( $this->as_admin );
	}

	/**
	 * @return GuestPass[]
	 */
	public function getGuestpasses()
	{
		return GuestPassDB::selectByOwnerMail($this->mail);
	}
}

