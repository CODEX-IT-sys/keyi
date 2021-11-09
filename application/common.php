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

use think\Collection;
use think\Request;
use think\Route;
use think\Db;

//header("content-Type: text/html; charset=Utf-8");

/**
 * 生成layui的table组件规定格式的数据
 *
 * @param \think\Paginator $list
 * @return array
 */
function generate_layui_table_data($list)
{
    return [
        'code'  => 0,
        'msg'   => '',
        'count' => $list->total(),
        'data'  => $list->getCollection(),
        //'sql' => Db::name('xt_dict')->getlastsql()
    ];
}

/*
 * // 词库 下拉多选值 公用方法
 * @param   int               $id             词汇分类ID
 * @param   array             $arr            选择值(数组值)
 * */
function dict($id, $arr=[])
{
    // 获取语言版本信息
    $language = session('language');

    if ($language == '中文'){
        $yy = Db::name('xt_dict')->field('id as value, cn_name as name, en_name as n')->where('c_id', $id)->select();
    }else{
        $yy = Db::name('xt_dict')->field('id as value, en_name as name, cn_name as n')->where('c_id', $id)->select();
    }

    if(!empty($arr)){

        foreach ($yy as $k => $v){
            if(in_array($v['name'], $arr) or in_array($v['n'], $arr)){
                $yy[$k]['selected'] = true;
            }
        }
    }

    return $yy;
}


/*
 * // 词库 下拉值 公用方法
 * @param   int               $id             词汇分类ID
 * */
function word($id)
{
    // 获取语言版本信息
    $language = session('language');

    if ($language == '中文'){
        $yy = Db::name('xt_dict')->field('cn_name as name')->where('c_id', $id)->select();
    }else{
        $yy = Db::name('xt_dict')->field('en_name as name')->where('c_id', $id)->select();
    }

    return $yy;
}



/*
 * 语言模板 判断
 *
 * */
function judge_language()
{
    if(session('language') == '中文'){
        echo 'cn';
    }else{
        echo 'en';
    }
}

// 文件上传(包含图片、音频、压缩文件)
function upload()
{
    // 文件对象
    $file = request()->file('file');

    // 文件信息
    //$i = $file->getInfo();

    // 获取文件后缀
    $temp = explode(".", $_FILES["file"]["name"]);
    $ext = end($temp);

    // 类型判断
    /*if(!in_array($ext,array("mp3","wav","aac","gif","jpeg","jpg","png","zip","rar","7z","pdf","doc","docx","xls","xlsx","xml"))){

        return json(['code' => 1, 'msg' => '上传失败，文件类型不合法']);
    }*/

    if ($file) {
        $info = $file->move('uploads', date('Ymd') . '/' .$_FILES["file"]["name"]);

        if ($info) {
            return json(['code' => 0, 'data' => '/uploads/' . $info->getSaveName(), 'msg' => '上传成功']);
        } else {
            return json(['code' => 1, 'data' =>'', 'msg' => $file->getError()]);
        }
    } else {
        return json(['code' => 1, 'data' =>'', 'msg' => '上传失败，请稍后再试']);
    }
}


// 读取xml 文件
function readxml($xml_path)
{

    // 文件路径
    //$xml_path = 'E:\www\ky\xml.xml';

    // 将整个文件内容读入到一个字符串中
    $str = file_get_contents($xml_path);

    // simpleXML
    $xml = simplexml_load_string($str);

    // 转为 json string
    $xmljson = json_encode($xml);
    //var_dump($xmljson);

    // 转为 数组
    $xmlarr = json_decode($xmljson,true);
    //var_dump($xmlarr);

    // file 数组
    //$file = $xmlarr['file'];
    //var_dump($file);

    // batchTotal 数组
    //$batchTotal = $xmlarr['batchTotal'];
    //var_dump($batchTotal);

    // 返回读取完成 需要的数据数组
    return $xmlarr;

    /*
     * 以下为原始节点取值实现的方法
     * */
    /*
            // 获取 file 元素
            $f = $xml->children()[1]->children()[0];

            // 获取 batchTotal 元素
            $b = $xml->children()[2]->children()[0];

            // 调试输出内容
            echo "file 如下：". "<br />";

            // 遍历子元素
            foreach($f as $child)
            {
                // 输出子元素名
                echo $child->getName() . "<br />";

                // 遍历 子元素 属性 及值
                foreach ($child->attributes() as $attributes)
                {
                    echo $attributes->getName() . ":" . $attributes. "<br />";
                }

                echo "<br />";
            }
    */

    /*
     * 以下为文件读取的方法
     * */
    /*
            // 1、逐行读取  文件内容转为数组
            $file = file('E:\www\ky\xml.xml');
            foreach($file as $line => $content){
                echo 'line '.($line + 1).':'.$content;
            }

            //读取二进制文件时，需要将第二个参数设置成'rb'
            $xml = fopen($xml_path, "r");
            //2、通过filesize获得文件大小，将整个文件一下子读到一个字符串中
            $contents = fread($xml, filesize ($xml_path));
            var_dump($contents);

            //3、将整个文件内容读入到一个字符串中
            $str = file_get_contents($xml_path);
            var_dump($str);
    */

}


/**
 * 查询表的所有字段
 *
 * @param  $table  string   数据库表名
 * @return array
 */
function getAllField($table)
{
    $sql = "SHOW FULL COLUMNS FROM $table";

    $r = Db::query($sql);

    return $r;
}

/*
 * 全表模糊搜索
 *
 * */
/*function searchAll($keyword)
{
    $sql = "SELECT * FROM tbl1
            WHERE 字段1 LIKE *CONCAT*('%',$keyword,'%')
            OR 字段2 LIKE CONCAT('%',$keyword,'%')
            OR 字段3 LIKE CONCAT('%',$keyword,'%')";

}*/


/*
 * 合同编码 C- + 公司编码 + 登记日期 生成
 * */
function contract_number($company_code)
{
    // 获取当前时间
    $now = date("Ymd");

    $code = 'C-' . $company_code . $now;

    return $code;
}

/*
 * 报价单编码 Q- + 公司编码 + 来稿日期 + 序号（01开始） 生成
 * */
function quote_number($company_code)
{

    $code = 'Q-' . $company_code;

    return $code;
}

/*
 * 请款单编码 I- + 公司编码 + 登记日期 + 序号（所有项目延续编号） 生成
 * */
function invoice_number($company_code)
{
    // 获取当前时间
    $now = date("Ymd");

    $code = 'I-' . $company_code . $now;

    return $code;
}

/*
 * 文件编码 F- + 日期 + 公司编号 + 序号（建议规则没有F） 生成
 * */
function filing_number($company_code, $no,$now='')
{
    // 获取当前时间
    if(!$now){
        $now = date("Ymd");
    }

    // 补位生成 序列单号
    $xh = str_pad(($no+1),2,0,STR_PAD_LEFT );

    // 拼接生成编码
    $code = 'F-' . $now . $company_code . $xh;

    return $code;
}

/*
 * 公司编码  生成
 * */
function company_code($str)
{

    $company_code = getSX($str);

    return $company_code;
}


/*
 * 获取字符串的 首字母缩写
 * */
function getSX($str)
{
    //$str = "科译达Codex Da";

    $str = "Codex yi Da ke ji gong shi";

    //$str = "重庆科译达科技有限公司";

    $szm = '';

    // 判断字符串类型
    switch (is_cn_or_en($str) ){

        case 'allen':
            //分割单词
            $arr = explode(' ', $str);
            // 获取数据长度
            $a_l = intval(count($arr));
            // 最多不超过5个
            if($a_l > 5){
                $a_l = 5;
            }
            // 遍历取每个单词的首字母
            for($i = 0; $i < $a_l; $i++){
                $szm .= getFirstCharter($arr[$i]);
            }
            break;

        case 'allcn':
            // 截取前五个字符
            for($i = 0; $i < 5; $i++){

                $str[$i] = substr($str, 0, $i+1);

                $szm .= getFirstCharter($str[$i]);
            }
            break;

        case 'encn':
            $szm = "不支持中英文混合命名";
    }

    return $szm;
}


// 获取首字母
function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});

    /*方法一*/
    $s1 = iconv('UTF-8',"gbk//ignore", $str);
    $s2 = iconv('GBK', 'UTF-8', $s1);

    /*方法二：需要先启用 mbstring 扩展库*/
    /*$s1 = mb_convert_encoding($str,"UTF-8","GBK");
    $s2 = mb_convert_encoding($s1,"GBK","UTF-8");*/

    $s = $s2 == $str ? $s1 : $str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';

    return null;
}


// 判断字符串是中文、英文或混合
function is_cn_or_en($s){

    $allen = preg_match("/^[^\x80-\xff]+$/", $s);   //判断是否是英文
    $allcn = preg_match("/^[\x7f-\xff]+$/", $s);  //判断是否是中文

    if($allen){//英文
        return 'allen';
    }else{
        if($allcn){//中文
            return 'allcn';
        }else{//中英混合
            return 'encn';
        }
    }
}