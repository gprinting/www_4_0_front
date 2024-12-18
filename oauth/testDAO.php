<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . '/common_dao/CommonDAO.inc');

class testDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 소셜회원 아이디 검사
     * @detail 멤버이름, 메일, 가입경로를 통해 검사한다.
     *
     */
    function selectSocialAcc($conn, $param) {

        //커넥션 체크
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        //인젝션 어택 방지
        $param = $this->parameterArrayEscape($conn, $param);
  
        $query  = "\nSELECT  A.member_seqno";
        $query .= "\n  FROM  gprinting.member AS A";
        $query .= "\n WHERE  1 = 1 ";

        if ($this->blankParameterCheck($param, "name")) {
            $query .= "\n    AND  A.member_name = ";
            $query .= $param["name"];
        }
        if ($this->blankParameterCheck($param, "mail")) {
            $query .= "\n    AND  A.mail = ";
            $query .= $param["mail"];
        }
        if ($this->blankParameterCheck($param, "join_path")) {
            $query .= "\n    AND  A.join_path = ";
            $query .= $param["join_path"];
        }

        $rs = $conn->Execute($query);

        return $rs;
    }

    /**
     * @brief 소셜로그인 회원정보 등록
     *
     * @param $conn  = connection identifier
     * @param $param = 검색조건 파라미터
     *
     * @return 검색결과
     */
    function insertSocialAcc($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
           return false;
        }
        
        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n INSERT INTO gprinting.member (";
        $query .= "\n        member_name";
        $query .= "\n       ,withdraw_yn";
        $query .= "\n       ,mail";
        $query .= "\n       ,first_join_date";
        $query .= "\n       ,grade";
        $query .= "\n       ,join_path";
        $query .= "\n ) VALUES (";
        $query .= "\n        %s ";
        $query .= "\n       ,%s ";
        $query .= "\n       ,%s ";
        $query .= "\n       ,now() ";
        $query .= "\n       ,%s ";
        $query .= "\n       ,%s)";
        $query  = sprintf($query, $param["name"]
                                , $param["withdraw_yn"] 
                                , $param["mail"] 
                                , $param["grade"] 
                                , $param["join_path"]);

        return $conn->Execute($query);

    }

    /**
     * @brief 통상로그인 회원정보 등록
     *
     * @param $conn  = connection identifier
     * @param $param = 파라미터
     *
     * @return 검색결과
     */
    function insertComMember($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
           return false;
        }
        
        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n INSERT INTO gprinting.member (";
        $query .= "\n        mail";
        $query .= "\n       ,passwd";
        $query .= "\n       ,grade";
        $query .= "\n       ,first_join_date";
        $query .= "\n       ,withdraw_yn";
        $query .= "\n ) VALUES (";
        $query .= "\n        %s ";
        $query .= "\n       ,%s ";
        $query .= "\n       ,%s ";
        $query .= "\n       ,now() ";
        $query .= "\n       ,%s)";
        $query  = sprintf($query, $param["mail"]
                                , $param["passwd"] 
                                , $param["grade"] 
                                , $param["withdraw_yn"]);

        return $conn->Execute($query);

    }

    /**
     * @brief 회원 아이디 중복 검사
     *
     */
    function selectDuplEmail($conn, $param) {

        //커넥션 체크
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        //인젝션 어택 방지
        $param = $this->parameterArrayEscape($conn, $param);
  
        $query  = "\nSELECT  A.member_seqno";
        $query .= "\n  FROM  gprinting.member AS A";
        $query .= "\n WHERE  1 = 1 ";

        if ($this->blankParameterCheck($param, "mail")) {
            $query .= "\n    AND  A.mail = ";
            $query .= $param["mail"];
        }
        if ($this->blankParameterCheck($param, "join_path")) {
            $query .= "\n    AND  A.join_path = ";
            $query .= $param["join_path"];
        }

        $rs = $conn->Execute($query);

        return $rs;
    }



}
