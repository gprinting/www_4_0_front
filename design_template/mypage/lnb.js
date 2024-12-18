var lnbHTML = '';
lnbHTML += '<ul>';
lnbHTML += '<li>';
lnbHTML += '<a href="#none" target="_self">회원 정보</a>';
lnbHTML += '<ul>';
lnbHTML += '<li><a href="#none" target="_self">회원정보 변경</a></li>';
lnbHTML += '<li><a href="#none" target="_self">회원등급</a></li>';
lnbHTML += '<li><a href="#none" target="_self">이벤트</a></li>';
lnbHTML += '<li><a href="#none" target="_self">포인트조회</a></li>';
lnbHTML += '<li><a href="#none" target="_self">쿠폰조회</a></li>';
lnbHTML += '<li><a href="#none" target="_self">카테고리별 할인</a></li>';
lnbHTML += '<li><a href="#none" target="_self">선입금 충전</a></li>';
lnbHTML += '<li><a href="#none" target="_self">회원탈퇴</a></li>';
lnbHTML += '</ul>';
lnbHTML += '</li>';
lnbHTML += '<li>';
lnbHTML += '<a href="#none" target="_self">관리 사업자등록증</a>';
lnbHTML += '</li>';
lnbHTML += '<li>';
lnbHTML += '<a href="#none" target="_self">주문 정보</a>';
lnbHTML += '<ul>';
lnbHTML += '<li><a href="#none" target="_self">전체주문</a></li>';
lnbHTML += '<li><a href="#none" target="_self">미결제주문</a></li>';
lnbHTML += '<li><a href="#none" target="_self">장바구니</a></li>';
lnbHTML += '<li><a href="#none" target="_self">관심상품</a></li>';
lnbHTML += '<li><a href="#none" target="_self">견적문의</a></li>';
lnbHTML += '</ul>';
lnbHTML += '</li>';
lnbHTML += '<li>';
lnbHTML += '<a href="#none" target="_self">배송 정보</a>';
lnbHTML += '<ul>';
lnbHTML += '<li><a href="#none" target="_self">배송관리</a></li>';
lnbHTML += '<li><a href="#none" target="_self">배송추적</a></li>';
lnbHTML += '</ul>';
lnbHTML += '</li>';
lnbHTML += '<li>';
lnbHTML += '<a href="#none" target="_self">결제 정보</a>';
lnbHTML += '<ul>';
lnbHTML += '<li><a href="#none" target="_self">결제내역</a></li>';
lnbHTML += '<li><a href="#none" target="_self">거래명세서</a></li>';
lnbHTML += '<li><a href="#none" target="_self">세금계산서</a></li>';
lnbHTML += '</ul>';
lnbHTML += '</li>';
lnbHTML += '<li>';
lnbHTML += '<a href="#none" target="_self">클레임/상담</a>';
lnbHTML += '<ul>';
lnbHTML += '<li><a href="#none" target="_self">클레임내역</a></li>';
lnbHTML += '<li><a href="#none" target="_self">1:1문의</a></li>';
lnbHTML += '</ul>';
lnbHTML += '</li>';
lnbHTML += '</ul>';

$(document).ready(function () {
    $('nav.lnb').html(lnbHTML);
    
    var text1 = $('header.title .location li:eq(2) span').text().replace(/\s/g, ''),
        text2 = $('header.title .location li:eq(3) span').text().replace(/\s/g, ''),
        thisText = '';

    $('nav.lnb > ul > li > a').each(function () {
        thisText = $(this).html().replace(/\s/g, '').split('<span')[0];
        if (thisText == text1) {
            $(this).closest('li').addClass('on');
            $(this).closest('li').children('ul').find('a').each(function () {
                thisText = $(this).html().replace(/\s/g, '').split('<span')[0];
                if(thisText == text2) {
                    $(this).closest('li').addClass('on');
                }
            });
        }
    });
});
