<?php
require_once APP_ROOT.'/model/Application.php';
require_once APP_ROOT.'/model/Package.php';
require_once APP_ROOT.'/model/Comment.php';
require_once APP_ROOT.'/model/UserPass.php';

class accountActions extends MainActions
{
	protected $app = null;

	const LINE_IN_PAGE = 20;

	public function initialize()
	{
		if ( ( $err = parent::initialize() ) ) {
			return $err;
		}

                if ( ! $this->login_user->isAdmin() ) {
                        // ログイン済みならTOPに飛ばす
                        return $this->redirect('/');
                }

		if ( in_array($this->getAction(), array('new', 'create') ) ) {
			return null;
		}
		/*
		$id = mfwRequest::param('id');
		$this->app = ApplicationDb::retrieveByPK($id);
		if(!$this->app){
			return $this->buildErrorPage('Not Found',array(self::HTTP_404_NOTFOUND));
		}
		*/
		return null;
	}

	public function build($params)
	{
		if(!isset($params['app'])){
			$params['app'] = $this->app;
		}
		return parent::build($params);
	}

	public function executeNew()
	{
		$params = array(
			);
		return $this->build($params);
	}

	public function executeCreate()
	{
		$email = mfwRequest::param('email');
		$password = mfwRequest::param('password');
		$as_admin = ( mfwRequest::param('as_admin') == "1" );

		$con = mfwDBConnection::getPDO();
		$con->beginTransaction();
		try{
			$user_pass = UserPassDb::insertNewUser($email, $password, $as_admin);
			$con->commit();
			$sendResetMail = false;
			if ( empty($password) ) {
				$user_pass->sendResetMail();
				$sendResetMail = true;
			} 
		}
		catch(Exception $e){
			error_log(__METHOD__.'('.__LINE__.'): '.get_class($e).":{$e->getMessage()}");
			$con->rollback();
			throw $e;
		}
		apache_log('user_id', $user_pass->getMail());

                $params = array(
                        'sendResetMail' => $sendResetMail,
                        );

		return $this->build($params);
	}

	public function executeIndex()
	{
	}

        public function executeUserlist()
        {
                $userlist = UserList::getUserList();
                //$user_list->sortByDesc('last_logged_in');
                $params = array(
                        'userlist' => $userlist,
                        );
                return $this->build($params);
        }

        public function executeDelete()
        {
                $email = mfwRequest::param('email');
                $user_pass = UserPassDb::selectByEmail($email);

                if ( $user_pass ) {
                        $user_pass->delete();
                }

                return $this->redirect('/account/userlist');
        }
}
