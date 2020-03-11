<?php
/**
 * Core file.
 *
 * @author Vince Wooll <sales@jomres.net>
 *
 * @version Jomres 9.20.0
 *
 * @copyright	2005-2019 Vince Wooll
 * Jomres (tm) PHP, CSS & Javascript files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project, and use it accordingly
 **/

// ################################################################
defined('_JOMRES_INITCHECK') or die('');
// ################################################################

class channelmanagement_framework_singleton
{
	public function __construct()
	{
		jr_import('jomres_call_api');
		
		$this->call_api = new jomres_call_api('system');
		$this->current_channels = array();
		
		$JRUser									= jomres_singleton_abstract::getInstance( 'jr_user' );
		jr_import('channelmanagement_framework_channels');
		$channelmanagement_framework_channels = new channelmanagement_framework_channels();
		$user_channels = $channelmanagement_framework_channels->get_user_channels($JRUser->userid);
		
		set_showtime('user_channels' , $user_channels );
		
		// Channels build reports of their existence
		$MiniComponents =jomres_getSingleton('mcHandler');
		$MiniComponents->triggerEvent('21001');

		$thin_channels = get_showtime("thin_channels");
		
		$this->current_channels = array();
		if ( !empty($thin_channels)) {
			foreach ($thin_channels as $channel ) {
				$this->current_channels[] = $channel ;
			}
		}
		
		if (!empty($user_channels) && is_array($thin_channels)) {
			$user_channels = array_merge( $user_channels , $thin_channels );
			set_showtime('user_channels' , $user_channels );
		} else {
			set_showtime('user_channels' , $thin_channels );
		}

		$this->init();
	}

	/*
	
	Channels calling in cannot get a list of all channels in the system for a user, they should not be able to modify other channels. The channel management framework that implements channel plugins, however, is an exception
	Here we will trigger the channel report minicomponents and then use the api to confirm their existence. If they don't exist, we'll create them in the database using POST {{cm_url}}/cmf/channel/announce/{{channel_name}}/{{channel_friendly_name}}
	
	*/
	public function init()
	{

		if ( get_showtime("task") != 'channelmanagement_framework' && get_showtime("task") != '' ) { // We only need to do this on the main page (I hope)
			return;
		}

		$MiniComponents =jomres_getSingleton('mcHandler');
		// Channels build reports of their existence
		$MiniComponents->triggerEvent('21001');

		$this->register_channels();
	}
	
	public function register_channels()
	{
		$user_channels = get_showtime("user_channels");

		if (!empty($user_channels)) {
			foreach ($user_channels as $channel ) {
				try {
					$JRUser									= jomres_singleton_abstract::getInstance( 'jr_user' );
					$headers = array ( "X-JOMRES-proxy_id: ".(int)$JRUser->userid );

					$method = 'GET';
					$endpoint = 'cmf/channel/announce/'.$channel['channel_name'];  // Endpoint that confirms the channel's existence
					$result = $this->call_api->send_request( $method , $endpoint , array() , $headers);

					if ( isset($result->data->response) && $result->data->response == 0 ) { // The channel does not exist in the CMS
						$method = 'POST';
						$endpoint = 'cmf/channel/announce/'.$channel['channel_name'].'/'.urlencode($channel['channel_friendly_name']);

						$result = $this->call_api->send_request( $method , $endpoint , [
								'params' => json_encode($channel['features'])
							] ,
							$headers
							);
						$channel["channel_id"] = $result->data->response ;
					} else {
						$channel["channel_id"] = $result->data->response ;
					}
					$this->current_channels[ $result->data->response ] = $channel ;
				}
				catch (Exception $e) {
					logging::log_message("Tried to add ".$channel['channel_name']."to database but failed ", 'CHANNEL_MANAGEMENT_FRAMEWORK', 'WARNING');
				}
			}

		}
		
	}
	
	public function rest_api_communicate( $channel_name = '' , $method = 'GET' , $endpoint = ''  , $data = array() )
	{
		if ( $channel_name == '' ) {
			throw new Exception( "Channel not passed" );
		}
		
		if ( $method == '' ) {
			throw new Exception( "Method not passed" );
		}
		
		if ( $endpoint == '' ) {
			throw new Exception( "Endpoint not passed" );
		}
		
		$JRUser									= jomres_singleton_abstract::getInstance( 'jr_user' );
		
		$headers = array ( "X-JOMRES-channel-name: ".$channel_name , "X-JOMRES-proxy_id: ".(int)$JRUser->userid );

		try {
			$result = $this->call_api->send_request( $method , $endpoint , $data , $headers );
			return $result;
			}
		catch (Exception $e) {
			logging::log_message("Tried to communicate with ".$endpoint." endpoint but failed ", 'CHANNEL_MANAGEMENT_FRAMEWORK', 'WARNING');
		}
	}


}
