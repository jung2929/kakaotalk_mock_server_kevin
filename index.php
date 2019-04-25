<?php

require './model/pdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//error_reporting(E_ALL); ini_set("display_errors", 1);

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    //Main Server API
    $r->addRoute('GET', '/', 'index');
    $r->addRoute('GET', '/kacao/test', 'test');

    //관리자 API
    $r->addRoute('POST', '/kacao/chat_data', 'chat_data');
    $r->addRoute('POST', '/kacao/emoticon_data', 'emoticon_data');
    $r->addRoute('POST', '/kacao/style_data', 'style_data');
    $r->addRoute('POST', '/kacao/image_data', 'image_data');

    //이메일 인증 API
    $r->addRoute('POST', '/kacao/emailAuthenticate', 'emailAuthenticate');

    //회원가입, 회원탈퇴, 로그인 API
    $r->addRoute('POST', '/kacao/user', 'user');
    $r->addRoute('DELETE', '/kacao/user', 'user_delete');
    $r->addRoute('POST', '/kacao/login', 'login');

    //프로필, 프로필확인, 히스토리 API
    $r->addRoute('POST', '/kacao/profile', 'profile');
    $r->addRoute('GET', '/kacao/profile', 'profile_check');
    $r->addRoute('GET', '/kacao/mystory', 'mystory');

    //친구목록, 친구추가, 친구차단, 차단해제, 차단친구, 친구찾기 API
    $r->addRoute('GET', '/kacao/friend', 'friend');
    $r->addRoute('POST', '/kacao/friend_add', 'friend_add');
    $r->addRoute('DELETE', '/kacao/friend_delete', 'friend_delete');
    $r->addRoute('PATCH', '/kacao/friend_delete', 'friend_delete_cancel');
    $r->addRoute('GET', '/kacao/friend_delete', 'friend_deleted');
    $r->addRoute('GET', '/kacao/friend/{Name}', 'friend_find');

    //즐겨찾기 (추가, 확인, 차단) API
    $r->addRoute('POST', '/kacao/favorites', 'favorites_add');
    $r->addRoute('GET', '/kacao/favorites', 'favorites_check');
    $r->addRoute('DELETE', '/kacao/favorites', 'favorites_delete');

    //이모티콘(전체, 검색, 신규, 인기, 스타일, 상세조회) API
    $r->addRoute('GET', '/kacao/emoticon', 'emoticon');
    $r->addRoute('GET', '/kacao/emoticon_find', 'emoticon_find');
    $r->addRoute('GET', '/kacao/emoticon_new', 'emoticon_new');
    $r->addRoute('GET', '/kacao/emoticon_pop', 'emoticon_pop');
    $r->addRoute('GET', '/kacao/emoticon_style', 'emoticon_style');

    //이모티콘 확인, 다운로드 API
    $r->addRoute('GET', '/kacao/emoticon_download', 'emoticon_download');
    $r->addRoute('GET', '/kacao/emoticon/{Eno}', 'emoticon_more');
    $r->addRoute('GET', '/kacao/emoticon_check', 'emoticon_check');

    //이모티콘 채팅 API
    $r->addRoute('POST', '/kacao/chat', 'chat');
    $r->addRoute('GET', '/kacao/chat/{Name}', 'chat_find');
    $r->addRoute('DELETE', '/kacao/chat/{Name}', 'chat_delete');

    //이모티콘 채팅 API
    $r->addRoute('POST', '/kacao/upload', 'upload');

//  $r->addRoute('GET', '/logs/error', 'ERROR_LOGS');
//  $r->addRoute('GET', '/logs/access', 'ACCESS_LOGS');


//  $r->addRoute('GET', '/users', 'get_all_users_handler');
//  // {id} must be a number (\d+)
//  $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//  // The /{title} suffix is optional
//  $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs =  new Logger('BIGS_ACCESS');
$errorLogs =  new Logger('BIGS_ERROR');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1]; $vars = $routeInfo[2];
        require './controller/mainController.php';

        break;
}




