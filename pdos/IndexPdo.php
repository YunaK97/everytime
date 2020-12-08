<?php

//READ
function getUsers($keyword)
{
    $pdo = pdoSqlConnect();
    if(!empty($keyword)){
        $query = "select id,password,profile,nick,student_id,school_idx, name from USER where id like concat('%',?,'%');";
        $st = $pdo->prepare($query);
        $st->execute([$keyword]);
    }
    else {
        $query = "select id,password,profile,nick,student_id,school_idx,name from USER;";
        $st = $pdo->prepare($query);
        $st->execute();
    }
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
/*
function getLecture($schoolIdx,$campusIdx,$keyword)
{
    $pdo = pdoSqlConnect();
    if(empty($keyword)){
        $query = "select id,password,profile,nick,student_id,school_idx, name from USER where id like concat('%',?,'%');";
        $st = $pdo->prepare($query);
        $st->execute([$keyword]);
    }else{
        $query = "select id,password,profile,nick,student_id,school_idx, name from USER where id like concat('%',?,'%');";
        $st = $pdo->prepare($query);
        $st->execute([$keyword]);
    }

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}*/
//READ
function getReviewDetails($lectureIdx)
{
    $pdo = pdoSqlConnect();
        $query = "SELECT content,semester,score,likereview_num FROM `REVIEW`
    INNER JOIN (SELECT review_idx,COUNT(*) AS likereview_num FROM LIKEREVIEW GROUP BY review_idx) AS  TEMP ON TEMP.review_idx=REVIEW.idx
WHERE lecture_idx =?
order by semester desc;";
        $st = $pdo->prepare($query);
        $st->execute($lectureIdx);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
//READ
function getPostsDetails($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,profile,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time,content,photo_id,like_num,comment_num,scrap_num
FROM POST
    LEFT JOIN USER ON USER.idx=POST.user_idx
    LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
     LEFT JOIN(SELECT post_idx,photo_id FROM `PHOTO`) AS TEMP2 ON TEMP2.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS scrap_num FROM `SCRAP` GROUP BY post_idx) AS TEMP3 ON TEMP3.post_idx=POST.idx
where POST.idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0];
}
function getBPostsDetails($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,profile,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time,title,content,photo_id,like_num,comment_num,scrap_num
FROM POST
    LEFT JOIN USER ON USER.idx=POST.user_idx
    LEFT JOIN DETAILED ON DETAILED.post_idx=POST.idx
    LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
     LEFT JOIN(SELECT post_idx,photo_id FROM `PHOTO`) AS TEMP2 ON TEMP2.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS scrap_num FROM `SCRAP` GROUP BY post_idx) AS TEMP3 ON TEMP3.post_idx=POST.idx
where POST.idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0];
}
function getPostsComments($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT `COMMENT`.idx as idx,profile,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,COMMENT.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,COMMENT.created_at,now())+'분 전')
                    ELSE date_format(COMMENT.created_at,'%m/%d %h:%i')
        END AS time,content,like_num,parent_idx,user_idx
FROM COMMENT
    LEFT JOIN USER ON USER.idx=COMMENT.user_idx
    LEFT JOIN (SELECT comment_idx,COUNT(*) AS like_num FROM `LIKECOMMENT` GROUP BY comment_idx) AS TEMP ON TEMP.comment_idx=COMMENT.idx
where post_idx=?
order by time desc;";
    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
//READ
function getBestPosts()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,profile,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time,content,like_num,comment_num,boardname,photo_num
FROM POST
    LEFT JOIN BOARD ON POST.board_idx=BOARD.idx
    LEFT JOIN USER ON USER.idx=POST.user_idx
    LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
     LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
where like_num>=5
 order by POST.created_at DESC;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
//READ
function getHotPosts()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,profile,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time,content,like_num,comment_num,boardname,photo_num
FROM POST
    LEFT JOIN BOARD ON POST.board_idx=BOARD.idx
    LEFT JOIN USER ON USER.idx=POST.user_idx
    LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
     LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
where like_num>=4
 order by POST.created_at DESC;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
//READ
function getBooks($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT bookname,author,publisher,photo,price,used_price,status,`BOOKSTORE`.created_at,CONCAT(schoolname,' 재학생') AS schoolname,BOOKSTORE.idx
    FROM BOOKSTORE
    INNER JOIN USER ON USER.idx=BOOKSTORE.user_idx
    INNER JOIN SCHOOL ON USER.school_idx=SCHOOL.idx
    WHERE bookname LIKE concat('%',?,'%');";
    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
//READ
function getScraps($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT nick,content,POST.created_at AS created_at,like_num,comment_num,boardname,anonymous
FROM POST
    INNER JOIN BOARD ON POST.board_idx=BOARD.idx
    INNER JOIN USER ON USER.idx=POST.user_idx
     INNER JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    INNER JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
WHERE POST.idx IN (SELECT post_idx FROM SCRAP WHERE SCRAP.user_idx=? ) order by created_at DESC;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function getUserComments($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,content,like_num,comment_num,boardname,photo_num,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time
FROM POST
    INNER JOIN BOARD ON POST.board_idx=BOARD.idx
    INNER JOIN USER ON USER.idx=POST.user_idx
     INNER JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    INNER JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
     LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
WHERE POST.idx IN (SELECT post_idx FROM COMMENT WHERE COMMENT.user_idx=?) order by POST.created_at DESC;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function getUserPosts($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,profile,content,like_num,comment_num,boardname,photo_num,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time
FROM POST
    LEFT JOIN BOARD ON POST.board_idx=BOARD.idx
    LEFT JOIN USER ON USER.idx=POST.user_idx
     LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
     LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
WHERE POST.user_idx =? order by POST.created_at DESC;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
//READ
function getUserDetail($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select id,password,profile,nick,student_id,school_idx, name from USER where idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}
//READ
function getBasicPosts($boardIdx,$tag)
{    $pdo = pdoSqlConnect();
    if(!empty($tag)){
        $query = "SELECT POST.idx,title,content,like_num,comment_num,photo_num,tag,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time
FROM POST
    LEFT JOIN USER ON USER.idx=POST.user_idx
     LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
     JOIN(SELECT post_idx,title,tag FROM DETAILED WHERE tag=?) AS TEMP3 ON TEMP3.post_idx=POST.idx
WHERE POST.board_idx =? order by POST.created_at DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$tag,$boardIdx]);
    }
    else {
        $query = "SELECT POST.idx,title,content,like_num,comment_num,photo_num,tag,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time
FROM POST
    LEFT JOIN USER ON USER.idx=POST.user_idx
     LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,title,tag FROM DETAILED) AS TEMP3 ON TEMP3.post_idx=POST.idx
WHERE POST.board_idx =? order by POST.created_at DESC;";
        $st = $pdo->prepare($query);
        $st->execute([$boardIdx]);
    }
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
//READ
function getGeneralPosts($boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT POST.idx,content,like_num,comment_num,photo_num,(CASE
    WHEN anonymous='N' then nick
    WHEN anonymous='Y' then '익명'
    end) nick,CASE WHEN TIMESTAMPDIFF(HOUR,POST.created_at,now())<1
                 THEN CONCAT(TIMESTAMPDIFF(MINUTE,POST.created_at,now())+'분 전')
                    WHEN DATEDIFF(now(),POST.created_at)<1
                    THEN date_format(POST.created_at,'%h:%i')
                    ELSE date_format(POST.created_at,'%m/%d')
        END AS time
FROM POST
    LEFT JOIN USER ON USER.idx=POST.user_idx
     LEFT JOIN (SELECT post_idx,COUNT(*) AS like_num FROM `LIKE` GROUP BY post_idx) AS TEMP ON TEMP.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS comment_num FROM `COMMENT` GROUP BY post_idx) AS TEMP1 ON TEMP1.post_idx=POST.idx
    LEFT JOIN(SELECT post_idx,COUNT(*) AS photo_num FROM `PHOTO` GROUP BY post_idx) AS TEMP2 ON TEMP2.post_idx=POST.idx
WHERE POST.board_idx =? order by POST.created_at DESC;";
    $st = $pdo->prepare($query);
    $st->execute([$boardIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}
//READ
function getFriendID($friendID)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from USER WHERE id=?;";
    $st = $pdo->prepare($query);
    $st->execute([$friendID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0][idx];
}
function getPostidx($content)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from POST WHERE content=?;";
    $st = $pdo->prepare($query);
    $st->execute([$content]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0][idx];
}
//READ
function getBoardID($boardname)
{
    $pdo = pdoSqlConnect();
    $query = "select idx from BOARD WHERE boardname=?;";
    $st = $pdo->prepare($query);
    $st->execute([$boardname]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0]['idx'];
}
//READ
function getBoardIdx($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select board_idx from POST WHERE idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res[0]['board_idx'];
}
//READ
function getLectureId($name)
{
    $pdo = pdoSqlConnect();
    $query = "select idx,class_id,professor,lecturename from LECTURE WHERE lecturename like concat('%',?,'%');";
    $st = $pdo->prepare($query);
    $st->execute([$name]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}
function createFriend($userIdx,$friendIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO FRIEND (user_idx,friend_idx) VALUES ($userIdx,$friendIdx),($friendIdx,$userIdx);";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$friendIdx]);
    $st = null;
    $pdo = null;
}
function createBoard($boardname,$schooIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO BOARD (boardname,school_idx) VALUES (?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$boardname,$schooIdx]);
    $st = null;
    $pdo = null;
}
function createGeneralboard($explain,$userIdx,$BoardIdx,$form,$anonymous)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO GENERALBOARD (`GENERALBOARD`.explains,user_idx,board_idx,form,anonymous) VALUES (?,?,?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$explain,$userIdx,$BoardIdx,$form,$anonymous]);
    $st = null;
    $pdo = null;
}
function createPost($userIdx,$boardIdx,$content,$photo,$anonymous)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO POST (photo_idx,content,user_idx,board_idx,anonymous) VALUES (?,?,?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$photo,$content,$userIdx,$boardIdx,$anonymous]);
    $st = null;
    $pdo = null;
}
function createComment($content,$anonymous,$parentIdx,$userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO COMMENT (user_idx,post_idx,content,parent_idx,anonymous) VALUES (?,?,?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx,$content,$parentIdx,$anonymous]);
    $st = null;
    $pdo = null;
}
function createBPost($tag,$title,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO DETAILED (title,post_idx,tag) VALUES (?,?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$title,$postIdx,$tag]);
    $st = null;
    $pdo = null;
}
function likePost($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `LIKE` (user_idx,post_idx,is_deleted) VALUES (?,?,'N');";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    $st = null;
    $pdo = null;
}
function scrapPost($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `LIKE` (user_idx,post_idx,is_deleted) VALUES (?,?,'N');";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    $st = null;
    $pdo = null;
}
function likeBoard($userIdx,$boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `FAVORITES` (user_idx,board_idx) VALUES (?,?);";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$boardIdx]);
    $st = null;
    $pdo = null;
}
function likeComment($userIdx,$commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO `LIKECOMMENT` (user_idx,comment_idx,is_deleted) VALUES (?,?,'N');";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$commentIdx]);
    $st = null;
    $pdo = null;
}
function rescrapPost($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `SCRAP` SET is_deleted='N' where user_idx=? and post_idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    $st = null;
    $pdo = null;
}

function deletescrapPost($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `SCRAP` SET is_deleted='Y' where user_idx=? and post_idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    $st = null;
    $pdo = null;
}

function deletelikeBoard($userIdx,$boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM `FAVORITES` where user_idx=? and board_idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$boardIdx]);
    $st = null;
    $pdo = null;
}
function deletePost($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE `POST` SET is_deleted='Y' where user_idx=? and idx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    $st = null;
    $pdo = null;
}
function createUser($ID, $pwd, $name,$nick,$schoolIdx,$studentID)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO USER (id,password,nick,name,school_idx,student_id) VALUES (?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$ID,$pwd,$nick,$name,$schoolIdx,$studentID]);

    $st = null;
    $pdo = null;
}
function deleteUser($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE USER SET is_deleted='Y' WHERE idx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st = null;
    $pdo = null;
}
function deleteFriend($userIdx,$Idx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE FRIEND SET status='N' WHERE user_idx=? and friend_idx=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$Idx]);
    $st->execute([$Idx,$userIdx]);
    $st = null;
    $pdo = null;
}
//READ
function getFriends($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT idx,name
                FROM USER
                INNER JOIN FRIEND ON FRIEND.friend_idx=USER.idx
                WHERE user_idx=? AND status='Y';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}
//READ
function getSchedule($Idx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT `SCHEDULE`.semester AS semester,lecturename,`TIMETABLE`.time,class_id
FROM SCHEDULE
    left join LECTURE ON LECTURE.idx=SCHEDULE.lecture_idx
    LEFT JOIN SETTIME ON SETTIME.lecture_idx=`LECTURE`.idx
    LEFT JOIN TIMETABLE ON SETTIME.time_idx=TIMETABLE.time_idx
WHERE SCHEDULE.user_idx=? AND num=1;";
    $st = $pdo->prepare($query);
    $st->execute([$Idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}
//READ
/*
function getLecture($schoolIdx,$campusIdx,$keyword,$major)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT `SCHEDULE`.semester AS semester,lecturename,`TIMETABLE`.time,class_id
FROM SCHEDULE
    left join LECTURE ON LECTURE.idx=SCHEDULE.lecture_idx
    LEFT JOIN SETTIME ON SETTIME.lecture_idx=`LECTURE`.idx
    LEFT JOIN TIMETABLE ON SETTIME.time_idx=TIMETABLE.time_idx
WHERE SCHEDULE.user_idx=? AND num=1;";
    $st = $pdo->prepare($query);
    $st->execute([$Idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}*/
//READ
function getMySchedule($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT `SCHEDULE`.semester AS semester,lecturename,`TIMETABLE`.time,class_id
FROM SCHEDULE
    left join LECTURE ON LECTURE.idx=SCHEDULE.lecture_idx
    LEFT JOIN SETTIME ON SETTIME.lecture_idx=`LECTURE`.idx
    LEFT JOIN TIMETABLE ON SETTIME.time_idx=TIMETABLE.time_idx
WHERE SCHEDULE.user_idx=? ;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}
//READ
function countComments($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from POST WHERE POST.idx IN (SELECT post_idx FROM COMMENT WHERE COMMENT.user_idx=?)) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
//READ
function countPosts($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from POST WHERE user_idx =?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
//READ
function isValidUserName($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where id like concat('%',?,'%')) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
//READ
function isValidkeyword($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from BOOKSTORE where bookname like concat('%',?,'%')) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function isVaildID($userID)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where id =?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
//READ
function isValidFriend($userIdx,$friendIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from FRIEND where user_idx =? and friend_idx=? and status='Y') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$friendIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}//READ
function isValidMe($userIdx,$Idx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where user_idx =? and friend_idx=? and status='Y') exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$Idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
//READ
function checkBoard($boardname)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from BOARD where boardname =?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$boardname]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
function isValidBoard($boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from BOARD where idx =?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$boardIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}

//READ
function isVaildNick($nick)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where nick =?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$nick]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]['exist'];
}
//READ
function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where idx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function isValidBBoardIdx($boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from BASICBOARD where board_idx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$boardIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function isValidPostIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from POST where idx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function isValidCommentIdx($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from COMMENT where idx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function isValidReview($name)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from LECTURE where lecturename like concat('%',?,'%')) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$name]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from USER where idx = ? AND is_deleted='Y') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkFriend($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from FRIEND where user_idx = ? AND status='Y') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkMyPost($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from POST where user_idx = ?AND idx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkCommentMine($commentIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from COMMENT where user_idx = ?AND idx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$commentIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkLike($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from `LIKE` where user_idx = ? AND post_idx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkIsdeleted($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from `SCRAP` where user_idx = ? AND post_idx=? AND is_deleted='Y') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkScrap($userIdx,$postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from `SCRAP` where user_idx = ? AND post_idx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//READ
function checkFavorites($userIdx,$boardIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from `FAVORITES` where user_idx = ? AND board_idx=?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$boardIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
