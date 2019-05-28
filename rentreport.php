<?php
// Set variables with fields names
$listname = "Зарплаты работников";
$accountantf = 'PROPERTY_111';
$accountantinvf = 'UF_CRM_1548926014';
$monthf = 'PROPERTY_103';
$yearf = 'PROPERTY_105';
$salaryf = 'PROPERTY_107';
$bonusf = 'PROPERTY_109';

if ($_REQUEST['AUTH_ID'] != "") {
    $auth = $_REQUEST['AUTH_ID']; 
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // take values from form
    $month= $_POST['month'];
    $monthcodelist = translatemonth($month);
    $monthcodelist2 = translatemonthcodes($month);
    $year = $_POST['year'];
    $autht = $_POST['autht']; 
    //$listname = $_POST['listname'];
    // check whether proper listname exist
    if ($autht) {
        $result = executeREST(
            'lists.get',
            array(
                'IBLOCK_TYPE_ID' => 'lists'
            ),
            $_REQUEST['DOMAIN'], $autht);

        $allblocks = $result; 
        $checkingres = checklistname($allblocks['result'], $listname); 

        if ($checkingres != "nolist") { 
    		$resultl = executeREST(
    	        'lists.element.get',
    	        array(
    	            'IBLOCK_TYPE_ID' => 'lists', 
    	            'IBLOCK_ID' => $checkingres
    	        ),
    	    	$_REQUEST['DOMAIN'], $autht);
            $listitems = $resultl; 
            $total = $listitems['total'];
            // calculate cost
            $blockcontent = executeBatch('lists.element.get',
        	        array(
        	            'IBLOCK_TYPE_ID' => 'lists', 
        	            'IBLOCK_ID' => $checkingres
        	   ),
        	$_REQUEST['DOMAIN'], $autht, $total);

            $arraypart = 0; 
            $arrayparts = count($blockcontent['result_total']);
            $costarray = array();        
            do { 
                $arraypart++; 
                foreach ($blockcontent['result']['result']['get_' . $arraypart] as $listval) {  
                    if (array_shift($listval[$monthf]) == $monthcodelist && array_shift($listval[$yearf]) == $year) {  
                        $act = array_shift($listval[$accountantf]); 
                        $users =  executeREST(
                            'user.get',
                            array(
                                'ORDER' => array("ID" => desc), 
                                'FILTER' => array("ID" => $act)
                            ),
                        $_REQUEST['DOMAIN'], $autht);
                        $act = $users['result'][0]['LAST_NAME'];
                        $costarray[$act]['cost'] = $costarray[$act]['cost'] + array_shift($listval[$salaryf]) + array_shift($listval[$bonusf]);
                    }             
                }
            } while ($arraypart <= $arrayparts);    
            
            // calculate revenue
            $resultaccounts = executeREST(
                'crm.invoice.list',
                array(   
                    'ORDER' => array("ID" => desc), 
                    // attemped to filter by period using this construction but failed, maybe yu could help to limit request amount                
                    // 'FILTER' => array("PAYED" => "Y", date("mY", strtotime("DATE_BILL")) => '022019')
                    'FILTER' => array("PAYED" => "Y")
                ),
            $_REQUEST['DOMAIN'], $autht);

            $totalact = $resultaccounts['total'];

            $resultconto = executeBatch('crm.invoice.list',
                    array(
                        'ORDER' => array("ID" => desc), 
                        'FILTER' => array("PAYED" => "Y")
               ),
            $_REQUEST['DOMAIN'], $autht, $totalact);  

            $arraypart = 0; 
            $arrayparts = count($resultconto['result_total']);
            $salesbypeople = array();              
            do { 
                $arraypart++; 
                foreach ($resultconto['result']['result']['get_' . $arraypart] as $contoval) { 
                    $invperiod = date("mY", strtotime($contoval['DATE_PAYED']));
                    if ($invperiod == $monthcodelist2.$year) {                    
                        $cocnreteconto = executeREST(
                            'crm.invoice.get',
                        array(
                            'ID' => $contoval['ID'],
                        ),
                        $_REQUEST['DOMAIN'], $autht);
                        $sact = (string)$cocnreteconto['result'][$accountantinvf];
                        $users =  executeREST(
                            'user.get',
                            array(
                                'ORDER' => array("ID" => desc), 
                                'FILTER' => array("ID" => $sact)
                            ),
                        $_REQUEST['DOMAIN'], $autht);
                        $sact = $users['result'][0]['LAST_NAME'];
                        $salesbypeople[$sact]['revenue'] =  $salesbypeople[$sact]['revenue'] + $contoval['PRICE'];               
                    }     
                }
            } while ($arraypart <= $arrayparts);    

            $res_arr = array_merge_recursive($costarray, $salesbypeople);

        }
    }
}

function checklistname($listarray, $listname) { 
	$retval = "nolist"; 
	foreach ($listarray as $lr) {		
		if ($lr['NAME'] == $listname) {
			$retval = $lr['ID'];
		}
	}
	return $retval;
}

function translatemonth($month) {
	switch ($month) {
		case "Январь":
		    return 93;		    
		case "Февраль":
		    return 95;
		case "Март":
		    return 97;		    
		case "Апрель":
		    return 99;
		case "Май":
		    return 101;
		case "Июнь":
		    return 103;
		case "Июль":
		    return 105;
		case "Август":
		    return 107;
		case "Сентябрь":
		    return 109;
		case "Октябрь":
		    return 111;
		case "Ноябрь":
		    return 113;
		case "Декабрь":
		    return 115;		
		}
}

function translatemonthcodes($month) {
    switch ($month) {
        case "Январь":
            return '01';          
        case "Февраль":
            return '02';
        case "Март":
            return '03';          
        case "Апрель":
            return '04';
        case "Май":
            return '05';
        case "Июнь":
            return '06';
        case "Июль":
            return '07';
        case "Август":
            return '08';
        case "Сентябрь":
            return '09';
        case "Октябрь":
            return '10';
        case "Ноябрь":
            return '11';
        case "Декабрь":
            return '12';     
        }
}

function executeBatch ($method, $params, $domain, $access_token, $total) {
	$calls = ceil($total / 50); $current_call = 0;

	$batch = array(); $result = array();

	do {
		$current_call++;

		$batch['get_' . $current_call] =
			$method.'?'.http_build_query(
				array_merge($params,
					array(
						"start" => ($current_call - 1) * 50
					)
				)
			);

		if ((count($batch) == 50) || ($current_call == $calls)) {

			global $debug;
			if ($debug) {
				echo 'batch: '.$method.': <pre>';
				print_r($batch);
				echo '</pre>';
			}

			$batch_result = executeREST('batch', array('cmd' => $batch), $domain, $access_token);

			$result = array_merge($result, $batch_result);

			$batch = array();
		}


	} while ($current_call < $calls);

	return $result;
}

function executeREST ($method, array $params, $domain, $auth) {
    $queryUrl = 'https://'.$domain.'/rest/'.$method.'.json';
    $queryData = http_build_query(
        array_merge($params, array("auth" => $auth))
    );
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    return json_decode(curl_exec($curl), true);
    curl_close($curl);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Отчет по рентабельности</title>
</head>
<body style="width:50%; margin:0 auto;">
<h2 class="text-center">Отчет по рентабельности</h2>
<hr>
<form action="" method="post">
        <p class="text-center">
        <select id="month" name="month">
            <option>Январь</option>
            <option>Февраль</option>
            <option>Март</option>
            <option>Апрель</option>
            <option>Май</option>
            <option>Июнь</option>
            <option>Июль</option>
            <option>Августь</option>
            <option>Сентябрь</option>
            <option>Октябрь</option>
            <option>Ноябрь</option>
            <option>Декабрь</option>
        </select>
    </p>
    <p class="text-center">
        <select id="year" name="year">
            <option>2019</option>
            <option>2020</option>
            <option>2021</option>
            <option>2022</option>
            <option>2023</option>
            <option>2024</option>
            <option>2025</option>
            <option>2026</option>
            <option>2027</option>
            <option>2028</option>
            <option>2029</option>
            <option>2030</option>
        </select>
    </p>
    <p class="text-center">
        <input type="hidden" id="autht" name="autht"/>
    </p>    
    <p class="text-center">
        <input name="operation" type="submit" value="Сформировать отчет"/>
    </p>
</form>
<?php
if ($res_arr) {  
  echo '<p class="text-center">Период: '.$month.$year.'</p>'; 
  echo '<table class="table">
        <tr>
            <td>Фамилия</td>
            <td>Затраты</td>
            <td>Выручка</td>
            <td>Прибыль</td>
        </tr>';

  foreach ($res_arr as $actn => $val)
    {
        echo '<tr>
                 <td>'.$actn.'</td>
                 <td>'.$val['cost'].'</td>
                 <td>'.$val['revenue'].'</td>
                 <td>'.round($val['revenue']/$val['cost'], 2).'</td>               
            </tr>';
    }
    echo '</table>';
}  
?>
</body>
<script type="text/javascript">
document.getElementById('autht').value = "<?php echo ($auth) ? $auth : $autht; ?>";
</script>
</html>

