<?php
/**
 * @class IFileLog
 * @brief 
 */
class IFileLog implements ILog
{
	//
	private $path;

	/**
	 * @brief 
	 */
	function __construct($path)
	{
		$logPath    = IWeb::$app->getBasePath();
		$logPath   .= isset(IWeb::$app->config['logs']) ? trim(IWeb::$app->config['logs']['path'],"/") : "backup/logs";
		$logPath   .= "/".trim($path,"/");
		$this->path = $logPath;
	}

	/**
	 * @brief  
	 * @param  mixed $logs  stringarray
	 * @return bool   
	 */
	public function write($logs = "")
	{
		if(!$this->path)
		{
			throw new IException('the file path is undefined');
		}

		//
		$fileName = $this->path;
		if(!file_exists($dirname = dirname($fileName)))
		{
			IFile::mkdir($dirname);
		}

		if(is_string($logs))
		{
			$result = $logs;
		}
		else
		{
			switch(gettype($logs))
			{
				case "array":
				{
					$logs[''] = ITime::getDateTime();
				}
				break;

				case "object":
				{
					$logs-> = ITime::getDateTime();
				}
				break;
			}

		    foreach($logs as $key => $item)
		    {
		        if(is_string($item))
		        {
					$content[] = $key.":".$item."\t";
		        }
		        else
		        {
		            $item = var_export($item,true)."\n";
		            $content[] = $key.":".$item;
		        }
		    }
		    $result = join("",$content);
		}
		return error_log($result."\n\n", 3 ,$fileName);
	}

	/**
	 * @brief 
	 * @return string 
	 */
	public function read()
	{
		return is_file($this->path) ? file_get_contents($this->path) : '';
	}
}