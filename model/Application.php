<?php
require_once __DIR__.'/ApplicationOwner.php';
require_once __DIR__.'/Tag.php';
require_once __DIR__.'/InstallLog.php';
require_once __DIR__.'/S3.php';
require_once __DIR__.'/Random.php';

/**
 * Row object for 'application' table.
 */
class Application extends mfwObject {
	const DB_CLASS = 'ApplicationDb';
	const SET_CLASS = 'ApplicationSet';

	protected $owners = null;
	protected $tags = null;

	public function getId(){
		return $this->value('id');
	}
	public function getTitle(){
		return $this->value('title');
	}
	public function getDescription(){
		return $this->value('description');
	}
	public function getRepository(){
		return $this->value('repository');
	}
	public function getIconUrl()
	{
		return S3::url($this->value('icon_key'));
	}
	public function getLastUpload(){
		return $this->value('last_upload');
	}
	public function updateLastUpload($con=null)
	{
		$this->row['last_upload'] = date('Y-m-d H:i:s');
		$sql = 'UPDATE application SET last_upload = :now WHERE id = :id';
		$bind = array(
			':id' => $this->getId(),
			':now' => $this->getLastUpload(),
			);
		mfwDBIBase::query($sql,$bind,$con);
	}
	public function getAPIKey()
	{
		return $this->value('api_key');
	}
	public function refreshApiKey($con=null)
	{
		$this->row['api_key'] = ApplicationDb::makeApiKey();
		$sql = 'UPDATE application SET api_key = :api_key WHERE id = :id';
		$bind = array(
			':id' => $this->getId(),
			':api_key' => $this->getApiKey(),
			);
		mfwDBIBase::query($sql,$bind,$con);
	}

	public function getCreated(){
		return $this->value('created');
	}
	public function getOwners()
	{
		if($this->owners===null){
			$this->owners = ApplicationOwnerDb::selectByAppId($this->getId());
		}
		return $this->owners;
	}
	public function isOwner(User $user)
	{
		$owners = $this->getOwners();
		$k = $owners->searchPK('owner_mail',$user->getMail());
		return $k!==null;
	}
	public function setOwners(array $owner_mails,$con=null)
	{
		$cur_mails = $this->getOwners()->getMailArray();

		$delete = array_diff($cur_mails,$owner_mails);
		$add = array_diff($owner_mails,$cur_mails);

		if(!empty($delete)){
			ApplicationOwnerDb::deleteOwner($this->getId(),$delete,$con);
		}
		if(!empty($add)){
			ApplicationOwnerDb::addOwner($this->getId(),$add,$con);
		}
		$this->owners = null;
	}

	public function getTags()
	{
		if($this->tags===null){
			$this->tags = TagDb::selectByAppId($this->getId());
		}
		return $this->tags;
	}

	public function getInstallUserCount()
	{
		return InstallLog::getApplicationInstallUserCount($this);
	}

	/**
	 * タグ名からTagSetを取得.
	 * 新しいtag_nameがあったら登録もする.
	 */
	public function getTagsByName($tag_names,PDO $con=null)
	{
		if(empty($tag_names)){
			return new TagSet();
		}
		$this->tags = TagDb::selectByAppIdForUpdate($this->getId(),$con);
		$tags = new TagSet();
		// タグの数はたかが知れているので、愚直に一つずつ探す
		foreach($tag_names as $name){
			if(!$name){
				continue;
			}
			$pk = $this->tags->searchPK('name',$name);
			if($pk){
				$tags[] = $this->tags[$pk];
			}
			else{
				$tag = TagDb::insertNewTag($this->getId(),$name,$con);
				$tags[] = $tag;
				$this->tags[] = $tag;
			}
		}
		return $tags;
	}

	public function deleteTags($tag_names,PDO $con=null)
	{
		$tags = TagDb::selectByAppIdForUpdate($this->getId(),$con);
		$delete_ids = array();
		$this->tags = new TagSet();
		foreach($tags as $tag){
			if(in_array($tag->getName(),$tag_names)){
				$delete_ids[] = $tag->getId();
			}
			else{
				$this->tags[] = $tag;
			}
		}
		TagDb::deleteByIds($delete_ids,$con);
	}

	public function updateInfo($title,$image,$description,$repository,$con=null)
	{
		$this->row['title'] = $title;
		$this->row['description'] = $description;
		$this->row['repository'] = $repository;

		$old_icon_key = null;
		if($image){
			$old_icon_key = $this->value('icon_key');
			$this->row['icon_key'] = ApplicationDb::uploadIcon($image,$this->getId());
		}
		$this->update($con);

		if($old_icon_key){
			try{
				S3::delete($old_icon_key);
			}
			catch(Exception $e){
				error_log(__METHOD__.": {$e->getMessage()}");
				// 画像削除は失敗しても気にしない
			}
		}
	}

}

/**
 * Set of Application objects.
 */
class ApplicationSet extends mfwObjectSet {
	public static function hypostatize(Array $row=array())
	{
		return new Application($row);
	}
	protected function unsetCache($id)
	{
		parent::unsetCache($id);
	}
}

/**
 * database accessor for 'application' table.
 */
class ApplicationDb extends mfwObjectDb {
	const TABLE_NAME = 'application';
	const SET_CLASS = 'ApplicationSet';

	const ICON_DIR = 'app-icons/';

	public static function uploadIcon($image,$app_id)
	{
		$im = new Imagick();
		$im->readImageBlob($image);
		$im->scaleImage(144,144);
		$im->setFormat('png');

		$key = static::ICON_DIR."$app_id/".Random::string(16).'.png';
		S3::upload($key,$im,'image/png','public-read');

		return $key;
	}

	public static function makeApiKey()
	{
		do{
			$api_key = Random::string();
		}while(static::selectByApiKey($api_key));
		return $api_key;
	}

	public static function selectByApiKey($key)
	{
		$query = 'WHERE api_key = ?';
		return static::selectOne($query,array($key));
	}

	public static function insertNewApp($owner,$title,$image,$description,$repository)
	{
		// insert new application
		$row = array(
			'title' => $title,
			'api_key' => static::makeApiKey(),
			'description' => $description,
			'repository' => $repository,
			'created' => date('Y-m-d H:i:s'),
			);
		$app = new Application($row);
		$app->insert();

		// upload icon to S3
		$icon_key = static::uploadIcon($image,$app->getId());

		$table = static::TABLE_NAME;
		mfwDBIBase::query(
			"UPDATE $table SET icon_key = :icon_key WHERE id= :id",
			array(':id'=>$app->getId(),':icon_key'=>$icon_key));

		// insert owner
		$row = array(
			'app_id' => $app->getId(),
			'owner_mail' => $owner->getMail(),
			);
		$owner = new ApplicationOwner($row);
		$owner->insert();

		return $app;
	}

}

