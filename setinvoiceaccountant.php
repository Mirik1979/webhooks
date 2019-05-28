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
    $domain            = 'b24-g9613x.bitrix24.ru'; 
    $auth              = 'hhel9fnu0zu2aibq';
    $user              = '1'; 
// fields with reference to year and month
    $accfield          = 'UF_CRM_1548926014';
/* INVOICE ID */
    $invoice_id = $_REQUEST['data']['FIELDS']['ID']; 
    
/* GET INVOICE COMPANY */
    $appParams = http_build_query(array(
        'halt' => 0,
        'cmd' => array(
            'get_inv' => 'crm.invoice.get?'
                .http_build_query(array(
                    'ID' => $invoice_id
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
    $companyid = $result['get_inv']['UF_COMPANY_ID'];
    curl_close($curl);  

/* TAKE RESPONSIBLE PERSON */
    $appParams = http_build_query(array(
        'halt' => 0,
        'cmd' => array(
            'get_inv' => 'crm.company.get?'
                .http_build_query(array(
                    'ID' => $companyid
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
    $out2=curl_exec($curl);
    $out2 = json_decode($out2, 1);    
    $result2 = $out2['result']['result'];
    $accountant = $result2['get_inv']['ASSIGNED_BY_ID'];
    curl_close($curl);  

    // writeToLog($accountant, 'test');

/* UPDATE ACCOUNTANT */
    $appParams2 = http_build_query(array(
        'halt' => 0,
        'cmd' => array(
            'get_inv' => 'crm.invoice.update?'
                .http_build_query(array(
                    'ID' => $invoice_id,
                    'FIELDS' => array($accfield => $accountant)
                ))
        )
    ));
    $curl=curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $appRequestUrl,
        CURLOPT_POSTFIELDS => $appParams2
    ));
    $out3=curl_exec($curl);    
    curl_close($curl);  
?>