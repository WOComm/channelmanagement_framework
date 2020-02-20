<?php
/**
* Jomres CMS Agnostic Plugin
* @author Woollyinwales IT <sales@jomres.net>
* @version Jomres 9 
* @package Jomres
* @copyright	2005-2019 Woollyinwales IT
* Jomres (tm) PHP files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project.
**/

// ################################################################
defined( '_JOMRES_INITCHECK' ) or die( 'Direct Access to this file is not allowed.' );
// ################################################################

class j00005channelmanagement_framework {
	function __construct($componentArgs)
		{
		// Must be in all minicomponents. Minicomponents with templates that can contain editable text should run $this->template_touch() else just return 
		$MiniComponents =jomres_getSingleton('mcHandler');
		if ($MiniComponents->template_touch)
			{
			$this->template_touchable=false; return;
			}
		
		$ePointFilepath = get_showtime('ePointFilepath');
		
		if (file_exists($ePointFilepath.'language'.JRDS.get_showtime('lang').'.php'))
			require_once($ePointFilepath.'language'.JRDS.get_showtime('lang').'.php');
		else
			{
			if (file_exists($ePointFilepath.'language'.JRDS.'en-GB.php'))
				require_once($ePointFilepath.'language'.JRDS.'en-GB.php');
			}
			
		if (!defined('JOMRES_CHANNEL_DICTIONARIES')) {
			define('JOMRES_CHANNEL_DICTIONARIES' , JOMRES_TEMP_ABSPATH.'dictionaries');
		}
		

		require_once($ePointFilepath.JRDS."vendor".JRDS."autoload.php");
		
		if (!is_dir(JOMRES_CHANNEL_DICTIONARIES)) {
			mkdir(JOMRES_CHANNEL_DICTIONARIES);
			if (!is_dir(JOMRES_CHANNEL_DICTIONARIES)) {
				throw new Exception("Cannot make ".JOMRES_CHANNEL_DICTIONARIES." directory, cannot continue.");
			}
		}
		
		$thisJRUser = jomres_singleton_abstract::getInstance('jr_user');
		
		if ($thisJRUser->accesslevel < 70) {
			return;
		}
		
		if (!defined('JOMRES_API_CMS_ROOT') ) {
			$channelmanagement_framework_singleton = jomres_singleton_abstract::getInstance('channelmanagement_framework_singleton'); 
		} 
		
		$property_uid = getDefaultProperty();
		
		if ($property_uid > 0)
			{
			$mrConfig = getPropertySpecificSettings($property_uid);
			
			
			
			$jomres_menu = jomres_singleton_abstract::getInstance('jomres_menu');
			
			if ($mrConfig[ 'is_real_estate_listing' ] != '1' && !get_showtime('is_jintour_property')) 
				{
				if ($thisJRUser->accesslevel >= 70) 
					{
					$jomres_menu->add_item(80, jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_TITLE', 'CHANNELMANAGEMENT_FRAMEWORK_TITLE', false), 'channelmanagement_framework', 'fa-sitemap');
					}
				}
				
			$jomres_menu->add_admin_item(50, jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_TITLE', 'CHANNELMANAGEMENT_FRAMEWORK_TITLE', false), $task = 'channelmanagement_framework', 'fa-share-alt-square');
			}
			
			
		// Create the webhook if we need to.
		$manager_uid = 0;
		$url = 'Channel Manager Framework'; // We don't need to set a url, the endpoint will be coded into the webhook script.
				
		jr_import("webhooks");
		$webhooks = new webhooks( $manager_uid );
		$all_webhooks = $webhooks->get_all_webhooks();
		$webhook_already_exists = false;
		if (!empty($all_webhooks)) {
			foreach ( $all_webhooks as $key=>$val ) {
				if ($val['settings']['url'] == $url ) {
					$webhook_already_exists = true; // A webhook for this site already exists, we will not create a new one
					}
				}
				
			}

		if (!$webhook_already_exists) {
			$integration_id = 0;

			$webhooks->set_setting( $integration_id , 'url' , $url );
			$webhooks->set_setting( $integration_id , 'authmethod' , 'channelmanagement_framework_webhook' );
			$webhooks->webhooks[$integration_id ]['enabled'] = 1;

			$webhooks->commit_integration($integration_id);
		}

		}

	// This must be included in every Event/Mini-component
	function getRetVals()
		{
		return null;
		}
	}
