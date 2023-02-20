<?php
/**
 * @brief Simple
 * @class Simple
 * @note
 */
class Simple extends IController
{
    public $layout='site_mini';

	function init()
	{

	}

	function login()
	{
		//ucenter
		if($this->user)
		{
			$this->redirect("/ucenter/index");
		}
		else
		{
			$this->redirect('login');
		}
	}

	//
    function logout()
    {
    	plugin::trigger('clearUser');
    	$this->redirect('login');
    }

    //
    function reg_act()
    {
    	//_userInfo
    	$result = plugin::trigger("userRegAct");

//    	if(is_array($result))
if(is_array($result) && $result['new'])

    	{

			//
			$msg = isset($result['msg']) ? $result['msg'] : "~";
			$this->redirect('/site/success?message='.urlencode($msg));
    	}
    	else
    	{

    		$this->setError($result);
    		$this->redirect('reg',false);
    		Util::showMessage($result);
    	}
    }

    //
    function login_act()
    {
    	//_userInfo
		$result = plugin::trigger('userLoginAct',$_POST);
		if(is_array($result))
		{
			//
			$callback = plugin::trigger('getCallback');
			if($callback)
			{
				$this->redirect($callback);
			}
			else
			{
				$this->redirect('/ucenter/index');
			}
		}
		else
		{
			$this->setError($result);
			$this->redirect('login',false);
			Util::showMessage($result);
		}
    }

    //[ajax]
    function joinCart()
    {
    	$link      = IReq::get('link');
    	$goods_id  = IFilter::act(IReq::get('goods_id'),'int');
    	$goods_num = IFilter::act(IReq::get('goods_num'),'int');
    	$goods_num = $goods_num == 0 ? 1 : $goods_num;
		$type      = IFilter::act(IReq::get('type'));

		//
    	$cartObj   = new Cart();
    	$addResult = $cartObj->add($goods_id,$goods_num,$type);

    	if($link != '')
    	{
    		if($addResult === false)
    		{
    			$this->cart(false);
    			Util::showMessage($cartObj->getError());
    		}
    		else
    		{
    			$this->redirect($link);
    		}
    	}
    	else
    	{
	    	if($addResult === false)
	    	{
		    	$result = array(
		    		'isError' => true,
		    		'message' => $cartObj->getError(),
		    	);
	    	}
	    	else
	    	{
		    	$result = array(
		    		'isError' => false,
		    		'message' => '',
		    	);
	    	}

//	    	echo JSON::encode($result);
echo JSON::encode($result, true);

    	}
    }

    //goods_id
    function getProducts()
    {
    	$id           = IFilter::act(IReq::get('id'),'int');
    	$productObj   = new IModel('products');
    	$productsList = $productObj->query('goods_id = '.$id,'sell_price,id,spec_array,goods_id','store_nums desc',7);
		if($productsList)
		{
			foreach($productsList as $key => $val)
			{
				$productsList[$key]['specData'] = goods_class::show_spec($val['spec_array']);
			}
		}
		echo JSON::encode($productsList);
    }

    //
    function removeCart()
    {
    	$link      = IReq::get('link');
    	$goods_id  = IFilter::act(IReq::get('goods_id'),'int');
    	$type      = IFilter::act(IReq::get('type'));

    	$cartObj   = new Cart();
    	$cartInfo  = $cartObj->getMyCart();
    	$delResult = $cartObj->del($goods_id,$type);

    	if($link)
    	{
    		if($delResult === false)
    		{
    			$this->cart(false);
    			Util::showMessage($cartObj->getError());
    		}
    		else
    		{
    			$this->redirect($link);
    		}
    	}
    	else
    	{
	    	if($delResult === false)
	    	{
	    		$result = array(
		    		'isError' => true,
		    		'message' => $cartObj->getError(),
	    		);
	    	}
	    	else
	    	{
		    	$goodsRow = $cartInfo[$type]['data'][$goods_id];
		    	if($goodsRow && isset($goodsRow['sell_price']) && isset($goodsRow['count']))
		    	{
    		    	$cartInfo['sum']   -= $goodsRow['sell_price'] * $goodsRow['count'];
    		    	$cartInfo['count'] -= $goodsRow['count'];

    		    	$result = array(
    		    		'isError' => false,
    		    		'data'    => $cartInfo,
    		    	);
		    	}
		    	else
		    	{
    	    		$result = array(
    		    		'isError' => true,
    		    		'message' => "",
    	    		);
		    	}
	    	}
	    	echo JSON::encode($result);
    	}
    }

    //
    function clearCart()
    {
    	$cartObj = new Cart();
    	$cartObj->clear();
    	$this->redirect('cart');
    }

    //div
    function showCart()
    {
    	$cartObj  = new Cart();
    	$cartList = $cartObj->getMyCart();
    	$data['data'] = array_merge($cartList['goods']['data'],$cartList['product']['data']);
    	$data['count']= $cartList['count'];
//    	$data['sum']  = round($cartList['sum'],2);
//    	echo JSON::encode($data);
$data['sum']  = $cartList['sum'];
echo JSON::encode($data, true);
    }

    //[]
    function cart()
    {
    	//
    	header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		//
		$cartObj  = new Cart();
    	$countObj = new CountSum();
    	$result   = $countObj->goodsCount($cartObj->getMyCart(true));

    	if(is_string($result))
    	{
    		IError::show($result,403);
    	}

    	//
    	$this->final_sum = $result['final_sum'];
    	$this->promotion = $result['promotion'];
    	$this->proReduce = $result['proReduce'];
    	$this->sum       = $result['sum'];
    	$this->goodsList = $result['goodsList'];
    	$this->count     = $result['count'];
    	$this->reduce    = $result['reduce'];
    	$this->weight    = $result['weight'];

		//
    	$this->redirect('cart');
    }

    //[ajax]
    function promotionRuleAjax()
    {
		$countSumObj    = new CountSum();
		$countSumResult = $countSumObj->cart_count();
//    	echo JSON::encode($countSumResult);
echo JSON::encode($countSumResult, true);
    }

    //cart2
    function cart2()
    {
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0",false);
		$id        = IFilter::act(IReq::get('id'),'int');
		$type      = IFilter::act(IReq::get('type'));//goods,product
		$buy_num   = IReq::get('num') ? IFilter::act(IReq::get('num'),'int') : 1;
		$tourist   = IReq::get('tourist');//

    	//
    	if($tourist === null && $this->user['user_id'] == null)
    	{
    		if($id == 0 || $type == '')
    		{
    			$this->redirect('/simple/login?tourist&callback=/simple/cart2');
    		}
    		else
    		{
    			$url = '/simple/login?tourist&callback=/simple/cart2/id/'.$id.'/type/'.$type.'/num/'.$buy_num;
    			$this->redirect($url);
    		}
    	}

		//user_id0
    	$user_id = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];

		//
		$countSumObj = new CountSum($user_id);
		$result = $countSumObj->cart_count($id,$type,$buy_num);
		if($countSumObj->error)
		{
			IError::show(403,$countSumObj->error);
		}

    	//
    	$addressObj  = new IModel('address');
    	$addressList = $addressObj->query('user_id = '.$user_id,"*","is_default desc");

		//$addressList
    	foreach($addressList as $key => $val)
    	{
    		$temp = area::name($val['province'],$val['city'],$val['area']);
    		if(isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']]))
    		{
	    		$addressList[$key]['province_str'] = $temp[$val['province']];
	    		$addressList[$key]['city_str']     = $temp[$val['city']];
	    		$addressList[$key]['area_str']     = $temp[$val['area']];
    		}
    	}

		//
		$ticketRow = [];
		if($user_id)
		{
			$memberObj  = new IModel('member');
			$memberRow  = $memberObj->getObj('user_id = '.$user_id,'prop');

			if(isset($memberRow['prop']) && ($propId = trim($memberRow['prop'],',')))
			{
				$porpObj = new IModel('prop');
				$propData = $porpObj->query('id in ('.$propId.') and NOW() between start_time and end_time and type = 0 and is_close = 0 and is_userd = 0 and is_send = 1','*','value desc');
				foreach($propData as $key => $item)
				{
				    $tempData = ticket::verify($item['id'],$result);
					if($tempData['result'] == true)
					{
						$ticketRow = [
							'price' => $tempData['price'],
							'name'  => $item['name'],
							'id'    => $item['id'],
						];
						break;
					}
				}
			}
		}

    	//
		$this->ticketRow = $ticketRow;
    	$this->final_sum = $result['final_sum'];
    	$this->promotion = $result['promotion'];
    	$this->proReduce = $result['proReduce'];
    	$this->sum       = $result['sum'];
    	$this->goodsList = $result['goodsList'];
    	$this->count       = $result['count'];
    	$this->reduce      = $result['reduce'];
    	$this->weight      = $result['weight'];
    	$this->freeFreight = $result['freeFreight'];
    	$this->sellerData  = $result['seller'];
    	$this->spend_point = $result['spend_point'];
    	$this->goodsType   = $result['goodsType'];

		//
		$this->takeselfList = $result['takeself'];

		//
		$this->addressList = $addressList;

		//
		$this->goodsTax    = number_format($result['tax'],7,'.','');

    	//
    	$this->redirect('cart2');
    }

	/**
	 * 
	 */
    function cart3()
    {
		//
    	if(IReq::get('timeKey'))
    	{
    		if(ISafe::get('timeKey') == IReq::get('timeKey'))
    		{
	    		IError::show(403,'');
    		}
    		ISafe::set('timeKey',IReq::get('timeKey'));
    	}

    	$address_id    = IFilter::act(IReq::get('radio_address'),'int');
    	$delivery_id   = IFilter::act(IReq::get('delivery_id'),'int');
    	$accept_time   = IFilter::act(IReq::get('accept_time'));
    	$payment       = IFilter::act(IReq::get('payment'),'int');
    	$accept_name   = IFilter::act(IReq::get('accept_name'));
    	$accept_mobile = IFilter::act(IReq::get('accept_mobile'));
    	$order_message = IFilter::act(IReq::get('message'));
    	$ticket_id     = IFilter::act(IReq::get('ticket_id'),'int');
    	$taxes         = IFilter::act(IReq::get('taxes'),'float');
    	$invoice_id    = IFilter::act(IReq::get('invoice_id'),'int');
    	$gid           = IFilter::act(IReq::get('id'),'int');
    	$num           = IFilter::act(IReq::get('num'),'int');
    	$type          = IFilter::act(IReq::get('type'));//
    	$takeself      = IFilter::act(IReq::get('takeself'),'int');
    	$startDate     = IFilter::act(IReq::get('start_date'),'date');
    	$endDate       = IFilter::act(IReq::get('end_date'),'date');
    	$dataArray     = [];
    	$user_id       = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];

		//
		$preorderDate= $startDate && $endDate ? [$startDate,$endDate] : [];
    	$countSumObj = new CountSum($user_id);
		$goodsResult = $countSumObj->cart_count($gid,$type,$num,$preorderDate);
		if($countSumObj->error)
		{
			IError::show(403,$countSumObj->error);
		}

		//
		if(IReq::get('taxes') === null)
		{
			$invoiceData = null;
		}
		else
		{
            //
            $invoiceData = $invoice_id ? Api::run('getInvoiceRowById',array('#id#',$invoice_id)) : ISafe::get('invoice');
            if(!$invoiceData)
            {
                IError::show(403,"");
            }
		}

        //
        if(goods_class::isDelivery($goodsResult['goodsType']))
        {
    		/**
    		 * 
    		 */
    		//1,
    		if($takeself)
    		{
    		    if(!$accept_name || !$accept_mobile)
    		    {
    		        IError::show(403,"");
    		    }

    		    $takeselfDB = new IModel('takeself');
    		    $addressRow = $takeselfDB->getObj($takeself);
    		    if(!$addressRow)
    		    {
    		        IError::show(403,"");
    		    }
    		    $addressRow['accept_name'] = $accept_name;
    		    $addressRow['mobile']      = $accept_mobile;

    		    //ID
    		    $delivery_id = 0;
    		}
    		//2,
    		else
    		{
        		//1,; 2,
        		if($user_id == 0)
        		{
        			$addressRow = ISafe::get('address');
        		}
        		else
        		{
        			$addressDB   = new IModel('address');
        			$addressRow  = $addressDB->getObj('id = '.$address_id.' and user_id = '.$user_id);
        		}

                //
                $deliveryObj = new IModel('delivery');
                $deliveryRow = $deliveryObj->getObj($delivery_id);
                if (!$deliveryRow)
                {
                    IError::show(403, '');
                }

                //1,
                if($deliveryRow['type'] == 0 && $payment == 0)
                {
                    IError::show(403,'');
                }
                //2,
                else if($deliveryRow['type'] == 1)
                {
                    $payment = 0;
                }
    		}

            if(!$addressRow)
            {
                IError::show(403,"");
            }

            $accept_name   = IFilter::act($addressRow['accept_name'],'name');
            $province      = $addressRow['province'];
            $city          = $addressRow['city'];
            $area          = $addressRow['area'];
            $address       = IFilter::act($addressRow['address']);
            $mobile        = IFilter::act($addressRow['mobile'],'mobile');
            $telphone      = isset($addressRow['telphone']) ? IFilter::act($addressRow['telphone'],'phone') : "";
            $zip           = isset($addressRow['zip']) ? IFilter::act($addressRow['zip'],'zip') : "";
        }
        else
        {
			$delivery_id= 0;
            $address_id = 0;
	        $mobile = $accept_mobile;
            if($payment == 0)
            {
                IError::show(403,'');
            }
        }

		//
    	$checkData = array(
    		"mobile" => $mobile,
    	);
    	$result = order_class::checkRepeat($checkData,$goodsResult['goodsList']);
    	if(is_string($result))
    	{
			IError::show(403,$result);
    	}

    	//
    	if(is_string($goodsResult) || !$goodsResult['goodsList'])
    	{
    		IError::show(403,'');
    	}

		$paymentObj = new IModel('payment');
		$paymentRow = $paymentObj->getObj('id = '.$payment,'type,name');
		if(!$paymentRow)
		{
			IError::show(403,'');
		}
		$paymentName= $paymentRow['name'];
		$paymentType= $paymentRow['type'];

		//
        $orderData = $countSumObj->countOrderFee($goodsResult,isset($province) ? $province : "",isset($delivery_id) ? $delivery_id : "",$taxes,0,$preorderDate);
        if(is_string($orderData))
		{
			IError::show(403,$orderData);
		}

		//
		if($ticket_id)
		{
			$memberObj = new IModel('member');
			$memberRow = $memberObj->getObj('user_id = '.$user_id,'prop');
			foreach($ticket_id as $tk => $tid)
			{
				//
				if(ISafe::get('ticket_'.$tid) == $tid || stripos(','.trim($memberRow['prop'],',').',',','.$tid.',') !== false)
				{
					$propObj   = new IModel('prop');
					$ticketRow = $propObj->getObj('id = '.$tid.' and NOW() between start_time and end_time and type = 0 and is_close = 0 and is_userd = 0 and is_send = 1');
					if(!$ticketRow)
					{
						IError::show(403,'');
					}

					//
                    $verifyResult = ticket::verify($tid,$goodsResult);
					if($verifyResult['result'] == false)
                    {
                        IError::show(403,$verifyResult['error']);
                    }
					unset($ticket_id[$tk]);
					break;
				}
			}
		}

		if(!$gid)
		{
			//
			$cartObj = new Cart();
			$cartObj->clear();
		}

		//
		$orderIdArray  = array();
		$orderNumArray = array();
		$final_sum     = 0;
		foreach($orderData as $seller_id => $goodsResult)
		{
			//
			$dataArray = array(
				'order_no'            => Order_Class::createOrderNum(),
				'user_id'             => $user_id,
				'accept_name'         => isset($accept_name) ? $accept_name : "",
				'pay_type'            => $payment,
				'distribution'        => isset($delivery_id) ? $delivery_id : "",
				'postcode'            => isset($zip) ? $zip : "",
				'telphone'            => isset($telphone) ? $telphone : "",
				'province'            => isset($province) ? $province : "",
				'city'                => isset($city) ? $city : "",
				'area'                => isset($area) ? $area : "",
				'address'             => isset($address) ? $address : "",
				'mobile'              => $mobile,
				'create_time'         => ITime::getDateTime(),
				'postscript'          => $order_message,
				'accept_time'         => isset($accept_time) ? $accept_time : "",
				'exp'                 => $goodsResult['exp'],
				'point'               => $goodsResult['point'],
				'type'                => $goodsResult['promo'],

				//
				'payable_amount'      => $goodsResult['sum'],
				'real_amount'         => $goodsResult['final_sum'],

				//
				'payable_freight'     => $goodsResult['deliveryOrigPrice'],
				'real_freight'        => $goodsResult['deliveryPrice'],

				//
				'invoice'             => $invoiceData ? 1                          : 0,
				'invoice_info'        => $invoiceData ? JSON::encode($invoiceData) : "",
				'taxes'               => $goodsResult['taxPrice'],

				//
				'promotions'          => $goodsResult['proReduce'] + $goodsResult['reduce'],

				//
				'order_amount'        => $goodsResult['orderAmountPrice'],

				//
				'insured'             => $goodsResult['insuredPrice'],

				//ID
				'takeself'            => $takeself,

				//ID
				'active_id'           => $goodsResult['active_id'],

				//ID
				'seller_id'           => $seller_id,

				//
				'note'                => '',

				//IDS
				'prorule_ids'         => $goodsResult['giftIds'],

                //
                'spend_point'         => $goodsResult['spend_point'],

                //
                'goods_type'          => $goodsResult['goodsType'],
			);

            //
            if(isset($verifyResult) && $verifyResult && $seller_id == $verifyResult['seller_id'])
            {
    			$ticketRow['value']         = $verifyResult['price'] >= $goodsResult['final_sum'] ? $goodsResult['final_sum'] : $verifyResult['price'];
    			$dataArray['prop']          = $tid;
    			$dataArray['promotions']   += $ticketRow['value'];
    			$dataArray['order_amount'] -= $ticketRow['value'];
    			$goodsResult['promotion'][] = array("plan" => "","info" => "".$ticketRow['value']."");

    			//
    			$propObj->setData(array('is_close' => 2));
    			$propObj->update('id = '.$tid);
            }

			//
			if(isset($goodsResult['promotion']) && $goodsResult['promotion'])
			{
				foreach($goodsResult['promotion'] as $key => $val)
				{
					$dataArray['note'] .= join("",$val)."";
				}
			}

//			$dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : $dataArray['order_amount'];
$dataArray['order_amount'] = $dataArray['order_amount'] <= 0 ? 0 : $dataArray['order_amount'] - $dataArray['real_freight'];

			//order
			$orderObj  = new IModel('order');
			$orderObj->setData($dataArray);
			$order_id = $orderObj->add();

			if($order_id == false)
			{
				IError::show(403,'');
			}

			/*order_goods*/
	    	$orderInstance = new Order_Class();
	    	$orderGoodsResult = $orderInstance->insertOrderGoods($order_id,$goodsResult['goodsResult']);
	    	if($orderGoodsResult !== true)
	    	{
	    		IError::show(403,$orderGoodsResult);
	    	}

			//
			if($dataArray['type'])
			{
				Active::orderCallback($dataArray['order_no'],$dataArray);
			}

			//0
			if($dataArray['order_amount'] <= 0)
			{
				Order_Class::updateOrderStatus($dataArray['order_no']);
			}
			else
			{
				$orderIdArray[]  = $order_id;
				$orderNumArray[] = $dataArray['order_no'];
				$final_sum      += $dataArray['order_amount'];
			}

			plugin::trigger('orderCreateFinish',$dataArray);
		}

		//
		if($user_id && $address_id)
		{
			$addressDefRow = $addressDB->getObj('user_id = '.$user_id.' and is_default = 1');
			if(!$addressDefRow)
			{
				$addressDB->setData(array('is_default' => 1));
				$addressDB->update('user_id = '.$user_id.' and id = '.$address_id);
			}
		}

		//
		$this->stockup_time = $this->_siteConfig->stockup_time ? $this->_siteConfig->stockup_time : 2;

		//
		$this->order_id    = join("_",$orderIdArray);
		$this->final_sum   = number_format($final_sum,7,'.','');
		$this->order_num   = join(" ",$orderNumArray);
		$this->payment     = $paymentName;
		$this->paymentType = $paymentType;
		$this->delivery    = isset($deliveryRow['name']) ? $deliveryRow['name'] : "";
		$this->tax_title   = isset($invoiceData['company_name']) ? $invoiceData['company_name'] : "";
		$this->deliveryType= isset($deliveryRow['type']) ? $deliveryRow['type'] : "";
		plugin::trigger('setCallback','/ucenter/order');

		//0
		if($this->final_sum <= 0)
		{
			$this->redirect('/site/success/message/'.urlencode(""));
		}
		else
		{
			$this->setRenderData($dataArray);
			$this->redirect('cart3');
		}
    }

    //
	function arrival_notice()
	{
		$user_id  = $this->user['user_id'];
		if(!$user_id)
		{
		    $this->redirect("/simple/login/_msg/");
		}

		$email    = IFilter::act(IReq::get('email'),'email');
		$mobile   = IFilter::act(IReq::get('mobile'),'mobile');
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$register_time = ITime::getDateTime();

		if(!$goods_id)
		{
			IError::show(403,'ID');
		}

		$model = new IModel('notify_registry');
		$obj = $model->getObj("email = '{$email}' and user_id = '{$user_id}' and goods_id = '$goods_id'");
		if(!$obj)
		{
			$model->setData(array('email'=>$email,'user_id'=>$user_id,'mobile'=>$mobile,'goods_id'=>$goods_id,'register_time'=>$register_time));
			$model->add();
		}
		else
		{
			$model->setData(array('email'=>$email,'user_id'=>$user_id,'mobile'=>$mobile,'goods_id'=>$goods_id,'register_time'=>$register_time,'notify_status'=>0));
			$model->update('id = '.$obj['id']);
		}
		$this->redirect('/site/success');
	}

	/**
	 * @brief 
	 */
    function find_password_email()
	{
		$username = IReq::get('username');
		if($username === null || !IValidate::name($username)  )
		{
			IError::show(403,"");
		}

		$email = IReq::get("email");
		if($email === null || !IValidate::email($email ))
		{
			IError::show(403,"");
		}

		$tb_user  = new IModel("user as u,member as m");
		$username = IFilter::act($username);
		$email    = IFilter::act($email);
		$user     = $tb_user->getObj(" u.id = m.user_id and u.username='{$username}' AND m.email='{$email}' ");
		if(!$user)
		{
			IError::show(403,"");
		}
		$hash = IHash::md5( microtime(true) .mt_rand());

		//
		$tb_find_password = new IModel("find_password");
		$tb_find_password->setData( array( 'hash' => $hash ,'user_id' => $user['id'] , 'addtime' => time() ) );

		if($tb_find_password->query("`hash` = '{$hash}'") || $tb_find_password->add())
		{
			$url     = IUrl::getHost().IUrl::creatUrl("/simple/restore_password/hash/{$hash}/user_id/".$user['id']);
			$content = mailTemplate::findPassword(array("{url}" => $url));

			$smtp   = new SendMail();
			$result = $smtp->send($user['email'],"",$content);

			if($result===false)
			{
				IError::show(403,",");
			}
		}
		else
		{
			IError::show(403,"HASH");
		}
		$message = "";
		$this->redirect("/site/success/message/".urlencode($message));
	}

	//
	function find_password_mobile()
	{
		$username = IReq::get('username');
		if($username === null || !IValidate::name($username))
		{
			IError::show(403,"");
		}

		$mobile = IReq::get("mobile");
		if($mobile === null || !IValidate::mobi($mobile))
		{
			IError::show(403,"");
		}

		$mobile_code = IFilter::act(IReq::get('mobile_code'));
		if($mobile_code === null)
		{
			IError::show(403,"");
		}

		$userDB = new IModel('user as u , member as m');
		$userRow = $userDB->getObj('u.username = "'.$username.'" and m.mobile = "'.$mobile.'" and u.id = m.user_id');
		if($userRow)
		{
			$findPasswordDB = new IModel('find_password');
			$dataRow = $findPasswordDB->getObj('user_id = '.$userRow['user_id'].' and hash = "'.$mobile_code.'"');
			if($dataRow)
			{
				//
				if(time() - $dataRow['addtime'] > 3600)
				{
					$findPasswordDB->del("user_id = ".$userRow['user_id']);
					IError::show(403,"");
				}
				else
				{
					$this->redirect('/simple/restore_password/hash/'.$mobile_code.'/user_id/'.$userRow['user_id']);
				}
			}
			else
			{
				IError::show(403,"");
			}
		}
		else
		{
			IError::show(403,"");
		}
	}

	//
	function send_message_mobile()
	{
		$username = IFilter::act(IReq::get('username'));
		$mobile = IFilter::act(IReq::get('mobile'));

		if($username === null || !IValidate::name($username))
		{
			die("");
		}

		if($mobile === null || !IValidate::mobi($mobile))
		{
			die("");
		}

		$userDB = new IModel('user as u , member as m');
		$userRow = $userDB->getObj('u.username = "'.$username.'" and m.mobile = "'.$mobile.'" and u.id = m.user_id');

		if($userRow)
		{
			$findPasswordDB = new IModel('find_password');
			$dataRow = $findPasswordDB->query('user_id = '.$userRow['user_id'],'*','addtime desc');
			$dataRow = current($dataRow);

			//120
			if( isset($dataRow['addtime']) && (time() - $dataRow['addtime'] <= 120) )
			{
				die("");
			}
			$mobile_code = rand(10000,99999);
			$findPasswordDB->setData(array(
				'user_id' => $userRow['user_id'],
				'hash'    => $mobile_code,
				'addtime' => time(),
			));
			if($findPasswordDB->add())
			{
				$result = _hsms::findPassword($mobile,array('{mobile_code}' => $mobile_code));
				if($result == 'success')
				{
					die('success');
				}
				die($result);
			}
		}
		else
		{
			die('');
		}
	}

	/**
	 * @brief 
	 */
	function restore_password()
	{
		$hash = IFilter::act(IReq::get("hash"));
		$user_id = IFilter::act(IReq::get("user_id"),'int');

		if(!$hash)
		{
			IError::show(403,"");
		}
		$tb = new IModel("find_password");
		$addtime = time() - 3600*72;
		$where  = " `hash`='$hash' AND addtime > $addtime ";
		$where .= $this->user['user_id'] ? " and user_id = ".$this->user['user_id'] : "";

		$row = $tb->getObj($where);
		if(!$row)
		{
			IError::show(403,"");
		}

		if($row['user_id'] != $user_id)
		{
			IError::show(403,"");
		}

		$this->formAction = IUrl::creatUrl("/simple/do_restore_password/hash/$hash/user_id/".$user_id);
		$this->redirect("restore_password");
	}

	/**
	 * @brief 
	 */
	function do_restore_password()
	{
		$hash = IFilter::act(IReq::get("hash"));
		$user_id = IFilter::act(IReq::get("user_id"),'int');

		if(!$hash)
		{
			IError::show(403,"");
		}
		$tb = new IModel("find_password");
		$addtime = time() - 3600*72;
		$where  = " `hash`='$hash' AND addtime > $addtime ";
		$where .= $this->user['user_id'] ? " and user_id = ".$this->user['user_id'] : "";

		$row = $tb->getObj($where);
		if(!$row)
		{
			IError::show(403,"");
		}

		if($row['user_id'] != $user_id)
		{
			IError::show(403,"");
		}

		//
		$pwd   = IReq::get("password");
		$repwd = IReq::get("repassword");
		if($pwd == null || strlen($pwd) < 6 || $repwd!=$pwd)
		{
			IError::show(403,"");
		}
		$pwd = md5($pwd);
		$tb_user = new IModel("user");
		$tb_user->setData(array("password" => $pwd));
		$re = $tb_user->update("id='{$row['user_id']}'");
		if($re !== false)
		{
			$message = "";
			$tb->del("`hash`='{$hash}'");
			$this->redirect("/site/success/message/".urlencode($message));
			return;
		}
		IError::show(403,"");
	}

    //
    function favorite_add()
    {
    	$goods_id = IFilter::act(IReq::get('goods_id'),'int');
    	$message  = '';

    	if($goods_id == 0)
    	{
    		$message = 'None selected';
    	}
    	else if(!isset($this->user['user_id']) || !$this->user['user_id'])
    	{
    		$message = 'Please login';
    	}
    	else
    	{
    		$favoriteObj = new IModel('favorite');
    		$goodsRow    = $favoriteObj->getObj('user_id = '.$this->user['user_id'].' and goods_id = '.$goods_id);
    		if($goodsRow)
    		{
    			$message = 'Followed successfully';
    		}
    		else
    		{
    			$catObj = new IModel('category_extend');
    			$catRow = $catObj->getObj('goods_id = '.$goods_id);
    			$cat_id = $catRow ? $catRow['category_id'] : 0;

	    		$dataArray   = array(
	    			'user_id' => $this->user['user_id'],
	    			'goods_id'=> $goods_id,
	    			'time'    => ITime::getDateTime(),
	    			'cat_id'  => $cat_id,
	    		);
	    		$favoriteObj->setData($dataArray);
	    		$favoriteObj->add();
	    		$message = 'Followed successfully';

	    		//
	    		$goodsDB = new IModel('goods');
	    		$goodsDB->setData(array("favorite" => "favorite + 1"));
	    		$goodsDB->update("id = ".$goods_id,'favorite');
    		}
    	}
		$result = array(
			'isError' => true,
			'message' => $message,
		);

    	echo JSON::encode($result);
    }

    //oauth
    public function oauth_login()
    {
    	$id = IFilter::act(IReq::get('id'),'int');
    	if($id)
    	{
    		$oauthObj = new OauthCore($id);
    		$url      = $oauthObj->getLoginUrl();
    		header('location: '.$url);
    	}
    	else
    	{
    	    IError::show(403,"");
    	}
    }

    //
    public function oauth_callback()
    {
    	$oauth_name = IFilter::act(IReq::get('oauth_name'));
    	$oauthObj   = new IModel('oauth');
    	$oauthRow   = $oauthObj->getObj('file = "'.$oauth_name.'"');

    	if(!$oauth_name && !$oauthRow)
    	{
    		IError::show(403,"{$oauth_name} ");
    	}
		$id       = $oauthRow['id'];
    	$oauthObj = new OauthCore($id);
    	$result   = $oauthObj->checkStatus($_GET);

    	if($result === true)
    	{
    		$oauthObj->getAccessToken($_GET);
	    	$userInfo = $oauthObj->getUserInfo();

	    	if(isset($userInfo['id']) && isset($userInfo['name']) && $userInfo['id'] && $userInfo['name'])
	    	{
	    		$this->bindUser($userInfo,$id);
	    		return;
	    	}
	    	else
	    	{
	    	    IError::show(403,",oauth");
	    	}
    	}
    	else
    	{
    		$this->redirect("/simple/login/_msg/");
    	}
    }

    //
    public function bindUser($userInfo,$oauthId)
    {
    	$userObj      = new IModel('user');
    	$oauthUserObj = new IModel('oauth_user');
    	$oauthUserRow = $oauthUserObj->getObj("oauth_user_id = '{$userInfo['id']}' and oauth_id = '{$oauthId}' ",'user_id');
		if($oauthUserRow)
		{
			//oauth_useruser
			$tempRow = $userObj->getObj("id = '{$oauthUserRow['user_id']}'");
			if(!$tempRow)
			{
				$oauthUserObj->del("oauth_user_id = '{$userInfo['id']}' and oauth_id = '{$oauthId}' ");
			}
		}

    	//oauth_useruser
    	if(isset($tempRow) && $tempRow)
    	{
    		$userRow = plugin::trigger("isValidUser",array($tempRow['username'],$tempRow['password']));
    		plugin::trigger("userLoginCallback",$userRow);
    		$callback = plugin::trigger('getCallback');
    		$callback = $callback ? $callback : "/ucenter/index";
			$this->redirect($callback);
    	}
    	//
    	else
    	{
	    	$userCount = $userObj->getObj("username = '{$userInfo['name']}'",'count(*) as num');

	    	//
	    	if($userCount['num'] == 0)
	    	{
	    		$username = $userInfo['name'];
	    	}
	    	else
	    	{
	    		//
	    		$username = $userInfo['name'].$userCount['num'];
	    	}
			$userInfo['name'] = $username;
	    	ISession::set('oauth_id',$oauthId);
	    	ISession::set('oauth_userInfo',$userInfo);
	    	$this->setRenderData($userInfo);
	    	$this->redirect('bind_user',false);
    	}
    }

	//
    public function bind_exists_user()
    {
    	$login_info     = IReq::get('login_info');
    	$password       = IReq::get('password');
    	$oauth_id       = IFilter::act(ISession::get('oauth_id'));
    	$oauth_userInfo = IFilter::act(ISession::get('oauth_userInfo'));

    	if(!$oauth_id || !$oauth_userInfo || !isset($oauth_userInfo['id']))
    	{
    		IError::show("oauth");
    	}

    	if($userRow = plugin::trigger("isValidUser",array($login_info,md5($password))))
    	{
    		$oauthUserObj = new IModel('oauth_user');

    		//
    		$oauthUserData = array(
    			'oauth_user_id' => $oauth_userInfo['id'],
    			'oauth_id'      => $oauth_id,
    			'user_id'       => $userRow['user_id'],
    			'datetime'      => ITime::getDateTime(),
    		);
    		$oauthUserObj->setData($oauthUserData);
    		$oauthUserObj->add();

    		plugin::trigger("userLoginCallback",$userRow);

			//
			$this->redirect('/site/success?message='.urlencode(""));
    	}
    	else
    	{
    		$this->setError("");
    		$_GET['bind_type'] = 'exists';
    		$this->redirect('bind_user',false);
    		Util::showMessage("");
    	}
    }

	//
    public function bind_not_exists_user()
    {
    	$oauth_id       = IFilter::act(ISession::get('oauth_id'));
    	$oauth_userInfo = IFilter::act(ISession::get('oauth_userInfo'));

    	if(!$oauth_id || !$oauth_userInfo || !isset($oauth_userInfo['id']))
    	{
    		IError::show("oauth");
    	}

    	//_userInfo
		$result = plugin::trigger('userRegAct');
		if(is_array($result))
		{
			//
			$oauthUserObj = new IModel('oauth_user');
			$oauthUserData = array(
				'oauth_user_id' => $oauth_userInfo['id'],
				'oauth_id'      => $oauth_id,
				'user_id'       => $result['id'],
				'datetime'      => ITime::getDateTime(),
			);
			$oauthUserObj->setData($oauthUserData);
			$oauthUserObj->add();
			$msg = isset($result['msg']) ? $result['msg'] : "~";
			$this->redirect('/site/success?message='.urlencode($msg));
		}
		else
		{
    		$this->setError($result);
    		$this->redirect('bind_user',false);
    		Util::showMessage($result);
		}
    }

	/**
	 * @brief 
	 */
	public function seller_reg()
	{
//		$seller_name = IValidate::name(IReq::get('seller_name')) ? IReq::get('seller_name') : "";
		$email       = IValidate::email(IReq::get('email'))      ? IReq::get('email')       : "";
//		$truename    = IValidate::name(IReq::get('true_name'))   ? IReq::get('true_name')   : "";
		$phone       = IValidate::phone(IReq::get('phone'))      ? IReq::get('phone')       : "";
		$mobile      = IValidate::mobi(IReq::get('mobile'))      ? IReq::get('mobile')      : "";
		$home_url    = IValidate::url(IReq::get('home_url'))     ? IReq::get('home_url')    : "";

		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$address     = IFilter::act(IReq::get('address'));

$seller_name = IFilter::act(IReq::get('seller_name'));
$truename    = IFilter::act(IReq::get('true_name'));
$person_name = IFilter::act(IReq::get('person_name'));

		if($password == '')
		{
			$errorMsg = '';
		}

		if($password != $repassword)
		{
			$errorMsg = '';
		}

		if(!$seller_name)
		{
			$errorMsg = '';
		}

if(!$truename || chinese_ratio($truename)<0.5) error_back('');

if(!$person_name || chinese_ratio($person_name)<0.8) error_back('');

if(!$address || chinese_ratio($address)<0.5) error_back('');

if(!$mobile) error_back('');

		//
		$sellerDB = new IModel("seller");
		if($seller_name && $sellerDB->getObj("seller_name = '{$seller_name}'"))
		{
//			$errorMsg = "";
error_back("");
		}
		else if($truename && $sellerDB->getObj("true_name = '{$truename}'"))
		{
//			$errorMsg = "";
error_back("");
		}

		//
		if(isset($errorMsg))
		{
			$this->sellerRow = IFilter::act($_POST,'text');
			$this->redirect('seller',false);
			Util::showMessage($errorMsg);
		}

		//
		$sellerRow = array(
			'true_name' => $truename,
			'phone'     => $phone,
			'mobile'    => $mobile,
			'email'     => $email,
			'address'   => $address,
			'province'  => $province,
			'city'      => $city,
			'area'      => $area,
			'home_url'  => $home_url,
			'is_lock'   => 1,

'person_name' => $person_name,
		);

		//,
		if( (isset($_FILES['paper_img']['name']) && $_FILES['paper_img']['name']) &&
		    (isset($_FILES['weixin']['name']) && $_FILES['weixin']['name']) &&
		    (isset($_FILES['person_id']['name']) && $_FILES['person_id']['name']) )
		{
		    $uploadDir = IWeb::$app->config['upload'].'/seller';
		    $uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();
			if(isset($photoInfo['paper_img']['img']) && $photoInfo['paper_img']['img'])
			{
				$sellerRow['paper_img'] = $photoInfo['paper_img']['img'];
			}
			if(isset($photoInfo['weixin']['img']) && $photoInfo['weixin']['img'])
			{
				$sellerRow['weixin'] = $photoInfo['weixin']['img'];
			}
			if(isset($photoInfo['person_id']['img']) && $photoInfo['person_id']['img'])
			{
				$sellerRow['person_id'] = $photoInfo['person_id']['img'];
			}
		}

		$sellerRow['seller_name'] = $seller_name;
		$sellerRow['password']    = md5($password);
		$sellerRow['create_time'] = ITime::getDateTime();

		$sellerDB->setData($sellerRow);
		$seller_id = $sellerDB->add();

		//
		plugin::trigger('sellerRegFinish',$seller_id);
		$this->redirect('/site/success?message='.urlencode(""));
	}
	//
	public function exceptCartGoodsAjax()
	{
		$data    = IFilter::act(IReq::get('data'));
		$data    = $data ? join(",",$data) : "";
		$cartObj = new Cart();
		$result  = $cartObj->setUnselected($data);

//		echo JSON::encode(array("result" => $result));
echo JSON::encode(array("result" => $result), true);

	}
}



function chinese_ratio($str){
   $str = trim($str);
   $chars = mb_strlen($str,"utf-8");
   preg_match_all("/[\x{4e00}-\x{9fa5}]/siu",$str,$match);
   $chinese_chars = sizeof($match[0]);
   return $chinese_chars / $chars;
}


function error_back($errormsg){
   message_back($errormsg);
}


?>