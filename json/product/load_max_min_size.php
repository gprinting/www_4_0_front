<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/06/16 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/front/NonStandardUtil.inc");

$fb = new FormBean();
$util = new NonStandardUtil();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$paper         = $fb["paper_name"];

$size_arr = $util->getMinMaxSize([
     "cate_sortcode" => $cate_sortcode
    ,"paper" => $paper
]);

$min_wid  = $size_arr["MIN_WID"];
$min_vert = $size_arr["MIN_VERT"];
$max_wid  = $size_arr["MAX_WID"];
$max_vert = $size_arr["MAX_VERT"];

$json  = '{';
$json .=   "\"min_wid\"  : \"%s\",";
$json .=   "\"min_vert\" : \"%s\",";
$json .=   "\"max_wid\"  : \"%s\",";
$json .=   "\"max_vert\" : \"%s\"";
$json .= '}';

echo sprintf($json, $min_wid
                  , $min_vert
                  , $max_wid
                  , $max_vert);
