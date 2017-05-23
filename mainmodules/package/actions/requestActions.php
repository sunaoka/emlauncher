<?php
require_once __DIR__.'/actions.php';

class requestActions extends packageActions
{
	const REQUESTS_IN_PAGE = 20;

	public function executeRequest()
	{
		$package_id = $this->package->getId();
		$request_count = RequestDb::selectCountByPackageId($package_id);

		$current_page = mfwRequest::param('page',1);
		$max_page = ceil($request_count/self::REQUESTS_IN_PAGE);
		$offset = (max(0,min($current_page,$max_page)-1)) * self::REQUESTS_IN_PAGE;
		$install_packages = $this->login_user->getInstallPackages($app_id);
		$install_packages->sort(function($a,$b){ return $a['id'] < $b['id']; });

		$params = array(
			'requests_in_page' => self::REQUESTS_IN_PAGE,
			'request_count' => $request_count,
			'requests' => $requests,
			'requested_package' => $requested_package,
			'cur_page' => $current_page,
			'max_page' => $max_page,
			'install_packages' => $install_packages,
			);
		return $this->build($params);
	}

	public function executeRequest_post()
	{
		$message = mfwRequest::param('message');
		$package_id = mfwRequest::param('id');

		$con = mfwDBConnection::getPDO();
		$con->beginTransaction();
		try {
			$this->app = ApplicationDb::retrieveByPkForUpdate($this->app->getId());

			$request = RequestDb::post($this->login_user,$this->app,$package_id,$message);

			$this->app->updateLastRequested($request->getCreated());

			$con->commit();
		}
		catch ( Exception $e ) {
			error_log(__METHOD__.'('.__LINE__.'): '.get_class($e).":{$e->getMessage()}");
			$con->rollback();
			throw $e;
		}

		$owners = $this->app->getOwners();
		$owners->noticeNewRequest($request, $this->app);

		return $this->redirect('/package', array('id' => $package_id));
	}

}

