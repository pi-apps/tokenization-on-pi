<?php
/**
 * @file pic.php
 * @brief 
 */
class Pic extends IController
{
	public $layout = '';

	function init()
	{

	}
	//
	function uploadFile()
	{
		//
		$state = false;

		//
		$specIndex = IFilter::act(IReq::get('specIndex'));
		if($specIndex === null)
		{
			$message = '';
		}
		else
		{
			//
			if(isset($_FILES['attach']) && $_FILES['attach']['name'])
			{
			 	//
			 	$uploadDir= IWeb::$app->config['upload'].'/spec/'.date('Ymd');
				$photoObj = new PhotoUpload($uploadDir);
				$photoInfo= $photoObj->run();
				$photoInfo= current($photoInfo);

				if($photoInfo['flag']==1)
				{
					$fileName = $photoInfo['img'];
					$state = true;
				}

				//
				$obj = new IModel('spec_photo');
				$insertData = array(
					'address'     => $photoInfo['img'],
					'create_time' => ITime::getDateTime(),
				);
				$obj->setData($insertData);
				$obj->add();
			}

			//
			else if($fileName=IReq::get('outerSrc','post'))
			{
				$state = true;
			}

			//
			else if($fileName=IReq::get('selectPhoto','post'))
			{
				$state = true;
			}
		}

		//
		if($state == true)
		{
			die("<script type='text/javascript'>parent.art.dialog({id:'addSpecWin'}).iframe.contentWindow.updatePic(".$specIndex.",'".$fileName."');</script>");
		}
		else
		{
			die("<script type='text/javascript'>alert('');window.history.go(-1);</script>");
		}
	}

	//
	function getPhotoList()
	{
		$obj = new IModel('spec_photo');
		$photoRs = $obj->query();
		echo JSON::encode($photoRs);
	}

	//kindeditor
	public function upload_json()
	{
		$upObj = new IUpload();
		$upObj->setDir($this->app->config['upload'].'/detail/'.date("Ymd"));
		$tempRes = $upObj->execute();
		if($tempRes && is_array($tempRes))
		{
    		$result = current(current($tempRes));
    		if($result['flag'] == 1)
    		{
    			$result['img'] = stripos($result['fileSrc'],"http") === 0 ? $result['fileSrc'] : IUrl::creatUrl('').$result['fileSrc'];
    			die(JSON::encode(['uploaded' => true,'url' => $result['img']]));
    		}
    		else
    		{
    		    $error = $result['error'];
    		}
		}
		$message = isset($error) ? $error : "".var_export($tempRes,true);
		die(JSON::encode(['uploaded' => false, 'error' => ['message' => $message]]));
	}

	//
	public function thumb()
	{
		//
		$mixData = IFile::dirExplodeDecode(IReq::get('img'));
		$mixData = IFilter::act($mixData);

		//http 304
		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == md5($mixData))
		{
			header("HTTP/1.1 304 Not Modified");
			exit;
		}

		if($mixData)
		{
			preg_match("#/w/(\d+)#",$mixData,$widthData);
			preg_match("#/h/(\d+)#",$mixData,$heightData);
			preg_match("#/type/(\w+)#",$mixData,$typeData);

			//1,
			$thumbSrc = $mixData;

			//2,
			if(isset($widthData[1]) && isset($heightData[1]))
			{
				$width  = $widthData[1];
				$height = $heightData[1];
				$type   = $width > 420 ? "pad" : "adapt";//420px

				$replaceArr = [$widthData[0],$heightData[0]];
				if($typeData && isset($typeData[1]))
				{
					$replaceArr[] = $typeData[0];
					$type         = $typeData[1];
				}

				$imageSrc = str_replace($replaceArr,"",$mixData);
				if(!$imageSrc)
				{
					return;
				}

				$thumbSrc = Thumb::get($imageSrc,$width,$height,$type);
			}

			//
			$fileExt = "";
			foreach(array("jpg","png","gif","tbi","jpeg") as $val)
			{
			    if(stripos($thumbSrc,".".$val) !== false)
			    {
			        $fileExt = $val;
			        break;
			    }
			}

			if(!$fileExt)
			{
				return;
			}

			$cacheTime  = 31104000;
			header('Pragma: cache');
			header('Cache-Control: max-age='.$cacheTime);
 			header('Content-type: image/'.$fileExt);
 			header("Etag: ".md5($mixData));
 			readfile($thumbSrc);
		}
	}

    //
	public function qrcode()
	{
	    $data = IFilter::act(IReq::get('data'));
	    $size = IFilter::act(IReq::get('size'),'int');
	    if($data)
	    {
	        $size = $size ? $size : 12;
	        QRcode::png($data,false,QR_ECLEVEL_M,$size);
	    }
	}
}