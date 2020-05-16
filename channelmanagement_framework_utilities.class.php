<?php
/**
* Jomres CMS Agnostic Plugin
* @author Woollyinwales IT <sales@jomres.net>
* @version Jomres 9 
* @package Jomres
* @copyright 2019 Woollyinwales IT
* Jomres (tm) PHP files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project.
**/

// ################################################################
defined( '_JOMRES_INITCHECK' ) or die( '' );
// ################################################################

use Gregwar\Image\Image;

class channelmanagement_framework_utilities
{
	
	function __construct()
	{
		
	}

	public static function get_channel_ids_for_channel_name ( $channel_name = '' )
	{
		if (!isset($channel_name) || $channel_name == '' ) {
			throw new Exception( "channel_name not set" );
		}

		$query = "SELECT id FROM #__jomres_channelmanagement_framework_channels WHERE channel_name = '".$channel_name."'";
		$result = doSelectSql($query);

		$channel_ids = array();
		if (!empty($result)) {
			foreach ($result as $channel) {
				$channel_ids[] = $channel->id;
			}
		}
		return $channel_ids;
	}

	/*
	*
	* Used by changelog scripts that want to update a local property
	*
	*/
	public static function get_manager_id_for_property_uid ($property_uid)
	{
		$channelmanagement_framework_user_accounts = new channelmanagement_framework_user_accounts();
		// We need to find the manager's uid so that we can send the call to the local system
		$manager_accounts = $channelmanagement_framework_user_accounts->find_channel_owners_for_property($property_uid);

		if (empty($manager_accounts)) {
			throw new Exception( "Tried to get manager id however no managers exist to enable access to API");
		}

		reset($manager_accounts);
		$manager_id = key($manager_accounts);
		if ($manager_id == 0 ) {
			throw new Exception( "Manager id is 0");
		}

		return $manager_id;
	}


	/*
	 *
	 * Mark a changelog item completed
	 *
	 */
	public static function complete_queue_item ( $item_id = 0  )
	{
		if ( !isset($item_id ) || (int)$item_id == 0 ) {
			throw new Exception( "item_id not set" );
		}

		$query = "UPDATE #__jomres_channelmanagement_framework_changelog_queue_items SET
						 `completed` = 1
						 WHERE 
						 `id` = ".(int)$item_id." 
						 LIMIT 1
						 ";
		doInsertSql($query);
	}

	public static function increment_attempts ( $item_id = 0  )
	{
		if ( !isset($item_id ) || (int)$item_id == 0 ) {
			throw new Exception( "item_id not set" );
		}

		$query = "UPDATE #__jomres_channelmanagement_framework_changelog_queue_items SET
						 attempts = attempts + 1
						 WHERE 
						 `id` = ".(int)$item_id." 
						 LIMIT 1
						 ";
		doInsertSql($query);
	}
/*
 *
 * Get changelog queue items to be processed by 27410 scripts
 *
 */
	public static function get_all_queue_items_for_property ( $property_uid = 0 )
	{
		if ( !isset($property_uid ) || (int)$property_uid == 0 ) {
			throw new Exception( "property_uid not set" );
		}

		$query = "SELECT id ,`channel_name`, `property_uid`, `unique_id`, `date_added`, `completed`, `attempts` , `item`  FROM #__jomres_channelmanagement_framework_changelog_queue_items WHERE property_uid = ".(int)$property_uid.' ORDER BY id';
		return doSelectSql($query );
	}

	/*
	 *
	 * Get changelog queue items to be processed by 27410 scripts
	 *
	 */
	public static function get_queue_items ( )
	{
		$query = "SELECT id ,`channel_name`, `property_uid`, `unique_id`, `date_added`, `completed`, `attempts` , `item`  FROM #__jomres_channelmanagement_framework_changelog_queue_items";
		return doSelectSql($query );
	}


	/*
	 *
	 * Insert or update a queue item. If the item's unique id already exists for this property then just the item description and completed flag can be set
	 *
	 * Returns the id of the queue item
	 *
	 */
	public static function store_queue_item ( $item )
	{

		if ( !isset($item['channel_name']) || $item['channel_name'] == '' ) {
			throw new Exception( "channel_name not set" );
		}

		if (!isset($item['local_property_id']) || $item['local_property_id'] == 0 ) {
			throw new Exception( "local_property_id not set" );
		}

		if (!isset($item['unique_id']) || $item['unique_id'] == '' ) {
			throw new Exception( "xxx not set" );
		}

		if (!isset($item['item']) ) {
			throw new Exception( "item not set" );
		}

		if (!isset($item['completed']) ) {
			$item['completed'] = false;
		}

		// See if the queue item already exists

		$query = "SELECT unique_id FROM #__jomres_channelmanagement_framework_changelog_queue_items WHERE unique_id = '".$item['unique_id']."' LIMIT 1";
		$result = doSelectSql($query , 1 );

		if ( empty($result) || $result == false ) {
			$query = "INSERT INTO #__jomres_channelmanagement_framework_changelog_queue_items
						(
						`channel_name`,
						`property_uid`,
						`unique_id`,
						`date_added`,
						`completed`,
						`item`
						)
						VALUES 
						(
						'".$item['channel_name']."',
						".(int)$item['local_property_id'].",
						'".$item['unique_id']."',
						'".date("Y-m-d H:i:s")."',
						'".(int)$item['completed']."',
						'".serialize($item['item'])."'
						)
						";
			$id = doInsertSql($query);
		} else {
			$query = "UPDATE #__jomres_channelmanagement_framework_changelog_queue_items SET
						 `completed` = '".$item['completed']."', 
						 `item` = '".serialize($item['item'])."'
						 WHERE 
						 `unique_id` = '".$result."' AND
						 `property_uid` = ".(int)$item['local_property_id']." 
						 LIMIT 1
						 ";
			doInsertSql($query);
			$id = $result;
		}
		return $id;
	}

	public static function get_current_channel( $obj = object , $pattern = array() )
	{
		if ( !is_object($obj) ) {
			throw new Exception( "Empty object passed" );
		}
		
		if ( empty($pattern)) {
			throw new Exception( 'Pattern varaible is not an array' );
		}
		
		$current_class = get_class($obj);
		return str_replace( $pattern , "" , $current_class);
	}
	
	public static function get_mapped_dictionary_items( $channel = "" , $mapped_to_jomres_only = false )
	{
		if ( $channel == "" ) {
			throw new Exception( "Channel not passed" );
		}
		
		$dictionary_class_name = 'channelmanagement_'.$channel.'_dictionaries';
		jr_import($dictionary_class_name);
		if ( !class_exists($dictionary_class_name) ) {
			throw new Exception( jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_MAPPING_CHANNEL_DICTIONARY_CLASS_DOESNT_EXIST','CHANNELMANAGEMENT_FRAMEWORK_MAPPING_CHANNEL_DICTIONARY_CLASS_DOESNT_EXIST',false, false ) );
		}
		
		$dictionary_class = new $dictionary_class_name();
		$dictionary_items = $dictionary_class->get_mappable_dictionary_items();
		
		jr_import('channelmanagement_framework_mapping');
		$channelmanagement_framework_mapping = new channelmanagement_framework_mapping();
		
		$all_channel_dictionary_items = array();
		foreach ($dictionary_items as $item_type=>$item) {
	
			$mapped_dictionary_items = $channelmanagement_framework_mapping->get_items_for_mapping( $channel , $item['jomres_type'] ); // These are the dictionary types that can be handled by this channel manager service. Next we will find all of the items that have already been cross referenced with Jomres "dictionary items", such as room types, property types etc

			if ($mapped_to_jomres_only && $mapped_dictionary_items != false ) {
				$temp_arr = array();
				foreach ($mapped_dictionary_items as $key=>$val) {
					if ($val->jomres_id > 0) {
						$temp_arr[$key] = $val;
						}
					$all_channel_dictionary_items[$item_type] = $temp_arr;
				}
			} else {
				$all_channel_dictionary_items[$item_type] = $mapped_dictionary_items;
			}
		}

		return $all_channel_dictionary_items;
	}

	
 	public static function get_image ($url , $property_uid , $resource_type = "property" , $resource_id = 0 )
	{
		$MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
		
		$siteConfig = jomres_singleton_abstract::getInstance('jomres_config_site_singleton');
		$jrConfig = $siteConfig->get();
		
		$mkdir_mode = 0755;
		
		$result = $MiniComponents->triggerEvent('03379' , array( "property_uid" => $property_uid ) );
		$resource_types = $MiniComponents->miniComponentData[ '03379' ];
		
		if (empty($resource_types)) { // Do nowt.
			return;
		}
		
		//if resource type is empty, return
		if ($resource_type == '')
			return;
		
		//if resource id is blank, make it 0
		if ($resource_id == '')
			$resource_id = '0';
		
		// A security check to ensure that the user's not trying to pass a resource type that we can't handle
		if (!array_key_exists($resource_type, $resource_types)) { // The resource type isn't recognised, let's get the hell outta Dodge.
			return;
		}
		
		$resource_id_required = $resource_types [$resource_type] [ 'resource_id_required' ];
		
		//set image upload paths
		if ($resource_id_required) {
			$abs_path = $resource_types [$resource_type] ['upload_root_abs_path'].$resource_type.JRDS.$resource_id.JRDS;
			$rel_path = $resource_types [$resource_type] ['upload_root_rel_path'].$resource_type.'/'.$resource_id.'/';
		} else {
			$abs_path = $resource_types [$resource_type] ['upload_root_abs_path'].$resource_type.JRDS;
			$rel_path = $resource_types [$resource_type] ['upload_root_rel_path'].$resource_type.'/';
		}

		if (!is_dir(JOMRES_TEMP_ABSPATH."temp_images_dirty".JRDS)) {
			mkdir(JOMRES_TEMP_ABSPATH."temp_images_dirty".JRDS, $mkdir_mode, true);
		}
		
		$resized_file_name = basename($url);
		$file = JOMRES_TEMP_ABSPATH."temp_images_dirty".JRDS . basename($url);
		$fileHandle = fopen($file, "w+");

		try {
			$client = new GuzzleHttp\Client();
			$client->request('GET',$url, [
				'sink' => $file,
				]);
		} catch (RequestException $e) {
			 return;
		} finally {
			@fclose($fileHandle);
		}
		
		$jomres_media_centre_images = jomres_singleton_abstract::getInstance('jomres_media_centre_images');
		
		//  Fullsize image
		if (!is_dir($abs_path)) {
			mkdir($abs_path, $mkdir_mode, true);
		}
		
		Image::open($file)
			->zoomCrop((int)$jrConfig[ 'maxwidth' ], (int)$jrConfig[ 'maxwidth' ] )
			->save( $abs_path.JRDS.$resized_file_name , 'jpg', 85);
			
		$jomres_media_centre_images->handle_uploaded_image(
			$property_uid,
			$resource_type,
			$resource_id,
			$resized_file_name, 
			'large',
			$resource_id_required
		);
		
		//  Medium image
		if (!is_dir($abs_path.JRDS.'medium')) {
			mkdir($abs_path.JRDS.'medium', $mkdir_mode, true);
		}
		
		Image::open($file)
			->zoomCrop((int)$jrConfig[ 'thumbnail_property_header_max_width' ], (int)$jrConfig[ 'thumbnail_property_header_max_height' ] )
			->save( $abs_path.JRDS.'medium'.JRDS.$resized_file_name , 'jpg', 85);
			
		$jomres_media_centre_images->handle_uploaded_image(
			$property_uid,
			$resource_type,
			$resource_id,
			$resized_file_name, 
			'medium',
			$resource_id_required
		);
		
		//  Thumbnail image
		if (!is_dir($abs_path.JRDS.'thumbnail')) {
			mkdir($abs_path.JRDS.'thumbnail', $mkdir_mode, true);
		}
		
		Image::open($file)
			->zoomCrop((int)$jrConfig[ 'thumbnail_property_list_max_width' ], (int)$jrConfig[ 'thumbnail_property_list_max_height' ] )
			->save(  $abs_path.JRDS.'thumbnail'.JRDS.$resized_file_name , 'jpg', 85);

		$jomres_media_centre_images->handle_uploaded_image(
			$property_uid,
			$resource_type,
			$resource_id,
			$resized_file_name, 
			'small',
			$resource_id_required
		);
		
		unlink($file); 
		
		$jomres_media_centre_images->get_images($property_uid);
		
		$MiniComponents->triggerEvent('03383');
		
		$webhook_notification								= new stdClass();
		$webhook_notification->webhook_event				= 'image_added';
		$webhook_notification->webhook_event_description	= 'Logs when images are added.';
		$webhook_notification->webhook_event_plugin			= 'channelmanagement_framework';
		$webhook_notification->data							= new stdClass();
		$webhook_notification->data->property_uid			= $property_uid;
		$webhook_notification->data->added_image			= $resized_file_name;
		$webhook_notification->data->resource_type			= $resource_type;

		add_webhook_notification($webhook_notification);
		
		return $jomres_media_centre_images->images[$resource_type];

	}
	
	
	public static function delete_image ($file_name , $property_uid , $resource_type = "property" , $resource_id = 0 )
	{
		$MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
		
		$result = $MiniComponents->triggerEvent('03379' , array( "property_uid" => $property_uid ) );
		$resource_types = $MiniComponents->miniComponentData[ '03379' ];
		
		if (empty($resource_types)) { // Do nowt.
			return false ;
		}
		
		//if resource type is empty, return
		if ($resource_type == '')
			return false ;
		
		//if resource id is blank, make it 0
		if ($resource_id == '')
			$resource_id = '0';
		
		// A security check to ensure that the user's not trying to pass a resource type that we can't handle
		if (!array_key_exists($resource_type, $resource_types)) { // The resource type isn't recognised, let's get the hell outta Dodge.
			return false ;
		}
		
		$resource_id_required = $resource_types [$resource_type] [ 'resource_id_required' ];
		
		//set image upload paths
		if ($resource_id_required) {
			$abs_path = $resource_types [$resource_type] ['upload_root_abs_path'].$resource_type.JRDS.$resource_id.JRDS;
			$rel_path = $resource_types [$resource_type] ['upload_root_rel_path'].$resource_type.'/'.$resource_id.'/';
		} else {
			$abs_path = $resource_types [$resource_type] ['upload_root_abs_path'].$resource_type.JRDS;
			$rel_path = $resource_types [$resource_type] ['upload_root_rel_path'].$resource_type.'/';
		}

		if ($file_name == '') {
			return false ;
		}
		
		$jomres_media_centre_images = jomres_singleton_abstract::getInstance('jomres_media_centre_images');
		
		//delete image from disk and db
		if (!$jomres_media_centre_images->delete_image($property_uid, $resource_type, $resource_id, $file_name, $abs_path, $resource_id_required)) {
			$response = array('message' => "Boo, we couldn't delete it. I'm going to have a little cry in the corner now.", 'success' => '0');
		} else {
			$response = array('message' => "Yay, we'll deleted this sukka", 'success' => '1');
		}
		
		$MiniComponents->triggerEvent('03383');
		$MiniComponents->triggerEvent('03384');
			
		$webhook_notification								= new stdClass();
		$webhook_notification->webhook_event				= 'image_deleted';
		$webhook_notification->webhook_event_description	= 'Logs when images are deleted.';
		$webhook_notification->webhook_event_plugin			= 'channelmanagement_framework';
		$webhook_notification->data							= new stdClass();
		$webhook_notification->data->property_uid			= $property_uid;
		$webhook_notification->data->deleted_image			= $file_name;
		$webhook_notification->data->resource_type			= $resource_type;
		add_webhook_notification($webhook_notification);
	
		$jomres_media_centre_images->get_images($property_uid);
		
		return $jomres_media_centre_images->images[$resource_type];

	}

}
