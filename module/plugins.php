<?php
/**
 * @brief 
 * @class plugins
 * @note  
 */
class plugins extends IController implements adminAuthorization
{
	public $layout='admin';
	public $checkRight = array('check' => 'all');

	function init()
	{

	}

	//
	public function plugin_edit()
	{
		$className = IFilter::act(IReq::get('class_name'));
		if(!$className || !$pluginRow = plugin::getItems($className))
		{
			IError::show("");
		}
		$this->pluginRow = $pluginRow;
		if($this->pluginRow['is_install'] == 0)
		{
			IError::show("");
		}
		$this->redirect('plugin_edit');
	}

	//
	public function plugin_update()
	{
		$className    = IFilter::act(IReq::get('class_name'));
		$isOpen       = IFilter::act(IReq::get('is_open'));
		$config_param = array();

		$pluginRow = plugin::getItems($className);
		if(!$pluginRow)
		{
			IError::show("");
		}

		if($pluginRow['is_install'] == 0)
		{
			IError::show("");
		}

		if($_POST)
		{
			foreach($_POST as $key => $val)
			{
				if(array_key_exists($key,$pluginRow['config_name']))
				{
					$config_param[$key] = is_array($val) ? join(";",$val) : $val;
					$config_param[$key] = IFilter::act(trim($config_param[$key]));
				}
			}
		}

		Config::edit('config/plugin_config.php',[$className => ['is_open' => $isOpen,'config_param' => JSON::encode($config_param)]]);
		$this->redirect('plugin_list');
	}

	//
	public function plugin_del()
	{
		$className = IFilter::act(IReq::get('class_name'));
		$pluginRow = plugin::getItems($className);
		if(!$pluginRow)
		{
			IError::show("");
		}

		if($pluginRow['is_install'] == 0)
		{
			IError::show("");
		}

		//uninstall
		$uninstallResult = call_user_func(array($pluginRow['class_name'],"uninstall"));
		if($uninstallResult === true)
		{
			Config::edit('config/plugin_config.php',[$className],'del');
		}
		else
		{
			$message = is_string($uninstallResult) ? $uninstallResult : "";
			IError::show($message);
		}
		$this->redirect('plugin_list');
	}

	//
	public function plugin_add()
	{
		$className = IFilter::act(IReq::get('class_name'));
		$pluginRow = plugin::getItems($className);
		if(!$pluginRow)
		{
			IError::show("");
		}

		if($pluginRow['is_install'] == 1)
		{
			IError::show("");
		}

		//install
		$installResult = call_user_func(array($pluginRow['class_name'],"install"));
		if($installResult === true)
		{
			Config::edit('config/plugin_config.php',[$className => ['is_open' => 0]]);
		}
		else
		{
			$message = is_string($installResult) ? $installResult : "";
			IError::show($message);
		}
		$this->redirect('plugin_list');
	}
}