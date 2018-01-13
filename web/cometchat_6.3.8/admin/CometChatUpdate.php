<?php
/*
CometChat
Copyright (c) 2016 Inscripts
License: https://www.cometchat.com/legal/license
*/
if(!class_exists('CometChat')){
	class CometChat {
	}
}
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
class CometChatUpdate extends CometChat{
/*
	CometChatUpdate  constructor
*/
	public $filehasharray = array();
	public $directoryhasharray = array();
	public $writablepath ;
	function __construct(){
		$this->writablepath = dirname(dirname(__FILE__)).DS.'writable'.DS;
	}
/*
	Checks if cometchat.zip file is present in updates folder.
*/
	public function checkAvailableZip(){
		global $settings;
		if(!empty($settings['LATEST_VERSION'])){
			if(file_exists($this->writablepath.'updates'.DS.$settings['LATEST_VERSION']['value'].DS.'cometchat.zip')){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
/*
	Extract zip file
*/
	public function extractZip(){
		global $settings;
		if(!empty($settings['LATEST_VERSION'])){
			$path = $this->writablepath.'updates'.DS.$settings['LATEST_VERSION']['value'].DS.'cometchat.zip';
			$to = $this->writablepath.'updates'.DS.$settings['LATEST_VERSION']['value'].DS;
			if(file_exists($path)){
				if(class_exists('ZipArchive')){
					$zip = new ZipArchive();
					$x = $zip->open($path);
					if ($x === true) {
						$zip->extractTo($to);
						$zip->close();
						@unlink($path);
						return true;
					}else{
						return false;
					}
				}else{
					include_once(dirname(dirname(__FILE__)).DS.'functions'.DS.'archive'.DS.'pclzip.lib.php');
					$archive = new PclZip($path);
					if ($archive->extract(PCLZIP_OPT_PATH, $to) == 0) {
						echo "fail to extract: ".$archive->errorName(true);
						return false;
					}else{
						return true;
					}
				}
			}
		}
	}
	public function applyChanges() {
		global $settings;
		if(!empty($settings['LATEST_VERSION'])){
			if(is_dir($this->writablepath.'updates'.DS.$settings['LATEST_VERSION']['value'].DS.'cometchat')){
				$exclude = array('writable','update.m.php','CometChatUpdate.php','integration.php','extra.js','extra.css','admin');
				$directory = dirname(dirname(__FILE__));
				$this->deleteFiles($directory,$exclude,$directory);
				$exclude = array('admin');
				$directory = dirname(dirname(__FILE__)).DS.'admin';
				$exclude = array('update.m.php','CometChatUpdate.php');
				$this->deleteFiles($directory,$exclude,$directory);
				$source = $this->writablepath.'updates'.DS.$settings['LATEST_VERSION']['value'].DS.'cometchat';
				$dest = dirname(dirname(__FILE__));
				$exclude = array('writable','integration.php','extra.js','extra.css');
				$this->copyFiles($source,$dest,$exclude);
				$this->copyFiles($source.DS.'writable'.DS.'hashes',$dest.DS.'writable'.DS.'hashes',$exclude);
				$directory = $this->writablepath.DS.'updates'.$settings['LATEST_VERSION']['value'].DS.'cometchat';
				$this->deleteFiles($directory,array('test'),'test');
				$this->deleteFiles($this->writablepath.'cache',array('index.html'),$this->writablepath.'cache');
				$frame = true;
				$frame = $this->DBChanges();
				return $frame;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	function deleteFiles($dir,$exclude,$nodelete) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != ".." && !in_array($object, $exclude) ) {
					if (is_dir($dir.DS.$object)){
						$this->deleteFiles($dir.DS.$object,$exclude,$nodelete);
					}else{
						@unlink($dir.DS.$object);
					}
				}
			}
			if($dir != $nodelete ){
				@rmdir($dir);
			}
		}
	}
	function copyFiles($src, $dst,$exclude) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' ) && !in_array($file, $exclude)) {
				if ( is_dir($src . DS . $file) ) {
					$this->copyFiles($src . DS . $file,$dst . DS . $file,$exclude);
				}
				else {
					@copy($src . DS . $file,$dst . DS . $file);
				}
			}
		}
		closedir($dir);
	}
/*
	Ask client to continue or skip update process
*/
	public function backupFiles(){
		global $currentversion;
		$source = dirname(dirname(__FILE__)).DS;
		if(!is_dir($this->writablepath.'updates'.DS.$currentversion.DS.'cometchat_backup')){
			@mkdir($this->writablepath.'updates'.DS.$currentversion);
			@mkdir($this->writablepath.'updates'.DS.$currentversion.DS.'cometchat_backup');
			$dest = $this->writablepath.'updates'.DS.$currentversion.DS.'cometchat_backup';
			$exclude = array('writable');
			$this->copyFiles($source, $dest,$exclude);
		}
		return true;
	}
/*
	Get the hash of given version stored in session
*/
	public function getHash($directory,$calhash){
		global $currentversion;
		@session_start();
		$_SESSION['cometchat']['hashes'][$currentversion] = array();
		$files = scandir($directory);
		sort($files);
		$filehash = array();
		foreach($files as $key => $value){
			$path = realpath($directory.DS.$value);
			if(!is_dir($path) ) {
				if(in_array($value, $calhash['files']) && $value != 'integration.php' && $value != 'license.php'){
					$hash = md5_file($path);
					$filehash[] = $hash;
				}
			} else if($value != "." && $value != ".." && $value != 'writable') {
				if(in_array($value, array('extensions','modules','plugins','transports'))){
					$extensions_dir = $directory.DS.$value;
					$handle = scandir($extensions_dir);
					sort($handle);
					foreach($handle as $key_temp => $value_temp){
						$path_temp = realpath($extensions_dir.DS.$value_temp);
						if(is_dir($path_temp) && $value_temp != "." && $value_temp != ".."){
							$dirhash = $this->CometChatDirectoryHash($path_temp);
							$this->filehasharray['directoryhash'][$value.'/'.$value_temp] = $dirhash;
						}
					}
				}else{
					$dirhash = $this->CometChatDirectoryHash($path);
					$this->filehasharray['directoryhash'][$value] = $dirhash;
				}
			}
		}
		$this->filehasharray['fileshash'] = md5(implode('', $filehash));
		unset($_SESSION['cometchat']['hashes']);
		$_SESSION['cometchat']['hashes'][$currentversion] = $this->filehasharray;
	}
/*
	generate hash of cometchat with all files
*/
	public function generateHash($directory){
		global $currentversion;
		$files = scandir($directory);
		sort($files);
		$filehash = array();
		foreach($files as $key => $value){
			$path = realpath($directory.DS.$value);
			if(!is_dir($path) ) {
				if($value != 'integration.php' && $value != 'license.php'){
					$hash = md5_file($path);
					$filehash[] = $hash;
					$this->filehasharray['files'][] = $value;
				}
			} else if($value != "." && $value != ".." && $value != 'writable') {
				if(in_array($value, array('extensions','modules','plugins','transports'))){
					$extensions_dir = $directory.DS.$value;
					$handle = scandir($extensions_dir);
					sort($handle);
					foreach($handle as $key_temp => $value_temp){
						$path_temp = realpath($extensions_dir.DS.$value_temp);
						if(is_dir($path_temp) && $value_temp != "." && $value_temp != ".."){
							$dirhash = $this->CometChatDirectoryHash($path_temp);
							$this->filehasharray['directory'][$value.'/'.$value_temp] = $dirhash;
						}
					}
				}else{
					$dirhash = $this->CometChatDirectoryHash($path);
					$this->filehasharray['directory'][$value] = $dirhash;
				}
			}
		}
		$this->filehasharray['fileshash'] = md5(implode('', $filehash));
		ksort($this->filehasharray);
	}
/*
	Store hash in file
*/
	public function insertHash($filename){
		global $currentversion;
		$exported = var_export($this->filehasharray, TRUE);
		file_put_contents($this->writablepath."hashes".DS.$currentversion.".php", '<?php $calhash = ' . $exported . '; ?>');
	}
/*
	Compare hash with present hash files and computed hash files
*/
	public function compareHashes(){
		global $currentversion;
		global $update;
		$os_type = PHP_OS;
		if(file_exists($this->writablepath."hashes".DS.$currentversion.".php")){
			include_once($this->writablepath."hashes".DS.$currentversion.".php");
		}else{
			return false;
		}
		$hash = $update->getHash(dirname(dirname(__FILE__)).DS,$calhash);
		$sessionhash = $_SESSION['cometchat']['hashes'][$currentversion];
		if($calhash['fileshash'] != $sessionhash['fileshash']){
			return false;
		}else{
			foreach ($sessionhash['directoryhash'] as $key => $value) {
				if($value != $calhash['directory'][$key]){
					return false;
				}
			}
			return true;
		}
	}
	public function CometChatDirectoryHash($directory){
		if (! is_dir($directory)){
			return false;
		}
		$forcefiles = array('.','..','integration.php','writable');
		$files = array();
		$dir = dir($directory);
		while (false !== ($file = $dir->read())){
			if (!in_array($file, $forcefiles)){
				if (is_dir($directory . DS . $file)){
					$files[] = $this->CometChatDirectoryHash($directory . DS . $file);
				}else{
					$file_ext =  strtolower(pathinfo($file,PATHINFO_EXTENSION));
					$allowed_ext = array('php','xml','js','json','css','htaccess','svg','txt','sql');
					if(in_array($file_ext, $allowed_ext)){
						$files[] = md5_file($directory . DS . $file);
					}
				}
			}
		}
		$dir->close();
		sort($files);
		return md5(implode('', $files));
	}
	public function saveZip($url,$version){
		if(!empty($url) && !empty($version)){
			if(!is_dir($this->writablepath."updates".DS.$version)){
				if(!@mkdir($this->writablepath."updates".DS.$version)){
					 $error = error_get_last();
					 return $error['message'];
				}
			}
			return $this->downloadZipFile($url,$this->writablepath."updates".DS.$version.DS."cometchat.zip");
		}
	}

	public function downloadZipFile($url, $filepath){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		$raw_file_data = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'error:' . curl_error($ch);
		}
		curl_close($ch);

		file_put_contents($filepath, $raw_file_data);
		return (filesize($filepath) > 0)? true : false;
	}

	public function DBChanges(){
		$folder = dirname(dirname(__FILE__)).DS.'updates';
		$files = array();
		$oldversion = $_SESSION['cometchat']['old_version'];
		$dir = opendir($folder);
		if(is_dir($folder)){
			while(false !== ( $file = readdir($dir)) ) {
				if($file != '.' && $file != '..'){
					$files[] = $file;
				}
			}
		}
		$versions = array();
		foreach ($files as $key => $value) {
			if($value > $oldversion){
				$versions[] = $value;
			}
		}
		if (count($versions)>0) {
			return $versions;
		}else{
			return true;
		}

	}
}
