<?php
ini_set('memory_limit', '1024M');

$history_path ="./log/history";

$store_path = "./log/store.log";

$store = json_decode(@file_get_contents($store_path), true) ?? [];


$directories = scandir($history_path, 0);



foreach ($directories AS $dir_name) {
    if (!in_array($dir_name, [".", "..", ".DS_Store","summary.log"])) {
        $date_path = "{$history_path}/{$dir_name}";
        if (is_dir($date_path)) {
            $date_files = scandir($date_path, 0);
            foreach ($date_files AS $file) {
                if (!in_array($file, [".", "..", ".DS_Store","summary.log"])){
                    $filePath = "{$date_path}/{$file}";
                    $jsonData = json_decode(file_get_contents($filePath), true);
                    foreach ($jsonData AS $store_id => &$_jsonData) {
                        if (!empty($_jsonData['id'])) {
                            setStoreInfo($_jsonData);
                            unset($_jsonData['name']);
                            unset($_jsonData['address']);
                            unset($_jsonData['phone']);
                            unset($_jsonData['id']);
                        }
                        if (!empty($_jsonData['updated_time'])){
                            $_jsonData['ctime'] = $_jsonData['updated_time'];
                            unset($_jsonData['updated_time']);
                        }
                    }

                    $info_json = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
                    $f = fopen($filePath, "w");
                    fwrite($f, $info_json);
                    fclose($f);
                    echo $filePath."\n";
                }
            }

            $store_json = json_encode($store, JSON_UNESCAPED_UNICODE);
            $f = fopen($store_path, "w");
            fwrite($f, $store_json);
            fclose($f);
        }
    }
}




function setStoreInfo($info){
    global $store;
    $tmp = [
        'id' => $info['id'],
        'name' => $info['name'],
        'address' => $info['address'],
        'phone' => $info['phone'],
    ];

    if (empty($store[$tmp['id']])){
        $store[$tmp['id']] = [];
    }

    $store[$tmp['id']] = $tmp + $store[$tmp['id']];
}