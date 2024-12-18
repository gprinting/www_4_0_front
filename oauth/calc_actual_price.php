<?php
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$fb = new FormBean();
$fb = $fb->getForm();

class CalcActualPriceUtil {
    // 일반 현수막 헤베당 단가
    const NORMAL_BANNER_COST = 4000;
    // 대형 현수막 헤베당 단가
    const LARGE_BANNER_COST_30 = 4900; // 30 ~ 49
    const LARGE_BANNER_COST_50 = 4500; // 50 ~ 99
    const LARGE_BANNER_COST_100 = 3900; // 100 ~

    private $hebe;
    private $cost;

    /**
     * @brief 헤베 계산
     *
     * @param $w = 너비 mm
     * @param $h = 높이 mm
     *
     * @return 헤베값
     */
    public function calcHebe($w, $h): float {
        $w = doubleval($w) / 1000.0; 
        $h = doubleval($h) / 1000.0; 

        $hebe = $w * $h;
        $this->hebe = $hebe;

        return $hebe;
    }

    /**
     * @brief 현수막 단가 계산
     *
     * @param $w = 너비 mm
     * @param $h = 높이 mm
     *
     * @return 현수막 가격
     */
    public function calcPrice($w, $h) {
        $hebe = $this->calcHebe($w, $h);

        if ($hebe < 30.0) {
            $this->cost = self::NORMAL_BANNER_COST;
            return ceil($hebe) * self::NORMAL_BANNER_COST;
        } else if (30.0 <= $hebe && $hebe < 50.0) {
            $this->cost = self::LARGE_BANNER_COST_30;
            return ceil($hebe) * self::LARGE_BANNER_COST_30;
        } else if (50.0 <= $hebe && $hebe < 100.0) {
            $this->cost = self::LARGE_BANNER_COST_50;
            return ceil($hebe) * self::LARGE_BANNER_COST_50;
        }

        $this->cost = self::LARGE_BANNER_COST_100;
        return ceil($hebe) * LARGE_BANNER_COST_100;
    }

    public function getHebe() {
        return $this->hebe;
    }

    public function getCost() {
        return $this->cost;
    }
}

$w = $fb["w"];
$h = $fb["h"];

$calcUtil = new CalcActualPriceUtil();
$price = $calcUtil->calcPrice($w, $h);
$hebe = $calcUtil->getHebe();
$cost = $calcUtil->getCost();

$str = sprintf("헤베 : %s / 헤베당 단가 : %s / 계산된 가격 : %s", $hebe, $cost, $price);

echo $str;
