<?php
require_once APP_ROOT.'/model/Application.php';
require_once APP_ROOT.'/model/Package.php';
require_once APP_ROOT.'/model/Comment.php';
require_once APP_ROOT.'/model/IOS_UDID.php';

class profileActions extends MainActions
{
	protected $device_uuid = null;

	public function initialize()
	{
		if ( ( $err = parent::initialize() ) ) {
			return $err;
		}
		return null;
	}

	public function build($params)
	{
		$this->device_uuid = uuid_create(UUID_TYPE_RANDOM);
		$params['device_uuid'] = $this->device_uuid;
		return parent::build($params);
	}

	private function makeSignedData($data)
	{
		$plain_profile = tempnam("/tmp" ,"plain_rofile");
		$signed_profile = tempnam("/tmp" ,"signed_rofile");
		$fp = fopen($plain_profile, "w");
		fwrite($fp, $data);
		fclose($fp);
		//openssl_pkcs7_sign($plain_profile , $signed_profile, "file://".realpath("/tmp/ssl_cert.pem"), array("file://".realpath("/tmp/ssl_private.pem"), ''), array(), PKCS7_DETACHED, realpath("/tmp/ssl_chain.pem"));
		$openssl_cmd = "openssl smime -sign -signer /tmp/ssl_cert.pem -inkey /tmp/ssl_private.pem -certfile /tmp/ssl_chain.pem -nodetach -outform der -in $plain_profile -out $signed_profile";
		system($openssl_cmd, $return_val);
		return file_get_contents($signed_profile);
	}


	public function executeDownload()
	{
		$emlauncher_url = mfwRequest::makeUrl('');
		$device_uuid = mfwRequest::param('device_uuid');
		header('Content-type: application/x-apple-aspen-config; chatset=utf-8');
		header('Content-Disposition: attachment; filename="get_udid_signed.mobileconfig"');
$data = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
    <dict>
        <key>PayloadContent</key>
	<dict>
	  <key>URL</key>
	  <string>https://secp.ipv6ready.me:8443/profile?device_uuid=$device_uuid</string>
	  <key>DeviceAttributes</key>
	  <array>
	    <string>UDID</string>
            <string>IMEI</string>
            <string>ICCID</string>
            <string>VERSION</string>
            <string>PRODUCT</string>
	  </array>
	</dict>
        <key>PayloadOrganization</key>
        <string>Hoge2Network</string>
        <key>PayloadDisplayName</key>
        <string>EMlauncher UDID Service</string>
        <key>PayloadVersion</key>
        <integer>1</integer>
        <key>PayloadUUID</key>
        <string>$device_uuid</string>
        <key>PayloadIdentifier</key>
        <string>hoge2.net.profile-service</string>
	<key>PayloadDescription</key>
        <string></string>
        <key>PayloadRemovalDisallowed</key>
        <false/>
        <key>PayloadType</key>
        <string>Profile Service</string>
    </dict>
</plist>
END;
		echo $this->makeSignedData($data);
		$owner = $this->login_user;
		$new_ios_udid = IOS_UDIDDb::insertNewIOS_UUID($owner, $device_uuid);
	}

	public function executeIndex()
	{
		return $this->build();
	}
}
