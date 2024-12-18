<?
include "../ISAF/inc/dbconn.php";
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
<meta http-equiv="imagetoolbar" content="no">
<title>무료배송 안내</title>
<link href="../sp_css/style.css" rel="stylesheet" type="text/css" />
<link href="../sp_css/style_order.css" rel="stylesheet" type="text/css" />
<link href="../sp_css/css_layout.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="../sp_js/jquery.lightbox-0.5.css" type="text/css" media="screen" />
<style type="text/css">
/*
body {
	margin-left: 0px;
	margin-top: 0px;
}
td {
	font-size: 12px;
	color:#555555;
	letter-spacing:0;
	text-decoration:none;
}
a:link {
	font-family: 돋움;
	font-size: 12px;
	letter-spacing:-0.5;
	color: #666666;
	text-decoration: none
}
a:visited {
	font-family: 돋움;
	font-size: 12px;
	letter-spacing:-0.5;
	color: #666666;
	text-decoration: none
}
a:active {
	font-family: 돋움;
	font-size: 12px;
	letter-spacing:-0.5;
	color: #666666;
	text-decoration: none
}
a:hover {
	font-family: 돋움;
	font-size: 12px;
	letter-spacing:-0.5;
	color: #cc2329;
	text-decoration: none
}
.dev_tt {
	font-family:돋움;
	font-size:12px;
	letter-spacing:-0.5;
	color:#FFFFFF;
	font-weight:bold;
	padding:7px 0 5px 0;
}
.dev_tb {
	font-family:돋움;
	font-size:12px;
	letter-spacing:-0.5;
	color:#666666;
	font-weight:bold;
	padding:7px 0 5px 0;
}
.dev_st {
	font-family:돋움;
	font-size:12px;
	letter-spacing:-0.5;
	color:#666666;
	font-weight:bold;
	padding:7px 0 5px 0;
}
.dev_st1 {
	font-family:돋움;
	font-size:11px;
	letter-spacing:-0.5;
	color:#666666;
	font-weight:bold;
	padding:7px 0 5px 0;
}
.dev_st2 {
	font-family:돋움;
	font-size:11px;
	letter-spacing:-0.5;
	color:#339933;
	font-weight:bold;
	padding:7px 0 5px 0;
}
.free_td2 {
	font-family: "돋움";
	font-size: 11px;
	border: 1px solid #FFFFFF;
	background-color: #F7FCFF;
	text-decoration: none;
	color: #666666;
	text-align: left;
	padding-left: 10px;
	font-weight: normal;
	padding-right: 10px;
	padding-top: 10px;
	padding-bottom: 10px;
}
.free_td1 {
	font-family: "돋움";
	font-size: 11px;
	border: 1px solid #FFFFFF;
	background-color: #EFF8FE;
	text-decoration: none;
	font-weight: bolder;
	color: #666666;
	text-align: center;
}
.free_title {
	font-family: "돋움";
	font-size: 14px;
	border: 1px solid #FFFFFF;
	background-color: #006699;
	text-decoration: none;
	font-weight: bolder;
	color: #FFFFFF;
	text-align: left;
	padding: 5px;
}
.free_title2 {
	font-family: "돋움";
	font-size: 12px;
	border: 1px solid #FFFFFF;
	text-decoration: none;
	font-weight: bolder;
	color: #3667DA;
	text-align: left;
	padding-left: 10px;
}
.free_tt {
	font-family: "돋움";
	font-size: 17px;
	font-weight: bold;
	color: #009933;
	text-decoration: none;
	padding: 10px;
}
.style2 {
	color: #999999
}
*/
</style>
<table width="460" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:10px;">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td><img src="../sp_img/sp_order/deliver_poping.jpg" /></td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:15px;">
				<tr>
					<td class="free_tt">무료배송 안내</td>
				</tr>
				<tr>
					<td height="300" class="dev_st2">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td height="5" colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td height="18" colspan="2" class="free_title">무료배송 가입</td>
							</tr>
							<tr>
								<td height="5" colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td height="30" colspan="2" class="free_title2">&gt;&gt;신청하기</td>
							</tr>
							<tr>
								<td width="100" height="20" class="free_td1">가입신청비</td>
								<td width="356" class="free_td2">5만원</td>
							</tr>
							<tr>
								<td width="100" height="30" class="free_td1">배송기간</td>
								<td width="356" class="free_td2">매월 1일 ~ 말일</td>
							</tr>
							<tr>
								<td width="100" height="30" class="free_td1">가입조건</td>
								<td width="356" class="free_td2">
									배송위치가 당사의 배송라인에 맞아야하며(배송기사 상담후 결정)<br />
									배송위치는 건물1층에 한하며, 도난이나 분실의 우려가 없는곳이어<br />야 하며, 비맞을 염려가 없는 곳이어야 합니다.
								</td>
							</tr>
							<tr>
								<td width="100" height="30" class="free_td1">가입기준</td>
								<td width="356" class="free_td2">
									가입시 5만원 선입금 하셔야 하며 <br />
									한달 거래금액이 30만원 이상인 경우 : 다음달 무료배송<br />
									한달 거래금액이 30만원 미만인 경우 : 다음달 초 5만원 청구<br />
									월중 가입시 가입달은 신청시점부터 말일까지 배송하며, 다음달<br />
									거래금액에 따라 청구 여부를 결정합니다.<br />
									(ex: 6월20일 가입시 7월 거래량이 30만원이상인 경우 8월 무료배송 입니다.)
								</td>
							</tr>
							<!--<tr>
                <td width="100" height="30" class="free_td1">가입신청</td>
                <td width="356" class="free_td2">가입신청은 마이페이지 1:1 상담하기에서 하시거나 02)2260-9011로<br />
				통화하시기 바랍니다. 약도를 작성하여 첨부해주시기 바랍니다. </td>
              </tr>-->
						</table>
						<?if($_SESSION[m_id]){?>
						<table width="120" border="0" cellspacing="0" cellpadding="0" align="right" style="margin-top:20px;">
							<tr>
								<td><a href="../sp_customer/pop_order_DeliFREE1.php"><img src="../sp_img/sp_customer/btn_deli.jpg" border="0"></a></td>
							</tr>
						</table>
						<?}?>
					</td>
				</tr>
				<tr>
					<td class="dev_st2">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td height="40" colspan="4"></td>
							</tr>
							<tr>
								<td height="20" colspan="4" class="free_title">배송가능지역</td>
							</tr>
							<tr>
								<td height="10" colspan="4">&nbsp;</td>
							</tr>
							<tr>
								<td height="30" colspan="4" class="free_title2">&nbsp;&gt;&gt; 주간배송</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">호차</td>
								<td width="75" height="30" class="free_td1">담당자</td>
								<td width="90" class="free_td1">연락처</td>
								<td width="199" class="free_td1">지역</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">서울배송 1</td>
								<td width="75" height="45" class="free_td2">신교근</td>
								<td width="90" height="45" class="free_td2">010-8406-8253</td>
								<td width="199" height="45" class="free_td2">광진구, 강동구, 송파구일부</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">서울배송 2</td>
								<td width="75" height="30" class="free_td2">신민규</td>
								<td width="90" height="30" class="free_td2">010-3169-9110</td>
								<td width="199" height="30" class="free_td2">덕양구, 일산, 금촌</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">서울배송 3-1</td>
								<td width="75" height="30" class="free_td2">조정형</td>
								<td width="90" height="30" class="free_td2">010-9181-4175</td>
								<td width="199" height="30" class="free_td2">마포구일부, 여의도, 영등포구</td>
							</tr>
                            <tr>
								<td width="90" height="30" class="free_td1">서울배송 3-2</td>
								<td width="75" height="30" class="free_td2">박이기</td>
								<td width="90" height="30" class="free_td2">010-5276-0951</td>
								<td width="199" height="30" class="free_td2">양천구, 강서구일부</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 4-1</td>
								<td width="75" class="free_td2">이광호</td>
								<td width="90" class="free_td2">010-4595-7912</td>
								<td width="199" class="free_td2">서대문, 광화문, 마포일부, 은평구</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 4-2</td>
								<td width="75" class="free_td2">김형원</td>
								<td width="90" class="free_td2">010-6277-0947</td>
								<td width="199" class="free_td2">동대문, 남대문, 을지로</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 5</td>
								<td width="75" class="free_td2"><p>원영준</p></td>
								<td width="90" class="free_td2">010-6818-7003</td>
								<td width="199" class="free_td2">
								<p>광진구일부, 송파구일부, <br>
								성남, 분당</p>
								</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 6</td>
								<td width="75" class="free_td2">김혁용</td>
								<td width="90" class="free_td2">010-3026-3267</td>
								<td width="199" class="free_td2">의정부, 포천, 양주, 백석, 동두천</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 7</td>
								<td width="75" class="free_td2">윤학수</td>
								<td width="90" class="free_td2">011-895-5733</td>
								<td width="199" class="free_td2">
								<p>미아동, 수유동, 창동, 노원, <br>
								중랑구, 구리</p>
								</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 8</td>
								<td width="75" class="free_td2">지갑석</td>
								<td width="90" class="free_td2">010-9135-5361</td>
								<td width="199" class="free_td2">용산, 구로, 금천, 방배동</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 9</td>
								<td width="75" class="free_td2">김영갑</td>
								<td width="90" class="free_td2">010-2761-6210</td>
								<td width="199" class="free_td2">안산, 시흥일부</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">서울배송 10</td>
								<td width="75" class="free_td2">이대수</td>
								<td width="90" class="free_td2">010-8401-9922</td>
								<td width="199" class="free_td2">강서구일부, 부천, 시흥일부</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="dev_st2">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td height="20" colspan="4">&nbsp;</td>
							</tr>
							<tr>
								<td height="30" colspan="4" class="free_title2">&nbsp;&gt;&gt; 야간배송</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">호차</td>
								<td width="75" height="30" class="free_td1">담당자</td>
								<td width="90" class="free_td1">연락처</td>
								<td width="199" class="free_td1">지역</td>
							</tr>
							<tr>
								<td width="90" height="45" class="free_td1">2호</td>
								<td width="75" height="45" class="free_td2">이석모</td>
								<td width="90" height="45" class="free_td2">010-6286-3145</td>
								<td width="199" height="45" class="free_td2">인천, 부평</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">3호</td>
								<td width="75" height="30" class="free_td2">이종기</td>
								<td width="90" height="30" class="free_td2">017-331-0222</td>
								<td width="199" height="30" class="free_td2">군포, 산본, 안양일부, 수원일부</td>
							</tr>
							<tr>
								<td width="90" height="30" class="free_td1">5호</td>
								<td width="75" height="30" class="free_td2">조정형</td>
								<td width="90" height="30" class="free_td2">010-9181-4175</td>
								<td width="199" height="30" class="free_td2"><p>안양일부, 부천, 김포</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">7호</td>
								<td width="75" class="free_td2">지건성</td>
								<td width="90" class="free_td2">010-2061-1976</td>
								<td width="199" class="free_td2">광주, 이천, 용인, 죽전, 수원일부</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">8호</td>
								<td width="75" class="free_td2"><p>임재락</p></td>
								<td width="90" class="free_td2">010-8371-1371</td>
								<td width="199" class="free_td2"><p>병점, 오산, 평택, 천안, 온양</td>
							</tr>
							<tr>
								<td width="90" class="free_td1">9호</td>
								<td width="75" class="free_td2">윤창수</td>
								<td width="90" class="free_td2">011-354-9767</td>
								<td width="199" class="free_td2">하남, 마석, 춘천, 원주, 여주, 양평</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="dev_st2">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td height="50" class="free_title2">&nbsp;</td>
							</tr>
							<tr>
								<td height="30" class="free_title">&gt;&gt;배송시 주의사항</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>
									<p class="free_td2">1. 무료배송은 디프린팅이 드리는 서비스 입니다.<br />
									<br />
									2. 전국 각 지역으로 배송되오나 부득이하게 배송이 안되는 지역이 있으니   신청후 꼭 상담받으시기 바랍니다.<br />
									<br />
									3. 배송이 가능한 지역은 배송지역에서 확인하시고 자세한 문의는 관리자 또는 배송과장님에게 문의해   주십시오.<br />
									<br />
									<strong> 배송과장님 : 황재호 010-3757-2996 </strong><br />
									<br />
									4. 배송시간은 날씨 및 도로교통 상황에 따라 변동 될 수   있습니다.<br />
									<br />
									5. 일요일, 공휴일은 배송되지 않습니다.<br />
									<br />
									6. 물량이 많은 경우는 분할되어 2회에 걸쳐 배송될 수 있으며 누락본이 발생한   경우에도 2회에 걸쳐 배송될 수 있습니다.<br />
									<span class="style2">- 이경우 다른 배송방법은 불가능합니다. </span><br />
									<br />
									7. 서울및 일부지역은 낮에 배송되고 서울   외 지역은 저녁부터 배송이 시작되오니 분실에 위헙이 있습니다.<br />
									<br />
									8. 야간배송은 새벽에도 배송이 되기 때문에 안전하게 배송을 받으실 수 있는   장소를 확보해 주십시오.<br />
									<span class="style2">(분실시 책임지지 않습니다.) </span><br />
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;">
				<tr>
					<td align="right"><a href="#close" onClick="javascript:parent.close()"><img src="../sp_img/sp_order/closelabel.gif" border="0" /></a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
