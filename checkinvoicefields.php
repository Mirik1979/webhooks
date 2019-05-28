<?php
/* LOG INFO */
writeToLog($_REQUEST, 'test');
function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/log.log', $log, FILE_APPEND);
    return true;
} 

/* AUTH */
    $domain            = 'rosco.bitrix24.ru'; 
    $auth              = '9xx3fizorbhsjwpc';
    $user              = '7834'; 
// fields with reference to year and month    
    
/* GET INVOICE DATE */
    $appParams = http_build_query(array(
        'halt' => 0,
        'cmd' => array(
            'get_inv' => 'crm.invoice.get?'
                .http_build_query(array(
                    'ID' => 3814
                ))
        )
    ));
    $appRequestUrl = 'https://'.$domain.'/rest/'.$user.'/'.$auth.'/batch';
    $curl=curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $appRequestUrl,
        CURLOPT_POSTFIELDS => $appParams
    ));
    $out=curl_exec($curl);
    $out = json_decode($out, 1);    
    $result = $out['result']['result'];
    
    echo "<pre>";
    print_r($result);
    print_r(json_decode($result, true));
    echo "</pre>";
?>