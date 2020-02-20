<?php
/**
 * Core file.
 *
 * @author Vince Wooll <sales@jomres.net>
 *
 * @version Jomres 9.8.21
 *
 * @copyright	2005-2017 Vince Wooll
 * Jomres (tm) PHP, CSS & Javascript files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project, and use it accordingly
 **/

// ################################################################
defined('_JOMRES_INITCHECK') or die('');
// ################################################################
	
	/**
	 * @package Jomres\Core\Minicomponents
	 *
	 * 
	 */

class j07320watcher_authmethod_process_channelmanagement_framework_webhook
{	
	/**
	 *
	 * Constructor
	 * 
	 * Main functionality of the Minicomponent 
	 *
	 * 
	 * 
	 */
	 
	public function __construct($componentArgs)
	{
		// Must be in all minicomponents. Minicomponents with templates that can contain editable text should run $this->template_touch() else just return
		$MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
		if ($MiniComponents->template_touch) {
			$this->template_touchable = false;
			return;
		}
		
		jr_import('channelmanagement_framework_user_accounts');
		if (!class_exists('channelmanagement_framework_user_accounts')) {
			throw new Exception('Error: Channel management framework plugin not installed');
		}

		$channelmanagement_framework_user_accounts = new channelmanagement_framework_user_accounts();
		
		// This script will collate and send information to the remote site using the authentication information provided in the componentArgs variable.
		$ePointFilepath=get_showtime('ePointFilepath');
		$this->retVals = false;

		$messages =  unserialize($componentArgs["payload"]); // Need to do this to get messages in 07320 scripts
		
		$channel_data = $messages['channel_data'];

		$webhook_messages = $messages['webhook_messages'];

		$method=$messages['settings']['authmethod'];

		if ( $method!=='channelmanagement_framework_webhook' ) 
			return;
		
		if (isset($componentArgs["task"])) {
			$task =  $componentArgs["task"];
		} else {
			$task = get_showtime('task');
		}
		
		logging::log_message("Received deferred message with contents " , 'CHANNEL_MANAGEMENT_FRAMEWORK', 'DEBUG' , serialize( $webhook_messages ) );
		logging::log_message("Received deferred message task ".$task , 'CHANNEL_MANAGEMENT_FRAMEWORK', 'DEBUG' , serialize($componentArgs) );

		$non_processing_tasks = array ( // A number of tasks should not result in webhooks being sent onwards to the app server.
			);

		if ( !empty($webhook_messages) && !in_array( $task , $non_processing_tasks) ) {
			$webhook_messages = array_unique( $webhook_messages, SORT_REGULAR ); // Remove duplicate objects

			$messages = array();
			foreach ( $webhook_messages as $webhook_notification ) {
				if ( isset($webhook_notification->data->property_uid)) {
					$property_uid = $webhook_notification->data->property_uid;
				}
				$messages[] = $webhook_notification->webhook_event;
			}

			$manager_accounts = array();
			if ( isset( $property_uid ) && $property_uid > 0 )  {
				$manager_accounts = $channelmanagement_framework_user_accounts->find_channel_owners_for_property($property_uid);
			}

			foreach ( $webhook_messages as $webhook_notification ) {
				logging::log_message("Webhook triggered ".$webhook_notification->webhook_event , 'CHANNEL_MANAGEMENT_FRAMEWORK', 'DEBUG' , '' );
				$data = $webhook_notification->data;
				
				if (isset($data) && $data !== false && isset($webhook_notification->webhook_event) ) { // The data, whatever it is, has been collected, let's send it off to the remote site
					$data->task = $webhook_notification->webhook_event;
					
					// Hand the webhook notification to individual tasks to see if they need to process the webhook (with multiple webhooks being called, this might be problematic, with timeouts if they take too long, might need to make those asynchronous tasks, or add them to some kind of queue to be processed individually)
					
					$MiniComponents->triggerEvent('27330' , [ 
						"webhook_notification" => $webhook_notification , // Pass the individual notifications, allow the thin plugin's handler to decide what it's going to do with it, if anything
						"channel_data" => $channel_data, // Pass the channel data. The webhook event may have been triggered by a channel. This allows the called plugin's handler to decide if it wants to ignore the event (such as bookings created by itself
						"managers" => $manager_accounts
						]);
				}
			}
		}
	logging::log_message("Completed Watcher's run." , 'CHANNEL_MANAGEMENT_FRAMEWORK', 'DEBUG' , '' );
	}

	

	public function getRetVals()
	{
		return $this->retVals;
	}
}
	