<?php

//ini_set("error_reporting", E_ALL);
//ini_set("display_errors", 1);

session_cache_limiter(false);
session_start();

require "../vendor/autoload.php";


/**
 * Load Twig plugin.
 */
$app = new \Slim\Slim(["view" => new \Slim\Views\Twig()]);
$view = $app->view();
$view->setTemplatesDirectory("../app/views");
$view->parserExtensions = [new \Slim\Views\TwigExtension()];

function translate($msg) {
    $translationsJson = file_get_contents("../app/translations/translations.json");

    // Fixes UTF-8 conversion issues.
    $translationsJson =  mb_convert_encoding($translationsJson, 'UTF-8', mb_detect_encoding($translationsJson, 'UTF-8, ISO-8859-1', true));

    $translations = json_decode($translationsJson);
    return !empty($translations->translations->$msg) && !empty($translations->translations->$msg->{$_SESSION['lang']}) ?
        $translations->translations->$msg->{$_SESSION['lang']} :
        $msg;
}


/**
 * Returns translated month name for input month number.
 */
function month($m) {

    if ( empty($m) ) {
        return "Geen datum";
    }
    $month = array (
        'januari',
        'februari',
        'maart',
        'april',
        'mei',
        'juni',
        'juli',
        'augustus',
        'september',
        'oktober',
        'november',
        'december'
    );

    return translate($month[(int)$m - 1]);
}


/**
 * Replace urls in text with web links.
 */
function insertLinks($text) {
    $text = preg_replace(
        "/((H|h)[0-9]+)/",
        "<a href=\"/haltes/$1\" class=\"halte-link\">$1</a>",
        $text);
    $text = preg_replace(
        "/((P|p)[0-9]+)/",
        "<a href=\"/parkeerplaatsen/$1\" class=\"parkeerplaats-link\">$1</a>",
        $text);
    $text = preg_replace(
        "/([^\s]+@([^\s]+\.)+[^\s\.]+)/",
        "<a href=\"mailto:$1\">$1</a>",
        $text);
    return $text;
}

$twig = $app->view()->getEnvironment();
$twig->addFunction('__', new Twig_Function_Function('translate'));
$twig->addFunction('maand', new Twig_Function_Function('month'));
$twig->addFunction('insertlinks', new Twig_Function_Function('insertLinks'));

/**
 * Quick fix for route dump
 */
Class RouteDumper extends \Slim\Router {
    public static function getAllRoutes() {
        $slim = \Slim\Slim::getInstance();
        return $slim->router->routes;
    }
}

require_once "../app/bootstrap.php"; // Bootstrap
require_once "routes.php"; // System routes
require_once "../app/routes/index.php"; // Project routes

/**
 * Run!
 */
$app->run();

