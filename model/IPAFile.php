<?php
require_once APP_ROOT.'/libs/CFPropertyList/classes/CFPropertyList/CFPropertyList.php';

class IPAFile {

        protected function unzipMobileProvisionFileName($ipafile)
        {
                $p = popen("unzip -l \"$ipafile\" \"Payload/*/embedded.mobileprovision\" 2>/dev/null",'r');
                $fname = null;
                while(($l=fgets($p))){
                        if(preg_match('/ +(Payload\/[^\/]+\.app\/embedded.mobileprovision)\n$/',$l,$m)){
                                $fname = $m[1];
                                break;
                        }
                }
                pclose($p);

                return $fname;
        }

	protected function unzipInfoPlistFileName($ipafile)
	{
		$p = popen("unzip -l \"$ipafile\" \"Payload/*/Info.plist\" 2>/dev/null",'r');
		$fname = null;
		while(($l=fgets($p))){
			if(preg_match('/ +(Payload\/[^\/]+\.app\/Info.plist)\n$/',$l,$m)){
				$fname = $m[1];
				break;
			}
		}
		pclose($p);

		return $fname;
	}

	protected function unzipFile($ipafile,$filename)
	{
		$p = popen("unzip -cq \"$ipafile\" \"$filename\" 2>/dev/null",'r');
		$ret = stream_get_contents($p);
		pclose($p);

		return $ret;
	}

        protected function unzipFileAndStrip($ipafile,$filename)
        {
                $p1 = popen("unzip -cq \"$ipafile\" \"$filename\" 2>/dev/null",'r');
                $ret1 = stream_get_contents($p1);
		pclose($p1);
		$descriptorspec = array(
   			0 => array("pipe", "r"),
   			1 => array("pipe", "w"),
   			2 => array("file", "/tmp/error-output.txt", "a")
		);

		$cwd = NULL; //'/tmp';
		$env = NULL; //array('some_option' => 'aeiou');

		$p2 = proc_open("/usr/bin/openssl smime -inform der -verify -noverify", $descriptorspec, $pipes, $cwd, $env);

		fwrite($pipes[0], $ret1);
		fclose($pipes[0]);

		$ret2 = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
                proc_close($p2);

                return $ret2;
        }

	public static function parseInfoPlist($ipafile)
	{
		$plist_name = self::unzipInfoPlistFileName($ipafile);
		if(!$plist_name){
			throw new UnexpectedValueException(__METHOD__.": Info.plist file not found.");
		}
		$info_plist = self::unzipFile($ipafile,$plist_name);
		$plutil = new CFPropertyList\CFPropertyList();
		$plutil->parse($info_plist);
		return $plutil->toArray();
	}

        public static function parseMobileProvision($ipafile)
        {
                $profile_name = self::unzipMobileProvisionFileName($ipafile);
		error_log("profile_name: " . $profile_name, 3, "/tmp/parse.log");
                if(!$profile_name){
                        throw new UnexpectedValueException(__METHOD__.": embedded.mobiion file not found.");
                }
                $mobile_provison = self::unzipFileAndStrip($ipafile,$profile_name);
                $plutil = new CFPropertyList\CFPropertyList();
                $plutil->parse($mobile_provison);
                return $plutil->toArray();
        }

}
