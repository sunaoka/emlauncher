<?php
require_once APP_ROOT.'/model/Config.php';

class S3 {

	protected static $singleton = null;
	protected $config;
	protected $client;

	protected function __construct()
	{
		$this->config = Config::get('aws');
		if ( $this->config == null ) {
			$client = null;
			$this->config = Config::get('local_filesystem');
		}
		else {
			$this->client = Aws\S3\S3Client::factory(
				array(
					'key' => $this->config['key'],
					'secret' => $this->config['secret'],
					'base_url' => $this->config['base_url'],
					));
		}
	}
	protected function singleton()
	{
		if(static::$singleton===null){
			static::$singleton = new static();
		}
		return static::$singleton;
	}

	public static function uploadData($key,$data,$type,$acl='private')
	{
		$s3 = static::singleton();
		if ( $s3->client == null ) {
			$path = $s3->config['path'];
			$path_info = pathinfo("{$path}/{$key}");
			if ( ! file_exists($path_info["dirname"]) ) {
				if ( ! is_file($path_info["dirname"]) ) {
					mkdir($path_info["dirname"], 0777, true);
				}
			}
                	file_put_contents("{$path}/{$key}", $data);
                	return;
		}
		$r = $s3->client->putObject(
			array(
				'Bucket' => $s3->config['bucket_name'],
				'Key' => $key,
				'ACL' => $acl,
				'ContentType' => $type,
				'Body' => Guzzle\Http\EntityBody::factory($data),
				));
		return $r;
	}

	public static function uploadFile($key,$filename,$type,$acl='private')
	{
		$s3 = static::singleton();
		if ( $s3->client == null ) {
			$path = $s3->config['path'];
			$path_info = pathinfo("{$path}/{$key}");
                        if ( ! file_exists($path_info["dirname"]) ) {
                                if ( ! is_file($path_info["dirname"]) ) {
                                        mkdir($path_info["dirname"], 0777, true);
                                }
                        }
			$fp = fopen($filename,'rb');
			file_put_contents("{$path}/{$key}", $fp);
			fclose($fp);
			return;
		}
		$fp = fopen($filename,'rb');
		$r = $s3->client->putObject(
			array(
				'Bucket' => $s3->config['bucket_name'],
				'Key' => $key,
				'ACL' => $acl,
				'ContentType' => $type,
				'Body' => $fp,
				));
		// Guzzleが中で勝手にfcloseしやがるのでここでfcloseしてはならない
		// fclose($fp)
		return $r;
	}

	public static function rename($srckey,$dstkey,$acl='private')
	{
		$s3 = static::singleton();
		if ( $s3->client == null ) {
			$path = $s3->config['path'];
			$path_info = pathinfo("{$path}/{$dstkey}");
                        if ( ! file_exists($path_info["dirname"]) ) {
                                if ( ! is_file($path_info["dirname"]) ) {
                                        mkdir($path_info["dirname"], 0777, true);
                                }
                        }
			rename("{$path}/{$srckey}", "{$path}/{$dstkey}");
			return;
		}
		$bucket = $s3->config['bucket_name'];

		// copy
		$s3->client->copyObject(
			array(
				'Bucket' => $bucket,
				'Key' => $dstkey,
				'ACL' => $acl,
				'CopySource' => "{$bucket}/{$srckey}",
				));
		// delete
		$s3->client->deleteObject(
			array(
				'Bucket' => $bucket,
				'Key' => $srckey,
				));
	}

	public static function delete($key)
	{
		$s3 = static::singleton();
		if ( $s3->client == null ) {
			$path = $s3->config['path'];
			unlink("{$path}/{$key}");
			return;
		}
		$bucket = $s3->config['bucket_name'];
		$s3->client->deleteObject(
			array(
				'Bucket' => $bucket,
				'Key' => $key,
				));
	}

	public static function url($key,$expires=null)
	{
		$s3 = static::singleton();
		if ( $s3->client == null ) {
			$url = $s3->config['url'];
                	return "{$url}/{$key}";
		}
		$bucket = $s3->config['bucket_name'];
		return $s3->client->getObjectUrl($bucket,$key,$expires);
	}

}

