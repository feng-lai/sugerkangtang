<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use think\Config;
use think\Db;
use think\Request;
use app\api\model\SensitiveWord;
use app\api\model\Message;
use app\api\model\User;
use app\common\tools\SendMsg;


define('G_HTTP', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? "https://" : "http://");
define('G_HTTP_HOST', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "");
defined('PUBLIC_PATH') or define('PUBLIC_PATH', dirname(G_HTTP . G_HTTP_HOST . $_SERVER['SCRIPT_NAME']));
// 生成签名URL
function getSignedUrl($accessKeyId, $accessKeySecret, $endpoint, $bucket, $object)
{
    $expires = 3600; // URL有效时间，单位：秒
    $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
    $signedUrl = $ossClient->signUrl($bucket, $object, $expires, 'GET');
    return $signedUrl;
}

function calculateIncrease($today, $yesterday) {
    if ($yesterday == 0) {
        return $today > 0 ? "100%" : "0%";
    }
    $increase = (($today - $yesterday) / $yesterday) * 100;
    return round($increase, 2) . "%";
}

/**
 * Author: Administrator
 * Date: 2025/2/5 0005
 * Time: 14:04
 * @param $user_uuid
 * @param $order_uuid
 * @param $feel_uuid
 * @param $content
 */
function send_msg($user_uuid, $order_uuid, $feel_uuid, $content,$course_uuid)
{
    $msg = new Message();
    $msg->uuid = uuid();
    $msg->user_uuid = $user_uuid;
    $msg->order_uuid = $order_uuid;
    $msg->feel_uuid = $feel_uuid;
    $msg->content = $content;
    $msg->course_uuid = $course_uuid;
    $msg->save();
    SendMsg::build()->send(User::build()->where('uuid',$user_uuid)->value('number'),$content);
}

/**
 * @param $time
 * @return string
 * @throws Exception
 * 月份获取当月最后一天的时间
 */
function get_last_time($time)
{
    $date = new \DateTime($time."-01"); // 创建该月的第一天
    $date->modify('last day of this month'); // 修改到该月的最后一天
    $end = $date->format('Y-m-d').' 23:59:59';
    return $end;
}

function checkDateFormat($date) {
    // 检查是否包含日（YYYY-MM-DD 或 YYYY/MM/DD）
    if (preg_match('/^\d{4}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
        return 1; // 精确到日
    }
    // 检查是否仅到月（YYYY-MM 或 YYYY/MM）
    elseif (preg_match('/^\d{4}[-\/]\d{2}$/', $date)) {
        return 2; // 仅到月
    }
    return false; // 未知格式
}

function get_end_time($time)
{
    return date('Y-m-d',strtotime($time)).' 23:59:59';
}
function cut_date($start_time, $end_time,$type)
{
    $start_date = date('Y-m-d', $start_time);
    $end_date = date('Y-m-d', $end_time);

    $startDate = new DateTime(date('Y-m-d', $start_time));
    $endDate = new DateTime(date('Y-m-d', $end_time));
    $num = ceil(($end_time - $start_time) / 3600 / 24);
    $date = [];
    if ($type == 2) {
        //天
        for ($i = 0; $i < $num; $i++) {
            $sTime = $start_time + 3600 * 24 * $i;
            $sTime = date('Y-m-d', $sTime);
            $date[] = $sTime;
        }
        if ($end_date > end($date)) {
            $date[] = $end_date;
        }
        return ['data' => $date, 'type' => 2];
    }
    if ($type == 1) {
        // 修改后的按月生成日期逻辑
        $startYear = date('Y', $start_time);
        $startMonth = date('m', $start_time);
        $endYear = date('Y', $end_time);
        $endMonth = date('m', $end_time);

        $totalMonths = ($endYear - $startYear) * 12 + ($endMonth - $startMonth);

        for ($i = 0; $i <= $totalMonths; $i++) {
            $year = $startYear + intval($i / 12);
            $month = ($startMonth + ($i % 12)) % 12;
            $month = $month ?: 12; // 处理12月的情况
            $date[] = sprintf('%d-%02d', $year, $month);
        }
        return ['data' => $date, 'type' => 1];
    }
}
/**
 * 将一个数组转换为 XML 结构的字符串
 * @param array $arr 要转换的数组
 * @param int $level 节点层级, 1 为 Root.
 * @return string XML 结构的字符串
 */
function array2xml($arr, $level = 1)
{
    $s = $level == 1 ? "<xml>" : '';
    foreach ($arr as $tagname => $value) {
        if (is_numeric($tagname)) {
            $tagname = $value['TagName'];
            unset($value['TagName']);
        }
        if (!is_array($value)) {
            $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
        } else {
            $s .= "<{$tagname}>" . $this->array2xml($value, $level + 1) . "</{$tagname}>";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s . "</xml>" : $s;
}

/**
 * 将xml转为array
 * @param string $xml xml字符串
 * @return array    转换得到的数组
 */
function xml2array($xml)
{
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $result;
}


/**
 * 21000 App Store不能读取你提供的JSON对象
 * 21002 receipt-data域的数据有问题
 * 21003 receipt无法通过验证
 * 21004 提供的shared secret不匹配你账号中的shared secret
 * 21005 receipt服务器当前不可用
 * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
 * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
 * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
 */
function acurl($receipt_data, $sandbox = 0)
{
    //小票信息
    $POSTFIELDS = array("receipt-data" => $receipt_data);
    $POSTFIELDS = json_encode($POSTFIELDS);

    //正式购买地址 沙盒购买地址
    $url_buy = "https://buy.itunes.apple.com/verifyReceipt";
    $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
    $url = $sandbox ? $url_sandbox : $url_buy;

    //简单的curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// 设置HTTP头，强制下载
function setDownloadHeader($filename)
{
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Content-Type: application/octet-stream");
}

function remove_quote(&$str)
{
    if (preg_match("/^\"/", $str)) {
        $str = substr($str, 1, strlen($str) - 1);
    }
    //判断字符串是否以'"'结束
    if (preg_match("/\"$/", $str)) {
        $str = substr($str, 0, strlen($str) - 1);;
    }
    return $str;
}

function yc_phone($str)
{
    $str = $str;
    $resstr = substr_replace($str, '****', 3, 4);
    return $resstr;
}

/**
 * 导出Excel文件
 *
 * @param string $fileName 导出文件名
 * @param array $dataArr 导出数据
 * @param array $headArr 表头
 * @param array $filterArr 过滤数据
 * @return 导出Excel表格
 * @example
 *  down_load_excel(
 *      '用户列表',
 *      [['id'=>1,'name'=>'张三','sex'=0],['id'=>2,'name'=>'李四','sex'=1]],
 *      ['id'=>'编号','name'=>'姓名','sex'=>'性别'],
 *      ['sex'=>['男','女']])
 *
 * @author Yacon
 *
 */
function down_load_excel($fileName = '文件名', $dataArr = [], $headArr = [], $filterArr = [])
{
    $fileName .= '_' . date('YmdHis', time()) . '.xlsx';
    $objPHPExcel = new \PHPExcel();

    // 设置文档属性
    $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    // 设置头信息
    $objPHPExcel->setActiveSheetIndex(0);

    $key = ord('A');
    foreach ($headArr as $v) {
        $colum = chr($key);
        $objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth(25);
        $objPHPExcel->getActiveSheet()->setCellValue($colum . '1', $v);
        $key += 1;
    }

    // 设置数据
    $data = $dataArr;
    $column = 2;
    foreach ($data as $rows) {
        $span = ord("A");
        foreach ($headArr as $k => $v) {
            if (array_key_exists($k, $filterArr)) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit(chr($span) . $column, $filterArr[$k][$rows[$k] ?? 0], PHPExcel_Cell_DataType::TYPE_STRING);
            } else if (is_array($rows[$k])) {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit(chr($span) . $column, json_encode($rows[$k]), PHPExcel_Cell_DataType::TYPE_STRING);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValueExplicit(chr($span) . $column, $rows[$k], PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $span++;
        }
        $column++;
    }

    // 激活第一个sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    // 导出文件
    // $objWriter->save(str_replace('.php', '.xls', RUNTIME_PATH . $fileName));
    // return "导出成功: " . RUNTIME_PATH . "{$fileName}";

    // 浏览器下载
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header('Content-Disposition:attachment;filename=' . $fileName . '');
    header("Content-Transfer-Encoding:binary");

    $objWriter->save('php://output');
}

//敏感词检查
function sensitive_word_check($word)
{
    $res = SensitiveWord::build()->select();
    foreach ($res as $v) {
        if (preg_match('/' . $v->name . '/', $word)) {
            $star = '*';
            for ($i = 1; $i < iconv_strlen($v->name, 'utf-8'); $i++) {
                $star .= '*';
            }
            $word = str_replace($v->name, $star, $word);
        }
    }
    return $word;
}

/**
 * 获取指定日期所在月的第一天和最后一天
 * @author Yacon
 * @datetime 2021-12-09 01:36
 */
function month_range($date)
{
    $firstDay = date('Y-m-01', strtotime($date));
    $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
    return array($firstDay, $lastDay);
}

/**
 * 调试输出
 * @param unknown $data
 */
function print_data($data, $var_dump = false)
{
    header("Content-type: text/html; charset=utf-8");
    echo "<pre>";
    if ($var_dump) {
        var_dump($data);
    } else {
        print_r($data);
    }
    exit();
}

/**
 * 将对象转换为数组
 */
function objToArray($data)
{

    return json_decode(json_encode($data), true);
}


function curlSend($url)
{
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    } catch (\Exception $e) {
        return null;
    }
}


/**
 * 获取图片的Base64编码(不支持url)
 * @param string $img_file 传入本地图片地址
 * @return string
 */
function imgToBase64($img_file)
{

    $img_base64 = '';
    if (file_exists($img_file)) {
        $app_img_file = $img_file; // 图片路径
        $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等

        //echo '<pre>' . print_r($img_info, true) . '</pre><br>';
        $fp = fopen($app_img_file, "r"); // 图片是否可读权限

        if ($fp) {
            $filesize = filesize($app_img_file);
            $content = fread($fp, $filesize);
            $file_content = chunk_split(base64_encode($content)); // base64编码
            switch ($img_info[2]) {           //判读图片类型
                case 1:
                    $img_type = "gif";
                    break;
                case 2:
                    $img_type = "jpg";
                    break;
                case 3:
                    $img_type = "png";
                    break;
            }

            $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content; //合成图片的base64编码

        }
        fclose($fp);
    }

    return $img_base64; //返回图片的base64
}

function to_last_month($num, $today)
{
    $arr = array();
    $m = '-' . $num . ' month';
    $old_time = strtotime($m, strtotime($today));
    for ($i = 0; $i <= $num - 1; ++$i) {
        $t = strtotime("+$i month", $old_time);
        $arr[] = date('Y-m', $t);
    }
    //return $str = "'".str_replace( ",","','", implode(',',$arr))."'";
    return $arr;
}


function getDateNum()
{
    $str = date("Ymdhis", time()) . getNumber(8);
    return $str;
}

/**
 * 计算两个日期内的小时数
 */
function getHours($startdate, $enddate)
{

    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);

    // 计算日期段内有多少小时
    $hour = ceil(($etimestamp - $stimestamp) / 3600);

    return $hour;
}

/**
 * 获取范围内的所有日期
 */
function getDateFromRange($startdate, $enddate)
{
    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);

    // 计算日期段内有多少天
    $days = ($etimestamp - $stimestamp) / 86400;

    // 保存每天日期
    $date = array();

    for ($i = 0; $i < $days; $i++) {
        $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
    }

    return $date;
}


/**
 * @param $date 2019-12-11
 * @return string
 * 获取指定日期是星期几
 */
function getWeek($date)
{

    $weekarray = array("日", "一", "二", "三", "四", "五", "六");
    $str = "星期" . $weekarray[date("w", strtotime($date))];

    return $str;
}

/**
 * @param $date 2019-12-11
 * @return string
 * 获取指定日期是几号数字
 */
function getWeekNum($date)
{

    $weekarray = array("7", "1", "2", "3", "4", "5", "6");
    $str = $weekarray[date("w", strtotime($date))];

    return $str;
}

function showMonthRange($start, $end)
{
    $end = date('Y-m', strtotime($end)); // 转换为月
    $range = [];
    $i = 0;
    do {
        $month = date('Y-m', strtotime($start . ' + ' . $i . ' month'));
        //echo $i . ':' . $month . '<br>';
        $range[] = $month;
        $i++;
    } while ($month < $end);

    return $range;
}

function bank_code($bank_name)
{
    //银行编码
    switch ($bank_name) {

        case '中国工商银行':
            $result = "1002";
            break;
        case '中国农业银行':
            //全部
            $result = "1005";
            break;
        case '中国银行':
            //全部
            $result = "1026";
            break;
        case '中国建设银行':
            //全部
            $result = "1003";
            break;
        case '招商银行':
            //全部
            $result = "1001";
            break;
        case '中国邮政储蓄银行':
            //全部
            $result = "1066";
            break;
        case '交通银行':
            //全部
            $result = "1020";
            break;
        case '上海浦东发展银行':
            //全部
            $result = "1004";
            break;
        case '中国民生银行':
            //全部
            $result = "1006";
            break;
        case '兴业银行':
            //全部
            $result = "1009";
            break;
        case '平安银行':
            //全部
            $result = "1010";
            break;
        case '中信银行':
            //全部
            $result = "1021";
            break;
        case '华夏银行':
            //全部
            $result = "1025";
            break;
        case '广东发展银行':
            //全部
            $result = "1027";
            break;
        case '中国光大银行':
            //全部
            $result = "1022";
            break;
        case '北京银行':
            //全部
            $result = "1032";
            break;
        case '宁波银行':
            //全部
            $result = "1056";
            break;
        default:
            return false;
    }
    return $result;
}

/**
 * @param $appId
 * @param $secretKey
 *
 * @return \think\response\Json
 */
function assess_token($appId, $secretKey)
{
    $response = getCurlcomm("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$secretKey}");
    $response = json_decode($response);
    $access_token = $response->access_token;
    if ($access_token == null) {
        return json(["error" => "access token not found"], 403);
    }
    return $access_token;
}

function getCurlcomm($url)
{
    try {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curlHandle);
        curl_close($curlHandle);
        return $result;
    } catch (Exception $e) {
        return null;
    }
}

function now_time($time)
{
    $data = date("Y-m-d H:i:s", $time);
    return $data;
}

function begin_time()
{
    $data = date("Y-m-d H:i:s", 946659661);
    return $data;
}

/**
 * @param $Obj
 * @param $key
 *
 * @return string
 */
function getSign($Obj, $key)
{
    //    var_dump($Obj);//die;
    foreach ($Obj as $k => $v) {
        $Parameters[$k] = $v;
    }
    //签名步骤一：按字典序排序参数
    ksort($Parameters);
    $String = formatBizQueryParaMap($Parameters, false);
    //echo '【string1】'.$String.'</br>';
    //签名步骤二：在string后加入KEY
    $String = $String . "&key=" . $key;
    //echo "【string2】".$String."</br>";
    //签名步骤三：MD5加密
    $String = md5($String);
    //echo "【string3】 ".$String."</br>";
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    //echo "【result】 ".$result_."</br>";
    return $result_;
}

/**
 * @param $length
 *
 * @return string|null
 */
function getRandChar($length)
{
    $str = null;
    $strPol = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str = $str . $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/**
 * @param $length
 *
 * @return string|null
 */
function sqlGetRandChar($length)
{
    $str = null;
    $strPol = "0123456789ABCDEF";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str = $str . $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/**
 * 获取范围内的天数，不足一天算一天
 */
function day_count($startdate, $enddate)
{
    $stimestamp = strtotime($startdate);
    $etimestamp = strtotime($enddate);

    // 计算日期段内有多少天
    $days = ($etimestamp - $stimestamp) / 86400 + 1;
    return ceil($days);
}

//获取当月日期
function get_day($date)
{

    $j = date("t", strtotime($date)); //获取当前月份天数
    $start_time = strtotime(date('Y-m-01', strtotime($date))); //获取本月第一天时间戳
    $array = array();
    for ($i = 0; $i < $j; $i++) {
        $array[] = date('Y-m-d', $start_time + $i * 86400); //每隔一天赋值给数组
    }
    return $array;
}

function get_month($year)
{

    for ($i = 1; $i <= 12; $i++) {
        if ($i < 10) {
            $array[] = $year . "-0" . $i;
        } else {
            $array[] = $year . "-" . $i;
        }
    }
    return $array;
}

/**
 * @param $length
 *
 * @return string|null
 */
function getNumber($length)
{
    $str = null;
    $strPol = "0123456789";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str = $str . $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

function getOrderNumber()
{
    $str = date('YmdHis') . rand(100, 999);
    return $str;
}

/**
 * 获取指定长度的随机数字
 * @param $length
 *
 * @return string|null
 */
function getNumberOne($length)
{
    $str = null;
    $strPol = "123456789";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str = $str . $strPol[rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}


/**
 * 根据文件后缀获取表格对象并加载数据
 * @param string $ext 文件后缀
 * @param string $filePath 文件路径
 */
function getPHPExcelData(string $ext, string $filePath): array
{
    if ($ext == 'xlsx') {
        // 加载表格数据
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objExcel = $objReader->load($filePath);
    } else if ($ext == 'xls') {
        // 加载表格数据
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objExcel = $objReader->load($filePath);
    }

    if (isset($objExcel)) {

        $result = $objExcel->getSheet()->toArray();
        $data = [];
        foreach ($result as $item) {
            if (empty(array_filter($item))) {
                continue;
            }
            $data[] = $item;
        }

        return $data;
    } else {
        return [];
    }
}

/**
 * @param $paraMap
 * @param $urlencode
 *
 * @return bool|string
 */
function formatBizQueryParaMap($paraMap, $urlencode)
{
    //    var_dump($paraMap);//die;
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v) {
        if ($urlencode) {
            $v = urlencode($v);
        }
        //$buff .= strtolower($k) . "=" . $v . "&";
        $buff .= $k . "=" . $v . "&";
    }

    if (strlen($buff) > 0) {
        $reqPar = substr($buff, 0, strlen($buff) - 1);
    }
    //    var_dump($reqPar);//die;
    return $reqPar;
}

/**
 * @param int $len
 *
 * @return bool|string
 */
function randString($len = 4)
{
    $chars = str_repeat('0123456789', $len);
    $chars = str_shuffle($chars);
    $str = substr($chars, 0, $len);
    return $str;
}

/**
 * @param $data
 * @param $key
 *
 * @return string
 */
function des_ecb_encrypt($data, $key)
{
    return openssl_encrypt($data, 'des-ecb', $key);
}

/**
 * @param $data
 * @param $key
 *
 * @return string
 */
function des_ecb_decrypt($data, $key)
{
    return openssl_decrypt($data, 'des-ecb', $key);
}

/**
 * @param $data
 *
 * @return array
 */
function my_xml_parser($data)
{
    $p = xml_parser_create();
    xml_parse_into_struct($p, $data, $vals);
    xml_parser_free($p);
    $result = [];
    foreach ($vals as $item) {
        if ($item['tag'] == 'XML') {
            continue;
        }
        $result[$item['tag']] = $item['value'];
    }
    return $result;
}

/**
 * 计算经纬度之间的距离
 *
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 *
 * @return float
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000; //approximate radius of earth in meters
    $lat1 = ($lat1 * pi()) / 180;
    $lng1 = ($lng1 * pi()) / 180;
    $lat2 = ($lat2 * pi()) / 180;
    $lng2 = ($lng2 * pi()) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}

/**
 * @param     $arrays
 * @param     $sort_key
 * @param int $sort_order
 * @param int $sort_type
 *
 * @return bool
 */
function my_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
{
    //数组排序
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}

function xml_to_array($xml)
{
    $array = (array)(simplexml_load_string($xml));
    foreach ($array as $key => $item) {
        $array[$key] = struct_to_array((array)$item);
    }
    return $array;
}

function struct_to_array($item)
{
    if (!is_string($item)) {
        $item = (array)$item;
        foreach ($item as $key => $val) {
            $item[$key] = struct_to_array($val);
        }
    }
}

/**
 * @param $origin
 * @param $destination
 * @ 计算两地距离
 * @return bool|string
 */
function distance($origin, $destination)
{
    $url = "http://restapi.amap.com/v3/distance?key=e1729d87fb5cfd4213f3d0ebc3342c03&origins=$origin&destination=$destination";
    //    var_dump($url);die;
    $result = curl_send($url);
    //    $json=json_decode($result);
    return $result;
}


/**
 * 根据经纬度获取省市区等信息(腾讯接口)
 * @param {string} $lon 经度
 * @param {string} $lat 维度
 *
 *
 * function getCityByLongLatByQQ($lon, $lat)
 * {
 * if ($lon == '' || $lat == '') return '';
 * // 应用key
 * $key = 'VFYBZ-XX7LQ-DZN5N-GEFTT-4HCLZ-22FOV';
 * // 应用密钥
 * $sk = 'UZh0BuRZYQz1fJihusuWRfwLOdEfWMHi';
 * // 生产签名
 * $sig = md5("/ws/geocoder/v1?key={$key}&location={$lat},{$lon}{$sk}");
 * for ($i = 0; $i < 5; $i++) {
 * //获取微信accesstoken
 * $url = "https://apis.map.qq.com/ws/geocoder/v1?location={$lat},{$lon}&key=$key&sig=$sig";
 * $ch = curl_init();
 * curl_setopt($ch, CURLOPT_URL, $url);
 * curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 * curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 * $res = curl_exec($ch);
 * curl_close($ch);
 * $data = json_decode($res);
 * if ($data != null) {
 * break;
 * }
 * sleep(1);
 * }
 * return objToArray($data);
 * }
 */

function getCityByLongLatByBaidu($lng, $lat)
{
    // 构造请求参数
    $param['ak'] = 'yDbbxFiy2hbM0lBdCKhRsgaTmukC84UM';
    $param['output'] = 'json';
    $param['coordtype'] = 'gcj02ll';
    $param['ret_coordtype'] = 'gcj02ll';
    $param['extensions_poi'] = '0';

    $param['location'] = $lat . ',' . $lng;
    // 请求地址
    $url = 'https://api.map.baidu.com/reverse_geocoding/v3';
    if (empty($url) || empty($param)) {
        return false;
    }

    $getUrl = $url . "?" . http_build_query($param);
    $curl = curl_init(); // 初始化curl
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_URL, $getUrl); // 抓取指定网页
    curl_setopt($curl, CURLOPT_TIMEOUT, 1000); // 设置超时时间1秒
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // curl不直接输出到屏幕
    curl_setopt($curl, CURLOPT_HEADER, 0); // 设置header
    $data = curl_exec($curl); // 运行curl

    if (!$data) {
        print("an error occured in function request_get(): " . curl_error($curl) . "\n");
    }

    curl_close($curl);

    return json_decode($data, true);
}

function getCityByLongLatByQQ($lon, $lat)
{
    if ($lon == '' || $lat == '') return '';
    // 应用key
    $key = 'ZYABZ-TZPWI-KQYGR-UIQ4Y-USZXJ-55B2Y';

    for ($i = 0; $i < 5; $i++) {
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location={$lat},{$lon}&key=$key";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res);
        if ($data != null) {
            break;
        }
        sleep(1);
    }
    return objToArray($data);
}

function address_to_lat($address)
{
    // 请求地址
    $url = 'https://api.map.baidu.com/geocoding/v3';

    // 构造请求参数
    $param['address'] = $address;
    $param['output'] = 'json';
    $param['ak'] = 'yDbbxFiy2hbM0lBdCKhRsgaTmukC84UM';
    //$param['callback']   = 'showLocation';
    if (empty($url) || empty($param)) {
        return false;
    }

    $getUrl = $url . "?" . http_build_query($param);
    $curl = curl_init(); // 初始化curl
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_URL, $getUrl); // 抓取指定网页
    curl_setopt($curl, CURLOPT_TIMEOUT, 1000); // 设置超时时间1秒
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // curl不直接输出到屏幕
    curl_setopt($curl, CURLOPT_HEADER, 0); // 设置header
    $data = curl_exec($curl); // 运行curl

    if (!$data) {
        print("an error occured in function request_get(): " . curl_error($curl) . "\n");
    }

    curl_close($curl);

    return json_decode($data, true);
}

/**
 * @param $origin
 * @param $destination
 *
 * @return bool|string
 */
function latitude_longitude($origin, $destination)
{
    //经纬度
    $address = $origin . "|" . $destination;
    //    var_dump($address);die;
    $url = "http://restapi.amap.com/v3/geocode/geo?key=e1729d87fb5cfd4213f3d0ebc3342c03&address=$address&batch=true";
    $result = curl_send($url);
    //        var_dump($result);die;
    $json = json_decode($result);
    if (empty($json->infocode)) {
        return false;
    }
    //        var_dump($json);die;
    //        var_dump($json);die;
    if ($json->infocode == "10000") {
        //            var_dump($json);die;
        return $json->geocodes[0]->location . "|" . $json->geocodes[1]->location;
    } else {
        return false;
    }
}

function secToTime($times)
{
    $result = '00:00:00';

    $hour = floor($times / 3600);
    $minute = floor(($times - 3600 * $hour) / 60);
    $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);
    $result = $hour . ':' . $minute . ':' . $second;

    return $result;
}

/**
 * 账户密码加密
 * @param string $str password
 * @return string(32)
 */
function md6($str)
{
    $key = 'account_nobody';
    return md5(md5($str) . $key);
}

function mutime()
{
    $mtimestamp = sprintf("%.3f", microtime(true)); // 带毫秒的时间戳

    $timestamp = floor($mtimestamp); // 时间戳
    $milliseconds = round(($mtimestamp - $timestamp) * 1000); // 毫秒

    return $datetime = date("Y-m-d H:i:s", $timestamp) . '.' . $milliseconds;
    //    return $time=sprintf("%s => %s", $mtimestamp, $datetime);//1523856374.820 => 2018-04-16
}

function get_menu()
{
    return $GLOBALS['menu'] ?? "";
}

function get_token()
{
    //从协议头获取token
    $token = Request::instance()->header('X-Access-Token');
    //如果从header取不到token 则尝试从get参数中取 后台导出使用
    if (empty($token)) {
        $token = input('get.X-Access-Token');
    }
    return $token;
}

function log_file($content, $title = 'LOG', $filename = 'pos')
{
    //LOG
    try {
        $titleShow = (strlen($title) > 30) ? substr($title, 0, 27) . '...' : $title;
        $spaceNum = (66 - strlen($titleShow)) / 2;
        $titleShow = '=' . str_repeat(' ', intval($spaceNum)) . $titleShow . str_repeat(' ', ceil($spaceNum)) . '=';

        $time = date('Y-m-d H:i:s');
        $content = var_export($content, true);

        $logContent = <<<EOT
====================================================================
{$titleShow}
====================================================================
time:     {$time}
title:    {$title}
--------------------------------------------------------------------
content:  \n{$content}\n\n\n
EOT;

        $logPath = RUNTIME_PATH;
        $logName = $filename . date('Ymd') . '.log';
        if (!is_dir($logPath)) {
            mkdir($logPath);
        }
        $logFile = fopen($logPath . $logName, "a");
        fwrite($logFile, $logContent);
        fclose($logFile);
    } catch (Exception $e) {
        // do nothing
    }
}

/**
 * 替换字符串中间位置字符为星号
 * @param  [type] $str [description]
 * @return [type] [description]
 */
function replaceToStar($str)
{
    $len = strlen($str) / 2; //a0dca4d0****************ba444758]
    return substr_replace($str, str_repeat('*', $len), floor(($len) / 2), $len);
}

function abs_str($page, $limit)
{
    if (preg_match("/^[1-9][0-9]*$/", $page) && preg_match("/^[1-9][0-9]*$/", $page)) {
        return true;
    } else {
        return false;
    }
}

function access_token($appid, $secret)
{
    //获取微信accesstoken
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    //    var_dump($res);die;
    //    var_dump($res);die;
    //     var_dump($ch);die;
    curl_close($ch);
    $jsonArray = json_decode($res);
    //    var_dump($res);
    return $jsonArray;
    //    var_dump($jsonArray);
}

function get_client_ip()
{
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
}

function h5_access_token($id)
{
    //获取微信accesstoken
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxb7e13ea0208bc4c1&secret=b2b4a4085061d15241936c3114b6d9d6&code=$id&grant_type=authorization_code";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    $jsonArray = json_decode($res);
    return $jsonArray;
    //    var_dump($jsonArray);
}

function get_latitude_longitude($address, $key)
{
    //获取微信accesstoken
    $url = "https://apis.map.qq.com/ws/geocoder/v1/?address=$address&key=$key";
    $addressInfo = json_decode(curl_send($url), true);
    return $addressInfo;
}

function sendRequest($method, $url, $requestBody = "")
{
    $ch = curl_init($url);
    if ($method == "1") {
        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true,
        ));
    } elseif ($method == "2") {
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SAFE_UPLOAD, false,
            CURLOPT_POSTFIELDS => $requestBody

        ));
    }
    // Send the request
    //    var_dump(curl_getinfo($ch));die;
    $response = curl_exec($ch);
    // Check for errors
    if ($response === false) {
        die(curl_error($ch));
    }

    return $response;
}

function uuid($prefix = '')
{
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8);
    $uuid .= substr($chars, 8, 4);
    $uuid .= substr($chars, 12, 4);
    $uuid .= substr($chars, 16, 4);
    $uuid .= substr($chars, 20, 12);
    return $prefix . $uuid;
}

function object2array($object)
{
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    } else {
        $array = $object;
    }
    return $array;
}

function curl_send($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function getalphnum($char)
{
    $array = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
    $sum = 0;
    $len = strlen($char);
    for ($i = 0; $i < $len; $i++) {
        $index = array_search($char[$i], $array);
        $sum += ($index + 1) * pow(26, $len - $i - 1);
    }
    return $sum;
}

function postData($url, $postfields, $headers = [])
{
    $ch = curl_init();
    $params[CURLOPT_URL] = $url;    //请求url地址
    $params[CURLOPT_HEADER] = false; //是否返回响应头信息
    $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
    $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
    $params[CURLOPT_POST] = true;
    $params[CURLOPT_POSTFIELDS] = $postfields;
    $params[CURLOPT_SSL_VERIFYPEER] = false;
    $params[CURLOPT_SSL_VERIFYHOST] = false;
    //以下是证书相关代码
    //            var_dump($params)
    curl_setopt_array($ch, $params); //传入curl参数
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $content = curl_exec($ch); //执行
    //        var_dump(curl_error($ch));
    curl_close($ch); //关闭连接
    return $content;
}

//无限分类树
function getTreeList($data, $pid = 0)
{
    $resultarr = array();
    foreach ($data as $teamdata) {
        if ($teamdata['pid'] == $pid) {
            $team_data = $teamdata;
            $children_data = getTreeList($data, $teamdata['uuid']);
            $team_data['children'] = $children_data;
            $resultarr[] = $team_data;
        }
    }
    return $resultarr;
}

//获取剩余时间
function remainingTime($create_time)
{
    $current_time = time();

    if ($create_time > $current_time) {
        $dtCurrent = DateTime::createFromFormat('U', $current_time);
        $dtCreate = DateTime::createFromFormat('U', $create_time);
        $diff = $dtCurrent->diff($dtCreate);

        $interval = $diff->format("%d天%h小时");
        return preg_replace('/(^0| 0) (years|months|days|hours|minutes|seconds)/', '', $interval);
    }
    return '';
}

function identifyNoCreate($type = 1, $prefix = 'JD')
{
    $code = mt_rand(100, 999);
    $time = date('Ynd');
    return $prefix . $type . $time . $code;
}

function adminActionLog($menu_name, $msg, $userInfo = null, $explain = null)
{
    //    $json = [
    //        'menu_name' => $menu_name,
    //        'operation' => $msg ?? '-',
    //        'obj' => json_encode($_REQUEST),
    //        'admin_ip' => Visitor::getIP(),
    //        'explain' => $explain ?? '-'
    //    ];
    //    AdminActionLog::build()->add($json, getAdmin());
}

/**
 * @param $value
 * @return bool
 */
function parseBoolean($value)
{
    if (isset($value) == false) return null;

    if (is_bool($value)) return $value;

    if (is_string($value)) {
        $string = [
            'true' => true,
            'false' => false,
            '' => false,
            '1' => true,
            '0' => false
        ];

        if (in_array($value, $string) == false) return true;

        return $string[$value];
    }

    if (is_numeric($value)) {
        if ($value === 0) return false;
        return true;
    }

    return null;
}

//获取汉字的首字母
function getFirstCharters($str)
{
    $str = mb_convert_encoding($str, "gb2312"); //如果程序是gbk的，此行就要注释掉
    if (preg_match("/^[\x7f-\xff]/", $str)) //判断是否全是中文
    {
        $fchar = ord($str[0]);
        if ($fchar >= ord("A") and $fchar <= ord("z")) return strtoupper($str[0]);
        $a = $str;
        $val = ord($a[0]) * 256 + ord($a[1]) - 65536;
        if ($val >= -20319 and $val <= -20284) return "A";
        if ($val >= -20283 and $val <= -19776) return "B";
        if ($val >= -19775 and $val <= -19219) return "C";
        if ($val >= -19218 and $val <= -18711) return "D";
        if ($val >= -18710 and $val <= -18527) return "E";
        if ($val >= -18526 and $val <= -18240) return "F";
        if ($val >= -18239 and $val <= -17923) return "G";
        if ($val >= -17922 and $val <= -17418) return "H";
        if ($val >= -17417 and $val <= -16475) return "J";
        if ($val >= -16474 and $val <= -16213) return "K";
        if ($val >= -16212 and $val <= -15641) return "L";
        if ($val >= -15640 and $val <= -15166) return "M";
        if ($val >= -15165 and $val <= -14923) return "N";
        if ($val >= -14922 and $val <= -14915) return "O";
        if ($val >= -14914 and $val <= -14631) return "P";
        if ($val >= -14630 and $val <= -14150) return "Q";
        if ($val >= -14149 and $val <= -14091) return "R";
        if ($val >= -14090 and $val <= -13319) return "S";
        if ($val >= -13318 and $val <= -12839) return "T";
        if ($val >= -12838 and $val <= -12557) return "W";
        if ($val >= -12556 and $val <= -11848) return "X";
        if ($val >= -11847 and $val <= -11056) return "Y";
        if ($val >= -11055 and $val <= -10247) return "Z";
    } else {
        return false;
    }
}

//百家姓中的生僻字
function rare_words($asc = '')
{
    $rare_arr = array(
        -3652 => array('word' => "窦", 'first_char' => 'D'),
        -8503 => array('word' => "奚", 'first_char' => 'X'),
        -9286 => array('word' => "酆", 'first_char' => 'F'),
        -7761 => array('word' => "岑", 'first_char' => 'C'),
        -5128 => array('word' => "滕", 'first_char' => 'T'),
        -9479 => array('word' => "邬", 'first_char' => 'W'),
        -5456 => array('word' => "臧", 'first_char' => 'Z'),
        -7223 => array('word' => "闵", 'first_char' => 'M'),
        -2877 => array('word' => "裘", 'first_char' => 'Q'),
        -6191 => array('word' => "缪", 'first_char' => 'M'),
        -5414 => array('word' => "贲", 'first_char' => 'B'),
        -4102 => array('word' => "嵇", 'first_char' => 'J'),
        -8969 => array('word' => "荀", 'first_char' => 'X'),
        -4938 => array('word' => "於", 'first_char' => 'Y'),
        -9017 => array('word' => "芮", 'first_char' => 'R'),
        -2848 => array('word' => "羿", 'first_char' => 'Y'),
        -9477 => array('word' => "邴", 'first_char' => 'B'),
        -9485 => array('word' => "隗", 'first_char' => 'K'),
        -6731 => array('word' => "宓", 'first_char' => 'M'),
        -9299 => array('word' => "郗", 'first_char' => 'X'),
        -5905 => array('word' => "栾", 'first_char' => 'L'),
        -4393 => array('word' => "钭", 'first_char' => 'T'),
        -9300 => array('word' => "郜", 'first_char' => 'G'),
        -8706 => array('word' => "蔺", 'first_char' => 'L'),
        -3613 => array('word' => "胥", 'first_char' => 'X'),
        -8777 => array('word' => "莘", 'first_char' => 'S'),
        -6708 => array('word' => "逄", 'first_char' => 'P'),
        -9302 => array('word' => "郦", 'first_char' => 'L'),
        -5965 => array('word' => "璩", 'first_char' => 'Q'),
        -6745 => array('word' => "濮", 'first_char' => 'P'),
        -4888 => array('word' => "扈", 'first_char' => 'H'),
        -9309 => array('word' => "郏", 'first_char' => 'J'),
        -5428 => array('word' => "晏", 'first_char' => 'Y'),
        -2849 => array('word' => "暨", 'first_char' => 'J'),
        -7206 => array('word' => "阙", 'first_char' => 'Q'),
        -4945 => array('word' => "殳", 'first_char' => 'S'),
        -9753 => array('word' => "夔", 'first_char' => 'K'),
        -10041 => array('word' => "厍", 'first_char' => 'S'),
        -5429 => array('word' => "晁", 'first_char' => 'C'),
        -2396 => array('word' => "訾", 'first_char' => 'Z'),
        -7205 => array('word' => "阚", 'first_char' => 'K'),
        -10049 => array('word' => "乜", 'first_char' => 'N'),
        -10015 => array('word' => "蒯", 'first_char' => 'K'),
        -3133 => array('word' => "竺", 'first_char' => 'Z'),
        -6698 => array('word' => "逯", 'first_char' => 'L'),
        -9799 => array('word' => "俟", 'first_char' => 'Q'),
        -6749 => array('word' => "澹", 'first_char' => 'T'),
        -7220 => array('word' => "闾", 'first_char' => 'L'),
        -10047 => array('word' => "亓", 'first_char' => 'Q'),
        -10005 => array('word' => "仉", 'first_char' => 'Z'),
        -3417 => array('word' => "颛", 'first_char' => 'Z'),
        -6431 => array('word' => "驷", 'first_char' => 'S'),
        -7226 => array('word' => "闫", 'first_char' => 'Y'),
        -9293 => array('word' => "鄢", 'first_char' => 'Y'),
        -6205 => array('word' => "缑", 'first_char' => 'G'),
        -9764 => array('word' => "佘", 'first_char' => 'S'),
        -9818 => array('word' => "佴", 'first_char' => 'N'),
        -9509 => array('word' => "谯", 'first_char' => 'Q'),
        -3122 => array('word' => "笪", 'first_char' => 'D'),
        -9823 => array('word' => "佟", 'first_char' => 'T'),
    );
    if (array_key_exists($asc, $rare_arr) && $rare_arr[$asc]['first_char']) {
        return $rare_arr[$asc]['first_char'];
    } else {
        return null;
    }
}

/**
 * 当存在token 获取用户信息
 * @return array
 */
function getUser()
{
    $token = get_token();
    $user = [];
    if ($token) $user = \app\api\model\AppUserToken::build()->Vali($token);

    return $user ? $user : [];
}

/**
 * 当存在token 获取用户信息
 * @return array
 */
function getAdmin()
{
    $token = get_token();

    $bool = \app\api\model\CmsToken::build()->vali($token);
    return $bool ? $bool : [];
}

/**
 * 获取数据库前缀
 * @param string $table_name
 * @return string
 */
function prefix(string $table_name = ''): string
{
    return strval(Config::get('database.prefix') . $table_name);
}


/**
 * 通过剩余的时间戳获取倒计时
 * @param int $remain
 * @return string
 */
function countdown(int $remain): string
{
    $day = floor($remain / 60 / 60 / 24);

    $hour = floor(($remain % (60 * 60 * 24)) / 60 / 60);

    $min = floor((($remain % (60 * 60 * 24)) % (60 * 60)) / 60);

    return ($day > 0 ? $day . '天' : 0 . '天') . ($hour > 0 ? $hour . '时' : 0 . '时') . ($min > 0 ? $min . '分' : 0 . '分');
    // return ($day > 0 ?$day.'天':'').($hour>0 ?$hour.'时':'').($min>0 ?$min.'分':'');
    // return $day.'天' . $hour.'时' .  $min.'分';
}

/**
 * 时间范围转换时间戳数组
 * @param string $dataRange
 * @return array
 */
function dateRangeToTimestampArray(string $dataRange): array
{
    if (strstr($dataRange, ' - ') == false) return [];

    $date = explode(' - ', $dataRange);

    $date[0] = strtotime($date[0]);
    $date[1] = strtotime($date[1]);

    return $date;
}

function getTime($url)
{
    //获取视频重定向后的链接
    $location = locationUrl($url);
    //获取视频Content-Length
    $responseHead = get_data($location);
    $list1 = explode("Content-Length: ", $responseHead);
    $list2 = explode("Connection", $list1[1]);
    $list = explode("x", $list2[0]);
    return $list[0];
}

//获取视频重定向后的链接
function locationUrl($url)
{
    $url_parts = @parse_url($url);
    if (!$url_parts) return false;
    if (!isset($url_parts['host'])) return false;
    if (!isset($url_parts['path'])) $url_parts['path'] = '/';
    $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : '80'), $errno, $errstr, 30);
    if (!$sock) return false;
    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?' . $url_parts['query'] : '') . " HTTP/1.1\r\n";
    $request .= 'Host: ' . $url_parts['host'] . "\r\n";
    $request .= "Connection: Close\r\n\r\n";
    fwrite($sock, $request);
    $response = '';
    while (!feof($sock)) {
        $response .= fread($sock, 8192);
    }
    fclose($sock);
    if (preg_match('/^Location: (.+?)$/m', $response, $matches)) {
        if (substr($matches[1], 0, 1) == "/") {
            return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        } else {
            return trim($matches[1]);
        }
    } else {
        return false;
    }
}

//审核视频 curl
function get_data($url)
{
    $oCurl = curl_init();
    //模拟浏览器
    $header[] = "deo.com";
    $user_agent = "Mozilla/4.0 (Linux; Andro 6.0; Nexus 5 Build) AppleWeb/537.36 (KHTML, like Gecko)";
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($oCurl, CURLOPT_HEADER, true);
    curl_setopt($oCurl, CURLOPT_NOBODY, true);
    curl_setopt($oCurl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    // 不用 POST 方式请求, 意思就是通过 GET 请求
    curl_setopt($oCurl, CURLOPT_POST, false);
    $sContent = curl_exec($oCurl);
    // 获得响应结果里的：头大小
    $headerSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
    // 根据头大小去获取头信息内容
    $header = substr($sContent, 0, $headerSize);
    curl_close($oCurl);
    return $header;
}

function getAliPayOptions()
{
    $options = new \Alipay\EasySDK\Kernel\Config();
    $options->protocol = 'https';
    $options->gatewayHost = 'openapi.alipay.com';
    $options->signType = 'RSA2';

    $alipay = Config::get('alipay');
    $options->appId = $alipay['AppID'];

    // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
    $options->merchantPrivateKey = $alipay['rsaPrivateKey'];
    //'<-- 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt -->'
    $options->alipayCertPath = ROOT_PATH . 'public' . DS . 'certs' . DS . 'alipay' . DS . 'alipayCertPublicKey_RSA2.crt';
    //'<-- 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt" -->'
    $options->alipayRootCertPath = ROOT_PATH . 'public' . DS . 'certs' . DS . 'alipay' . DS . 'alipayRootCert.crt';
    //'<-- 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt -->'
    $options->merchantCertPath = ROOT_PATH . 'public' . DS . 'certs' . DS . 'alipay' . DS . 'appCertPublicKey_2021001186675244.crt';

    //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
    //$options->alipayPublicKey = $alipay['alipayPublicKey'];

    //可设置异步通知接收服务地址（可选）
    $options->notifyUrl = "";

    //可设置AES密钥，调用AES加解密相关接口时需要（可选）
    $options->encryptKey = "";


    return $options;
}

// 验证身份证号
function checkIdCard($id_card)
{
    if (empty($id_card)) {
        return false;
    }
    $idcard = $id_card;
    $City = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
    $iSum = 0;
    $idCardLength = strlen($idcard);
    //长度验证
    if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) and !preg_match('/^\d{15}$/i', $idcard)) {
        return '身份证不正确';
    }
    //地区验证
    if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
        return '身份证不正确';
    }
    // 15位身份证验证生日，转换为18位
    if ($idCardLength == 15) {
        $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
        //    $d = new DateTime($sBirthday);
        //    $dd = $d->format('Y-m-d');
        //    if($sBirthday != $dd)
        if ($sBirthday != $sBirthday) {
            return '身份证不正确';
        }
        $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9); //15to18
        $Bit18 = getVerifyBit($idcard); //算出第18位校验码
        $idcard = $idcard . $Bit18;
    }
    // 判断是否大于2078年，小于1900年
    $year = substr($idcard, 6, 4);
    if ($year < 1900 || $year > 2078) {
        return '身份证不正确';
    }

    //18位身份证处理
    $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
    //    var_dump($sBirthday);
    //    $d = new DateTime($sBirthday);

    //    $dd = $d->format('Y-m-d');
    //    echo $dd;
    //    die();
    //    if($sBirthday != $dd)
    if ($sBirthday != $sBirthday) {
        return '身份证不正确';
    }
    //身份证编码规范验证
    $idcard_base = substr($idcard, 0, 17);
    if (strtoupper(substr($idcard, 17, 1)) != getVerifyBit($idcard_base)) {
        return '身份证不正确';
    }
    return true;
}

// 计算身份证校验码，根据国家标准GB 11643-1999
function getVerifyBit($idcard_base)
{
    if (strlen($idcard_base) != 17) {
        return '身份证不正确';
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++) {
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}

function post_url($url, $jsonData)
{
    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;



    } catch (Exception $e) {
        return null;
    }
}

function get_url($url,$header){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    if (curl_errno($ch)) {
        return '';
    }
    curl_close($ch);
    $data = json_decode($output,true);
    if($data['code'] == 200){
        return $data['data']['platformList'];
    }else{
        return '';
    }
}

// 校验银行卡是否正确
function checkBankNo($bankNo)
{
    $api_url = "https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?cardNo={$bankNo}&cardBinCheck=true";
    $res = file_get_contents($api_url);
    $bankCard = json_decode($res, true);
    $bank = [
        "CDB" => "国家开发银行", "ICBC" => "中国工商银行", "ABC" => "中国农业银行", "BOC" => "中国银行", "CCB" => "中国建设银行", "PSBC" => "中国邮政储蓄银行", "COMM" => "交通银行", "CMB" => "招商银行", "SPDB" => "上海浦东发展银行", "CIB" => "兴业银行", "HXBANK" => "华夏银行", "GDB" => "广东发展银行", "CMBC" => "中国民生银行", "CITIC" => "中信银行", "CEB" => "中国光大银行", "EGBANK" => "恒丰银行", "CZBANK" => "浙商银行", "BOHAIB" => "渤海银行", "SPABANK" => "平安银行", "SHRCB" => "上海农村商业银行", "YXCCB" => "玉溪市商业银行", "YDRCB" => "尧都农商行", "BJBANK" => "北京银行", "SHBANK" => "上海银行", "JSBANK" => "江苏银行", "HZCB" => "杭州银行", "NJCB" => "南京银行", "NBBANK" => "宁波银行", "HSBANK" => "徽商银行", "CSCB" => "长沙银行", "CDCB" => "成都银行", "CQBANK" => "重庆银行", "DLB" => "大连银行", "NCB" => "南昌银行", "FJHXBC" => "福建海峡银行", "HKB" => "汉口银行",
        "WZCB" => "温州银行", "QDCCB" => "青岛银行", "TZCB" => "台州银行", "JXBANK" => "嘉兴银行", "CSRCB" => "常熟农村商业银行", "NHB" => "南海农村信用联社", "CZRCB" => "常州农村信用联社", "H3CB" => "内蒙古银行", "SXCB" => "绍兴银行", "SDEB" => "顺德农商银行", "WJRCB" => "吴江农商银行", "ZBCB" => "齐商银行", "GYCB" => "贵阳市商业银行", "ZYCBANK" => "遵义市商业银行", "HZCCB" => "湖州市商业银行", "DAQINGB" => "龙江银行", "JINCHB" => "晋城银行JCBANK", "ZJTLCB" => "浙江泰隆商业银行", "GDRCC" => "广东省农村信用社联合社", "DRCBCL" => "东莞农村商业银行", "MTBANK" => "浙江民泰商业银行", "GCB" => "广州银行", "LYCB" => "辽阳市商业银行", "JSRCU" => "江苏省农村信用联合社", "LANGFB" => "廊坊银行", "CZCB" => "浙江稠州商业银行", "DYCB" => "德阳商业银行", "JZBANK" => "晋中市商业银行", "BOSZ" => "苏州银行", "GLBANK" => "桂林银行", "URMQCCB" => "乌鲁木齐市商业银行", "CDRCB" => "成都农商银行",
        "ZRCBANK" => "张家港农村商业银行", "BOD" => "东莞银行", "LSBANK" => "莱商银行", "BJRCB" => "北京农村商业银行", "TRCB" => "天津农商银行", "SRBANK" => "上饶银行", "FDB" => "富滇银行", "CRCBANK" => "重庆农村商业银行", "ASCB" => "鞍山银行", "NXBANK" => "宁夏银行", "BHB" => "河北银行", "HRXJB" => "华融湘江银行", "ZGCCB" => "自贡市商业银行", "YNRCC" => "云南省农村信用社", "JLBANK" => "吉林银行", "DYCCB" => "东营市商业银行", "KLB" => "昆仑银行", "ORBANK" => "鄂尔多斯银行", "XTB" => "邢台银行", "JSB" => "晋商银行", "TCCB" => "天津银行", "BOYK" => "营口银行", "JLRCU" => "吉林农信", "SDRCU" => "山东农信", "XABANK" => "西安银行", "HBRCU" => "河北省农村信用社", "NXRCU" => "宁夏黄河农村商业银行", "GZRCU" => "贵州省农村信用社", "FXCB" => "阜新银行", "HBHSBANK" => "湖北银行黄石分行", "ZJNX" => "浙江省农村信用社联合社", "XXBANK" => "新乡银行", "HBYCBANK" => "湖北银行宜昌分行",
        "LSCCB" => "乐山市商业银行", "TCRCB" => "江苏太仓农村商业银行", "BZMD" => "驻马店银行", "GZB" => "赣州银行", "WRCB" => "无锡农村商业银行", "BGB" => "广西北部湾银行", "GRCB" => "广州农商银行", "JRCB" => "江苏江阴农村商业银行", "BOP" => "平顶山银行", "TACCB" => "泰安市商业银行", "CGNB" => "南充市商业银行", "CCQTGB" => "重庆三峡银行", "XLBANK" => "中山小榄村镇银行", "HDBANK" => "邯郸银行", "KORLABANK" => "库尔勒市商业银行", "BOJZ" => "锦州银行", "QLBANK" => "齐鲁银行", "BOQH" => "青海银行", "YQCCB" => "阳泉银行", "SJBANK" => "盛京银行", "FSCB" => "抚顺银行", "ZZBANK" => "郑州银行", "SRCB" => "深圳农村商业银行", "BANKWF" => "潍坊银行", "JJBANK" => "九江银行", "JXRCU" => "江西省农村信用", "HNRCU" => "河南省农村信用", "GSRCU" => "甘肃省农村信用", "SCRCU" => "四川省农村信用", "GXRCU" => "广西省农村信用", "SXRCCU" => "陕西信合", "WHRCB" => "武汉农村商业银行", "YBCCB" => "宜宾市商业银行",
        "KSRB" => "昆山农村商业银行", "SZSBK" => "石嘴山银行", "HSBK" => "衡水银行", "XYBANK" => "信阳银行", "NBYZ" => "鄞州银行", "ZJKCCB" => "张家口市商业银行", "XCYH" => "许昌银行", "JNBANK" => "济宁银行", "CBKF" => "开封市商业银行", "WHCCB" => "威海市商业银行", "HBC" => "湖北银行", "BOCD" => "承德银行", "BODD" => "丹东银行", "JHBANK" => "金华银行", "BOCY" => "朝阳银行", "LSBC" => "临商银行", "BSB" => "包商银行", "LZYH" => "兰州银行", "BOZK" => "周口银行", "DZBANK" => "德州银行", "SCCB" => "三门峡银行", "AYCB" => "安阳银行", "ARCU" => "安徽省农村信用社", "HURCB" => "湖北省农村信用社", "HNRCC" => "湖南省农村信用社", "NYNB" => "广东南粤银行", "LYBANK" => "洛阳银行", "NHQS" => "农信银清算中心", "CBBQS" => "城市商业银行资金清算中心"
    ];
    if (!$bankCard['validated']) return ['check' => false, 'bank_name' => ''];
    $bank_name = $bank[$bankCard['bank']];
    return ['check' => true, 'bank_name' => $bank_name];
}

/**
 * 返回阿里云oss资源的完整地址
 * @param string $key
 * @return string
 */
function oss_url(string $key)
{
    $config = Config::get('alioss');

    return $config['url'] . $key;
}

/**
 * 获取平台im账号
 */
function getPlatformImUsername(): string
{
    $value = \app\api\model\Config::where('label', 'service_im_username')->value('value');

    return strval($value);
}

/**
 * 获取商家的im username
 * @param string $merchant_uuid
 * @return string
 */
function getUsernameByMerchantUuid(string $merchant_uuid): string
{
    $value = \app\api\model\MerchantMain::where('uuid', $merchant_uuid)->value('user_uuid');
    if (empty($value)) return getPlatformImUsername();

    $value = \app\api\model\UserIM::where('user_uuid', $value)->value('username');

    return strval($value);
}


/**
 * 缓存消息阅读状态
 * @param string $user_uuid
 * @param string $type
 * @param bool $status 已读|未读
 */
function setMessageReadStatus(string $user_uuid, string $type, bool $status = false)
{
    $read = \think\Cache::get($user_uuid . '_message_read');
    $read[$type] = $status;

    \think\Cache::set($user_uuid . '_message_read', $read);
}


/**
 * 读取缓存中的消息阅读状态
 * @param string $user_uuid
 * @param string $type
 * @return bool
 */
function getMessageReadStatus(string $user_uuid, string $type): bool
{
    $status = \think\Cache::get($user_uuid . '_message_read');

    return boolval($status[$type] ?? false);
}

function getLocation(string $address)
{
    $ak = 'jd2EYPH4uTp6Pij4rhArQSO24y41jLMn';

    $url = 'http://api.map.baidu.com/geocoding/v3/?address=' . $address . '&output=json&ak=' . $ak . '&callback=showLocation';
    $result = file_get_contents($url);

    $result = json_decode(substr($result, strpos($result, '{'), strrpos($result, ')') - strlen($result)), true);

    if ($result['status'] == 1) throw new \think\exception\HttpException(400, '邮寄地址输入有误');
    return $result['result']['location'];
}

/**
 * 验证姓名及身份证是否一致
 * @param $name
 * @param $id
 */
function verifyIdCard($name, $id)
{
    $url = "https://eid.shumaidata.com/eid/check";
    $appcode = "dd215ae3a7ed400fa6acc09f2629d449";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);

    $result = postData($url, ['idcard' => $id, 'name' => $name], $headers);
    $result = json_decode($result, true);
    if ($result['code'] != 0) return ['msg' => $result['message']];
    if ($result['code'] == 0 && $result['result']['res'] != 1) return ['msg' => '身份证号不一致'];
    return true;
}


/*
 * oss路径
 * */
function ossBaseUrl()
{
    return config('oss_url');
}

/*
 * 校验手机号
 */
function checkMobile($mobile)
{
    return preg_match("/^1[3456789]\d{9}$/", $mobile);
}

/*
 * logic返回
 */
function logicSuccess($msg = "", $data = [])
{
    return logicResp(0, $msg, $data);
}

function logicError($msg = "", $data = [])
{
    return logicResp(1, $msg, $data);
}

function logicResp($code = 0, $msg = "", $data = [])
{
    return ['code' => $code, 'msg' => $msg, 'data' => $data];
}

function isLogicSuccess($result)
{
    if (is_array($result) && isset($result['code']) && $result['code'] == 0) {
        return true;
    } else {
        return false;
    }
}

function getLogicMsg($result)
{
    if (is_array($result) && isset($result['msg'])) {
        return $result['msg'];
    } else {
        return 'error:获取错误信息失败';
    }
}

function getLogicData($result)
{
    if (is_array($result) && isset($result['data'])) {
        return $result['data'];
    } else {
        return null;
    }
}

function getExceptionErrorMsg($msg, \Exception $e = null)
{
    return $msg . " error:" . $e->getMessage();
}

/*
 * 阿里云短信发送
 */
function smsSend($type, $mobile, $data)
{
    AlibabaCloud::accessKeyClient(config('sms.access_key_id'), config('sms.access_key_secret'))
        ->regionId('cn-hangzhou')
        ->asDefaultClient();
    $template_code = config('sms.template_code');

    try {
        $result = AlibabaCloud::rpc()
            ->product('Dysmsapi')
            ->version('2017-05-25')
            ->action('SendSms')
            ->method('POST')
            ->host('dysmsapi.aliyuncs.com')
            ->options([
                'query' => [
                    'RegionId' => "cn-hangzhou",
                    'PhoneNumbers' => $mobile,
                    'SignName' => config('sms.sign_name'),
                    'TemplateCode' => $template_code[$type],
                    'TemplateParam' => json_encode($data),
                ],
            ])
            ->request()
            ->toArray();
        if (isset($result) && $result['Code'] == 'OK') {
            return logicSuccess("发送成功");
        } else {
            return logicError("发送失败 error:" . $result['Message']);
        }
    } catch (ClientException $e) {
        return logicError("发送失败 error:" . $e->getErrorMessage());
    } catch (ServerException $e) {
        return logicError("发送失败 error:" . $e->getErrorMessage());
    }
}

function miniAccessToken()
{
    $mini = config('wx.mini');
    $redis = getRedis();
    $access_token = $redis->get('cache:' . $mini['appid'] . ':access_token');
    if (!empty($access_token)) {
        return $access_token;
    }
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $mini['appid'] . "&secret=" . $mini['secret'];
    $data = @json_decode(@file_get_contents($url), true);
    if (!empty($data['access_token'])) {
        $redis->set('cache:' . $mini['appid'] . ':access_token', $data['access_token'], 1800);
        return $data['access_token'];
    } else {
        return '';
    }
}

function wxSubscribeNoticeSend($type, $openid, $data, $page = '')
{
    $subscribe_notice = config('wx.subscribe_notice');
    $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . miniAccessToken();
    $tmp_data = [];
    foreach ($data as $k => $v) {
        $tmp_data[$k] = [
            'value' => $v,
        ];
    }
    $request = ['touser' => $openid, 'template_id' => $subscribe_notice[$type], 'data' => $tmp_data];
    !empty($page) && $request['page'] = $page;
    $result = @json_decode(curl_post($url, @json_encode($request, JSON_UNESCAPED_UNICODE)), true);
    if ($result['errcode'] == 0) {
        return logicSuccess("发送成功");
    } else if ($result['errcode'] == 43101) {
        return logicError("发送失败 error:用户未订阅,或拒收通知");
    } else {
        return logicError("发送失败 error:错误码" . $result['errcode']);
    }
}

/**
 * 生成编号
 * @Author   CCH
 * @DateTime 2020-05-23T11:59:13+0800
 * @return   编号
 */
function numberCreate()
{
    return date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);
}


/*
 * 实例化redis返回
 */
function getRedis($is_origin = false)
{
    $options = config('cache.redis');
    $options_base = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
    ];
    $options = array_merge($options_base, $options);
    if ($is_origin) {
        $redis = new \Redis;
        if ($options['persistent']) {
            $redis->pconnect($options['host'], $options['port'], $options['timeout'], 'persistent_id_' . $options['select']);
        } else {
            $redis->connect($options['host'], $options['port'], $options['timeout']);
        }
        if ('' != $options['password']) {
            $redis->auth($options['password']);
        }
        if (0 != $options['select']) {
            $redis->select($options['select']);
        }
        return $redis;
    } else {
        return new \think\cache\driver\Redis($options);
    }
}

/*
 * 获取当前日期
 */
function getDateTime($time = '')
{
    if (empty($time)) {
        return date('Y-m-d H:i:s');
    }
    return date('Y-m-d H:i:s', $time);
}

/*
 * 获取图片完整地址
 */
function getImageUrl($path)
{
    //    return ossBaseUrl() . $path;
    return $path;
}

/*
 * 获取oss path
 */
function getOssPath()
{
    return config('oss_path');
}

/*
 * 格式化为两位小数展示
 */
function fmtNumberTwo($num)
{
    return sprintf("%.2f", $num);
}

/*
 * 隐藏手机号中间四位
 */
function hideMobileCenterFour($mobile)
{
    if (empty($mobile)) {
        return '';
    }
    return substr_replace($mobile, '****', 3, 4);
}

/*
 * 导出excel表格
 */
function excelExprot($name, $title, $content)
{
    $newExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();  //创建一个新的excel文档
    $objSheet = $newExcel->getActiveSheet();  //获取当前操作sheet的对象
    $objSheet->setTitle('sheet1');  //设置当前sheet的标题
    $word = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE", "AF", "AG", "AH", "AI", "AJ", "AK", "AL", "AM", "AN", "AO"];

    //设置title
    foreach ($title as $k => $v) {
        $objSheet->setCellValue($word[$k] . '1', $v);
    }
    //设置内容
    $num = 2;
    foreach ($content as $each) {
        foreach ($each as $k => $v) {
            $objSheet->setCellValue($word[$k] . $num, ' ' . $v);
        }
        $num++;
    }
    ob_end_clean(); //清除缓冲区,避免乱码
    // excel头参数
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=" . $name . '.xlsx');
    header('Cache-Control: max-age=0');

    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $name . '.xlsx"');

    header("Content-Disposition:attachment;filename=$name.xlsx");

    $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($newExcel, 'Xlsx');

    $objWriter->save('php://output');
    exit();
}

/*
 * 导入excel表格
 */
function excelImport($file_path, $pindex = 0)
{
    $reader = \PHPExcel_IOFactory::createReader('Excel2007');
    //载入excel文件
    $excel = $reader->load($file_path, $encode = 'utf-8');
    //读取第一张表
    $sheet = $excel->getSheet($pindex);
    if (empty($sheet)) {
        return [];
    }
    $data = $sheet->toArray();
    foreach ($data as $k => $v) {
        if ($k == 0) {
            unset($data[$k]); //删除标题
        }
    }
    return array_values($data);
}

//请求过程中因为编码原因+号变成了空格
//需要用下面的方法转换回来
function define_str_replace($data)
{
    return str_replace(' ', '+', $data);
}

/**
 * 发送HTTP请求方法
 */
function httpCurl($url, $params, $method = 'POST', $header = array(), $multi = false)
{
    date_default_timezone_set('PRC');
    $opts = array(
        CURLOPT_TIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_COOKIESESSION => true,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_COOKIE => session_name() . '=' . session_id(),
    );
    /* 根据请求类型设置特定参数 */
    switch (strtoupper($method)) {
        case 'GET':
            // $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            // 链接后拼接参数  &  非？
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) throw new Exception('请求发生错误：' . $error);
    return $data;
}

/**
 * 微信信息解密
 * @param string $appid 小程序id
 * @param string $sessionKey 小程序密钥
 * @param string $encryptedData 在小程序中获取的encryptedData
 * @param string $iv 在小程序中获取的iv
 * @return array 解密后的数组
 */
function decryptData($appid, $sessionKey, $encryptedData, $iv)
{
    $OK = 0;
    $IllegalAesKey = -41001;
    $IllegalIv = -41002;
    $IllegalBuffer = -41003;
    $DecodeBase64Error = -41004;

    if (strlen($sessionKey) != 24) {
        return $IllegalAesKey;
    }
    $aesKey = base64_decode($sessionKey);

    if (strlen($iv) != 24) {
        return $IllegalIv;
    }
    $aesIV = base64_decode($iv);

    $aesCipher = base64_decode($encryptedData);

    $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
    $dataObj = json_decode($result);
    if ($dataObj == NULL) {
        return $IllegalBuffer;
    }
    if ($dataObj->watermark->appid != $appid) {
        return $DecodeBase64Error;
    }
    $data = json_decode($result, true);

    return $data;
}

//获取当前请求带协议头的域名  例如https://www.baidu.com
function domain_name()
{
    //    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    return "https://$_SERVER[HTTP_HOST]";
}

function plog($content)
{
    Db::name('log')->insert([
        'content' => $content,
        'create_time' => time(),
    ]);
}

//post json
function curl_post($url = '', $postdata = '', $header = [])
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

//发送邮件
function send_email($title, $content, $email_address)
{
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        //服务器配置
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();
        $mail->Host = 'smtp.qq.com'; //SMTP服务器域名
        $mail->SMTPAuth = true;
        $mail->Username = config('email')['username']; //用户名
        $mail->Password = config('email')['password']; //密码
        $mail->SMTPSecure = 'ssl'; //可选参数tls ssl
        $mail->Port = '465'; //一般无加密25 ssl465 tls 587 具体看服务商端口
        $mail->setFrom(config('email')['username'], '扬师傅物流平台'); //发件人
        $mail->addAddress($email_address); //收件人
        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body = $content;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function generateAvatar($text1,$text2)
{
    // 设置图片的尺寸（正方形）
    $width = 200;
    $height = $width;

    // 创建一个真彩色图像
    $image = imagecreatetruecolor($width, $height);

    // 分配颜色（例如：蓝色背景）
    $backgroundColor = imagecolorallocate($image, 62, 147, 255);
    // 填充背景色
    imagefill($image, 0, 0, $backgroundColor);

    // 设置字体颜色和字体路径（请确保字体文件路径正确）
    $fontColor = imagecolorallocate($image, 255, 255, 255); // 白色字体
    $fontPath = ROOT_PATH . 'public/fonts/simhei.ttf';

    // 计算文字的边界框（用于确定文字的位置）
    $bbox1 = imagettfbbox(50, 0, $fontPath, $text1);
    $bbox2 = imagettfbbox(50, 0, $fontPath, $text2);

    // 计算文字起始位置，使文字居中显示（这里简单处理，可能需要根据实际字体调整）
    $textX1 = ($width - ($bbox1[2] - $bbox1[0])) / 2 -40 ;
    $textY1 = ($height - ($bbox1[1] - $bbox1[7])) / 2 +50 ;// 稍微调整Y坐标以垂直居中

    $textX2 = ($width - ($bbox2[2] - $bbox2[0])) / 2 + 40;
    $textY2 = $textY1; // 在第一个文字下方添加第二个文字，并留出一些间隔

    // 添加文字到图像上
    imagettftext($image, 50, 0, $textX1, $textY1, $fontColor, $fontPath, $text1);
    imagettftext($image, 50, 0, $textX2, $textY2, $fontColor, $fontPath, $text2);

    if(!file_exists('upload/'.date('Ymd'))){
        mkdir('upload/'.date('Ymd'));
    }
    $path = 'upload/'.date('Ymd').'/'.uuid().'.jpg';

    // 设置保存路径
    $savePath = ROOT_PATH . 'public/'.$path;

    // 保存图片到本地
    imagepng($image, $savePath);

    // 销毁图像资源
    imagedestroy($image);

    // 返回成功信息（或你可以返回图片路径等其他信息）
    return $path;
}

function print_qr_code($content)
{
    require_once __DIR__ . '/extra/phpqrcode.php';
    QRcode::png($content, false, 'L', 5, 2);
    exit();
}

function getMemberId($member_id)
{
    return config('member_id_prefix') . str_pad($member_id, config('member_id_prefix_length'), '0', STR_PAD_LEFT);
}

function memberIdOriginal($member_id)
{
    return ltrim(ltrim($member_id, config('member_id_prefix')), 0);
}

function nameUnder2Hump($underStr, $firstCapital = false)
{
    $arr = explode('_', $underStr);
    $arr = array_map(function ($str) {
        return ucfirst($str);
    }, $arr);
    $hump = implode('', $arr);
    return $firstCapital ? $hump : lcfirst($hump);
}

function findItemByList($value, $list = [], $key = 'uuid', $field = null, $default = null)
{
    if (!is_array($list) && !($list instanceof \think\Collection)) return $default;
    foreach ($list as $index => $item) {
        $type = gettype($item);
        if (($type === 'object' && $item->$key == $value) || ($type === 'array' && $item[$key] == $value)) {
            return isset($field) ? ($type === 'object' ? $item->$field : $item[$field]) : $item;
        }
    }
    return $default;
}
