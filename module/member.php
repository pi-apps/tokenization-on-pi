<?php
/**
 * @brief 
 * @class Member
 * @note  
 */
class Member extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
    public $layout='admin';
	private $data = array();

	function init()
	{

	}

	/**
	 * @brief 
	 */
	function member_edit()
	{
		$uid  = IFilter::act(IReq::get('uid'),'int');
		$userData = array();

		//
		if($uid)
		{
			$userData = Api::run('getMemberInfo',$uid);
			if(!$userData)
			{
				$this->member_list();
				Util::showMessage("");
				exit;
			}
		}
		$this->setRenderData(array('userData' => $userData));
		$this->redirect('member_edit');
	}

	//
	function member_save()
	{
		$user_id    = IFilter::act(IReq::get('user_id'),'int');
		$user_name  = IFilter::act(IReq::get('username'));
		$email      = IFilter::act(IReq::get('email'));
		$password   = IReq::get('password');
		$repassword = IReq::get('repassword');
		$group_id   = IFilter::act(IReq::get('group_id'),'int');
		$truename   = IFilter::act(IReq::get('true_name'));
		$sex        = IFilter::act(IReq::get('sex'),'int');
		$telephone  = IFilter::act(IReq::get('telephone'));
		$mobile     = IFilter::act(IReq::get('mobile'));
		$birthday   = IFilter::act(IReq::get('birthday'));
		$province   = IFilter::act(IReq::get('province'),'int');
		$city       = IFilter::act(IReq::get('city'),'int');
		$area       = IFilter::act(IReq::get('area'),'int');
		$contact_addr = IFilter::act(IReq::get('contact_addr'));
		$zip        = IFilter::act(IReq::get('zip'));
		$qq         = IFilter::act(IReq::get('qq'));
		$exp        = IFilter::act(IReq::get('exp'),'int');
		$point      = IFilter::act(IReq::get('point'),'int');
		$status     = IFilter::act(IReq::get('status'),'int');

		$_POST['area'] = "";
		if($province && $city && $area)
		{
			$_POST['area'] = ",{$province},{$city},{$area},";
		}

		if(!$user_id && $password == '')
		{
			$this->setError('');
		}

		if($password != $repassword)
		{
			$this->setError('');
		}

		//
		$userDB   = new IModel("user");
		$memberDB = new IModel("member");

		if($userDB->getObj("username='".$user_name."' and id != ".$user_id))
		{
			$this->setError('');
		}

		if($email && $memberDB->getObj("email='".$email."' and user_id != ".$user_id))
		{
			$this->setError('');
		}

		if($mobile && $memberDB->getObj("mobile='".$mobile."' and user_id != ".$user_id))
		{
			$this->setError('');
		}

		//
		if($errorMsg = $this->getError())
		{
			$this->setRenderData(array('userData' => $_POST));
			$this->redirect('member_edit',false);
			Util::showMessage($errorMsg);
		}

		$member = array(
			'email'        => $email,
			'true_name'    => $truename,
			'telephone'    => $telephone,
			'mobile'       => $mobile,
			'area'         => $_POST['area'],
			'contact_addr' => $contact_addr,
			'qq'           => $qq,
			'sex'          => $sex,
			'zip'          => $zip,
			'exp'          => $exp,
			'point'        => $point,
			'group_id'     => $group_id,
			'status'       => $status,
			'birthday'     => $birthday,
		);

		//
		if(!$user_id)
		{
			$user = array(
				'username' => $user_name,
				'password' => md5($password),
			);
			$userDB->setData($user);
			$user_id = $userDB->add();

			$member['user_id'] = $user_id;
			$member['time']    = ITime::getDateTime();

			$memberDB->setData($member);
			$memberDB->add();
		}
		//
		else
		{
			$user = array(
				'username' => $user_name,
			);
			//
			if($password)
			{
				$user['password'] = md5($password);
			}
			$userDB->setData($user);
			$userDB->update('id = '.$user_id);

			$member_info = $memberDB->getObj('user_id='.$user_id);

			//
			if($point != $member_info['point'])
			{
				$ctrlType = $point > $member_info['point'] ? '' : '';
				$diffPoint= $point-$member_info['point'];

				$pointObj = new Point();
				$pointConfig = array(
					'user_id' => $user_id,
					'point'   => $diffPoint,
					'log'     => ''.$this->admin['admin_name'].''.$ctrlType.$diffPoint.'',
				);
				$pointObj->update($pointConfig);
			}

			$memberDB->setData($member);
			$memberDB->update("user_id = ".$user_id);
		}
		$this->redirect('member_list');
	}

	/**
	 * @brief 
	 */
	function member_list()
	{
		$search = IFilter::act(IReq::get('search'),'strict');
		$keywords = IFilter::act(IReq::get('keywords'));
		$where = ' 1 ';
		if($search && $keywords)
		{
			$where .= " and $search like '%{$keywords}%' ";
		}
		$this->data['search'] = $search;
		$this->data['keywords'] = $keywords;
		$tb_user_group = new IModel('user_group');
		$data_group = $tb_user_group->query();
		$group      = array();
		foreach($data_group as $value)
		{
			$group[$value['id']] = $value['group_name'];
		}
		$this->data['group'] = $group;
		$this->setRenderData($this->data);
        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("user as u");
        $query->join   = 'left join member as m on m.user_id = u.id';
        $query->where  = 'm.status != 2 and '.$where;
        $query->fields = 'm.*,u.username';
        $query->order  = 'm.user_id desc';
        $query->page   = $page;
        $this->query   = $query;
		$this->redirect('member_list');
	}

	/**
	 * 
	 */
	function member_balance()
	{
		$this->layout = '';
		$this->redirect('member_balance');
	}
	/**
	 * @brief 
	 */
	function member_reclaim()
	{
		$user_ids = IReq::get('check');
		$user_ids = is_array($user_ids) ? $user_ids : array($user_ids);
		$user_ids = IFilter::act($user_ids,'int');
		if($user_ids)
		{
			$ids = implode(',',$user_ids);
			if($ids)
			{
				$tb_member = new IModel('member');
				$tb_member->setData(array('status'=>'2'));
				$where = "user_id in (".$ids.")";
				$tb_member->update($where);
			}
		}
		$this->member_list();
	}
	//
    function member_recharge()
    {
    	$id       = IFilter::act(IReq::get('check'),'int');
    	$balance  = IFilter::act(IReq::get('balance'),'float');
    	$type     = IFIlter::act(IReq::get('type')); // recharge,withdraw
    	$even     = '';

    	if(!$id)
    	{
			die(JSON::encode(array('flag' => 'fail','message' => '')));
			return;
    	}

    	//
    	$id = is_array($id) ? join(',',$id) : $id;
    	$memberDB = new IModel('member');
    	$memberData = $memberDB->query('user_id in ('.$id.')');

		foreach($memberData as $value)
		{
			//account_log
			$log = new AccountLog();
			$config=array
			(
				'user_id'  => $value['user_id'],
				'admin_id' => $this->admin['admin_id'],
				'event'    => $type,
				'num'      => $balance,
				'way'      => '',
			);
			$re = $log->write($config);
			if($re == false)
			{
				die(JSON::encode(array('flag' => 'fail','message' => $log->error)));
			}
		}
		die(JSON::encode(array('flag' => 'success')));
    }
	/**
	 * @brief 
	 */
	function group_edit()
	{
		$gid = (int)IReq::get('gid');
		// 
		if($gid)
		{
			$db = new IModel('user_group');
			$group_info = $db->getObj($gid);

			if($group_info)
			{
				$this->data['group'] = $group_info;
			}
			else
			{
				$this->redirect('group_list',false);
				Util::showMessage("");
				return;
			}
		}
		$this->setRenderData($this->data);
		$this->redirect('group_edit');
	}

	/**
	 * @brief 
	 */
	function group_save()
	{
		$group_id = IFilter::act(IReq::get('id'),'int');
		$maxexp   = IFilter::act(IReq::get('maxexp'),'int');
		$minexp   = IFilter::act(IReq::get('minexp'),'int');
		$discount = IFilter::act(IReq::get('discount'),'float');
		$group_name = IFilter::act(IReq::get('group_name'));

		$group = array(
			'maxexp' => $maxexp,
			'minexp' => $minexp,
			'discount' => $discount,
			'group_name' => $group_name
		);

		if($discount > 100)
		{
			$errorMsg = '100';
		}

		if($maxexp <= $minexp)
		{
			$errorMsg = '';
		}

		if(isset($errorMsg) && $errorMsg)
		{
			$group['id'] = $group_id;
			$data = array($group);

			$this->setRenderData($data);
			$this->redirect('group_edit',false);
			Util::showMessage($errorMsg);
			exit;
		}
		$tb_user_group = new IModel("user_group");
		$tb_user_group->setData($group);

		if($group_id)
		{
			$affected_rows = $tb_user_group->update("id=".$group_id);
			$this->redirect('group_list');
		}
		else
		{
			$tb_user_group->add();
			$this->redirect('group_list');
		}
	}

	/**
	 * @brief 
	 */
	function group_del()
	{
		$group_ids = IReq::get('check');
		$group_ids = is_array($group_ids) ? $group_ids : array($group_ids);
		$group_ids = IFilter::act($group_ids,'int');
		if($group_ids)
		{
			$ids = implode(',',$group_ids);
			if($ids)
			{
				$tb_user_group = new IModel('user_group');
				$where = "id in (".$ids.")";
				$tb_user_group->del($where);
			}
		}
		$this->redirect('group_list');
	}

	/**
	 * @brief 
	 */
	function recycling()
	{
		$tb_user_group = new IModel('user_group');
		$data_group    = $tb_user_group->query();
		$group         = array();
		foreach($data_group as $value)
		{
			$group[$value['id']] = $value['group_name'];
		}
		$this->data['group'] = $group;
		$this->setRenderData($this->data);
        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("member as m");
        $query->join   = 'left join user as u on m.user_id = u.id left join user_group as gp on m.group_id = gp.id';
        $query->where  = 'm.status = 2';
        $query->fields = 'm.*,u.username,gp.group_name';
        $query->order  = 'm.user_id desc';
        $query->page   = $page;
        $this->query   = $query;
		$this->redirect('recycling');
	}

	/**
	 * @brief 
	 */
	function member_del()
	{
		$user_ids = IReq::get('check');
		$user_ids = is_array($user_ids) ? $user_ids : array($user_ids);
		$user_ids = IFilter::act($user_ids,'int');
		if($user_ids)
		{
			$ids = implode(',',$user_ids);

			if($ids)
			{
				$tb_member = new IModel('member');
				$where = "user_id in (".$ids.")";
				$tb_member->del($where);

				$tb_user = new IModel('user');
				$where = "id in (".$ids.")";
				$tb_user->del($where);

				$logObj = new log('db');
				$logObj->write('operation',array(":".$this->admin['admin_name'],"","ID".$ids));
			}
		}
		$this->redirect('member_list');
	}

	/**
	 * @brief 
	 */
	function member_restore()
	{
		$user_ids = IReq::get('check');
		$user_ids = is_array($user_ids) ? $user_ids : array($user_ids);
		if($user_ids)
		{
			$user_ids = IFilter::act($user_ids,'int');
			$ids = implode(',',$user_ids);
			if($ids)
			{
				$tb_member = new IModel('member');
				$tb_member->setData(array('status'=>'1'));
				$where = "user_id in (".$ids.")";
				$tb_member->update($where);
			}
		}
		$this->redirect('recycling');
	}

	//[] ajax
	function withdraw_del()
	{
		$id = IFilter::act(IReq::get('id'));

		if($id)
		{
			$id = IFilter::act($id,'int');
			$withdrawObj = new IModel('withdraw');

			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$withdrawObj->del($where);
		}
	}

	//[] 
	function withdraw_detail()
	{
		$id = IFilter::act( IReq::get('id'),'int' );

		if($id)
		{
			$withdrawObj = new IModel('withdraw');
			$this->withdrawRow = $withdrawObj->getObj($id);

			$userDB = new IModel('user as u,member as m');
			$this->userRow = $userDB->getObj('u.id = m.user_id and u.id = '.$this->withdrawRow['user_id']);
			$this->redirect('withdraw_detail');
		}
		else
		{
			$this->redirect('withdraw_list');
		}
	}

	//
	function pay_countfee()
	{
		set_time_limit(0);
		ini_set("max_execution_time",0);
		$ids  = IFilter::act(IReq::get('ids'),'int');
		$type = IFilter::act(IReq::get('type'));

		if(!$ids)
		{
			die('');
		}

		$withdrawObj = new IModel('withdraw');
		$memberDB    = new IModel('member');
		$error       = '';
		$successNum  = 0;
		$failNum     = 0;
		foreach($ids as $id)
		{
			$withdrawRow = $withdrawObj->getObj($id);
			$memberRow   = $memberDB->getObj('user_id = '.$withdrawRow['user_id'],'balance');
			if($memberRow['balance'] < $withdrawRow['amount'])
			{
				$failNum++;
				$error .= $withdrawRow['name'].'';
				continue;
			}

			$payCheck = false;
			$billNo   = 'T'.Order_Class::createOrderNum();
			switch($type)
			{
				//
				case "wechatBalance":
				{
					include_once(dirname(__FILE__)."/../plugins/transfer/wechatBalance.php");
					$oauthUserDB = new IModel('oauth_user');

					//openid
					$relationRow = $oauthUserDB->getObj('user_id = '.$withdrawRow['user_id']);
					if($relationRow && ($relationRow['openid'] || $relationRow['openid_mini']))
					{
						if($relationRow['openid'])
						{
							$openid = $relationRow['openid'];
							$payment_id = wechatBalance::WAP_WECHAT_PAYMENT_ID;
						}
						else
						{
							$openid = $relationRow['openid_mini'];
							$payment_id = wechatBalance::MINI_WECHAT_PAYMENT_ID;
						}

						//
						$data = [
							'openid'           => $openid,
							're_user_name'     => $withdrawRow['name'],
							'amount'           => $withdrawRow['amount'],
							'desc'             => IWeb::$app->getController()->_siteConfig->name.'',
							'partner_trade_no' => $billNo,
							'payment_id'       => $payment_id,
						];

						$transferObj = new wechatBalance();
						$tranResult = $transferObj->run($data);
						if(is_array($tranResult) && $tranResult['result_code'] == 'SUCCESS')
						{
							$PayNo    = $tranResult['payment_no'];
							$payCheck = true;
						}
						else
						{
							$error .= $tranResult;
						}
					}
					else
					{
						$error .= "ID[".$withdrawRow['name']."] ";
					}
				}
				break;

				//
				case "offline":
				{
					$PayNo    = '88888888';
					$payCheck = true;
				}
				break;
			}

			if($payCheck == true)
			{
				//account_log
				$log    = new AccountLog();
				$config = [
					'user_id'  => $withdrawRow['user_id'],
					'admin_id' => $this->admin['admin_id'],
					'event'    => "withdraw",
					'num'      => $withdrawRow['amount'],
					'way'      => AccountLog::way($type),
				];
				$result = $log->write($config);
				if($result == false)
				{
					$failNum++;
					$error .= "".$withdrawRow['name']."".$result->error;
				}
				else
				{
					$successNum++;

					$withdrawObj->setData(['way' => $config['way'],'status' => 2,'pay_no' => $PayNo,'finish_time' => ITime::getDateTime()]);
					$withdrawObj->update($id);

					//
					plugin::trigger('withdrawStatusUpdate',$id);
				}
			}
			else
			{
				$failNum++;
			}
		}

		die(''.$successNum.'; '.$failNum.'; '.$error);
	}

	//[] 
	function withdraw_status()
	{
		$id      = IFilter::act( IReq::get('id'),'int');
		$re_note = IFilter::act( IReq::get('re_note'),'string');
		$status  = IFilter::act(IReq::get('status'),'int');

		if($id)
		{
			$withdrawObj = new IModel('withdraw');
			$withdrawObj->setData([
				'status'  => $status,
				're_note' => $re_note,
			]);
			$withdrawObj->update($id);
		}

		$this->redirect('withdraw_list');
	}

    //
    public function seller_list()
    {
        $where = Util::search(IReq::get('search'));
        $page  = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query = new IQuery("seller");
        $query->where = 'is_del = 0 and '.$where;
        $query->order = 'id desc';
        $query->page  = $page;
        $this->query  = $query;
        $this->redirect('seller_list');
    }

	/**
	 * @brief 
	 */
	public function seller_edit()
	{
		$seller_id = IFilter::act(IReq::get('id'),'int');

		//
		if($seller_id)
		{
			$sellerDB        = new IModel('seller');
			$this->sellerRow = $sellerDB->getObj('id = '.$seller_id);
		}
		$this->redirect('seller_edit');
	}

	/**
	 * @brief 
	 */
	public function seller_add()
	{
		$seller_id   = IFilter::act(IReq::get('id'),'int');
		$seller_name = IFilter::act(IReq::get('seller_name'));
		$email       = IFilter::act(IReq::get('email'));
		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		$truename    = IFilter::act(IReq::get('true_name'));
		$phone       = IFilter::act(IReq::get('phone'));
		$mobile      = IFilter::act(IReq::get('mobile'));
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$cash        = IFilter::act(IReq::get('cash'),'float');
		$is_vip      = IFilter::act(IReq::get('is_vip'),'int');
		$is_lock     = IFilter::act(IReq::get('is_lock'),'int');
		$address     = IFilter::act(IReq::get('address'));
		$account     = IFilter::act(IReq::get('account'));
		$server_num  = IFilter::act(IReq::get('server_num'));
		$home_url    = IFilter::act(IReq::get('home_url'));
		$sort        = IFilter::act(IReq::get('sort'),'int');
		$discount     = IFilter::act(IReq::get('discount'),'float');

		if(!$seller_id && $password == '')
		{
			$errorMsg = '';
		}

		if($password != $repassword)
		{
			$errorMsg = '';
		}

		//
		$sellerDB = new IModel("seller");

		if($sellerDB->getObj("seller_name = '{$seller_name}' and id != {$seller_id}"))
		{
			$errorMsg = "";
		}
		else if($sellerDB->getObj("true_name = '{$truename}' and id != {$seller_id}"))
		{
			$errorMsg = "";
		}

		//
		if(isset($errorMsg))
		{
			$this->sellerRow = $_POST;
			$this->redirect('seller_edit',false);
			Util::showMessage($errorMsg);
		}

		//
		$sellerRow = array(
			'true_name' => $truename,
			'account'   => $account,
			'phone'     => $phone,
			'mobile'    => $mobile,
			'email'     => $email,
			'address'   => $address,
			'is_vip'    => $is_vip,
			'is_lock'   => $is_lock,
			'cash'      => $cash,
			'province'  => $province,
			'city'      => $city,
			'area'      => $area,
			'server_num'=> $server_num,
			'home_url'  => $home_url,
			'sort'      => $sort,
		    'discount'   => $discount,
		);

		//$_FILE
		if($_FILES)
		{
		    $uploadDir = IWeb::$app->config['upload'].'/seller';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();

			//
			if(isset($photoInfo['paper_img']['img']) && $photoInfo['paper_img']['img'])
			{
				$sellerRow['paper_img'] = $photoInfo['paper_img']['img'];
			}

			//logo
			if(isset($photoInfo['logo']['img']) && $photoInfo['logo']['img'])
			{
				$sellerRow['logo'] = $photoInfo['logo']['img'];
			}
		}

		//
		if(!$seller_id)
		{
			$sellerRow['seller_name'] = $seller_name;
			$sellerRow['password']    = md5($password);
			$sellerRow['create_time'] = ITime::getDateTime();

			$sellerDB->setData($sellerRow);
			$sellerDB->add();
		}
		//
		else
		{
			//
			if($password)
			{
				$sellerRow['password'] = md5($password);
			}

			$sellerDB->setData($sellerRow);
			$sellerDB->update("id = ".$seller_id);
		}
		$this->redirect('seller_list');
	}
	/**
	 * @brief 
	 */
	public function seller_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$sellerDB = new IModel('seller');
		$data = array('is_del' => 1);
		$sellerDB->setData($data);

		if(is_array($id))
		{
			$sellerDB->update('id in ('.join(",",$id).')');
		}
		else
		{
			$sellerDB->update('id = '.$id);
		}
		$this->redirect('seller_list');
	}
	/**
	 * @brief 
	 */
	public function seller_recycle_del()
	{
		$id       = IFilter::act(IReq::get('id'),'int');
		$sellerDB = new IModel('seller');

		if(is_array($id))
		{
			$id = join(",",$id);
		}

		//
		$sellerExtTable = array("merch_ship_info","spec","delivery_extend","category_seller","delivery_doc","promotion","regiment","ticket","bill","takeself","prop","goods_extend_preorder_discount","goods_extend_preorder_disnums");
		foreach($sellerExtTable as $tableName)
		{
			$selletExtDB = new IModel($tableName);
			$selletExtDB->del('seller_id in ('.$id.')');
		}
		$sellerDB->del('id in ('.$id.')');
		$this->redirect('seller_recycle_list');
	}
	/**
	 * @brief 
	 */
	public function seller_recycle_restore()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$sellerDB = new IModel('seller');
		$data = array('is_del' => 0);
		$sellerDB->setData($data);
		if(is_array($id))
		{
			$sellerDB->update('id in ('.join(",",$id).')');
		}
		else
		{
			$sellerDB->update('id = '.$id);
		}

		$this->redirect('seller_recycle_list');
	}
	//ajax
	public function ajax_seller_lock()
	{
		$id   = IFilter::act(IReq::get('id'));
		$lock = IFilter::act(IReq::get('lock'));
		$sellerObj = new IModel('seller');
		$sellerObj->setData(array('is_lock' => $lock));
		$sellerObj->update("id = ".$id);

		//
		plugin::trigger("updateSellerStatus",$id);
	}

	//excel
	public function balance_report()
	{
		$memberQuery = new IQuery('member as m');
		$memberQuery->join   = "left join user as u on m.user_id=u.id";
		$memberQuery->fields = "u.username,m.time,m.email,m.mobile,m.balance";
		$memberQuery->where  = "m.balance > 0";
		$memberList          = $memberQuery->find();

		$reportObj = new report('balance');
		$reportObj->setTitle(["","","","",""]);
		foreach($memberList as $k => $val)
		{
			$insertData = array($val['username'],$val['mobile'],$val['time'],$val['email'],$val['balance']);
			$reportObj->setData($insertData);
		}
		$reportObj->toDownload();
	}
}