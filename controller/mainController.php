<?php

    require 'jwt_secret_key.php';
    require 'function.php';
    $res = (Object)Array();
    $result = (Object)Array();
    $token = (Object)Array();

    header('Content-Type: json; charset=utf-8');
    $req = json_decode(file_get_contents("php://input"));
    try {
        addAccessLogs($accessLogs, $req);
        switch ($handler) {
            case "index":
                echo "API Server";
                break;

            case "ACCESS_LOGS":
//              header('content-type text/html charset=utf-8');
                header('Content-Type: text/html; charset=UTF-8');

                getLogs("./logs/access.log");
                break;
            case "ERROR_LOGS":
//              header('content-type text/html charset=utf-8');
                header('Content-Type: text/html; charset=UTF-8');

                getLogs("./logs/errors.log");
                break;
      
            /*
            * API No. 0
            * API Name : 테스트 API
            * 마지막 수정 날짜 : 18.08.16
            */

            case "test":
                http_response_code(200);
                $res->result = test();
                $res->code = 100;
                $res->message = "테스트 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /* ************************************************************************* */
            /*
            * API No. 1
            * API Name : 관리자 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "chat_data":
                $Content = $_POST["Content"];

                http_response_code(200);
                $res->result = chat_data($Content);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            case "emoticon_data":
                $Name = $_POST["Name"];
                $Made = $_POST["Made"];
                $Price = $_POST["Price"];
                $Date = date("Y-m-d");
                $Download = $_POST["Download"];

                http_response_code(200);
                $res->result = emoticon_data($Name, $Made, $Price, $Date, $Download);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            
            case "style_data":
                $Eno = $_POST["Eno"];
                $Style = $_POST["Style"];

                http_response_code(200);
                $res->result = style_data($Eno, $Style);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            case "image_data":
                $Eno = $_POST["Eno"];
                $URL = $_POST["URL"];
                
                http_response_code(200);
                $res->result = image_data($Eno, $URL);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /* ************************************************************************* */
            /*
            * API No. 2
            * API Name : 이메일 인증 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emailAuthenticate":
                $SendEmail = $req->SendEmail;
                                
                if(!isset($SendEmail)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(!preg_match("/([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/",$SendEmail)){
                    $res->code = 501;
                    $res->message = "잘못된 이메일 형식";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                http_response_code(200);
                $res->result = emailAuthenticate($SendEmail);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 3
            * API Name : 회원가입 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "user":

                $Email = $req->Email;
                $Pw = $req->Pw;
                $Name = $req->Name;
                $Tel = $req->Tel;

                $Random = $req->Random;
                                
                if(!isset($Email) || !isset($Pw) || !isset($Name) || !isset($Tel)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(!preg_match("/([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/",$Email)){
                    $res->code = 501;
                    $res->message = "잘못된 이메일 형식";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(ValidEmail($Email) != NULL) {
                    $res->code = 502;
                    $res->message = "존재하는 이메일";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(ValidTel($Tel) != NULL) {
                    $res->code = 502;
                    $res->message = "존재하는 번호";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(ValidRandom($Random) == NULL) {
                    $res->code = 507;
                    $res->message = "유효하지 않은 번호";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                http_response_code(200);
                $res->result = user($Email, $Pw, $Name, $Tel);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            /*
            * API No. 4
            * API Name : 회원탈퇴 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "user_delete":
                
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;
            
                http_response_code(200);
                $res->result = user_delete($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /* ************************************************************************* */
            /*
            * API No. 5
            * API Name : 로그인 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "login":
                $Email = $req->Email;
                $Pw = $req->Pw;

                if(!isset($Email) || !isset($Pw)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                $valid = login($Email,$Pw);
                if($valid == NULL) {
                    $res->code = 503;
                    $res->message = "존재하지 않는 회원";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                //로그인 성공시 JWT 발급 코드
                $jwt = getJWToken($Email, $Pw, JWT_SECRET_KEY);
                $res->token->jwt = $jwt;

                http_response_code(200);
                $res->result = $valid;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            
            /* ************************************************************************* */
            /*
            * API No. 6
            * API Name : 프로필 추가 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "profile":                
                $Prof_img = $req->Prof_img;
                $Back_img = $req->Back_img;
                $Status = $req->Status;
                $Date = date("Y-m-d");

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = profile($Email, $Prof_img, $Back_img, $Status, $Date);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 7
            * API Name : 프로필 확인 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "profile_check":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = profile_check($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 8
            * API Name : 히스토리 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "mystory":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = mystory($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /* ************************************************************************* */
            /*
            * API No. 9
            * API Name : 친구 목록 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "friend":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = friend($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 10
            * API Name : 친구 추가 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "friend_add":
                $Friend_Email = $req->Friend_Email;
                $Tel = $req->Tel;

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(ValidEmail($Friend_Email) == NULL) {
                    $res->code = 503;
                    $res->message = "없는 회원";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(isset($Friend_Email)) {
                    $res->result = friend_add_Email($Email, $Friend_Email);
                    $code = 100;
                    $message = "성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(isset($Tel)) {
                    $res->result = friend_add_Tel($Email, $Tel);
                    $code = 100;
                    $message = "성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                http_response_code(200);
                $res->code = 500;
                $res->message = "빈칸을 입력해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 11
            * API Name : 친구 차단 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "friend_delete":
                $Friend_Email = $req->Friend_Email;

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(ValidEmail($Friend_Email) == NULL) {
                    $res->code = 503;
                    $res->message = "없는 회원";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                http_response_code(200);
                $res->result = friend_delete($Email, $Friend_Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 12
            * API Name : 차단 해제 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "friend_delete_cancel":
                $Friend_Email = $req->Friend_Email;

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(ValidEmail($Friend_Email) == NULL) {
                    $res->code = 503;
                    $res->message = "없는 회원";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                http_response_code(200);
                $res->result = friend_delete_cancel($Email, $Friend_Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 13
            * API Name : 차단 친구 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "friend_deleted":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = friend_deleted($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 14
            * API Name : 친구 조회 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "friend_find":
                $Name = $vars["Name"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(!isset($Name)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }/*
                if(ValidEmail($Name) == NULL) {
                    $res->code = 503;
                    $res->message = "없는 회원";   
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } */

                http_response_code(200);
                $res->result = friend_find($Email, $Name);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;


            /* ************************************************************************* */
            /*
            * API No. 15
            * API Name : 즐겨찾기 (추가) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "favorites_add":
                $Friend_Email = $req->Friend_Email;

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(!isset($Friend_Email)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidEmail($Friend_Email) == NULL){
                    $res->code = 503;
                    $res->message = "없는 회원";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                
                http_response_code(200);
                $res->result = favorites_add($Email, $Friend_Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);  

                break;

            /*
            * API No. 16
            * API Name : 즐겨찾기 (확인) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "favorites_check":

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;
                
                http_response_code(200);
                $res->result = favorites_check($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK); 

                break;

            /*
            * API No. 17
            * API Name : 즐겨찾기 (제거) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "favorites_delete":
                $Friend_Email = $req->Friend_Email;

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;
                
                if(!isset($Friend_Email)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidEmail($Friend_Email) == NULL){
                    $res->code = 503;
                    $res->message = "없는 회원";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 

                http_response_code(200);
                $res->result = favorites_delete($Email, $Friend_Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);  

                break;

            /* ************************************************************************* */
            /*
            * API No. 18
            * API Name : 이모티콘 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                http_response_code(200);
                $res->result = emoticon();
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 19
            * API Name : 이모티콘(검색) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_find":
                $Name = $_GET["Name"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                if(!isset($Name)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidEmoticon($Name) == NULL) {
                    $res->code = 504;
                    $res->message = "존재하지 않는 이모티콘";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                http_response_code(200);
                $res->result = emoticon_find($Name);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 20
            * API Name : 이모티콘(신규) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_new":
                $Today = date("Y-m-d");

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                http_response_code(200);
                $res->result = emoticon_new($Today);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 21
            * API Name : 이모티콘(인기) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_pop":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                http_response_code(200);
                $res->result = emoticon_pop();
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 22
            * API Name : 이모티콘(스타일) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_style":
                $Name = $_GET["Name"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                if(!isset($Name)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(ValidEmoticonR($Name) == NULL) {
                    $res->code = 505;
                    $res->message = "없는 스타일";
                    echo json_encode($res, JSON_NUMERIC_CHECK); 
                } 
                http_response_code(200);
                $res->result = emoticon_style($Name);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 23
            * API Name : 이모티콘(다운) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_download":
                $Name = $_GET["Name"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(!isset($Name)) {
                    $res->code = 500;
                    $res->message = "빈칸을 채워주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidEmoticon($Name) == NULL){
                    $res->code = 504;
                    $res->message = "존재하지 않는 이모티콘";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidEmoticonR($Email, $Name) != NULL) {
                    $res->code = 505;
                    $res->message = "이미 있는 이모티콘";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                
                http_response_code(200);
                $res->result = emoticon_download($Email, $Name);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 24
            * API Name : 이모티콘(상세조회) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_more":
                $Eno = $vars["Eno"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!isset($Eno)) {
                    $res->code = 500;
                    $res->message = "빈칸을 채워주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }

                http_response_code(200);
                $res->result = emoticon_more($Eno);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 25
            * API Name : 이모티콘(조회) API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "emoticon_check":
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = emoticon_check($Email);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /* ************************************************************************* */
            /*
            * API No. 26
            * API Name : 채팅 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "chat":
                $Name = $req->Name;
                $Text = $req->Text;

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                if(!isset($Name) || !isset($Text)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                http_response_code(200);
                $res->result = chat($Email, $Name, $Text);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;

            /*
            * API No. 27
            * API Name : 채팅 기록 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "chat_find":
                $Page = $_GET["Page"];
                $Name = $vars["Name"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
         
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(!isset($Name)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                if(ValidChat($Email, $Name) == NULL){
                    $res->code = 506;
                    $res->message = "존재하지 않는 채팅";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
               
                http_response_code(200);
                $res->result = chat_find($Email, $Name, $Page);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);

                break;

            /*
            * API No. 28
            * API Name : 채팅 삭제 API
            * 마지막 수정 날짜 : 19.04.08
            */
            case "chat_delete":
                $Name = $vars["Name"];

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } 
                $info = getDataByJWToken($jwt, JWT_SECRET_KEY);
                $Email = $info->Email;

                if(!isset($Name)){
                    $res->code = 500;
                    $res->message = "빈칸을 입력해주세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                }
                if(ValidChat($Email, $Name) == NULL){
                    $res->code = 506;
                    $res->message = "존재하지 않는 채팅";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    return;
                } 
                
                http_response_code(200);
                $res->result = chat_delete($Email, $Name);
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);        
                break;

            /*
            * API No. 29
            * API Name : 업로드 API
            * 마지막 수정 날짜 : 19.04.13
            */
            case "upload":
                $error = $_FILES['File']['error'];
                $name = $_FILES['File']['name'];
                $ext = array_pop(explode('.', $name));

                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            
                //유효성 검사 및 JWT 파싱 코드
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->code = 201;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                http_response_code(200);
                $res->result = upload($error, $name, $ext); //$_FILES;
                $res->code = 100;
                $res->message = "성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                
                break;
        
        }
    } catch (Exception $e) {

        return getSQLErrorException($errorLogs, $e, $req);
    }
