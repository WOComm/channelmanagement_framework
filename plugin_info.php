<?php
/**
* Jomres CMS Agnostic Plugin
* @author Woollyinwales IT <sales@jomres.net>
* @version Jomres 9 
* @package Jomres
* @copyright	2005-2020 Vince Wooll
* Jomres (tm) PHP files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project.
**/


// ################################################################
defined( '_JOMRES_INITCHECK' ) or die( '' );
// ################################################################

class plugin_info_channelmanagement_framework
	{
	function __construct()
		{
		$this->data=array(
			"name"=>"channelmanagement_framework",
			"category"=>"Integration",
			"marketing"=>"Plugin that offers framework code for channel management plugins",
			"version"=>(float)"1.9",
			"description"=> "Plugin that offers framework code for channel management plugins",
			"lastupdate"=>"2021/09/24",
			"min_jomres_ver"=>"9.21.5",
			"manual_link"=>'',
			'change_log'=>'v1.1 Added UI functionality to frontend for importing properties via ajax, plus webhook handling updates. v1.2 Variety of changes for jomres2jomres which mean that Rentals United thin plugin will require some refactoring before it can be progressed in dev.  v1.3 Various fixes for jomres2jomres property import. v1.4 Lots of changes, primarily with respect to webhook processing v1.5 Proxy header changed to use hyphens v1.6 A variety of changes to support the Rentals United plugin development. v1.7 Improved how the CMF framework reports import errors 1.8 Resolved an issue where task could not be reliably detected. v1.9 Improved handling of properties to be imported so that it is clearer that some properties without changelog items, or properties that are not completed on the remote server, they cannot be imported (import button is disabled)',
			'highlight'=>'',
			'image'=>'',
			'demo_url'=>''
			);
		}
	}
