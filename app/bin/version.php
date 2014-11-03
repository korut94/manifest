#!/usr/bin/env php
<?php
#
# PHP-script to manage manifest's version number
#

// required autoloader
if (file_exists($autoload = __DIR__.'/../vendor/autoload.php')) {
    require_once $autoload;
} else {
    die("> ERROR !! - composer autoloader '$autoload' not found!".PHP_EOL."You need to install Composer's dependencies!");
}

// required settings
if (file_exists($opts = __DIR__.'/../src/settings.php')) {
    require_once $opts;
} else {
    die("> ERROR !! - settings '$opts' not found!");
}

// usage string & exit
function usage($status = 0) {
    $args                   = settings('argv');
    echo <<<EOT
usage:      {$args[0]}  [-h | -q]  [<action> = read]  [<version>]

options:
    -q         : "quiet" mode
    <action>   : "read" (default) or "update"
    <version>  : new version number for the "update" action


EOT;
    exit($status);
}

// usage info if so
if (count($argv)>1 && in_array($argv[1], array('help', '-h', '--help')) && function_exists('usage')) {
    usage(); exit();
}

// debug info if so
if (count($argv)>1 && in_array($argv[1], array('-x', '--debug'))) {
    debug(settings());
    exit();
}

// quiet mode if so
if (count($argv)>1 && in_array($argv[1], array('-q', '--quiet'))) {
    settings('quiet', true);
}

// let's go
extract(settings());
$ctt = file_get_contents($mde_manifest);
if (preg_match('/^\* version: ([[:alnum:]|\.|-]+)$/im', $ctt, $result) && count($result)>1) {
    $old_version = $result[1];
} else {
    error("can't guess version number from file '$mde_manifest'");
}
if (isset($argv[1]) && $argv[1]=='update') {
//    $old_version = str_replace('@dev', '', $old_version);
    if (isset($argv[2])) {
        $new_version = $argv[2];
    } else {
        list($x,$y,$z) = explode('.', $old_version);
        $new_version = $x.'.'.$y.'.'.($z + 1);
    }
    info("new version is: ".$new_version);

    foreach ($version_files as $fname) {
        $f = $$fname;
        if (!empty($f)) {
            if ($ok = file_put_contents(
                $f, str_replace($old_version, $new_version, file_get_contents($f))
            )) {
                echo $f, PHP_EOL;
            } else {
                error("An error occurred while trying to write in file '$f'!");
            }
        }
    }
} else {
    echo (isset($quiet) && $quiet ? '' : 'MDE manifest '), $old_version, PHP_EOL;
    exit(0);
}
