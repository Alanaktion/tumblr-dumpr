<?php

/**
 * Zip class
 * @author Code Synthesis <www.codesynthesis.co.uk>
 * @see http://www.codesynthesis.co.uk/tutorials/zip-a-directory-and-automatically-download-using-php
 */
class Zip {
	private $zip;

	public function __construct($file_name, $zip_directory) {
		$this->zip = new ZipArchive();
		$this->path = dirname(__FILE__) . $zip_directory . $file_name . '.zip';
		$this->zip->open($this->path, ZipArchive::CREATE);
	}

   /**
	 * Get the absolute path to the zip file
	 * @return string
	 */
	public function getZipPath() {
		return $this->path;
	}

	/**
	 * Add a directory to the zip
	 * @param string $directory
	 */
	public function addDirectory($directory) {
		if(is_dir($directory) && $handle = opendir($directory)) {
			$this->zip->addEmptyDir($directory);
			while(($file = readdir($handle)) !== false) {
				if (!is_file($directory . '/' . $file)) {
					if (!in_array($file, array('.', '..'))) {
						$this->addDirectory($directory . '/' . $file );
					}
				}
				else {
					$this->addFile($directory . '/' . $file);
				}
			}
		}
	}

	/**
	 * Add files from a directory to the root of the zip
	 * @param string $directory
	 */
	public function addDirectoryFilesToRoot($directory) {
		if(is_dir($directory) && $handle = opendir($directory)) {
			while(($file = readdir($handle)) !== false) {
				if($file != '.' && $file != '..') {
					$this->zip->addFile($directory . '/' . $file, $file);
				}
			}
			closedir($handle);
		}
	}

	/**
	 * Add a single file to the zip
	 * @param string $path
	 */
	public function addFile($path) {
		$this->zip->addFile($path, $path);
	}

	/**
	 * Close the zip file
	 */
	public function save() {
		$this->zip->close();
	}
}

// Setup
session_start();
if(empty($_SESSION['token'])) {
	header("HTTP/1.1 403 Forbidden");
	exit("No token found in session.");
}
if(empty($_REQUEST['blog'])) {
	exit("No blog specified.");
}
$blog = $_REQUEST['blog'];
if(!strpos($blog, ".")) {
	$blog .= ".tumblr.com";
}

// Create zip file
$zip = new Zip($_SESSION['token'], '/zips/');
$zip->addDirectoryFilesToRoot($blog);
$zip->save();

// Download zip
$zip_path = $zip->getZipPath();
if(!is_file($zip_path)) {
	exit('Zip file not found.');
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=\"{$blog}.zip\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($zip_path));
readfile($zip_path);

// Delete zip
unlink($zip_path);
