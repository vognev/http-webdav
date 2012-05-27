<?php
/**
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Runner/Version.php';

$phpunitVersion = PHPUnit_Runner_Version::id();
if ($phpunitVersion == '@package_version@' || version_compare($phpunitVersion, '3.5.5', '>=')) {
    require_once 'PHPUnit/Autoload.php'; // >= PHPUnit 3.5.5
} else {
    require_once 'PHPUnit/Framework.php'; // < PHPUnit 3.5.5
}

if (file_exists('options.php')) {
    require_once 'options.php';
} else {
    require_once 'options.php.dist';
}

unset($phpunitVersion);