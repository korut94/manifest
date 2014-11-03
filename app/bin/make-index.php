#!/usr/bin/env php
<?php
#
# PHP-script to re-generate the MDE quick template
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
    $html5_quick_template   = settings('html5_quick_template');
    $mde_console            = settings('mde_console');
    $mde_manifest           = settings('mde_manifest');
    echo <<<EOT

usage:  php  {$args[0]}  <manifest.md>  <html5-quick-template-path>  <mde-console-path>

defaults from DOCUMENT_ROOT:
    <manifest.md>                   : {$mde_manifest}
    <html5-quick-template-path>     : {$html5_quick_template}
    <mde-console-path>              : {$mde_console}


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

// verbose mode if so
if (count($argv)>1 && in_array($argv[1], array('-v', '--verbose'))) {
    settings('verbose', true);
}

// generate HTML template
ob_start();
require $html5_quick_template;
$_tpl = ob_get_contents();
ob_end_clean();

// write it in $target_file
extract(settings());
if ($ok = file_put_contents($template_file, $_tpl, LOCK_EX)) {
    info("template updated in file '$template_file' with string of length ".strlen($_tpl));
    $cmd = settings('php_bin').' '.$mde_console
        .(isset($verbose) && $verbose ? ' -v' : '')
        .' -t='.$template_file
        .' -o='.$target_file
        .' -c='.$mde_config
        .' -f="'.$mde_output_format.'" '
        .$mde_manifest;
    if ($ok = exec($cmd, $output, $status) || $status == 0) {
        if (isset($verbose) && $verbose) {
            echo join(PHP_EOL, $output);
        }
        info("index updated in file '$target_file' parsing '$mde_manifest'");
    } else {
        if (isset($verbose) && $verbose) {
            echo join(PHP_EOL, $output);
        }
        error("an error occurred while trying to write in file '$target_file'!");
    }
} else {
    error("an error occurred while trying to write in file '$template_file'!");
}
