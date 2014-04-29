<?php

ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL | E_STRICT);

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', dirname(dirname(__FILE__)));
define('VIEW_DIR', BASE_DIR . DS . 'view');
define('APP_DIR', BASE_DIR . DS . 'app');
define('LIB_DIR', BASE_DIR . DS . 'lib');
define('VAR_DIR', BASE_DIR . DS . 'var');

if (isset($argv[1]) && $argv[1] == 'reindex') {

    require_once APP_DIR . DS . 'Search.php';
    $search = new Search();
    $search->index(BASE_DIR . DS . 'wiki');

} elseif (isset($_GET['q'])) {

    require_once APP_DIR . DS . 'Search.php';
    $search = new Search();
    $hits = $search->query($_GET['q']);
    header('Content-type: text/html; charset=utf8');
    $html = '<ul>';
    if (is_array($hits)) {
        foreach ($hits as $hit) {
            $html .= sprintf(
                '<li><a href="%s">%s</a><span>%s</span></li>',
                $hit['path'],
                $hit['name'],
                $hit['excerpt']
            );
        }
    }
    $html .= '</ul>';
    echo $html;

} else {

    require_once APP_DIR . DS . 'Wiki.php';
    $wiki = new Wiki(BASE_DIR . DS . 'wiki');
    $path = isset($_SERVER['REQUEST_URI'])
        ? preg_replace('#^/index.php(/|$)#', '/', $_SERVER['REQUEST_URI'])
        : '/';
    echo $wiki->render($path);

}

