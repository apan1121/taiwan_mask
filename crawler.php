<?php
$data = @file_get_contents('https://data.nhi.gov.tw/resource/mask/maskdata.csv');

$data = explode("\n", $data);

array_shift($data);

$columns = [
    'id',
    'name',
    'address',
    'phone',
    'adult',
    'child',
    'updated_time'
];

$info = [];
$updated_time = "";
foreach ($data AS $item) {
    $item = str_replace(["\r\n", "\r", "\n"], '', $item);
    if (!empty($item)) {
        $items = explode(",", $item);
        $new_item = [];
        foreach ($items AS $index => $val) {
            $new_item[$columns[$index]] = $val;
        }
        $info[$new_item["id"]] = $new_item;
        $updated_time = $new_item["updated_time"];
    }
}

$update_date = date("Y_m_d", strtotime($updated_time));
$update_time = date("H_i_s", strtotime($updated_time));


$info_json = json_encode($info, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);

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

                $summary[$key]["adult"][] = [
                    "count" => $data['adult'],
                    "updated_time" => $data['updated_time'],
                ];

                $summary[$key]["child"][] = [
                    "count" => $data['child'],
                    "updated_time" => $data['updated_time'],
                ];
            }
        }
    }
}


$info_json = json_encode($summary, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);

$f = fopen($history_path."/summary.log", "w");
fwrite($f, $info_json);
fclose($f);


$f = fopen("./log/summary.log", "w");
fwrite($f, $info_json);
fclose($f);

