<?php

/*
	A simple (private) Homegirl file server

	by Matthias Weiß <info@codeandcreate.de>
*/

//--- Configuration:

// Turns logging on/off
$debug = false;

// Directory that will be served; must be writeable for the webserver
$dataFolder = dirname(__FILE__) . "/data";

// General user configuration
$userPermissions = [
	'master' => false,		// Reserved for user management
	'home' => "/inbox",		// Home directory; writeable by default
	'globalWrite' => false	// If true, it makes the hole $dataFolder writeable
];

//--- TODO: User Manager will be inserted here!

//--- The main part
if ($debug === true) {
	file_put_contents(dirname(__FILE__) . "/log/requests.log", $_SERVER['REQUEST_METHOD']. ": " .$_SERVER['REQUEST_URI']."\n", FILE_APPEND);
}
$cleanedUpRequestUri = str_replace("../", "", $_SERVER['REQUEST_URI']);
if ($userPermissions['master'] === true) {
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			if (is_dir($dataFolder . $cleanedUpRequestUri)) {
				$folder_content = array_diff(scandir($dataFolder . $cleanedUpRequestUri), ['.', '..']);
				foreach ($folder_content AS $fileOrFolder) {
					if (substr($fileOrFolder, 0, 2) !== "._" && $fileOrFolder !== ".DS_Store") {
						$linkToFileOrFolder = ($cleanedUpRequestUri !== "/" ? $cleanedUpRequestUri : "") . $fileOrFolder;
						if (is_dir($dataFolder . $cleanedUpRequestUri . "/" . $fileOrFolder)) {
							echo '<a href="' . $linkToFileOrFolder . '/">' . $fileOrFolder . '/</a><br>';
						} else {
							echo '<a href="' . $linkToFileOrFolder . '">' . $fileOrFolder . '</a><br>';
						}
					}
				}
			} else if (is_file($dataFolder . $cleanedUpRequestUri)) {
				echo file_get_contents($dataFolder . $cleanedUpRequestUri);
			} else {
				header("HTTP/1.0 404 Not Found");
			}
			break;
		case 'PUT':
			if (
				substr($cleanedUpRequestUri, 0, strlen($userPermissions['home'])) === $userPermissions['home'] || 
				$userPermissions['globalWrite'] === true
			) {
				$requestBody = file_get_contents('php://input');

				$filePathPieces = explode("/", $cleanedUpRequestUri);
				$fileName = array_pop($filePathPieces);
				$filePath = implode("/", $filePathPieces);
				if (!is_dir($dataFolder . $filePath)) {
					mkdir($dataFolder . $filePath, 0777, true);
				}

				file_put_contents($dataFolder . $cleanedUpRequestUri, $requestBody);
			} else {
				header("HTTP/1.0 400 Bad Request");
			}
			break;
		case 'DELETE':
			if (
				substr($cleanedUpRequestUri, 0, strlen($userPermissions['home'])) === $userPermissions['home'] || 
				$userPermissions['globalWrite'] === true
			) {
				if (is_dir($dataFolder . $cleanedUpRequestUri)) {
					function delTree($dir) {
						$files = array_diff(scandir($dir), ['.', '..']);
						foreach ($files as $file) {
							if (is_dir($dir . "/" . $file)) {
								delTree($dir . "/" . $file);
							} else {
								unlink($dir . "/" . $file);
							}
						}
						return rmdir($dir);
  					}
  					if (delTree($dataFolder . $cleanedUpRequestUri)) {
  						header("HTTP/1.0 200 Ok");
  					} else {
						header("HTTP/1.0 400 Bad Request");
  					}
				} else if (is_file($dataFolder . $cleanedUpRequestUri)) {
					if (unlink($dataFolder . $cleanedUpRequestUri)) {
  						header("HTTP/1.0 200 Ok");
  					} else {
						header("HTTP/1.0 400 Bad Request");
  					}
				} else {
					header("HTTP/1.0 400 Bad Request");
				}
			} else {
				header("HTTP/1.0 400 Bad Request");
			}
			break;
	}
}