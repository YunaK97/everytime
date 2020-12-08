<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
               break;
        /*
               * API No. 1
               * API Name : 사용자생성 API
               * 마지막 수정 날짜 : 19.04.29
               */
        case "getUsers":
            http_response_code(200);
            $keyword=$_GET['keyword'];
            $res->result = getUsers($keyword);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "사용자 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                * AP$userIdxI No. 2
                * API Name : 테스트 Path Variable API
                * 마지막 수정 날짜 : 19.04.29
                */
        case "getUserDetail":
            http_response_code(200);
            //토큰의 유저인덱스==경로변수의 유저인덱스
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            $userIdx=$vars['userIdx'];
            //echo($userIdxInToken);
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = getUserDetail($vars["userIdx"]);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "해당 유저 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                * API No. 3
                * API Name : 테스트 Body & Insert API
                * 마지막 수정 날짜 : 19.04.29
                */
        case "createUser":
            http_response_code(200);
            // Packet의 Body에서 데이터를 파싱합니다.
          $userID = $req->userID;
          $pwd=$req->pwd;
          $pwd_hash = password_hash($pwd, PASSWORD_DEFAULT); // Password Hash
          $name = $req->name;
          $nick=$req->nick;
          $schoolIdx=$req->schoolIdx;
          $studentID=$req->studentID;
          if(empty($userID)||empty($pwd)||empty($name)||empty($nick)
              ||empty($schoolIdx)||empty($studentID)){
              $res->is_success = FALSE;
              $res->code = 200;
              $res->message = "필요 정보 모두 입력하지 않았습니다";
              echo json_encode($res, JSON_NUMERIC_CHECK);
              break;
          }
          $pattern = '/^.*(?=^.{8,15}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!@#$%^&+=]).*$/'; //영문자, + 숫자 + 특수문자 조합 8자리이상 15자리 이하 정규식
          if(!preg_match($pattern ,$pwd)){
              $res->is_success = FALSE;
              $res->code = 201;
              $res->message = "패스워드 형식은 영문자, + 숫자 + 특수문자 조합 8자리이상 15자리 이하 ";
              echo json_encode($res, JSON_NUMERIC_CHECK);
              break;

          }
          if (!preg_match("/^[a-zA-Z가-힣 ]*$/", $name)) {
              $res->is_success = FALSE;
              $res->code = 202;
              $res->message = "영문자와 한글만 가능합니다!";
              echo json_encode($res, JSON_NUMERIC_CHECK);
              break;
            }
         if (!preg_match("/^[a-z0-9]{4,10}$/", $userID)) {//영어소문자 숫자로만 4-10자
             $res->is_success = FALSE;
             $res->code = 203;
             $res->message = "아이디 형식은 영어소문자 숫자로만 4-10자";
             echo json_encode($res, JSON_NUMERIC_CHECK);
             break;
         }
            if(isVaildID($userID)){
              $res->is_success = FALSE;
              $res->code = 101;
              $res->message = "ID 중복";
              echo json_encode($res, JSON_NUMERIC_CHECK);
              break;
          }
          if(isVaildNick($nick)){
              $res->is_success = FALSE;
              $res->code = 102;
              $res->message = "닉네임 중복";
              echo json_encode($res, JSON_NUMERIC_CHECK);
              break;
          }
          createUser($userID,$pwd_hash, $name,$nick,$schoolIdx,$studentID);
          $res->is_success = TRUE;
          $res->code = 100;
          $res->message ="사용자 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
              * API No. 4
              * API Name : 사용자 제거
        */
        case "deleteUser":
            http_response_code(200);
            $userIdx=$vars['userIdx'];
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 400;
                $res->message = "이미 삭제된 사용자";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteUser($vars['userIdx']);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="사용자 제거 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
              * API No. 5
             * API Name : 친구 목록 조회
           */
        case "getFriends":
            http_response_code(200);
            $userIdx=$vars['userIdx'];
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!checkFriend($userIdx)){
                $res->is_success = FALSE;
                $res->code = 400;
                $res->message = "친구 없음";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = getFriends($vars['userIdx']);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "친구 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                * API No. 6
               * API Name : 친구 생성
                           */
        case "createFriend":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx =$vars['userIdx'];
            $friendID = $req->friendID;
            if(empty($friendID)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "친구 아이디 입력을 안했습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isVaildID($friendID)) {
                $res->is_success = FALSE;
                $res->code = 101;
                $res->message = "올바르지 않은 상대입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
          $friendIdx=getFriendID($friendID);
            if(isValidFriend($userIdx,$friendIdx)){
                $res->is_success = FALSE;
                $res->code = 102;
                $res->message = "이미 친구인 상대입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
           createFriend($userIdx,$friendIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="친구 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                    * API No. 7
                    * API Name : 친구 시간표 조회
            */
        case "getSchedule":
            http_response_code(200);
            $friendIdx=$vars['friendIdx'];
            if(!isValidUserIdx($friendIdx)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
                $res->result = getSchedule($friendIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "친구 시간표 조회 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
        /*
                   * API No. 8
                   * API Name : 친구 삭제
           */
        case "deleteFriend":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $friendIdx=$vars['friendIdx'];
            if(!isValidFriend($userIdx,$friendIdx)){
                $res->isSuccess = FALSE;
                $res->code = 102;
                $res->message = "친구가 원래 아님";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
           deleteFriend($userIdx,$friendIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message ="친구 제거 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                 * API No. 9
                 * API Name : 리뷰 검색
         */
        case "getReview":
            http_response_code(200);
           $name=$_GET['name'];
            if(!isValidReview($name)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "검색된 강의가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
        $res->result=getLectureId($name);
      //      $res->result = getReview($idx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                   * API No. 10
                   * API Name : 리뷰 상세 조회
           */
        case "getReviewDetails":
            http_response_code(200);
           // $lectureIdx=$vars['lectureIdx'];
          //  $res->result = getReviewDetails($lectureIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                           * API No. 14
                           * API Name : 기본게시판 조회
                   */
        case "getReviewDetails":
            http_response_code(200);
            $schoolIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->schoolIdx;
            if($schoolIdxInToken!=$vars['schooIdx']){
                $res->is_success = FALSE;
                $res->code = 400;
                $res->message = "권한이 없는 학교 id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $schoolIdx=$vars['schoolIdx'];
            //  $res->result = getReviewDetails($lectureIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
               * API No. 15-2
               * API Name : 일반게시판 조회
        */
        case "createBoard":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $schoolIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->schoolIdx;
            if($schoolIdxInToken!=$vars['schooIdx']){
                $res->is_success = FALSE;
                $res->code = 400;
                $res->message = "권한이 없는 학교 id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $schooIdx=$vars['schooIdx'];
            $boardname= $req->boardname;
            $explain=$req->explain;
            $form=$req->form;
            $anonymous=$req->anonymous;
            if(empty($boardname)||empty($explain)||empty($form)||empty($anonymous)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "필요 정보 모두 입력하지 않았습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkBoard($boardname )) {
                $res->is_success = FALSE;
                $res->code = 101;
                $res->message = "이미 존재하는 게시판";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            createBoard($boardname,$schooIdx);
            $BoardIdx=getBoardID($boardname);
            createGeneralboard($explain,$userIdx,$BoardIdx,$form,$anonymous);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="게시판 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                  * API No. 16
                  * API Name : 게시판 찜누르기
    *
           */
        case "likeBoard":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $boardIdx=$vars['boardIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 user_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidBoard($boardIdx)){
                $res->is_success = FALSE;
                $res->code = 400;
                $res->message = "유효하지않은 boardidx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(checkFavorites($userIdx,$boardIdx)){
                deletelikeBoard($userIdx,$boardIdx);
                    $res->is_success = TRUE;
                    $res->code = 101;
                    $res->message ="게시판 찜 취소";
            }
            else{
                likeBoard($userIdx,$boardIdx);
                $res->is_success = TRUE;
                $res->code = 100;
                $res->message ="게시판 찜 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
           * API No. 17
           * API Name : 기본게시판 글 조회
         */
        case "getBasicPosts":
            http_response_code(200);
            $boardIdx=$vars['boardIdx'];
            $tag=$_GET['tag'];
            if(!isValidBoardIdx($boardIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 boardidx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=getBasicPosts($boardIdx,$tag);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "기본게시판 글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
       * API No. 18
       * API Name : 일반게시판 글 조회
     */
        case "getGeneralPosts":
            http_response_code(200);
            $boardIdx=$vars['boardIdx'];
            if(isValidBoardIdx($boardIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 일반게시판idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=getGeneralPosts($boardIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "일반게시판 글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
          * API No. 19
          * API Name : 유저가 쓴 글 조회
        */
        case "getUserPosts":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!countPosts($userIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "쓴 글이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=getUserPosts($userIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "내가 쓴 글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                        * API No. 20
                        * API Name : BEST 게시판
                 */
        case "getBestPosts":
            http_response_code(200);
            $res->result=getBestPosts();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "BEST 게시판 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                * API No. 21
                * API Name : HOT 게시판
         */
        case "getHotPosts":
            http_response_code(200);
            $res->result=getHotPosts();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "hot 게시판 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                * API No. 22
                * API Name : 댓글 단 글 조회
         */
        case "getUserComments":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!countComments($userIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "댓글 단 글이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=getUserComments($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "내가 댓글 단 글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
                 * API No. 23
                 * API Name : 게시글 생성
          */
        case "createPost":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $boardIdx=$vars['boardIdx'];
            $content= $req->content;
            $photo=$req->photo;
            $anonymous=$req->anonymous;
            $tag=$req->tag;
            $title=$req->title;
            if(strlen($content)>=5000){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "글의 내용이 5000자가 넘습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($title)>=45){
                $res->is_success = FALSE;
                $res->code = 203;
                $res->message = "제목이 45자가 넘습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($tag)>=45){
                $res->is_success = FALSE;
                $res->code = 204;
                $res->message = "태그 45자가 넘습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(empty($content)||empty($anonymous)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "필요 정보 모두 입력하지 않았습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isValidBoardIdx($boardIdx)){
                if(empty($tag)||empty($title)){
                    $res->is_success = FALSE;
                    $res->code = 201;
                    $res->message = "기본게시판에 필요한 정보 모두 입력하지 않았습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                createPost($userIdx,$boardIdx,$content,$photo,$anonymous);
                $postIdx=getPostidx($content);
                createBPost($tag,$title,$postIdx);
            }else{
                createPost($userIdx,$boardIdx,$content,$photo,$anonymous);
            }
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="게시글 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                       * API No. 24
                       * API Name : 게시글 공감누르기
         *
                */
        case "likePost":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $postIdx=$vars['postIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 user_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidPostIdx($postIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "유효하지않은 post_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkMyPost($userIdx,$postIdx)){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "내가 쓴 글은 공감할 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkLike($userIdx,$postIdx)){
                $res->is_success = FALSE;
                $res->code = 203;
                $res->message = "이미 공감한 글입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            likePost($userIdx,$postIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="공감 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                 * API No. 25
                 * API Name : 게시글 스크랩누르기

                       */
        case "scrapPost":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $postIdx=$vars['postIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 user_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidPostIdx($postIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "유효하지않은 post_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkMyPost($userIdx,$postIdx)){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "내가 쓴 글은 스크랩할 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkScrap($userIdx,$postIdx)){
                if(checkIsdeleted($userIdx,$postIdx)){
                    rescrapPost($userIdx,$postIdx);
                    $res->is_success = TRUE;
                    $res->code = 102;
                    $res->message ="스크랩 재성공";
                }
                else{
                    deletescrapPost($userIdx,$postIdx);
                    $res->is_success = TRUE;
                    $res->code = 101;
                    $res->message ="스크랩 취소";
                }

            }
            else{
                scrapPost($userIdx,$postIdx);
                $res->is_success = TRUE;
                $res->code = 100;
                $res->message ="스크랩 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                       * API No. 26
                      * API Name : 유저 댓글 생성

            */
        case "createComment":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $postIdx=$vars['postIdx'];
            $content=$req->content;
            $anonymous=$req->anonymous;
            $parentIdx=$req->parentIdx;
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 user_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidPostIdx($postIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "유효하지않은 post_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($content)>=1000){
                $res->is_success = FALSE;
                $res->code = 204;
                $res->message = "댓글이 1000자가 넘습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
//            if(empty($content)||empty($anonymous)||empty($parentIdx)){
//                $res->is_success = FALSE;
//                $res->code = 203;
//                $res->message = "필요 정보 모두 입력하지 않았습니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
            createComment($content,$anonymous,$parentIdx,$userIdx,$postIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="댓글 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                 * API No. 27
               * API Name : 댓글 공감 누르기

                   */
        case "likeComment":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $commentIdx=$vars['commentIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 user_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidCommentIdx($commentIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "유효하지않은 comment_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(checkCommentMine($commentIdx,$userIdx)){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "내가 쓴 댓글에는 공감할 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            likeComment($userIdx,$commentIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="댓글 공감 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                         * API No. 28
                         * API Name : 게시글삭제

                               */
        case "deletePost":
            http_response_code(200);
            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $postIdx=$vars['postIdx'];
            if(!isValidUserIdx($userIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 user_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidPostIdx($postIdx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "유효하지않은 post_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!checkMyPost($userIdx,$postIdx)){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "내가 쓰지않은 글은 삭제할 수 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deletePost($userIdx,$postIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                       * API No. 28
                       * API Name : 게시글상세조회

                             */
        case "getPostsDetails":
            http_response_code(200);

            $postIdx=$vars['postIdx'];
            if(!isValidPostIdx($postIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 post_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $boardIdx=getBoardIdx($postIdx);
            if(isValidBBoardIdx($boardIdx)){ //기본게시판일 경우
                $res->result= getBPostsDetails($postIdx);
            }else{ //일반게시판일 경우
                $res->result= getPostsDetails($postIdx);
            }

            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="게시글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                  * API No. 29
                  * API Name : 게시글댓글조회

                                 */
        case "getPostsComments":
            http_response_code(200);
            $postIdx=$vars['postIdx'];
            if(!isValidPostIdx($postIdx)){
                $res->is_success = FALSE;
                $res->code = 200;
                $res->message = "유효하지않은 post_idx입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result= getPostsComments($postIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="게시글 댓글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                  * API No. 30
                  * API Name : 유저스케줄조회

          */
        case "getMySchedule":
            http_response_code(200);

            $jwt=$_SERVER['HTTP_X_ACCESS_TOKEN'];
            $userIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            if($userIdxInToken!=$vars['userIdx']){
                $res->is_success = FALSE;
                $res->code = 300;
                $res->message = "권한이 없는 유저입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=$vars['userIdx'];
            $res->result= getMySchedule($userIdx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="스케줄 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
                      * API No. 31
                      * API Name : 강의 검색

              */
        case "getLecture":
            http_response_code(200);
            $schoolIdxInToken=getDataByJWToken($jwt,JWT_SECRET_KEY)->schoolIdx;
            if($schoolIdxInToken!=$vars['schooIdx']){
                $res->is_success = FALSE;
                $res->code = 400;
                $res->message = "권한이 없는 학교 id입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $schoolIdx=$vars['schoolIdx'];
            $campusIdx=$vars['campusIdx'];
            $keyword=$_GET['keyword'];
            $res->result= getLecture($schoolIdx,$campusIdx,$keyword);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message ="스케줄 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        //api-server 3번
        case "getBooks":
            http_response_code(200);
            $keyword=$_GET['keyword'];
            if(!isValidkeyword($keyword)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "검색된 책이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = getBooks($keyword);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "책 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

      //api-server 4번
        case "getScraps":
            http_response_code(200);
            $userIdx=$vars["userIdx"];
//            if(!isValidUserIdx($userIdx)){
//                $res->isSuccess = FALSE;
//                $res->code = 200;
//                $res->message = "유효하지않는 ID입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
            $res->result = getScraps($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "스크랩 글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
