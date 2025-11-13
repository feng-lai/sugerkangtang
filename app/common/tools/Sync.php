<?php

namespace app\common\tools;

use app\api\model\User;
use think\Cache;

use think\Db;

use app\api\model\College;
use think\Exception;

class Sync
{
    private $url = 'https://api.bit.edu.cn/call/getToken';
    private $info_url = 'https://api.bit.edu.cn/call/b429';
    private $teacher_url = 'https://api.bit.edu.cn/call/b430';
    private $student_url = 'https://api.bit.edu.cn/call/b429';
    private $student_url2 = 'https://api.bit.edu.cn/call/b428';
    private $secretKey = 'bAeRSOQ4mrbDX5xaxEylYQ==';
    private $secretKey2 = 'KRR+MN+CiqDha47EheS/mQ==';
    private $loginName = 'LGH_209';
    private $loginName2 = 'zhpt_209';
    private $token = '';
    private $token2 = '';
    private $rows = 5000;
    private $page = 1;
    private $pages = 2;
    private $records = [];

    public static function build()
    {
        return new self();
    }

    /**
     * Author: Administrator
     * Date: 2025/2/20 0020
     * Time: 15:48
     * @param type type=1 student; type=2 teacher;
     */
    public function getData($type)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        echo json_encode(['result' => 'true']);
        fastcgi_finish_request();
        if ($type == 1) {
            //学生
            //研究生
            do {
                $this->getToken();
                $res = $this->post_url($this->student_url, http_build_query(['token' => $this->token, 'loginName' => $this->loginName, 'rows' => $this->rows, 'page' => $this->page]));
                $record = $res['records'];
                if($record){
                    foreach ($record as $k=>$v){
                        $record[$k]['托管学院'] = '研究生';
                    }
                }

                $this->pages = $res['pages'];

                $this->records = array_merge($this->records, (array)$record);
                $this->page++;
            } while ($this->pages >= $this->page);

            //本科生
            $this->page = 1;
            do {
                $this->getToken2();
                $res = $this->post_url($this->student_url2, http_build_query(['token' => $this->token2, 'loginName' => $this->loginName2, 'rows' => $this->rows, 'page' => $this->page]));

                $this->pages = $res['pages'];
                $this->records = array_merge($this->records, $res['records']);

                $this->page++;
            } while ($this->pages >= $this->page);
            $college = $this->get_college($type);

            $this->set_data($college, $type);

            $query = $this->arrayToInsertSql('user');


        } else {
            //教师
            do {
                $this->getToken();
                $res = $this->post_url($this->teacher_url, http_build_query(['token' => $this->token, 'loginName' => $this->loginName, 'rows' => $this->rows, 'page' => $this->page]));
                $this->pages = $res['pages'];
                $this->records = array_merge($this->records, $res['records']);
                $this->page++;
            } while ($this->pages >= $this->page);
            $college = $this->get_college($type);
            $this->set_data( $college, $type);
            $query = $this->arrayToInsertSql('admin');
        }

        Db::query($query);
        return true;
    }

    private function has($name){
        $college = [
            "精工书院"=>"精工书院",
            "机械与车辆学院"=>"精工书院",
            "机电学院"=>"精工书院",
            "宇航学院"=>"精工书院",
            "令闻书院"=>"令闻书院",
            "留学生中心"=>"令闻书院",
            "北京书院"=>"北京书院",
            "北京学院"=>"北京书院",
            "明德书院"=>"明德书院",
            "知艺书院"=>"明德书院",
            "管理学院"=>"明德书院",
            "外国语学院"=>"明德书院",
            "设计与艺术学院"=>"明德书院",
            "教育学院"=>"明德书院",
            "经济学院"=>"明德书院",
            "法学院"=>"明德书院",
            "管理与经济学院"=>"明德书院",
            "经管书院"=>"明德书院",
            "人文与社会科学学院"=>"明德书院",
            "求是书院"=>"求是书院",
            "材料学院"=>"求是书院",
            "医学技术学院"=>"求是书院",
            "生命学院"=>"求是书院",
            "数学与统计学院"=>"求是书院",
            "化学与化工学院"=>"求是书院",
            "物理学院"=>"求是书院",
            "睿信书院"=>"睿信书院",
            "自动化学院"=>"睿信书院",
            "光电学院"=>"睿信书院",
            "计算机学院"=>"睿信书院",
            "信息与电子学院"=>"睿信书院",
            "集成电路与电子学院"=>"睿信书院",
            "网络空间安全学院"=>"睿信书院",
            "特立书院"=>"特立书院",
            "徐特立学院"=>"特立书院"
        ];
        if($name == '研究生'){
            return '研究生';
        }
        if(array_key_exists($name,$college)){
            return $college[$name];
        }else{
            return '兜底';
        }
    }

    private function get_college($type)
    {
        $college = [];
        $text = '';
        $data = $this->records;
        foreach ($data as $k => $v) {
            if ($type == 1) {
                if ($v['托管学院']) {
                    $text = $this->has($v['托管学院']);
                }else{
                    $text = '兜底';
                }
            } else {
                //if ($v['机构名称']) {
                // $text = $v['机构名称'];
                //}
            }
            $data[$k]['托管学院'] = $text;
            if($text){
                $college[] = $text;
            }
        }
        $this->records = $data;
        $college = array_unique($college);
        $res = [];
        foreach ($college as $k => $v) {
            $uuid = College::build()->where('name', $v)->where('is_deleted',1)->value('uuid');
            if (!$uuid) {
                $uuid = uuid();
                College::build()->insert([
                    'name' => $v,
                    'uuid' => $uuid,
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time())
                ]);
            }
            $res[$v] = $uuid;
        }
        return $res;
    }


    private function set_data($college, $type)
    {
        $res = [];
        $data = $this->records;
        foreach ($data as $k => $v) {
            if ($type == 1) {
                $res[$k] = [
                    'uuid' => uuid(),
                    'name' => trim($v['姓名']),
                    'number' => trim($v['学号']),
                    'major' => isset($v['学科']) ? trim($v['学科']) : trim($v['专业名称']),
                    'grade' => trim($v['年级'])
                ];
                if ($v['托管学院']) {
                    $res[$k]['college_uuid'] = $college[trim($v['托管学院'])];
                } else {
                    $res[$k]['college_uuid'] = '';
                }
            } else {
                $res[$k] = [
                    'uuid' => uuid(),
                    'name' => trim($v['姓名']),
                    'number' => trim($v['工号'])
                ];
                //if ($v['机构名称']) {
                // $res[$k]['college_uuid'] = $college[trim($v['机构名称'])];
                //} else {
                //$res[$k]['college_uuid'] = '';
                //}
            }


            if ($v['性别']) {
                if ($v['性别'] == '男性') {
                    $res[$k]['gender'] = 1;
                }
                if ($v['性别'] == '女性') {
                    $res[$k]['gender'] = 2;
                }
                if ($v['性别'] == '未知的性别') {
                    $res[$k]['gender'] = 0;
                }
            } else {
                $res[$k]['gender'] = 0;
            }
        }
        $this->records = $res;
    }

    private function arrayToInsertSql($table)
    {
        $data = $this->records;
        $keys = array_keys($data[0]); // 获取所有列名

        $columns = implode(', ', $keys); // 列名组合成字符串

        $sql = "INSERT INTO ".$table." (".$columns.") VALUES ";

        $values = [];
        foreach ($data as $row) {
            $values[] = '(' . implode(', ', array_map(function ($value) {
                    return is_null($value) ? 'NULL' : "'$value'";
                }, $row)) . ')';
        }

        $sql .= implode(', ', $values);

        $end = [];
        foreach($keys as $v){
            $end[] = $v .'= values('.$v.')';
        }
        $end = implode(', ', $end);
        return $sql . ' ON duplicate KEY UPDATE '.$end;
    }

    public function getToken()
    {
        $token = Cache::get('sync_token');
        if ($token) {
            $this->token = $token;
            return $token;
        }
        $token = $this->post_url($this->url, http_build_query(['secretKey' => $this->secretKey, 'loginName' => $this->loginName]));

        if ($token) {
            Cache::set('sync_token', $token, 30 * 60);
        }
        $this->token = $token;
        return $token;
    }

    private function getInfo($userid){
        $token = $this->getToken();
        $res = $this->post_url($this->info_url, http_build_query(['token' => $token, 'loginName' => $this->loginName, 'IS_MAIN' => 1, 'DING_USERID' => $userid]));
        if($res && isset($res['records']) && isset($res['records']['学号']) && $res['records']['学号']){
            return $res['records']['学号'];
        }else{
            throw new Exception($res['msg'], 500);
        }
    }

    public function getToken2()
    {
        $token = Cache::get('sync_token2');
        if ($token) {
            $this->token2 = $token;
            return '';
        }
        $token = $this->post_url($this->url, http_build_query(['secretKey' => $this->secretKey2, 'loginName' => $this->loginName2]));

        if ($token) {
            Cache::set('sync_token2', $token, 30 * 60);
        }
        $this->token2 = $token;
        return '';
    }

    public function post_url($url, $jsonData, $header = '')
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 目标URL
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 返回结果而不是直接输出
            curl_setopt($ch, CURLOPT_POST, true); // 设置为POST请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // 设置POST字段为JSON格式
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            if ($data['code'] == 200) {
                return $data['data'];
            } else {
                return '';
            }

        } catch (Exception $e) {
            return null;
        }
    }
}