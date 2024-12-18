<?
class EasyPay_Client
{
    var $deli_fs;
    var $deli_gs;
    var $deli_rs;
    var $deli_us;
    
    var $_req_data_cnt;
    
    var $gw_url;
    var $gw_port;
    var $home_dir;
    var $log_dir;
    var $log_level;
    var $cert_file;
    
    var $enc_data;
    var $snd_key;
    var $trace_no;
    
    var $_easypay_txreq;
    
    var $_easypay_reqdata;
    var $_easypay_resdata;
  
    function __construct() {
        $this->deli_fs = chr(28);
        $this->deli_gs = chr(29);
        $this->deli_rs = chr(30);
        $this->deli_us = chr(31);

        $this->gw_url    = "";
        $this->gw_port   = "";
        $this->home_dir  = "";
        $this->log_dir   = "";
        $this->log_level = "";
        $this->cert_file = "";
        $this->enc_data  = "";
        $this->snd_key   = "";
        
        $this->_req_data_cnt  = 0;
        $this->_easypay_txreq = "";
        $this->_easypay_reqdata = [];
        $this->_easypay_resdata = [];
    }

    function  clearup_msg(){
        $this->enc_data = "";
        $this->snd_key  = "";
        $this->trace_no = "";

        $this->_easypay_txreq = "";

        $this->_easypay_reqdata = [];
        $this->_easypay_resdata = [];
    }
  
    function set_gw_url($gw_url) {
        $this->gw_url = $gw_url;
    }

    function set_gw_port($gw_port) {
        $this->gw_port = $gw_port;
    }

    function set_home_dir($home_dir) {
        $this->home_dir = $home_dir;
    }
  
    function set_log_dir($log_dir) {
        $this->log_dir = $log_dir;
    }

    function set_log_level($log_level) {
        $this->log_level = $log_level;
    }
  
    function set_cert_file($cert_file) {
        $this->cert_file = $cert_file;
    }

    function set_enc_data($enc_data) {
        $this->enc_data = $enc_data;
    }
  
    function set_snd_key($snd_key) {
        $this->snd_key = $snd_key;
    }
  
    function set_trace_no($trace_no) {
        $this->trace_no = $trace_no;
    }
  
    function set_easypay_deli_fs($value) {
        if ($value != "" && strlen($value)) {
            $this->_easypay_txreq .= $value . $this->deli_fs;
        }
    }
  
    function set_easypay_deli_us($no, $key, $value) {
        if ($value != "" && strlen($value) != 0) {
            $this->_easypay_reqdata[$no][1] .= $key ."=" . $value . $this->deli_us;
        }
    }
  
    function set_easypay_deli_rs($idx1, $idx2) {
        $this->_easypay_reqdata[$idx1][1] .= $this->_easypay_reqdata[$idx2][0] . 
                                           "=" . 
                                           $this->_easypay_reqdata[$idx2][1] . 
                                           $this->deli_rs;
    }

    function set_easypay_deli_gs($no, $key, $value) {
        if ($value != "" && strlen($value) != 0) {
            $this->_easypay_reqdata[$no][1] .= $key ."=" . $value . $this->deli_gs;
        }
    }
  
    function set_easypay_item($data_name) {
        $i;
        for ($i = 0; $i < $this->_req_data_cnt; $i++)
            if ($this->_easypay_reqdata[$i][0] == $data_name)
                break;
      
        if ($i == $this->_req_data_cnt) {
            $this->_easypay_reqdata[$i][0] = $data_name;
            $this->_req_data_cnt++;
        }
        
        return $i;
    }

    function easypay_exec($mall_id, $tr_cd, $order_no, $cust_ip, $opt) {
      
        $this->set_tx_req_data($opt);

        $res_data = $this->mf_exec( $this->home_dir . "/bin/linux_64/ep_cli",
                                    "-h",
                                    "order_no="  . $order_no          . "," .
                                    "cert_file=" . $this->cert_file   . "," .
                                    "mall_id="   . $mall_id           . "," .
                                    "tr_cd="     . $tr_cd             . "," .
                                    "gw_url="    . $this->gw_url      . "," .
                                    "gw_port="   . $this->gw_port     . "," .
                                    "plan_data=" . $this->_easypay_txreq . "," .
                                    "enc_data="  . $this->enc_data    . "," .
                                    "snd_key="   . $this->snd_key     . "," .
                                    "trace_no="  . $this->trace_no    . "," .
                                    "cust_ip="   . $cust_ip           . "," .
                                    "log_dir="   . $this->log_dir     . "," .
                                    "log_level=" . $this->log_level   . "," .
                                    "opt="       . $opt               . "" );

        if ($opt == "UTF-8") {
            $res_data = iconv("euc-kr", "utf-8", $res_data);
        }
    
        if ( $res_data == "" )
        {
            $res_data = "res_cd=M114" . $this->deli_us . "res_msg=연동 모듈 실행 오류";
        }
          
        parse_str(str_replace($this->deli_us, "&", $res_data), $this->_easypay_resdata);
    }
  
    function get_easypay_item($data_name) {
        $i;
        $result;
      
        for ($i = 0; $i < $this->_req_data_cnt; $i++) {
            if ($this->_easypay_reqdata[$i][0] == $data_name) {
                $result = $data_name . "=" . $this->_easypay_reqdata[$i][1];
                break;
            }
        }
      
      return $result;
    }
  
    function set_tx_req_data($opt) {    
        if($opt == "UTF-8") {
            $pay_data = iconv( "utf-8", "euc-kr", $this->get_easypay_item("pay_data"));
            $order_data = iconv( "utf-8", "euc-kr", $this->get_easypay_item("order_data"));
            $escrow_data = iconv( "utf-8", "euc-kr", $this->get_easypay_item("escrow_data"));
            $mgr_data = iconv( "utf-8", "euc-kr", $this->get_easypay_item("mgr_data"));
            $cash_data = iconv( "utf-8", "euc-kr", $this->get_easypay_item("cash_data"));
        }
        else {
            $pay_data = $this->get_easypay_item("pay_data");
            $order_data = $this->get_easypay_item("order_data");
            $escrow_data = $this->get_easypay_item("escrow_data");
            $mgr_data = $this->get_easypay_item("mgr_data");
            $cash_data = $this->get_easypay_item("cash_data");
        }
          
        if($pay_data != "") {
            $this->set_easypay_deli_fs($pay_data) ;
        }
        if($order_data != "") {
            $this->set_easypay_deli_fs($order_data) ;
        }
        if($escrow_data != "") {
            $this->set_easypay_deli_fs($escrow_data) ;
        }
        if($mgr_data != "") {
            $this->set_easypay_deli_fs($mgr_data) ;
        }
        if($cash_data != "") {
            $this->set_easypay_deli_fs($cash_data) ;
        }    
    }
  
    function get_easypay_txreq() {
        return $this->_easypay_txreq;
    }

    function mf_exec() {
        $arg = func_get_args();

        if(is_array($arg[0])) {
            $arg = $arg[0];
        }

        while(list(,$i) = each($arg))
        {
            $exec_cmd .= " " . escapeshellarg( $i );
        }

        $res_data = exec( $exec_cmd );

        return  $res_data;
    }
    
    function get_remote_addr()
    {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $client_ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $client_ip;
    }
}
?>
