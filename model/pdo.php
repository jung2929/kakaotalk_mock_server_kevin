<?php

    require "database.php";
    
    /* ************************************************************************* */

    function test(){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM TEST_TB";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;

    }

    /* ************************************************************************* */

    //챗봇 데이터 추가
    function chat_data($Content){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO chat_TB(Content) VALUES (?)";

        $st = $pdo->prepare($query);
        $st->execute([$Content]);

        $st=null;$pdo = null;

        return true;
    }

    //이모티콘 데이터 추가
    function emoticon_data($Name, $Made, $Price, $Date, $Download){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO emoticon_TB(Name, Made, Price, Date, Download) VALUES (?, ?, ?, ?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Name, $Made, $Price, $Date, $Download]);

        $st=null;$pdo = null;

        return true;
    }

    //이모티콘 카테고리 추가
    function style_data($Eno, $Style){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO emoticonR_TB(Eno, Style) VALUES (?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Eno, $Style]);

        $st=null;$pdo = null;

        return true;
    }

    //이모티콘 이미지 추가
    function image_data($Eno, $URL){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO image_TB(Eno, URL) VALUES (?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Eno, $URL]);

        $st=null;$pdo = null;

        return true;
    }

    /* ************************************************************************* */
    
    //이메일 유효성 검사
    function ValidEmail($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM user_TB WHERE Email = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }

    //번호 유효성 검사
    function ValidTel($Tel){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM user_TB WHERE Tel = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Tel, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }

    //이모티콘 유효성 검사
    function ValidEmoticon($Name){
        $pdo = pdoSqlConnect();
        $like = preg_replace("/\s+/", "", $Name);

        $query = "SELECT * FROM emoticon_TB WHERE Name LIKE ?";

        $st = $pdo->prepare($query);
        $st->execute(["%$like%"]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }

    //이모티콘 유효성 검사
    function ValidEmoticonR($Email, $Name){
        $pdo = pdoSqlConnect();
        $like = preg_replace("/\s+/", "", $Name);

        $query = "SELECT No 
            FROM userE_TB AS UE
            INNER JOIN user_TB AS U ON U.Uno = UE.Uno
            INNER JOIN emoticon_TB AS E ON E.Eno = UE.Eno
            WHERE U.Email = ? AND E.Name LIKE ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, "%$like%"]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }

    //채팅 유효성 검사
    function ValidChat($Email, $Name){
        $pdo = pdoSqlConnect();
        $query = "SELECT No 
            FROM chatR_TB 
            WHERE UName = (SELECT Name From user_TB WHERE Email = ? AND Deleted = ?) 
            AND RName = ? AND Deleted = ?";
        
        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', $Name, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $stmt=null;$st=null;$pdo = null;

        return $res;
    }

    //인증번호 유효성 검사
    function ValidRandom($Random){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM auth_TB WHERE Random = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Random]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;
    }

    /* ************************************************************************* */ 

    //이메일 인증
    function emailAuthenticate($SendEmail) {
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM auth_TB";  
        
        $st = $pdo->prepare($query);
        $st->execute();
        $compCodeArr = array();  

        while($row = $st->fetch()) {  
            // 저장된 comp_code를 배열화
            array_push($compCodeArr, $row["comp_code"]);  
        }         
   
        // 중복되지 않을때까지 루프, 형식은 KA라는 문자열과 해당년도 두자리, 그리고 랜덤의 숫자 4자리를 포함  
        while(in_array($data["comp_code"] = "KA".date("y",time()).substr(10000+rand(1,9999),1),$compCodeArr) == true);  
        
        foreach ($data as $res) {
            $Random = $res;
        }

        /* ------------------------------------------------ */
        $sql = "INSERT INTO auth_TB(Random)
            SELECT ? FROM DUAL
            WHERE NOT EXISTS (SELECT * FROM auth_TB WHERE Random = ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$Random, $Random]);

        Authenticate($SendEmail, $Random);
        
        $stmt=null;$st=null;$pdo = null;

        return '이메일을 확인해보세요.';//$Random;
    }

    //회원가입
    function user($Email, $Pw, $Name, $Tel){
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO user_TB(Email, Pw, Name, Tel) VALUES (?, ?, ?, ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Email, $Pw, $Name, $Tel]);

        //회원가입과 동시에 기본프로필 적용
        /* ------------------------------------------------ */
        $get = "SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?";

        $U = $pdo->prepare($get);
        $U->execute([$Email, 'N']);
        $U->setFetchMode(PDO::FETCH_ASSOC);
        while($row = $U->fetch()){
            $Uno = $row["Uno"];
        }

        $sql = "INSERT INTO profile_TB(Uno, Prof_img, Back_img, Status, Date) VALUES (?, ?, ?, ?, ?)";

        $pro = "http://kaca5.com/imageP/%EA%B8%B0%EB%B3%B8_%ED%94%84%EC%82%AC.png";
        $bac = "http://kaca5.com/imageP/%EA%B8%B0%EB%B3%B8_%EB%B0%B0%EA%B2%BD.png";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$Uno, (string)$pro, (string)$bac, '', date("Y-m-d")]);

        $stmt=null;$st=null;$pdo = null;

        return true;
    }

    //회원탈퇴
    function user_delete($Email){
        $pdo = pdoSqlConnect();
        $get = "SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?";

        $stg = $pdo->prepare($get);
        $stg->execute([$Email, 'N']);
        $stg->setFetchMode(PDO::FETCH_ASSOC);
        while($row = $stg->fetch()) {
            $res = $row["Uno"];
        }

        /* ------------------------------------------------ */

        $query = "UPDATE user_TB SET Deleted = ? WHERE Email = ?";

        $st = $pdo->prepare($query);
        $st->execute(['Y', $Email]);

        /* ------------------------------------------------ */

        $sql = "UPDATE profile_TB SET Deleted = ? WHERE Uno = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Y', $res]);

        $stmt=null;$st=null;$pdo = null;

        return true;

    }

    /* ************************************************************************* */

    //로그인
    function login($Email, $Pw){

        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM user_TB WHERE Email = ? AND Pw = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, $Pw, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $st=null;$pdo = null;

        return $res;

    }

    /* ************************************************************************* */

    //프로필 추가
    function profile($Email, $Prof_img, $Back_img, $Status, $Date){
        
        $pdo = pdoSqlConnect();
        $query = "SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $st->fetch()) {
            $tmp = $row["Uno"];
        }

        /* ------------------------------------------------ */
        $sql = "INSERT INTO profile_TB(Uno, Prof_img, Back_img, Status, Date) VALUES (?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tmp, $Prof_img, $Back_img, $Status, $Date]);

        $stmt=null;$tmp=null;$st=null;$pdo = null;

        return profile_check($Email);
    }

    //프로필 확인
    function profile_check($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT P.Pno, P.Prof_img, P.Back_img, P.Status 
            FROM user_TB AS U
            INNER JOIN profile_TB AS P ON P.Uno = U.Uno 
            WHERE U.Email = ? AND U.Deleted = ? AND P.Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $elements = (Object)Array();

        while ($row = $st->fetch()) {

            if($row["Prof_img"] != '') {
                $elements->Prof_img = $row["Prof_img"];
            }
            if($row["Back_img"] != '') {
                $elements->Back_img = $row["Back_img"];
            }
            if($row["Status"] != '') {
                $elements->Status = $row["Status"];
            }    
        }   

        $st=null;$pdo = null;

        return $elements;
    }

    //내 스토리
    function mystory($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT P.Prof_img, P.Back_img, P.Status, P.Date
            FROM user_TB AS U
            INNER JOIN profile_TB AS P ON P.Uno = U.Uno 
            WHERE U.Email = ? AND U.Deleted = ? AND P.Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            if($row["Prof_img"] != '') {
                $elements->Prof_img = $row["Prof_img"];
            }
            if($row["Back_img"] != '') {
                $elements->Back_img = $row["Back_img"];
            }
            if($row["Status"] != '') {
                $elements->Status = $row["Status"];
            } array_push($res, $elements);
        }   

        $st=null;$pdo = null;

        return $res;
    }

    /* ************************************************************************* */

    //친구 추가(Email)
    function friend_add_Email($Email, $Friend_Email){
        $pdo = pdoSqlConnect();
        $get = "SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?";

        $U = $pdo->prepare($get);
        $U->execute([$Email, 'N']);
        $U->setFetchMode(PDO::FETCH_ASSOC);

        //유저의 회원넘버
        while ($row = $U->fetch()) {
            $Uno = $row["Uno"];
        }

        $F = $pdo->prepare($get);
        $F->execute([$Friend_Email, 'N']);
        $F->setFetchMode(PDO::FETCH_ASSOC);
        
        //친구의 회원넘버
        while ($row = $F->fetch()) {
            $Fno = $row["Uno"];
        }

        /* ------------------------------------------------ */
        $query = "INSERT INTO friend_TB(Uno, Fno) 
            SELECT ?, ? FROM DUAL
            WHERE NOT EXISTS (SELECT * FROM friend_TB WHERE Uno = ? AND Fno = ?)"; 
        
        $st = $pdo->prepare($query);
        $st->execute([$Uno, $Fno, $Uno, $Fno]);

        $Fno=null;$F=null;$Uno=null;$U=null;$st=null;$pdo = null;

        return friend($Email);
    }

    //친구 추가(Tel)
    function friend_add_Tel($Email, $Tel){
        $pdo = pdoSqlConnect();
        
        $get = "SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?";

        $U = $pdo->prepare($get);
        $U->execute([$Email, 'N']);
        $U->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $U->fetch()) {
            $Uno = $row["Uno"];
        }

        $get = "SELECT Uno FROM user_TB WHERE Tel = ? AND Deleted = ?";
        $F = $pdo->prepare($get);
        $F->execute([$Tel, 'N']);
        $F->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $F->fetch()) {
            $Fno = $row["Uno"];
        }
        
        /* ------------------------------------------------ */
        $query = "INSERT INTO friend_TB(Uno, Fno) 
            SELECT ?, ? FROM DUAL
            WHERE NOT EXISTS (SELECT * FROM friend_TB WHERE Uno = ? AND Fno = ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Uno, $Fno, $Uno, $Fno]);

        $Fno=null;$F=null;$Uno=null;$U=null;$st=null;$pdo = null;
     
        return friend($Email);
    }

    //친구 차단 
    function friend_delete($Email, $Friend_Email){
        $pdo = pdoSqlConnect();

        $query = "SELECT No 
            FROM friend_TB AS F
            INNER JOIN user_TB AS U ON U.Uno = F.Uno 
            WHERE U.Email = ? AND U.Deleted = ? AND F.Deleted = ? 
            AND F.Fno = (SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', 'N', $Friend_Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        while($row = $st->fetch()) {
            $No = $row["No"];
        }

        /* ------------------------------------------------ */
        $sql = "UPDATE friend_TB SET Deleted = ? WHERE No = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Y', $No]);

        $stmt=null;$st=null;$pdo = null;
        
        return true;
    }

    //차단 해제
    function friend_delete_cancel($Email, $Friend_Email){
        $pdo = pdoSqlConnect();

        $query = "SELECT No 
            FROM friend_TB AS F
            INNER JOIN user_TB AS U ON U.Uno = F.Uno 
            WHERE U.Email = ? AND U.Deleted = ? AND F.Deleted = ? 
            AND F.Fno = (SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', 'Y', $Friend_Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        while($row = $st->fetch()) {
            $No = $row["No"];
        }

        /* ------------------------------------------------ */
        $sql = "UPDATE friend_TB SET Deleted = ? WHERE No = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['N', $No]);

        $stmt=null;$st=null;$pdo = null;
        
        return true;
    }

    //차단 목록
    function friend_deleted($Email){
        $pdo = pdoSqlConnect();
        $query = " SELECT Email, Name 
            FROM user_TB 
            WHERE Deleted = ?
            AND Uno IN (
                SELECT F.Fno
                FROM friend_TB AS F
                INNER JOIN user_TB AS U ON U.Uno = F.Uno 
                WHERE U.Email = ? AND U.Deleted = ? AND F.Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute(['N', $Email, 'N', 'Y']);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Email = $row["Email"];
            $elements->Name = $row["Name"];
            
            $data = profile_check($row["Email"]);
            $elements->Prof_img = $data->Prof_img;
            $elements->Back_img = $data->Back_img;
            $elements->Status = $data->Status;
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //친구 목록
    function friend($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT DISTINCT U.Email, U.Name
            FROM user_TB AS U
            INNER JOIN friend_TB AS F ON F.Fno = U.Uno 
            WHERE U.Deleted = ? AND F.Deleted = ?
            AND F.Fno IN (SELECT SF.Fno 
                FROM user_TB AS SU
                INNER JOIN friend_TB AS SF ON SF.Uno = SU.Uno 
                WHERE SU.Email = ? AND SU.Deleted = ? AND SF.Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute(['N', 'N', $Email, 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Email = $row["Email"];
            $elements->Name = $row["Name"];
            
            $data = profile_check($row["Email"]);
            $elements->Prof_img = $data->Prof_img;
            $elements->Back_img = $data->Back_img;
            $elements->Status = $data->Status;
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //친구 조회
    function friend_find($Email, $Name){
        $pdo = pdoSqlConnect();
        $like = preg_replace("/\s+/", "", $Name);

        $query = "SELECT DISTINCT U.Email, U.Name
            FROM user_TB AS U
            INNER JOIN friend_TB AS F ON F.Fno = U.Uno
            WHERE U.Name LIKE ? AND U.Deleted = ? AND F.Deleted = ?
            AND F.Fno IN (SELECT SF.Fno 
                FROM user_TB AS SU
                INNER JOIN friend_TB AS SF ON SF.Uno = SU.Uno 
                WHERE SU.Email = ? AND SU.Deleted = ? AND SF.Deleted = ?) ";

        $st = $pdo->prepare($query);
        $st->execute(["%$like%", 'N', 'N', $Email, 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Email = $row["Email"];
            $elements->Name = $row["Name"];
            
            $data = profile_check($row["Email"]);
            $elements->Prof_img = $data->Prof_img;
            $elements->Back_img = $data->Back_img;
            $elements->Status = $data->Status;
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    /* ************************************************************************* */
    
    //대표 이미지 출력
    function emoticon_image($Eno){
        $pdo = pdoSqlConnect();
        $query = "SELECT URL FROM image_TB WHERE Eno = ?";
            
        $st = $pdo->prepare($query);
        $st->execute([$Eno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $row = $st->fetch();

        $res = Array();
        array_push($res, $row["URL"]);

        $st=null;$pdo = null;

        return $res;
    }

    //이모티콘 (전체)
    function emoticon(){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM emoticon_TB";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        //$res = $st->fetchAll();

        $res = Array();
        while($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->image = emoticon_image($row["Eno"]);
            array_push($res, $elements);
        } 


        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //이모티콘 (검색)
    function emoticon_find($Name){
        $pdo = pdoSqlConnect();
        $like = preg_replace("/\s+/", "", $Name);

        $query = "SELECT * FROM emoticon_TB WHERE Name LIKE ? OR Made LIKE ?";

        $st = $pdo->prepare($query);
        $st->execute(["%$like%", "%$like%"]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->image = emoticon_image($row["Eno"]);
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //이모티콘 (신규)
    function emoticon_new($Today){
        $pdo = pdoSqlConnect();
        $query = "SELECT * 
            FROM emoticon_TB
            WHERE Date BETWEEN ? AND ?";

        $st = $pdo->prepare($query); //$Today
        $st->execute(['2019-04-21', (string)date("Y-m-d", strtotime($Today.'+7 days'))]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->image = emoticon_image($row["Eno"]);
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //이모티콘 (인기)
    function emoticon_pop(){
        $pdo = pdoSqlConnect();
        $query = "SELECT Eno, Name, Made, Download FROM emoticon_TB ORDER BY Download DESC";

        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->Download = $row["Download"];
            $elements->image = emoticon_image($row["Eno"]);
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //이모티콘 (스타일)
    function emoticon_style($Name){
        $pdo = pdoSqlConnect();
        $like = preg_replace("/\s+/", "", $Name);

        $query = "SELECT E.Eno, E.Name, E.Made, E.Price, ER.Style 
            FROM emoticonR_TB AS ER
            INNER JOIN emoticon_TB AS E ON E.ENO = ER.ENO
            WHERE Style LIKE ?";

        $st = $pdo->prepare($query);
        $st->execute(["%$like%"]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->Style = $row["Style"];
            $elements->image = emoticon_image($row["Eno"]);
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;

        return $res;
    }

    //이모티콘 (다운)
    function emoticon_download($Email, $Name){
        $pdo = pdoSqlConnect();
        
        /* ------------------------------------------------ */
        $queryE = "SELECT Eno FROM emoticon_TB WHERE Name = ?";
        $stE = $pdo->prepare($queryE);
        $stE->execute([$Name]);
        $stE->setFetchMode(PDO::FETCH_ASSOC);
        $rowE = $stE->fetch();

        /* ------------------------------------------------ */
        $queryU = "SELECT Uno FROM user_TB WHERE Email = ?";
        $stU = $pdo->prepare($queryU);
        $stU->execute([$Email]);
        $stU->setFetchMode(PDO::FETCH_ASSOC);
        $rowU = $stU->fetch();
        
        /* ------------------------------------------------ */
        //다운로드
        $queryD = "UPDATE emoticon_TB SET Download = Download + 1 WHERE Name = ?";
        $stD = $pdo->prepare($queryD);
        $stD->execute([$Name]);

        /* ------------------------------------------------ */
        //삽입
        $query = "INSERT INTO userE_TB(Uno, Eno) VALUES (?, ?)";
        $st = $pdo->prepare($query);
        $st->execute([$rowU["Uno"], $rowE["Eno"]]);

        $stE=null;$stU=null;$stD=null;$st=null;$pdo = null;
        
        return emoticon_check($Email);
    }

    //이모티콘 (상세조회)
    function emoticon_more($Eno){
        $pdo = pdoSqlConnect();
        $query = "SELECT E.Eno, E.Name, E.Made, E.Price, E.Download
            FROM emoticon_TB AS E
            INNER JOIN image_TB AS I ON I.Eno = E.Eno
            WHERE E.Eno = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Eno]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->Price = $row["Price"];
            $elements->Download = $row["Download"];

            /* ------------------------------------------------ */
            $sql = "SELECT URL FROM image_TB WHERE Eno = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$Eno]);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            $data = Array();
            while($rows = $stmt->fetch()) {
                array_push($data, $rows["URL"]);
            }
            $elements->image = $data;
        }
        array_push($res, $elements);
        
        $elements=null;$stmt=null;$st=null;$pdo = null;
        
        return $res;
    }

    //내 이모티콘 (조회)
    function emoticon_check($Email){
        $pdo = pdoSqlConnect();
        $query = "SELECT E.Eno, E.Name, E.Made
            FROM user_TB AS U
            INNER JOIN userE_TB AS UE ON UE.Uno = U.Uno
            INNER JOIN emoticon_TB AS E ON E.Eno = UE.Eno 
            WHERE U.Email = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email]);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (Object)Array();
            $elements->Eno = $row["Eno"];
            $elements->Name = $row["Name"];
            $elements->Made = $row["Made"];
            $elements->image = emoticon_image($row["Eno"]);
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    /* ************************************************************************* */

    //채팅
    function chat($Email, $Name, $Text){
        $pdo = pdoSqlConnect();
        
        /* ------------------------------------------------ */
        $queryU = "SELECT Name FROM user_TB WHERE Email = ? AND Deleted = ?";
        $stU = $pdo->prepare($queryU);
        $stU->execute([$Email, 'N']);
        $stU->setFetchMode(PDO::FETCH_ASSOC);
        $rowU = $stU->fetch();
        
        /* ------------------------------------------------ */
        $queryN = "SELECT Name FROM user_TB WHERE Name = ? AND Deleted = ?";
        $stN = $pdo->prepare($queryN);
        $stN->execute([$Name, 'N']);
        $stN->setFetchMode(PDO::FETCH_ASSOC);
        $rowN = $stN->fetch();
        
        /* ------------------------------------------------ */
        $query = "SELECT Content FROM chat_TB ORDER BY RAND() LIMIT 1";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $rowS = $st->fetch();

        /* ------------------------------------------------ */
        $sql = "INSERT INTO chatR_TB(UName, RName, UText, RText) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rowU["Name"], $rowN["Name"], $Text, $rowS["Content"]]);

        /* ------------------------------------------------ */
        $result = "SELECT * FROM chatR_TB WHERE Deleted = ?";
        $stR = $pdo->prepare($result);
        $stR->execute(['N']);
        $stR->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        while($row = $stR->fetch()) {
            $elements = (object) Array();
            //$elements->No = $row["No"];
            $elements->UName = $row["UName"];
            $elements->RName = $row["RName"];
            $elements->UText = $row["UText"];
            $elements->RText = $row["RText"];
        } $res = $elements;
        
        $stR=null;$stU=null;$stN=null;$stmt=null;$st=null;$pdo = null;
        
        return $res;
    }

    //채팅 (기록)
    function chat_find($Email, $Name, $Page){
        $pdo = pdoSqlConnect();
        $query = "SELECT * FROM (
                    SELECT @ROWNUM := @ROWNUM + 1 AS NUM, UName, RName, UText, RText 
                    FROM (SELECT @ROWNUM := 0) R, chatR_TB 
                    WHERE UName = (SELECT Name 
                        FROM user_TB 
                        WHERE Email = ? AND Deleted = ?)
                    AND RName = ? AND Deleted = ?) A
                WHERE NUM >= ? ";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', $Name, 'N', $Page]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $stmt=null;$st=null;$pdo = null;
       
        return $res;
    }

    //채팅 (삭제)
    function chat_delete($Email, $Name){
        $pdo = pdoSqlConnect();
        $query = "SELECT Name FROM user_TB WHERE Email = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $row = $st->fetch();

        /* ------------------------------------------------ */
        $sql = "UPDATE chatR_TB SET Deleted = ? WHERE UName = ? AND RName = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Y', $row["Name"], $Name]);

        $stmt=null;$st=null;$pdo = null;

        return true;
    }

    /* ************************************************************************* */

    //즐찾 추가
    function favorites_add($Email, $Friend_Email) {
        $pdo = pdoSqlConnect();
        $query = "SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $row = $st->fetch();

        $st = $pdo->prepare($query);
        $st->execute([$Friend_Email, 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $rows = $st->fetch();
        
        /* ------------------------------------------------ */
        $sql = "INSERT INTO favorites_TB(Uno, Fno)
            SELECT ?, ? FROM DUAL
            WHERE NOT EXISTS (SELECT * FROM favorites_TB WHERE Uno = ? AND Fno = ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$row["Uno"], $rows["Uno"], $row["Uno"], $rows["Uno"]]);

        $stmt=null;$st=null;$pdo = null;

        return favorites_check($Email);
    }

    //즐찾 확인
    function favorites_check($Email) {
        $pdo = pdoSqlConnect();
        $query = "SELECT U.Email, U.Name
            FROM favorites_TB AS F
            INNER JOIN user_TB AS U ON U.Uno = F.Fno
            WHERE F.Uno = (SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?) AND U.Deleted = ?";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', 'N']);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        
        $res = Array();
        while ($row = $st->fetch()) {
            $elements = (object) Array();
            $elements->Email = $row["Email"];
            $elements->Name = $row["Name"];
            
            $data = profile_check($row["Email"]);
            $elements->Prof_img = $data->Prof_img;
            $elements->Back_img = $data->Back_img;
            $elements->Status = $data->Status;
            array_push($res, $elements);
        }
        
        $elements=null;$st=null;$pdo = null;
        
        return $res;
    }

    //즐찾 제거
    function favorites_delete($Email, $Friend_Email) {
        $pdo = pdoSqlConnect();
        $query = "DELETE FROM favorites_TB 
            WHERE Uno = (SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?) 
            AND Fno = (SELECT Uno FROM user_TB WHERE Email = ? AND Deleted = ?)";

        $st = $pdo->prepare($query);
        $st->execute([$Email, 'N', $Friend_Email, 'N']);

        $st=null;$pdo = null;

        return favorites_check($Email);
    }

    function upload($error, $name, $ext) {
        $uploads_dir = '/var/www/html/Projects/kacao/profile_img';//'http://test_k.kaca5.com/profile_img';
        $allowed_ext = array('jpg','jpeg','png','gif');
 
        // 오류 확인
        if( $error != UPLOAD_ERR_OK ) {
	        switch( $error ) {
		        case UPLOAD_ERR_INI_SIZE:
		        case UPLOAD_ERR_FORM_SIZE:
			        $string = "파일이 너무 큽니다. ($error)";
			        break;
		        case UPLOAD_ERR_NO_FILE:
                    $string = "파일이 첨부되지 않았습니다. ($error)";
			        break;
		        default:
			        $string = "파일이 제대로 업로드되지 않았습니다. ($error)";
	        }
	        return $string;
        }
 
        // 확장자 확인
        if( !in_array($ext, $allowed_ext) ) {
	        return "허용되지 않는 확장자입니다.";
        }
 
        // 파일 이동
        move_uploaded_file( $_FILES['File']['tmp_name'], "$uploads_dir/$name");

        return "http://kaca5.com/profile_img/$name";
    }