<?php
/**
 * @brief 
 * @class Goods
 * @note  
 */
class Goods extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
    public $layout = 'admin';
    public $data = array();

	public function init()
	{

	}
	/**
	 * @brief 
	 */
	public function goods_img_upload()
	{
	 	//
		$photoObj = new PhotoUpload();
		$result   = current($photoObj->run());
		echo JSON::encode($result);
	}
    /**
	 * @brief /
	 */
    public function model_update()
    {
    	// POST
    	$model_id   = IFilter::act(IReq::get("model_id"),'int');
    	$model_name = IFilter::act(IReq::get("model_name"));
    	$attribute  = IFilter::act(IReq::get("attr"));

    	//Model
		$modelObj = new Model();

		//
		$result = $modelObj->model_update($model_id,$model_name,$attribute);

		if($result)
		{
			$this->redirect('model_list');
		}
		else
		{
			//post
    		$result = $modelObj->postArrayChange($attribute);
			$this->data = array(
				'id'         => $model_id,
				'name'       => $model_name,
				'model_attr' => $result['model_attr'],
			);
    		$this->setRenderData($this->data);
			$this->redirect('model_edit',false);
		}
    }
	/**
	 * @brief 
	 */
    public function model_edit()
    {
    	// POST
    	$id = IFilter::act(IReq::get("id"),'int');
    	if($id)
    	{
    		//Model
    		$modelObj = new Model();
    		//
			$model_info = $modelObj->get_model_info($id);
			//
			$this->setRenderData($model_info);
    	}
		$this->redirect('model_edit');
    }

	/**
	 * @brief 
	 */
    public function model_del()
    {
    	//POST
    	$id = IFilter::act(IReq::get("id"),'int');
    	$id = !is_array($id) ? array($id) : $id;

    	if($id)
    	{
	    	foreach($id as $key => $val)
	    	{
	    		//goods_attribute
	    		$goods_attrObj = new IModel("goods_attribute");

	    		//
	    		$attrData = $goods_attrObj->query("model_id = ".$val);
	    		if($attrData)
	    		{
	    			$this->redirect('model_list',false);
	    			Util::showMessage("");
	    		}

	    		//Model
	    		$modelObj = new IModel("model");

	    		//
				$result = $modelObj->del("id = ".$val);
	    	}
    	}
		$this->redirect('model_list');
    }

	/**
	 * @breif 
	 * */
	function member_price()
	{
		$this->layout = '';

		$goods_id   = IFilter::act(IReq::get('goods_id'),'int');
		$product_id = IFilter::act(IReq::get('product_id'),'int');
		$sell_price = IFilter::act(IReq::get('sell_price'),'float');

		$date = array(
			'sell_price' => $sell_price
		);

		if($goods_id)
		{
			$where  = 'goods_id = '.$goods_id;
			$where .= $product_id ? ' and product_id = '.$product_id : '';

			$priceRelationObject = new IModel('group_price');
			$priceData = $priceRelationObject->query($where);
			$date['price_relation'] = $priceData;
		}

		$this->setRenderData($date);
		$this->redirect('member_price');
	}
	/**
	 * @brief 
	 */
	public function goods_edit()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');

		//
		$goods_class = new goods_class();

		//
		$data = $goods_class->edit($goods_id);

		if($goods_id && !$data)
		{
			die("");
		}

        if($data)
        {
            $data['type'] = $data['form']['type'];
        }
        else
        {
            $data = array('type' => IReq::get('type') ? IReq::get('type') : "default");
        }
		$this->setRenderData($data);
		$this->redirect('goods_edit');
	}
	/**
	 * @brief 
	 */
	function goods_update()
	{
		$id       = IFilter::act(IReq::get('id'),'int');
		$callback = IReq::get('callback');
		$callback = strpos($callback,'goods_list') === false ? '' : $callback;

		//
		if(!$_POST)
		{
			die('');
		}

		if($saleRow = Active::isSale($id))
		{
			die('->'.$saleRow['name'].'');
		}

		//
		unset($_POST['id']);
		unset($_POST['callback']);

		$goodsObject = new goods_class();
		$goodsObject->update($id,$_POST);

		//
		$logObj = new log('db');
		$logData = $id ? [":".$this->admin['admin_name'],"","ID".$id."".IFilter::act(IReq::get('name'))] : [":".$this->admin['admin_name'],"","".IFilter::act(IReq::get('name'))];
		$logObj->write('operation',$logData);

		$callback ? $this->redirect($callback) : $this->redirect("goods_list");
	}

	/**
	 * @brief 
	 */
	function goods_del()
	{
		//post
	    $id = IFilter::act(IReq::get('id'),'int');

	    //goods
	    $tb_goods = new IModel('goods');
	    $tb_goods->setData(array('is_del'=>1));
	    if($id)
		{
			$tb_goods->update(Util::joinStr($id));
		}
		else
		{
			die('');
		}
		$this->redirect("goods_list");
	}
	/**
	 * @brief 
	 */
	function goods_stats()
	{
		//post
	    $id   = IFilter::act(IReq::get('id'),'int');
	    $type = IFilter::act(IReq::get('type'));

	    //goods
	    $tb_goods = new IModel('goods');
	    if($type == 'up')
	    {
	    	$updateData = array('is_del' => 0,'up_time' => ITime::getDateTime(),'down_time' => null);
	    }
	    else if($type == 'down')
	    {
	    	$updateData = array('is_del' => 2,'up_time' => null,'down_time' => ITime::getDateTime());
	    }
	    else if($type == 'check')
	    {
	    	$updateData = array('is_del' => 3,'up_time' => null,'down_time' => null);
	    }

	    $tb_goods->setData($updateData);

	    if($id)
		{
			$tb_goods->update(Util::joinStr($id));
		}
		else
		{
			Util::showMessage('');
		}

		if(IClient::isAjax() == false)
		{
			$this->redirect("goods_list");
		}
	}
	/**
	 * @brief 
	 * */
	function goods_recycle_del()
	{
		//post
	    $id = IFilter::act(IReq::get('id'),'int');

	    //goods
	    $goods = new goods_class();
	    if($id)
		{
			if(is_array($id))
			{
				foreach($id as $key => $val)
				{
					$goods->del($val);
				}
			}
			else
			{
				$goods->del($id);
			}
		}

		$this->redirect("goods_recycle_list");
	}
	/**
	 * @brief 
	 * */
	function goods_recycle_restore()
	{
		//post
	    $id = IFilter::act(IReq::get('id'),'int');
	    //goods
	    $tb_goods = new IModel('goods');
	    $tb_goods->setData(array('is_del'=>0));
	    if($id)
		{
			$tb_goods->update(Util::joinStr($id));
		}
		else
		{
			Util::showMessage('');
		}
		$this->redirect("goods_recycle_list");
	}

	/**
	 * @brief 
	 */
	function goods_list()
	{
		//
		$search = IFilter::act(IReq::get('search'));
		$page   = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

		//
		list($join,$where) = goods_class::getSearchCondition($search);
		$searchString      = http_build_query(array('search' => $search));

		//sql
		$goodsHandle = new IQuery('goods as go');
		$goodsHandle->order  = "go.id desc";
		$goodsHandle->fields = "distinct go.id,go.name,go.sell_price,go.market_price,go.store_nums,go.img,go.is_del,go.seller_id,go.is_share,go.sort,go.promo,go.type,go.spec_array";
		$goodsHandle->page   = $page;
		$goodsHandle->where  = $where;
		$goodsHandle->join   = $join;
		$this->goodsHandle   = $goodsHandle;
		$this->setRenderData(array('search' => $searchString));
		$this->redirect("goods_list");
	}

	// Excel
	public function goods_report()
	{
		//
		$search = IFilter::act(IReq::get('search'));
		//
		list($join,$where) = goods_class::getSearchCondition($search);
		$join .= ' left join products as pro on go.id = pro.goods_id ';
		//sql
		$goodsHandle = new IQuery('goods as go');
		$goodsHandle->order    = "go.id desc";
		$goodsHandle->fields   = "go.id,goods_no,go.name,go.sell_price,go.store_nums,go.sale,go.is_del,go.create_time,go.seller_id,pro.products_no,pro.store_nums as p_store_nums,pro.sell_price as p_sell_price,pro.spec_array";
		$goodsHandle->join     = $join;
		$goodsHandle->where    = $where;
		$goodsList = $goodsHandle->find();

		// Excel table;
		$reportObj = new report('goods');
        $catMax    = 1;
		foreach($goodsList as $k => $val)
		{
		    $catArray = goods_class::getGoodsCategory($val['id']);
		    $itemCount= count($catArray);
		    if($itemCount > $catMax)
		    {
		        $catMax = $itemCount;
		    }

			//
			$specArray = [];
			if($val['spec_array'])
			{
				$tempSpec = JSON::decode($val['spec_array']);
				foreach($tempSpec as $ss)
				{
					$specArray[] = $ss['name'].':'.$ss['value'];
				}
			}

			$insertData = [
				$val['name'],
				$val['products_no']  ? $val['products_no']  : $val['goods_no'],
				join(',',$specArray),
				$val['p_sell_price'] ? $val['p_sell_price'] : $val['sell_price'],
				$val['p_store_nums'] ? $val['p_store_nums'] : $val['store_nums'],
				$val['sale'],
				$val['create_time'],
				goods_class::statusText($val['is_del']),
				$val['seller_id'] ? "" : "",
			];
			$insertData = array_merge($insertData,$catArray);
			$reportObj->setData($insertData);
		}
		$titleArray = ["","","","","","","","",""];
		for($i = 1;$catMax >= $i;$i++)
		{
		    $titleArray[] = "".$i;
		}

		$reportObj->setTitle($titleArray);
		$reportObj->toDownload();
	}

	//
	public function goods_import()
	{
		//$_FILE
		if ($_FILES && isset($_FILES['goods_csv']))
		{
			//
			$uploadInstance = new IUpload(9999999, ['html']);
			$uploadDir      = 'upload/excel/' . date('Y-m-d');
			$uploadInstance->setDir($uploadDir);
			$result = $uploadInstance->execute();
			$result = current($result['goods_csv']);
			if(isset($result['error']) && $result['error'] != '')
			{
				$this->redirect('/goods/goods_list/_msg/'.$result['error']);
				return;
			}

			//
			$csvContent = file_get_contents($result['fileSrc']);
			$successCount = 0;

			preg_match("#<tr.*>.*</tr>#is",$csvContent,$match);
			if($match && isset($match[0]) && $match[0])
			{
				$goodsDB   = new IModel('goods');
				$productDB = new IModel('products');

				$matchArray = explode('</tr>',$match[0]);
				foreach($matchArray as $key => $line)
				{
					if(!$line)
					{
						continue;
					}

					$items = explode('</td>',$line);
					$goods_name = trim(strip_tags($items[0]));
					$goods_no   = trim(strip_tags($items[1]));
					$spec_array = trim(strip_tags($items[2]));
					$sell_price = trim(strip_tags($items[3]));
					$store_nums = trim(strip_tags($items[4]));

					//
					if(is_numeric($store_nums) == false || is_numeric($sell_price) == false)
					{
						continue;
					}

					//
					if($spec_array)
					{
						$productDB->setData(['store_nums' => $store_nums,'sell_price' => $sell_price]);
						$productDB->update('products_no = "'.$goods_no.'"');
					}
					//
					else
					{
						$goodsDB->setData(['store_nums' => $store_nums,'sell_price' => $sell_price]);
						$goodsDB->update('goods_no = "'.$goods_no.'"');
					}
					$successCount++;
				}
				$this->redirect('/goods/goods_list/_msg/'.$successCount.'');
			}
			else
			{
				$this->redirect('/goods/goods_list/_msg/');
			}
		}
		else
		{
			$this->redirect('/goods/goods_list/_msg/');
		}
	}

	/**
	 * @brief 
	 */
	function category_edit()
	{
		$category_id = IFilter::act(IReq::get('cid'),'int');
		if($category_id)
		{
			$categoryObj = new IModel('category');
			$this->categoryRow = $categoryObj->getObj('id = '.$category_id);
		}
		$this->redirect('category_edit');
	}

	/**
	 * @brief 
	 */
	function category_save()
	{
		//post
		$category_id = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$parent_id = IFilter::act(IReq::get('parent_id'),'int');
		$visibility = IFilter::act(IReq::get('visibility'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');
		$title = IFilter::act(IReq::get('title'));
		$keywords = IFilter::act(IReq::get('keywords'));
		$descript = IFilter::act(IReq::get('descript'));

		$childString = goods_class::catChild($category_id);//ID
		if($parent_id > 0 && stripos(",".$childString.",",",".$parent_id.",") !== false)
		{
			$this->redirect('/goods/category_list/_msg/');
			return;
		}

		$tb_category = new IModel('category');
		$category_info = array(
			'name'      => $name,
			'parent_id' => $parent_id,
			'sort'      => $sort,
			'visibility'=> $visibility,
			'keywords'  => $keywords,
			'descript'  => $descript,
			'title'     => $title
		);

		if(isset($_FILES['img']['name']) && $_FILES['img']['name'])
		{
		    $uploadDir = IWeb::$app->config['upload'].'/category';
			$uploadObj = new PhotoUpload($uploadDir);
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();
			if(isset($photoInfo['img']['img']))
			{
				$category_info['img'] = $photoInfo['img']['img'];
			}
		}

		$tb_category->setData($category_info);
		if($category_id)									//
		{
			$where = "id=".$category_id;
			$tb_category->update($where);
		}
		else												//
		{
			$tb_category->add();
		}
		$this->redirect('category_list');
	}

	/**
	 * @brief 
	 */
	function category_del()
	{
		$category_id = IFilter::act(IReq::get('cat_id'),'int');
		if($category_id)
		{
			$category_id = is_array($category_id) ? join(',',$category_id) : $category_id;
			$tb_category = new IModel('category');
			$catRow      = $tb_category->getObj('parent_id in ( '.$category_id.')');

			//
			if($catRow)
			{
				$this->category_list();
				Util::showMessage('');
				exit;
			}

			if($tb_category->del('id in ('.$category_id.')'))
			{
				$tb_category_extend  = new IModel('category_extend');
				$tb_category_extend->del('category_id in  ('.$category_id.')');
				//
				$categoryRateObj = new IModel('category_rate');
				$categoryRateObj->del('category_id in (' .$category_id.')');

				$this->redirect('category_list');
			}
			else
			{
				$this->category_list();
				$msg = "";
				Util::showMessage($msg);
			}
		}
		else
		{
			$this->category_list();
			$msg = "";
			Util::showMessage($msg);
		}
	}

	/**
	 * @brief 
	 */
	function category_list()
	{
		$isCache = false;
		$tb_category = new IModel('category');
		$cacheObj = new ICache('file');
		$data = $cacheObj->get('sortdata');
		if(!$data)
		{
			$goods = new goods_class();
			$data = $goods->sortdata($tb_category->query(false,'*','sort asc'));
			$isCache ? $cacheObj->set('sortdata',$data) : "";
		}
		$this->data['category'] = $data;
		$this->setRenderData($this->data);
		$this->redirect('category_list',false);
	}

	//
	function spec_edit()
	{
		$this->layout = '';

		$id        = IFilter::act(IReq::get('id'),'int');
		$seller_id = IFilter::act(IReq::get('seller_id'),'int');

		$dataRow = array(
			'id'        => '',
			'name'      => '',
			'type'      => '',
			'value'     => '',
			'note'      => '',
			'seller_id' => $seller_id,
		);

		if($id)
		{
			$obj     = new IModel('spec');
			$dataRow = $obj->getObj("id = {$id}");
		}

		$this->setRenderData($dataRow);
		$this->redirect('spec_edit');
	}

	//
    function spec_update()
    {
    	$id        = IFilter::act(IReq::get('id'),'int');
    	$name      = IFilter::act(IReq::get('name'));
    	$sort      = IFilter::act(IReq::get('sort'),'int');
    	$image     = IFilter::act(IReq::get('image'));
    	$note      = IFilter::act(IReq::get('note'));
    	$seller_id = IFilter::act(IReq::get('seller_id'),'int');
    	$value     = IFilter::act(IReq::get('value'));

		//
		$data = JSON::encode(array_combine($value,$image));
		if(!$data)
		{
			die( JSON::encode(array('flag' => 'fail','message' => '0')) );
		}

		if(!$name)
		{
			die( JSON::encode(array('flag' => 'fail','message' => '')) );
		}

    	$editData = array(
    		'id'        => $id,
    		'name'      => $name,
    		'value'     => $data,
    		'note'      => $note,
    		'seller_id' => $seller_id,
    		'sort'      => $sort,
    	);

		//
		$obj = new IModel('spec');
    	$obj->setData($editData);

    	//
    	if($id)
    	{
    		$where = 'id = '.$id;
    		if($seller_id)
    		{
    			$where .= ' and seller_id = '.$seller_id;
    		}
    		$result = $obj->update($where);
    	}
    	//
    	else
    	{
    		$result = $obj->add();
    	}

		//
    	if($result===false)
    	{
			die( JSON::encode(array('flag' => 'fail','message' => '')) );
    	}
    	else
    	{
    		//ID json
    		$editData['id']    = $id ? $id : $result;
    		$editData['id']    = strval($editData['id']);
    		$editData['value'] = IFilter::stripSlash($editData['value']);
    		die( JSON::encode(array('flag' => 'success','data' => $editData)) );
    	}
    }

	//
    function spec_del()
    {
    	$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$obj = new IModel('spec');
			$obj->setData(array('is_del'=>1));
			$obj->update(Util::joinStr($id));
			$this->redirect('spec_list');
		}
		else
		{
			$this->redirect('spec_list',false);
			Util::showMessage('');
		}
    }
	//
    function spec_recycle_del()
    {
    	$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$obj = new IModel('spec');
			$obj->del(Util::joinStr($id));
			$this->redirect('spec_recycle_list');
		}
		else
		{
			$this->redirect('spec_recycle_list',false);
			Util::showMessage('');
		}
    }
	//
    function spec_recycle_restore()
    {
    	$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$obj = new IModel('spec');
			$obj->setData(array('is_del'=>0));
			$obj->update(Util::joinStr($id));
			$this->redirect('spec_recycle_list');
		}
		else
		{
			$this->redirect('spec_recycle_list',false);
			Util::showMessage('');
		}
    }
    //
    function spec_photo_del()
    {
    	$id = IFilter::act(IReq::get('id','post'),'int');
    	if($id)
    	{
    		$obj = new IModel('spec_photo');
    		foreach($id as $rs)
    		{
    			$photoRow = $obj->getObj('id = '.$rs,'address');
    			if(file_exists($photoRow['address']))
    			{
    				unlink($photoRow['address']);
    			}
    		}

	    	$where = ' id in ('.join(",",$id).')';
	    	$obj->del($where);
	    	$this->redirect('spec_photo');
    	}
    	else
    	{
    		$this->redirect('spec_photo',false);
    		Util::showMessage('id');
    	}
    }

	/**
	 * @brief 
	 */
	function category_sort()
	{
		$category_id = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$flag = 0;
		if($category_id)
		{
			$tb_category = new IModel('category');
			$category_info = $tb_category->getObj('id='.$category_id);
			if(count($category_info)>0)
			{
				if($category_info['sort']!=$sort)
				{
					$tb_category->setData(array('sort'=>$sort));
					if($tb_category->update('id='.$category_id))
					{
						$flag = 1;
					}
				}
			}
		}
		echo $flag;
	}
	/**
	 * @brief 
	 */
	public function brand_sort()
	{
		$brand_id = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');
		$flag = 0;
		if($brand_id)
		{
			$tb_brand = new IModel('brand');
			$brand_info = $tb_brand->getObj('id='.$brand_id);
			if(count($brand_info)>0)
			{
				if($brand_info['sort']!=$sort)
				{
					$tb_brand->setData(array('sort'=>$sort));
					if($tb_brand->update('id='.$brand_id))
					{
						$flag = 1;
					}
				}
			}
		}
		echo $flag;
	}

	//
	public function ajax_sort()
	{
		$id   = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$goodsDB = new IModel('goods');
		$goodsDB->setData(array('sort' => $sort));
		$goodsDB->update("id = {$id}");
	}

	//
	public function update_store()
	{
		$data     = IFilter::act(IReq::get('data'),'int'); //key => IDID ; value => 
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');//
		$goodsSum = array_sum($data);

		if(!$data)
		{
			die(JSON::encode(array('result' => 'fail','data' => '')));
		}

		//
		if($goods_id)
		{
			$productDB = new IModel('products');
			foreach($data as $key => $val)
			{
				$productDB->setData(array('store_nums' => $val));
				$productDB->update('id = '.$key);
			}
		}
		else
		{
			$goods_id = key($data);
		}

		$goodsDB = new IModel('goods');
		$goodsDB->setData(array('store_nums' => $goodsSum));
		$goodsDB->update('id = '.$goods_id);

		die(JSON::encode(array('result' => 'success','data' => $goodsSum)));
	}

	//
	public function update_price()
	{
		$data     = IFilter::act(IReq::get('data')); //key => IDID ; value => 
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');//

		if(!$data)
		{
			die(JSON::encode(array('result' => 'fail','data' => '')));
		}

		//
		if($goods_id)
		{
			$productDB  = new IModel('products');
			$updateData = current($data);
			foreach($data as $pid => $item)
			{
				$productDB->setData($item);
				$productDB->update("id = ".$pid);
			}
		}
		else
		{
			$goods_id   = key($data);
			$updateData = current($data);
		}

		$goodsDB = new IModel('goods');

		if($saleRow = Active::isSale($goods_id))
		{
			$goodsDB->rollback();
			die(JSON::encode(array('result' => 'fail','data' => '->'.$saleRow['name'].'')));
		}

		$goodsDB->setData($updateData);
		$goodsDB->update('id = '.$goods_id);

		die(JSON::encode(array('result' => 'success','data' => number_format($updateData['sell_price'],2))));
	}

	//
	public function update_commend()
	{
		$data = IFilter::act(IReq::get('data')); //key => IDID ; value => commend 1~4
		if(!$data)
		{
			die(JSON::encode(array('result' => 'fail','data' => '')));
		}

		$goodsCommendDB = new IModel('commend_goods');

		//commend
		$goodsIdArray = array_keys($data);
		$goodsCommendDB->del("goods_id in (".join(',',$goodsIdArray).")");

		//commend
		foreach($data as $id => $commend)
		{
			foreach($commend as $k => $value)
			{
				if($value > 0)
				{
					$goodsCommendDB->setData(array('commend_id' => $value,'goods_id' => $id));
					$goodsCommendDB->add();
				}
			}
		}
		die(JSON::encode(array('result' => 'success')));
	}

	//
	public function goods_share()
	{
		$idArray = explode(',',IReq::get('id'));
		$id      = IFilter::act($idArray,'int');

		$goodsDB = new IModel('goods');
		$goodsData = $goodsDB->query('id in ('.join(',',$id).')');

		foreach($goodsData as $key => $val)
		{
			$is_share = $val['is_share'] == 1 ? 0 : 1;
			$goodsDB->setData(array('is_share' => $is_share));
			$goodsDB->update('id = '.$val['id'].' and seller_id = 0');
		}
	}

	/**
	 * @brief 
	 */
	function goods_setting()
	{
		$idArray   = explode(',',IReq::get('id'));
		$id        = IFilter::act($idArray,'int');
		$seller_id = IFilter::act(IReq::get('seller_id'),'int');

		if (empty($id))
		{
			exit('');
		}
		$data = array();
		$data['goods_id']  = implode(",", $id);
		$data['seller_id'] = $seller_id;

		$this->layout = '';
		$this->setRenderData($data);
		$this->redirect('goods_setting');
	}

	/**
	 * @brief 
	 */
	function goods_setting_save()
	{
		$idArray   = explode(',',IReq::get('goods_id', 'post'));
		$seller_id = IFilter::act(IReq::get('seller_id'),'int');
		$idArray   = IFilter::act($idArray,'int');

		if (empty($idArray))
		{
			exit('');
		}

		$goodsObject = new goods_class($seller_id);
		$goodsObject->multiUpdate($idArray, $_POST);
		die('<script type="text/javascript">parent.artDialogCallback();</script>');
	}
	/**
	 * @brief ajax
	 */
	public function categoryAjax()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$parent_id = IFilter::act(IReq::get('parent_id'),'int');
		if($id && is_array($id))
		{
			foreach($id as $category_id)
			{
				$childString = goods_class::catChild($category_id);//ID
				if($parent_id > 0 && stripos(",".$childString.",",",".$parent_id.",") !== false)
				{
					die(JSON::encode(array('result' => 'fail')));
				}
			}

			$catDB     = new IModel('category');
			$catDB->setData(array('parent_id' => $parent_id));
			$result = $catDB->update('id in ('.join(",",$id).')');
			if($result)
			{
				die(JSON::encode(array('result' => 'success')));
			}
		}
		die(JSON::encode(array('result' => 'fail')));
	}

	//
	function search()
	{
		$this->setRenderData($_GET);
		$this->redirect('search');
	}

	//
	function search_result()
	{
		//
		$search      = IFilter::act(IReq::get('search'));
		$page        = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$is_products = IFilter::act(IReq::get('is_products'));
		$type        = IReq::get('type') == "checkbox" ? "checkbox" : "radio";

		//
		list($join,$where) = goods_class::getSearchCondition($search);

		$goodsHandle = new IQuery('goods as go');
		$goodsHandle->order  = "go.id desc";
		$goodsHandle->fields = "distinct go.id as goods_id,go.name,go.img,go.store_nums,go.goods_no,go.sell_price,go.spec_array";
		$goodsHandle->limit  = 20;
		$goodsHandle->where  = $where;
		$goodsHandle->join   = $join;
		$data = $goodsHandle->find();

		//
		if($is_products && $data)
		{
			$goodsIdArray = array();
			foreach($data as $key => $val)
			{
				//
				if($val['spec_array'])
				{
					$goodsIdArray[$key] = $val['goods_id'];
					unset($data[$key]);
				}
			}

			if($goodsIdArray)
			{
				$productsDB        = new IQuery('products as pro');
				$productsDB->join  = "left join goods as go on go.id = pro.goods_id";
				$productsDB->where = "pro.goods_id in (".join(',',$goodsIdArray).")";
				$productsDB->fields="pro.goods_id,go.name,go.img,pro.id as product_id,pro.products_no as goods_no,pro.spec_array,pro.sell_price,pro.store_nums";
				$productDate       = $productsDB->find();
				$data              = array_merge($data,$productDate);
			}
		}

		$this->goodsData = $data;
		$this->type      = $type;
		$this->redirect('search_result');
	}
	/**
	 * @brief 
	 */
	public function goods_rate_edit()
	{
	    $id = IFilter::act(IReq::get('id'),'int');
	    if($id)
	    {
	        $goodsRateObj = new IModel('goods_rate');
	        $where        = 'goods_id = '.$id;
	        $goodsRateRow = $goodsRateObj->getObj($where);
	        if(!$goodsRateRow)
	        {
	            die("");
	        }
	        //
	        $goodsObj = new IModel('goods');
	        $goodsRow = $goodsObj->getObj('id = '.$goodsRateRow['goods_id']);
	        $result = array(
	            'isError' => false,
	            'data'    => $goodsRow,
	        );
	        $goodsRateRow['goodsRow'] = JSON::encode($result);
	        $this->goodsRateRow = $goodsRateRow;
	    }
	    $this->redirect('goods_rate_edit');
	}
	/**
	 * 
	 */
	public function goods_rate_save()
	{
	    $goods_id = IFilter::act(IReq::get('goods_id'),'int');
	    $goods_rate = IFilter::act(IReq::get('goods_rate'),'float');

	    $dataArray = array(
	        'goods_id' => $goods_id,
	        'goods_rate' => $goods_rate,
	        'goodsRow' => null,
	    );

	    if ($goods_id)
	    {
	        //
	        $goodsObj = new IModel('goods');
	        $goodsRow = $goodsObj->getObj('id = '.$goods_id);
	        $result = array(
	            'isError' => false,
	            'data'    => $goodsRow,
	        );
	        $dataArray['goodsRow'] = JSON::encode($result);
	    }
	    else
	    {
	        $this->goodsRateRow = $dataArray;
	        $this->redirect('goods_rate_edit', false);
	        Util::showMessage('');
	    }

	    if (0 > $goods_rate || 100 < $goods_rate)
	    {
	        $this->goodsRateRow = $dataArray;
	        $this->redirect('goods_rate_edit', false);
	        Util::showMessage('0~100');
	    }

	    $goodsRateObj = new IModel('goods_rate');
	    $goodsRateArray = array(
	        'goods_id' => $goods_id,
	        'goods_rate' => $goods_rate,
	    );
	    $goodsRateObj->setData($goodsRateArray);
	    $goodsRateObj->replace();

		//
		$logObj = new log('db');
		$logData = [":".$this->admin['admin_name'],"","ID".$goods_id."".$goodsRow['name']."".$goods_rate."%"];
		$logObj->write('operation',$logData);

	    $this->redirect('goods_rate_list');
	}
	/**
	 * 
	 */
	public function goods_rate_del()
	{
	    $ids = IFilter::act(IReq::get('check'),'int');
	    $ids = is_array($ids) ? $ids : array($ids);
	    if ($ids)
	    {
	        $ids = implode(',', $ids);
	        $goodsRateObj = new IModel('goods_rate');
	        $where        = 'goods_id in(' .$ids. ')';
	        $goodsRateObj->del($where);
	    }

		//
		$logObj = new log('db');
		$logData = [":".$this->admin['admin_name'],"","ID".$ids];
		$logObj->write('operation',$logData);

	    $this->redirect('goods_rate_list');
	}
	/**
	 * @brief 
	 */
	public function category_rate_edit()
	{
	    $id = IFilter::act(IReq::get('id'),'int');
	    if($id)
	    {
	        $categoryRateObj = new IModel('category_rate');
	        $where        = 'category_id = '.$id;
	        $categoryRateRow = $categoryRateObj->getObj($where);
	        if(!$categoryRateRow)
	        {
	            die("");
	        }
	        $this->categoryRateRow = $categoryRateRow;
	    }
	    $this->redirect('category_rate_edit');
	}
	/**
	 * 
	 */
	public function category_rate_save()
	{
	    $category_id = IFilter::act(IReq::get('category_id'),'int');
	    $category_rate = IFilter::act(IReq::get('category_rate'),'float');

	    $dataArray = array(
	        'category_id' => $category_id,
	        'category_rate' => $category_rate,
	    );
	    // 
	    $categoryObj = new IModel('category');
	    $categoryRow = $categoryObj->getObj('id = '.$category_id);
	    if (!$categoryRow)
	    {
	        $this->categoryRateRow = $dataArray;
	        $this->redirect('category_rate_edit', false);
	        Util::showMessage('');
	    }

	    if (0 > $category_rate || 100 < $category_rate)
	    {
	        $this->categoryRateRow = $dataArray;
	        $this->redirect('category_rate_edit', false);
	        Util::showMessage('0~100');
	    }

	    $categoryRateObj = new IModel('category_rate');
	    $categoryRateObj->setData($dataArray);
	    $categoryRateObj->replace();

		//
		$logObj = new log('db');
		$logData = [":".$this->admin['admin_name'],"","ID".$category_id."".$category_rate."%"];
		$logObj->write('operation',$logData);

	    $this->redirect('category_rate_list');
	}
	/**
	 * 
	 */
	public function category_rate_del()
	{
	    $ids = IFilter::act(IReq::get('check'),'int');
	    $ids = is_array($ids) ? $ids : array($ids);
	    if ($ids)
	    {
	        $ids = implode(',', $ids);
	        $categoryRateObj = new IModel('category_rate');
	        $where           = 'category_id in(' .$ids. ')';
	        $categoryRateObj->del($where);

			//
			$logObj = new log('db');
			$logData = [":".$this->admin['admin_name'],"","ID".$ids];
			$logObj->write('operation',$logData);
	    }
	    $this->redirect('category_rate_list');
	}

    //
	public function preorder_setting()
	{
		$id    = IFilter::act(IReq::get('id'),'int');
		$type  = IFilter::act(IReq::get('type'));
		$seller_id = IFilter::act(IReq::get('seller_id'),'int');

		if(!$id)
		{
			exit('');
		}

    	$dataRow = $type == 'goods' ? Api::run('getGoodsInfo',array("id" => $id)) : Api::run('getProductInfo',array("id" => $id));
        if(!$dataRow || ($seller_id && $dataRow['seller_id'] != $seller_id))
        {
            exit('');
        }

        $whereCond = 'goods_id = '.$dataRow['goods_id'].' and product_id = '.$dataRow['product_id'];
        $whereCond.= $seller_id ? ' and seller_id = '.$seller_id : '';

        //
		$discountDB = new IModel('goods_extend_preorder_discount');
		$discountData = $discountDB->query($whereCond);

		//
		$disnumDB = new IModel('goods_extend_preorder_disnums');
        $disnumsData = $disnumDB->query($whereCond);

		$data = [
		    'id'       => $id,
		    'seller_id'=> $seller_id,
		    'goodsRow' => $dataRow,
		    'type'     => $type,
		    'discount' => $discountData,
		    'disnums'  => $disnumsData,
		];

		$this->layout = '';
		$this->setRenderData($data);
		$this->redirect('preorder_setting');
	}

	//
	public function preorder_setting_save()
	{
	    $id        = IFilter::act(IReq::get('id'),'int');
	    $seller_id = IFilter::act(IReq::get('seller_id'),'int');
	    $type      = IFilter::act(IReq::get('type'));
	    $colName   = $type == 'goods' ? 'goods_id' : 'product_id';

        $product_id = 0;
        $goods_id   = $id;
	    if($type == 'product')
	    {
	        $proDB = new IModel('products');
	        $proRow= $proDB->getObj($id);

	        $product_id = $id;
	        $goods_id   = $proRow['goods_id'];
	    }

	    //
        $discountDateStart = IFilter::act(IReq::get('discountDateStart'),'date');
        $discountDateEnd   = IFilter::act(IReq::get('discountDateEnd'),'date');
        $discount          = IFilter::act(IReq::get('discount'),'float');

        $discountDB = new IModel('goods_extend_preorder_discount');
        $whereCond  = $colName.'='.$id;
        $whereCond .= $seller_id ? ' and seller_id = '.$seller_id : '';
        $discountDB->del($whereCond);

        if($discountDateStart && count($discountDateStart) == count($discountDateEnd) && count($discountDateEnd)  == count($discount))
        {
            foreach($discount as $key => $item)
            {
                $discountDB->setData([
                    'goods_id'  => $goods_id,
                    'product_id'=> $product_id,
                    'start_date'=> $discountDateStart[$key],
                    'end_date'  => $discountDateEnd[$key],
                    'discount'  => $discount[$key],
                    'seller_id' => $seller_id,
                ]);
                $discountDB->add();
            }
        }

	    //
        $disnumsDateStart  = IFilter::act(IReq::get('disnumsDateStart'),'date');
        $disnumsDateEnd    = IFilter::act(IReq::get('disnumsDateEnd'),'date');
        $disnums           = IFilter::act(IReq::get('disnums'),'int');

        $disnumDB = new IModel('goods_extend_preorder_disnums');
        $whereCond  = $colName.'='.$id;
        $whereCond .= $seller_id ? ' and seller_id = '.$seller_id : '';
        $disnumDB->del($whereCond);

        if($disnumsDateStart && count($disnumsDateStart) == count($disnumsDateEnd) && count($disnumsDateEnd)  == count($disnums))
        {
            foreach($disnums as $key => $item)
            {
                $disnumDB->setData([
                    'goods_id'  => $goods_id,
                    'product_id'=> $product_id,
                    'start_date'=> $disnumsDateStart[$key],
                    'end_date'  => $disnumsDateEnd[$key],
                    'disnums'   => $disnums[$key],
                    'seller_id' => $seller_id,
                ]);
                $disnumDB->add();
            }
        }

        die('<script type="text/javascript">parent.artDialogCallback();</script>');
	}
}
