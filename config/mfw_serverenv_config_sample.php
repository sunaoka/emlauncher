<?php
/**
 * ServerEnv configuration
 */
$serverenv_config = array(
	'application_identifier' => 'ohoflight2-dev',

	'local' => array(
		'database' => array(
			'authfile' => '/home/dbauth/httpd',
			'default_master' => 'mysql:dbname=emlauncher;host=localhost',
			),
		'http_proxy' => array(
			),
		'memcache' => array(
			'host' => 'localhost',
			'port' => 11211,
			),
		),

	);

$serverenv_config['ec2'] = $serverenv_config['local'];
$serverenv_config['ec2']['database']['authfile'] = '/home/ohoflight2/dbauth/httpd';

