<?php
require_once __DIR__.'/actions.php';

class requestActions extends appActions
{
	const REQUESTS_IN_PAGE = 20;

	public function executeRequest()
	{
		$app_id = $this->app->getId();
		$request_count = RequestDb::selectCountByAppId($app_id);

		$current_page = mfwRequest::param('page',1);
		$max_page = ceil($request_count/self::REQUESTS_IN_PAGE);
		$offset = (max(0,min($current_page,$max_page)-1)) * self::REQUESTS_IN_PAGE;

		$requests = RequestDb::selectByAppId($app_id,self::REQUESTS_IN_PAGE,$offset);

		$requested_package = PackageDb::retrieveByPKs($requests->getColumnArray('package_id'));

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
		$package_id = mfwRequest::param('package_id');

		$con = mfwDBConnection::getPDO();
		$con->beginTransaction();
		try{
			$this->app = ApplicationDb::retrieveByPkForUpdate($this->app->getId());

			$request = RequestDb::post($this->login_user,$this->app,$package_id,$message);

			$this->app->updateLastRequested($request->getCreated());

			$con->commit();
		}
		catch(Exception $e){
			error_log(__METHOD__.'('.__LINE__.'): '.get_class($e).":{$e->getMessage()}");
			$con->rollback();
			throw $e;
		}

		$owners = $this->app->getOwners();
		$owners->noticeNewRequest($request,$this->app);

		return $this->redirect('/app/request',array('id'=>$this->app->getId()));
	}

}

