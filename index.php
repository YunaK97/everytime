<?php
//phpmyinfo();
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/JWTPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   JWT   ****************** */
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    /* ***************** USER ***************** */
    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']); //모든 사용자 or 사용자 id일부로 조회
    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']); //사용자 idx로 상세조회
    $r->addRoute('POST', '/users', ['IndexController', 'createUser']); // 비밀번호 해싱 예시 추가
    $r->addRoute('DELETE', '/users/{userIdx}', ['IndexController', 'deleteUser']); //사용자 제거
    $r->addRoute('POST', '/users/{userIdx}/boards/{boardIdx}/post', ['IndexController', 'createPost']); // 게시글 생성
    $r->addRoute('DELETE', '/users/{userIdx}/post/{postIdx}', ['IndexController', 'deletePost']); // 게시글 삭제
    $r->addRoute('GET', '/users/{userIdx}/comments', ['IndexController', 'getUserComments']); //사용자 댓글 단 글 조회
    $r->addRoute('GET', '/users/{userIdx}/posts', ['IndexController', 'getUserPosts']); //유저가 쓴 글 조회
    $r->addRoute('POST', '/users/{userIdx}/posts/{postIdx}/like', ['IndexController','likePost']); //공감 버튼 누르기
    $r->addRoute('POST', '/users/{userIdx}/posts/{postIdx}/scrap', ['IndexController','scrapPost']); //스크랩 버튼 누르기
    $r->addRoute('POST', '/users/{userIdx}/boards/{boardIdx}/like', ['IndexController', 'likeBoard']); // 게시판 찜
    $r->addRoute('POST', '/users/{userIdx}/posts/{postIdx}/comment', ['IndexController','createComment']); //유저 댓글 생성
    $r->addRoute('POST', '/users/{userIdx}/comments/{commentIdx}/like', ['IndexController','likeComment']); //유저 댓글 공감
    $r->addRoute('GET', '/users/{userIdx}/schedule', ['IndexController', 'getMySchedule']); //유저가 스케줄 조회
    /* ***************** FRIEND ***************** */
    $r->addRoute('GET', '/users/{userIdx}/friends', ['IndexController', 'getFriends']); //사용자의 친구목록 조회
    $r->addRoute('POST', '/users/{userIdx}/friend', ['IndexController', 'createFriend']); //친구 추가
    $r->addRoute('DELETE', '/users/{userIdx}/friends/{friendIdx}', ['IndexController', 'deleteFriend']); //친구 제거
    /* ***************** SCHEDULE ***************** */
    $r->addRoute('GET', '/friends/{friendIdx}/schedule', ['IndexController', 'getSchedule']); // 시간표 조회
    $r->addRoute('GET', '//schools/{schooIdx}/campus/{campusIdx}/lecture', ['IndexController', 'getLecture']); // 강의
    //    $r->addRoute('GET', '/review', ['IndexController', 'getReview']); //리뷰 조회
    $r->addRoute('GET', '/review/{lectureIdx}', ['IndexController', 'getReviewDetails']); //리뷰 상세 조회
    $r->addRoute('GET', '/books', ['IndexController', 'getBooks']);
    /* ******************   TimeTable   ****************** */
    $r->addRoute('GET', '/scraps/{userIdx}', ['IndexController', 'getScraps']);
    /* ******************   BOARD   ****************** */
    $r->addRoute('GET', '/schools/{schooIdx}/basic-boards', ['IndexController', 'BasicBoard']); //기본게시판 조회
    $r->addRoute('GET', '/schools/{schooIdx}/general-boards', ['IndexController', 'GeneralBoard']); //일반게시판 조회

    $r->addRoute('POST', '/users/{userIdx}/schools/{schooIdx}/boards', ['IndexController', 'createBoard']); //게시판 생성
    $r->addRoute('GET', '/posts/hotposts', ['IndexController', 'getHotPosts']); //핫게시판 조회
    $r->addRoute('GET', '/posts/bestposts', ['IndexController', 'getBestPosts']); //베스트게시판 조회
    $r->addRoute('GET', '/basic-boards/{boardIdx}', ['IndexController', 'getBasicPosts']); //기본게시판 글 조회
    $r->addRoute('GET', '/general-boards/{boardIdx}', ['IndexController', 'getGeneralPosts']); //일반게시판 글 조회
    $r->addRoute('GET', '/posts/{postIdx}', ['IndexController', 'getPostsDetails']); //게시판 글조회
    $r->addRoute('GET', '/posts/{postIdx}/comments', ['IndexController', 'getPostsComments']); //게시판 댓글조회
//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
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
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
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
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;
            /*case 'EventController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EventController.php';
                break;
            case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
?>