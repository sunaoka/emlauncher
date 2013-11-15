<?php
require_once __DIR__.'/actions.php';

class preferencesActions extends appActions
{
	public function initialize()
	{
		if(($err=parent::initialize())){
			return $err;
		}
		if(!$this->app->isOwner($this->login_user)){
			return $this->buildErrorPage(
				'Permission Denied',array(self::HTTP_403_FORBIDDEN));
		}
		return null;
	}

	public function executePreferences()
	{
		$params = array(
			);
		return $this->build($params);
	}

	public function executePreferences_refresh_apikey()
	{
		$oldkey = mfwRequest::param('api-key','POST');
		$con = mfwDBConnection::getPDO();
		$con->beginTransaction();
		try{
			$this->app = ApplicationDb::retrieveByPkForUpdate($this->app->getId());
			if($this->app->getApiKey()===$oldkey){
				$this->app->refreshApiKey($con);
			}
			$con->commit();
		}
		catch(Exception $e){
			$con->rollback();
			error_log(__METHOD__.": {$e->getMessage()}");
			throw $e;
		}
		return $this->redirect("/app/preferences?id={$this->app->getId()}#refresh-apikey");
	}

	public function executePreferences_update()
	{
		$title = mfwRequest::param('title');
		$data = mfwRequest::param('icon-data');
		$description = mfwRequest::param('description');
		$repository = mfwRequest::param('repository');
		$image = null;

		if(!$title || ($data&&!preg_match('/^data:[^;]+;base64,(.+)$/',$data,$match))){
			error_log(__METHOD__.": bad request: $title, ".substr($data,0,30));
			return $this->response(self::HTTP_400_BADREQUEST);
		}
		if($data){
			$image = base64_decode($match[1]);
		}

		$con = mfwDBConnection::getPDO();
		$con->beginTransaction();
		try{
			$this->app = ApplicationDb::retrieveByPkForUpdate($this->app->getId());
			$this->app->updateInfo($title,$image,$description,$repository,$con);

			$con->commit();
		}
		catch(Exception $e){
			error_log(__METHOD__.": {$e->getMessage()}");
			$con->rollback();
			throw $e;
		}
		return $this->redirect("/app/preferences?id={$this->app->getId()}#edit-info");
	}

	public function executePreferences_delete_tags()
	{
		$tag_names = mfwRequest::param('tags');
		if(!empty($tag_names)){
			$con = mfwDBConnection::getPDO();
			$con->beginTransaction();
			try{
				$this->app = ApplicationDb::retrieveByPkForUpdate($this->app->getId());

				$this->app->deleteTags($tag_names,$con);

				$con->commit();
			}
			catch(Exception $e){
				error_log(__METHOD__.": {$e->getMessage()}");
				$con->rollback();
				throw $e;
			}
		}
		return $this->redirect("/app/preferences?id={$this->app->getId()}#delete-tags");
	}


}
