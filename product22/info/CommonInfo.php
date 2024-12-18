<?
abstract class CommonInfo {
    // db 커넥션
    var $conn = null;
    // 주문페이지에 값을 표현할 템플릿 객체
    var $template = null;
    // 카테고리 분류코드
    var $cate_sortcode = null;
    // $template->reg 할 때 고유값을 만들기 위한 구분값
    var $dvs = null;
    // 혼합형 여부
    var $mix_yn = false;
    // 사이즈에서 계열 노출여부
    var $affil_yn = false;
    // 사이즈에서 자리수 노출여부
    var $pos_yn = false;
    // 사이즈명 사이즈종류 노출여부
    var $size_typ_yn = false;
    // 종이분류 분리여부
    var $paper_sort_yn = false;
    // 종이정보 분리여부
    var $paper_name_yn = false;
    // 종이명 별칭 사용여부
    var $paper_nick_yn = false;
    // 모바일 페이지 여부
    var $mobile_yn = false;
    // 들어온 링크
    var $link = null;
    // 기본값 배열
    var $def_arr = [];

    abstract function init();
}
?>
