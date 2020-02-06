<?php
ini_set('memory_limit', '1024M');
// $data = @file_get_contents('https://data.nhi.gov.tw/resource/mask/maskdata.csv');


$url = 'https://data.nhi.gov.tw/resource/mask/maskdata.csv';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);


$data = explode("\n", $data);

array_shift($data);

$columns = [
    'id',
    'name',
    'address',
    'phone',
    'adult',
    'child',
    'ctime'
];

$info = [];
$ctime = "";

foreach ($data AS $item) {
    $item = str_replace(["\r\n", "\r", "\n"], '', $item);
    if (!empty($item)) {
        $items = explode(",", $item);
        $new_item = [];
        foreach ($items AS $index => $val) {
            $new_item[$columns[$index]] = $val;
        }
        $info[$new_item["id"]] = $new_item;
        $ctime = $new_item["ctime"];
    }
}


$update_date = date("Y_m_d", strtotime($ctime));
$update_time = date("H_i_s", strtotime($ctime));


$info_json = json_encode($info, JSON_UNESCAPED_UNICODE);

$history_path ="./log/history/{$update_date}";
if (!is_dir($history_path)) {
    mkdir($history_path, 0777, true);
}


$f = fopen($history_path."/{$update_time}.log", "w");
fwrite($f, $info_json);
fclose($f);


$f = fopen("./log/current.log", "w");
fwrite($f, $info_json);
fclose($f);


$directories = scandir($history_path, 0);
$summary = [];

foreach ($directories AS $dir_name) {
    if (!in_array($dir_name, [".", "..", "summary.log"])) {
        $json = file_get_contents($history_path . "/" .$dir_name);
        $json = json_decode($json, true);
        foreach ($json AS $key => $data) {
            if (!isset($summary[$key])) {
                $summary[$key] = [
                    "adult" => [],
                    "child" => [],
                ];
            }

            /* 資料壓縮，跟上一次的值一樣 就不紀錄 */
            foreach (['adult', 'child'] AS $target_key) {
                $insert_flag = true;
                if (count($summary[$key][$target_key]) > 0) {
                    $total = count($summary[$key][$target_key]);
                    if ($summary[$key][$target_key][ $total - 1]['count'] === $data[$target_key]) {
                        $insert_flag = false;
                    }
                }

                if (!!$insert_flag) {
                    $summary[$key][$target_key][] = [
                        "count" => $data[$target_key],
                        "ctime" => $data['ctime'] ?? $data['updated_time'],
                    ];
                    // echo "$key  $target_key 紀錄\n";
                } else {
                    // echo "$key  $target_key 不紀錄\n";
                }
            }





            // $summary[$key]["adult"][] = [
            //     "count" => $data['adult'],
            //     "ctime" => $data['ctime'],
            // ];

            // $summary[$key]["child"][] = [
            //     "count" => $data['child'],
            //     "ctime" => $data['ctime'],
            // ];
        }
    }
}


$info_json = json_encode($summary, JSON_UNESCAPED_UNICODE);

$f = fopen($history_path."/summary.log", "w");
fwrite($f, $info_json);
fclose($f);


$f = fopen("./log/summary.log", "w");
fwrite($f, $info_json);
fclose($f);

