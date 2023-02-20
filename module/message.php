<?php
/**
 * @brief 
 * @class Message
 * @note  
 */
class Message extends IController implements adminAuthorization
{
	public $checkRight = 'all';
	public $layout     = 'admin';
	private $data      = array();

	function init()
	{

	}

	//
	function registry_del()
	{
		$ids = IFilter::act(IReq::get('id'),'int');
		if(!$ids)
		{
			$this->redirect('registry_list',false);
			Util::showMessage('');
			exit;
		}

		if(is_array($ids))
		{
			$ids = join(',',$ids);
		}

		$registryObj = new IModel('email_registry');
		$registryObj->del('id in ('.$ids.')');
		$this->redirect('registry_list');
	}

	/**
	 * @brief 
	 */
	function notify_del()
	{
		$notify_ids = IFilter::act(IReq::get('check'),'int');
		if($notify_ids)
		{
			$ids = join(',',$notify_ids);
			$tb_notify = new IModel('notify_registry');
			$where = "id in (".$ids.")";
			$tb_notify->del($where);
		}
		$this->redirect('notify_list');
	}

	/**
	 * @brief 
	 */
	function notify_email_send()
	{
		$smtp  = new SendMail();
		$error = $smtp->getError();

		if($error)
		{
			$return = array(
				'isError' => true,
				'message' => $error,
			);
			echo JSON::encode($return);
			exit;
		}

		$notify_ids = IFilter::act(IReq::get('notifyid'));
		if($notify_ids && is_array($notify_ids))
		{
			$ids = join(',',$notify_ids);
			$query = new IQuery("notify_registry as notify");
			$query->join   = "right join goods as goods on notify.goods_id=goods.id ";
			$query->fields = "notify.*,goods.name as goods_name,goods.store_nums";
			$query->where  = "notify.id in(".$ids.")";
			$items = $query->find();

			//0 
			$succeed = 0;
			$failed  = 0;
			$tb_notify_registry = new IModel('notify_registry');

			foreach($items as $value)
			{
				// 
				$notify_status = decbin($value['notify_status']);
				// 
				if (1 == ($notify_status & 1)) {
					$failed++;
					continue;
				}

				$body   = mailTemplate::notify(array('{goodsName}' => $value['goods_name'],'{url}' => IUrl::getHost().IUrl::creatUrl('/site/products/id/'.$value['goods_id'])));
				$status = $smtp->send($value['email'],"",$body);

				if($status)
				{
					//
					$succeed++;
					$notify_status = $notify_status | 1;
					// 
					$notify_status = bindec($notify_status);
					$data = array('notify_time' => ITime::getDateTime(),'notify_status' => $notify_status);
					$tb_notify_registry->setData($data);
					$tb_notify_registry->update('id='.$value['id']);
				}
				else
				{
					//
					$failed++;
				}
			}
		}

		$return = array(
			'isError' => false,
			'count'   => count($items),
			'succeed' => $succeed,
			'failed'  => $failed,
		);
		echo JSON::encode($return);
	}

	/**
	 * @brief 
	 */
	function notify_sms_send()
	{
		$notify_ids = IFilter::act(IReq::get('notifyid'));
		if($notify_ids && is_array($notify_ids))
		{
			$ids = join(',',$notify_ids);
			$query = new IQuery("notify_registry as notify");
			$query->join   = "right join goods as goods on notify.goods_id=goods.id ";
			$query->fields = "notify.*,goods.name as goods_name,goods.store_nums";
			$query->where  = "notify.id in(".$ids.")";
			$items = $query->find();

			//0 
			$succeed = 0;
			$failed  = 0;
			$tb_notify_registry = new IModel('notify_registry');

			foreach($items as $value)
			{
				// 
				$notify_status = decbin($value['notify_status']);
				// 
				if (10 == ($notify_status & 10)) {
					$failed++;
					continue;
				}

				$send_result = _hsms::notify($value['mobile'], array('{goodsName}' => $value['goods_name'],'{url}' => IUrl::getHost().IUrl::creatUrl('/site/products/id/'.$value['goods_id'])));
				if($send_result == 'success')
				{
					//
					$succeed++;
					$notify_status = $notify_status | 10;
					// 
					$notify_status = bindec($notify_status);
					$data = array('notify_time' => ITime::getDateTime(),'notify_status' => $notify_status);
					$tb_notify_registry->setData($data);
					$tb_notify_registry->update('id='.$value['id']);
				}
				else
				{
					//
					$failed++;
				}
			}
		}

		$return = array(
			'isError' => false,
			'count'   => count($items),
			'succeed' => $succeed,
			'failed'  => $failed,
		);
		echo JSON::encode($return);
	}

	/**
	 * @brief 
	 */
	function registry_message_send()
	{
		set_time_limit(0);
		$ids     = IFilter::act(IReq::get('ids'),'int');
		$title   = IFilter::act(IReq::get('title'));
		$content = IReq::get("content");

		$smtp  = new SendMail();
		$error = $smtp->getError();

		$list = array();
		$tb   = new IModel("email_registry");

		$ids_sql = "1";
		if($ids)
		{
			$ids_sql = "id IN ({$ids})";
		}

		$start = 0;
		$query = new IQuery("email_registry");
		$query->fields = "email";
		$query->order  = "id DESC";
		$query->where  = $ids_sql;

		do
		{
			$query->limit = "{$start},50";
			$list = $query->find();
			if(!$list)
			{
				die('');
				break;
			}
			$start += 51;

			$to = array_pop($list);
			$to = $to['email'];
			$bcc = array();
			foreach($list as $value)
			{
				$bcc[] = $value['email'];
			}
			$bcc = join(";",$bcc);
			$result = $smtp->send($to,$title,$content,$bcc);
			if(!$result)
			{
				die('');
			}
		}
		while(count($list)>=50);
		echo "success";
	}

	/**
	 * @brief 
	 */
	function marketing_sms_list()
	{
		$tb_user_group = new IModel('user_group');
		$data_group = $tb_user_group->query();
		$data_group = is_array($data_group) ? $data_group : array();
		$group      = array();
		foreach($data_group as $value)
		{
			$group[$value['id']] = $value['group_name'];
		}
		$this->data['group'] = $group;

		$this->setRenderData($this->data);
		$this->redirect('marketing_sms_list');
	}

	/**
	 * @brief 
	 */
	function marketing_sms_send()
	{
		$this->layout = '';
		$this->redirect('marketing_sms_send');
	}

	/**
	 * @brief 
	 */
	function start_marketing_sms()
	{
		set_time_limit(0);
		$info    = IFilter::act(IReq::get('search'));
		$info    = $info ? array_filter($info) : null;
		$content = IFilter::act(IReq::get('content'));

		if(!$content)
		{
			die('<script type="text/javascript">parent.startMarketingSmsCallback("");</script>');
		}

		$list = array();
		$offset = 0;
		// 
		$length = 50;
		$succeed = 0;

		$query = new IQuery("member");
		$query->fields = "mobile";
		$query->order  = "user_id DESC";

		//
		if($info)
		{
    		$searchRes = Util::searchUser($info);
            if($searchRes)
            {
                $userIds = $searchRes['id'];
                $revInfo = $searchRes['note'];

    			$query->where = "user_id IN ({$userIds}) AND `mobile` IS NOT NULL AND `mobile`!='' ";
    			$list = $query->find();
            }
		}
		//
		else
		{
		    $revInfo = "";
			$query->where = " `mobile` IS NOT NULL AND `mobile`!='' ";
			$list = $query->find();
		}

		if($list)
		{
			$mobile_array = array();
			foreach ($list as $value)
			{
				if(false != IValidate::mobi($value['mobile']))
				{
					$mobile_array[] = $value['mobile'];
				}
			}
			unset($list);
			$mobile_count = count($mobile_array);
			if (0 < $mobile_count)
			{
				$send_num = ceil($mobile_count / $length);
				for ($i = 0; $i < $send_num; $i++)
				{
					$mobiles = array_slice($mobile_array, $offset, $length);
					$mobile_string = implode(",", $mobiles);
					$send_result = Hsms::send($mobile_string, $content, 0);
					if($send_result == 'success')
					{
						$succeed += count($mobiles);
					}
					$offset += $length;
				}
			}
		}
		else
		{
		    die('<script type="text/javascript">parent.startMarketingSmsCallback("");</script>');
		}

		//marketing_sms
		$tb_marketing_sms = new IModel('marketing_sms');
		$tb_marketing_sms->setData(array(
			'content'=>$content,
			'send_nums' =>$succeed,
			'time'=> ITime::getDateTime(),
			'rev_info' => $revInfo,
		));
		$tb_marketing_sms->add();
		die('<script type="text/javascript">parent.startMarketingSmsCallback("success");</script>');
	}

	/**
	 * @brief 
	 */
	function marketing_sms_del()
	{
		$refer_ids = IFilter::act(IReq::get('check'),'int');
		$refer_ids = is_array($refer_ids) ? $refer_ids : array($refer_ids);
		if($refer_ids)
		{
			$ids = implode(',',$refer_ids);
			if($ids)
			{
				$tb_refer = new IModel('marketing_sms');
				$where = "id in (".$ids.")";
				$tb_refer->del($where);
			}
		}
		$this->marketing_sms_list();
	}

	//
	function marketing_sms_show()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$item = Api::run("getMarketingRowById",array('id'=>$id));
		if(!$item)
		{
			IError::show(403,'');
		}
		$this->setRenderData(array('smsRow' => $item));
		$this->redirect('marketing_sms_show');
	}
}