<?php
if(class_exists("Whook_Scanner")) return;
  
class Whook_Scanner
{
    public static function Whook_Add_Scanner()
    {
        add_action( 'wp_ajax_whook_plg_scan', array('Whook_Scanner', 'Whook_ScanPlugin') );
    }
    public function Whook_ScanPlugin()
    {
        $plugins = self::Whook_getAllPluginsInfo();
        if(count($plugins) < 1) {
           self::Whook_JsonResponseOutput(array("status"=>0,"msg"=>"No plugins Found"));
        }
        $pluginsData = array();

        unset($plugins['whook-security/whook-security.php']);
        foreach ($plugins as $key => $data) {
            $tmp        = explode("/", $key);
            $version    = $data['Version'];
            $pluginName = $tmp[0];
		
			$api_url = 'https://wpvulndb.com/api/v3/plugins/'.$pluginName;
			
			$context = stream_context_create(array(
			'http' => array(
			'header' => "Authorization: Token token=EUtoxaVa61SmQE5NdioVsr4nT8JuuiZ5u2MOzQRsw7g",
			),
			));
			
			$response = @file_get_contents($api_url, false, $context);
			if(isset($response) && !empty($response))
			{
				  $response = json_decode($response, true);
				  $pluginsData[$pluginName] = self::Whook_ScanPluginInfo($version,$pluginName,$response);
			}
        }
        self::Whook_JsonResponseOutput(array("status"=>1,"data"=>$pluginsData));
    }

    public function Whook_getAllPluginsInfo()
    {
        return get_plugins();
    }

    public function Whook_ScanPluginInfo($currentVersion,$pluginName,$data)
    {
          $dataTosend = array();  
		  if(!isset($data[$pluginName]["vulnerabilities"])) {
			    $dataTosend['status']['vulnerable']['vulnerable_status'] = 0;
				$dataTosend['status']['vulnerable']['vulnerable_error'] = array();
				return $dataTosend;
		  }
          $vulnerable = self::Whook_CheckVulnerabPlugin($currentVersion,$data,$pluginName);
		  
		  $dataTosend['status']['vulnerable']['vulnerable_status'] = $vulnerable['vulnerable_status'];
		  $dataTosend['status']['vulnerable']['vulnerable_error'] = $vulnerable['vulnerable_error'];
		  
          return $dataTosend;
    }
	
	public function Whook_CheckVulnerabPlugin($currentVersion,$data,$pluginName)
	{
		$vul_data = array('vulnerable_status'=>0,
		                  'vulnerable_error'=>array(),
		                 );
	    foreach($data[$pluginName]['vulnerabilities'] as $val)
		{
			if(is_null($val['fixed_in']))
			{
				$vul_data['vulnerable_status'] = 1; //  plugin vulnerable and update not available
				$vul_data['vulnerable_error'][] = $val['title'];
			}
			
			if(isset($val['fixed_in']) && !is_null($val['fixed_in']) && $currentVersion <= $val['fixed_in'])
			{
				if($currentVersion < $val['fixed_in'])
				{
				  $vul_data['vulnerable_status'] = 2;  //  plugin vulnerable and update available
				  $vul_data['vulnerable_error'][] = $val['title'];
				}elseif($currentVersion == $val['fixed_in'])
				{
				  $vul_data['vulnerable_status'] = 0;  // //  plugin not vulnerable
				  $dataTosend['status']['vulnerable']['vulnerable_error'] = array();
				}
			}
		}
		return $vul_data;
	}
    public function Whook_JsonResponseOutput($data){
          header('Content-Type: application/json');
          echo json_encode($data);
          die();
    }
}
