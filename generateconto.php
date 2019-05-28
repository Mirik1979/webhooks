<?

writetolog($_REQUEST, 'new request');	

$deal = $_REQUEST['deal'];

$type = $_REQUEST['type'];

/* AUTH */

$domain            = 'da-bankrot.bitrix24.ru'; 

$auth              = '531d9g8h5e2nphd5';

$user              = '210'; 

$listid            = '54';



$dealinfo = executeREST(

            'crm.deal.get',

            array(

                    'ID' => $deal

            ),

            $domain, $auth, $user);



$cnt      = $dealinfo['result']['CONTACT_ID'];

writetolog($dealinfo, 'dealinfo');

$tarif    = $dealinfo['result']['UF_CRM_1556861587']; 

writetolog($tarif, 'tarif');



if ($type == 'EPE') {

	$productlist = executeREST(

            'crm.product.get',

            array(

                        'ID' => 5340

                    ),

    $domain, $auth, $user);



	$price = $productlist['result']['PRICE'];



	// обновляем сумму сделки 

	$opportunitypd = executeREST(

            'crm.deal.update',

            array(

					'ID' => $deal,	

					'FIELDS' => array (

						'OPPORTUNITY' => $price,

						),

					'PARAMS' => array (

						'REGISTER_SONET_EVENT' => "N",

						),

                    ),

	$domain, $auth, $user);

	writetolog($opportunitypd, 'updo');



	// добавляем счет

	$contoadd = executeREST(

            'crm.invoice.add',

            array(

                        'FIELDS' => array(   

								'ORDER_TOPIC' => 'Счет на ЭПЭ',

								'STATUS_ID' => 'N',

								'ACCOUNT_NUMBER' => 4,

								'USER_DESCRIPTION' => 'Счет на ЭПЭ',

								'UF_QUOTE_ID' => 0,

							    'UF_DEAL_ID' => $deal,

								'UF_COMPANY_ID' => 0,

								'UF_CONTACT_ID' => $cnt,

								'UF_MYCOMPANY_ID' => 0,

								'PAYED' => 'N',

								'PAY_SYSTEM_ID' => 4,

                                'PERSON_TYPE_ID' => 8,

                                'INVOICE_PROPERTIES' => array (

                                    'FIO' => 'testct'

								),

								'PRODUCT_ROWS' => array( 

									array (  

										'ID' => 0,

										'PRODUCT_ID' => 5340,

										'PRODUCT_NAME' => 'ЭПЭ',

										'QUANTITY' => 1,

										'PRICE' => $price,

									),

								),

                            ),

                    ),

		$domain, $auth, $user);

        writetolog($contoadd, 'conto');

    } elseif ($type == 'DOG') {

	// отрабатываем VIP-тариф, после добавления аналитики по следующим тарифам докрутить if-ы

	// в условиях коробки можно нарастить функционал настройкам, так приходиться малость хардкодить :(

	if ($tarif == 1126) {

		$listgoods = executeREST(

            'crm.product.list',

			array(

                  'ORDER' => array (

						"NAME" => asc

					), 

				  'FILTER' => array (

						"CATALOG_ID" => 24, 

    					"SECTION_ID" => 76	

				   )

            ),

		$domain, $auth, $user);

		// вытаскиваем из папки товары

		$i = 0; 

		foreach ($listgoods['result'] as $good) {

			$i++; 

			writetolog($good, 'goods');

			$gid = $good['ID'];

			writetolog($gid, 'goods');

			$productlist = executeREST(

            'crm.product.get',

            	array(

                        'ID' => $gid

                    ),

    			$domain, $auth, $user);



			$price = $productlist['result']['PRICE'];

			// ппп

			$dealinfo = executeREST(

            'crm.deal.get',

            	array(

                    'ID' => $deal

            		),

            $domain, $auth, $user);

			$opp = $dealinfo['OPPORTUNITY'];

			$newopp = $price + $opp; 

			writetolog($newopp, 'newopp');

			/* $opportunitypd = executeREST(

            'crm.deal.update',

			array(

				'ID' => $deal,	

				'FIELDS' => array (

					'OPPORTUNITY' => $newopp, //тут подтягивается 30к - последний счет, а надо сумму все счетов по сделке.

				 ),

				'PARAMS' => array (

						'REGISTER_SONET_EVENT' => "N",

					),

				),

			$domain, $auth, $user);	

			writetolog($opportunitypd, 'newoppupd'); */



			// добавляем счет

			$contoadd = executeREST(

            'crm.invoice.add',

            	array(

                        'FIELDS' => array(   

								'ORDER_TOPIC' => $good['NAME'],

								'STATUS_ID' => 'N',

								'ACCOUNT_NUMBER' => 4,

								'USER_DESCRIPTION' => $good['NAME'],

								'UF_QUOTE_ID' => 0,

							    'UF_DEAL_ID' => $deal,

								'UF_COMPANY_ID' => 0,

								'UF_CONTACT_ID' => $cnt,

								'UF_MYCOMPANY_ID' => 0,

								'PAYED' => 'N',

								'PAY_SYSTEM_ID' => 4,

								'PERSON_TYPE_ID' => 8,

								'INVOICE_PROPERTIES' => array (

									'FIO' => 'testct'

								),

								'PRODUCT_ROWS' => array( 

									array (  

										'ID' => 0,

										'PRODUCT_ID' => $good['ID'],

										'PRODUCT_NAME' => $good['NAME'],

										'QUANTITY' => 1,

										'PRICE' => $price,

									),

								),

                            ),

                    ),

			$domain, $auth, $user);

			writetolog($priceplus, 'contonum');

		}

    }

    elseif ($tarif == 1122) {

		$listgoods = executeREST(

            'crm.product.list',

			array(

                  'ORDER' => array (

						"NAME" => asc

					), 

				  'FILTER' => array (

						"CATALOG_ID" => 24, 

    					"SECTION_ID" => 78	

				   )

            ),

		$domain, $auth, $user);

		// вытаскиваем из папки товары

		$i = 0; 

		foreach ($listgoods['result'] as $good) {

			$i++; 

			writetolog($good, 'goods');

			$gid = $good['ID'];

			writetolog($gid, 'goods');

			$productlist = executeREST(

            'crm.product.get',

            	array(

                        'ID' => $gid

                    ),

    			$domain, $auth, $user);



			$price = $productlist['result']['PRICE'];

			// ппп

			$dealinfo = executeREST(

            'crm.deal.get',

            	array(

                    'ID' => $deal

            		),

            $domain, $auth, $user);

			$opp = $dealinfo['OPPORTUNITY'];

			$newopp = $price + $opp; 

			writetolog($newopp, 'newopp');

			/* $opportunitypd = executeREST(

            'crm.deal.update',

			array(

				'ID' => $deal,	

				'FIELDS' => array (

					'OPPORTUNITY' => $newopp, //тут подтягивается 30к - последний счет, а надо сумму все счетов по сделке.

				 ),

				'PARAMS' => array (

						'REGISTER_SONET_EVENT' => "N",

					),

				),

			$domain, $auth, $user);	

			writetolog($opportunitypd, 'newoppupd'); */



			// добавляем счет

			$contoadd = executeREST(

            'crm.invoice.add',

            	array(

                        'FIELDS' => array(   

								'ORDER_TOPIC' => $good['NAME'],

								'STATUS_ID' => 'N',

								'ACCOUNT_NUMBER' => 4,

								'USER_DESCRIPTION' => $good['NAME'],

								'UF_QUOTE_ID' => 0,

							    'UF_DEAL_ID' => $deal,

								'UF_COMPANY_ID' => 0,

								'UF_CONTACT_ID' => $cnt,

								'UF_MYCOMPANY_ID' => 0,

								'PAYED' => 'N',

								'PAY_SYSTEM_ID' => 4,

								'PERSON_TYPE_ID' => 8,

								'INVOICE_PROPERTIES' => array (

									'FIO' => 'testct'

								),

								'PRODUCT_ROWS' => array( 

									array (  

										'ID' => 0,

										'PRODUCT_ID' => $good['ID'],

										'PRODUCT_NAME' => $good['NAME'],

										'QUANTITY' => 1,

										'PRICE' => $price,

									),

								),

                            ),

                    ),

			$domain, $auth, $user);

			writetolog($priceplus, 'contonum');

		}

    }

    elseif ($tarif == 1124) {

		$listgoods = executeREST(

            'crm.product.list',

			array(

                  'ORDER' => array (

						"NAME" => asc

					), 

				  'FILTER' => array (

						"CATALOG_ID" => 24, 

    					"SECTION_ID" => 80	

				   )

            ),

		$domain, $auth, $user);

		// вытаскиваем из папки товары

		$i = 0; 

		foreach ($listgoods['result'] as $good) {

			$i++; 

			writetolog($good, 'goods');

			$gid = $good['ID'];

			writetolog($gid, 'goods');

			$productlist = executeREST(

            'crm.product.get',

            	array(

                        'ID' => $gid

                    ),

    			$domain, $auth, $user);



			$price = $productlist['result']['PRICE'];

			// ппп

			$dealinfo = executeREST(

            'crm.deal.get',

            	array(

                    'ID' => $deal

            		),

            $domain, $auth, $user);

			$opp = $dealinfo['OPPORTUNITY'];

			$newopp = $price + $opp; 

			writetolog($newopp, 'newopp');

			/* $opportunitypd = executeREST(

            'crm.deal.update',

			array(

				'ID' => $deal,	

				'FIELDS' => array (

					'OPPORTUNITY' => $newopp, //тут подтягивается 30к - последний счет, а надо сумму все счетов по сделке.

				 ),

				'PARAMS' => array (

						'REGISTER_SONET_EVENT' => "N",

					),

				),

			$domain, $auth, $user);	

			writetolog($opportunitypd, 'newoppupd'); */



			// добавляем счет

			$contoadd = executeREST(

            'crm.invoice.add',

            	array(

                        'FIELDS' => array(   

								'ORDER_TOPIC' => $good['NAME'],

								'STATUS_ID' => 'N',

								'ACCOUNT_NUMBER' => 4,

								'USER_DESCRIPTION' => $good['NAME'],

								'UF_QUOTE_ID' => 0,

							    'UF_DEAL_ID' => $deal,

								'UF_COMPANY_ID' => 0,

								'UF_CONTACT_ID' => $cnt,

								'UF_MYCOMPANY_ID' => 0,

								'PAYED' => 'N',

								'PAY_SYSTEM_ID' => 4,

								'PERSON_TYPE_ID' => 8,

								'INVOICE_PROPERTIES' => array (

									'FIO' => 'testct'

								),

								'PRODUCT_ROWS' => array( 

									array (  

										'ID' => 0,

										'PRODUCT_ID' => $good['ID'],

										'PRODUCT_NAME' => $good['NAME'],

										'QUANTITY' => 1,

										'PRICE' => $price,

									),

								),

                            ),

                    ),

			$domain, $auth, $user);

			writetolog($priceplus, 'contonum');

		}

    }	

}



function executeREST ($method, array $params, $domain, $auth, $user) {

            $queryUrl = 'https://'.$domain.'/rest/'.$user.'/'.$auth.'/'.$method.'.json';

            $queryData = http_build_query($params);

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



function writeToLog($data, $title = '') {

    $log = "\n------------------------\n";

    $log .= date("Y.m.d G:i:s") . "\n";

    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";

    $log .= print_r($data, 1);

    $log .= "\n------------------------\n";

    file_put_contents(getcwd() . '/generateconto.log', $log, FILE_APPEND);

    return true;

}