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

class j06002channelmanagement_framework_import_properties {
	function __construct($componentArgs)
		{
		// Must be in all minicomponents. Minicomponents with templates that can contain editable text should run $this->template_touch() else just return
		$MiniComponents =jomres_getSingleton('mcHandler');
		if ($MiniComponents->template_touch)
			{
			$this->template_touchable=false; return;
			}

		$ePointFilepath = get_showtime('ePointFilepath');

		jomres_cmsspecific_addheaddata('javascript', JOMRES_NODE_MODULES_RELPATH.'blockui-npm/', 'jquery.blockUI.js');
		
		$JRUser									= jomres_singleton_abstract::getInstance( 'jr_user' );
		
		$channelmanagement_framework_singleton = jomres_singleton_abstract::getInstance('channelmanagement_framework_singleton'); 
		
		$channel_name	= trim(filter_var($_GET['channel_name'], FILTER_SANITIZE_SPECIAL_CHARS));
		
		$properties_list_class_name = 'channelmanagement_'.$channel_name.'_list_remote_properties';
		jr_import($properties_list_class_name);
		if ( !class_exists($properties_list_class_name) ) {
			throw new Exception( jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_MAPPING_CHANNEL_DICTIONARY_CLASS_DOESNT_EXIST','CHANNELMANAGEMENT_FRAMEWORK_MAPPING_CHANNEL_DICTIONARY_CLASS_DOESNT_EXIST',false, false ) );
		}
		
		
		$local_properties = channelmanagement_framework_properties::get_local_property_ids_for_channel( $channel_name );
		$local_property_remote_uids = array();
		if (!empty($local_properties)) {
			foreach ($local_properties as $local_property) {
				$local_property_remote_uids[] = $local_property->remote_property_uid;
			}
			
		}
		

		$properties_list_class = new $properties_list_class_name();
		$remote_properties = $properties_list_class->get_remote_properties();

		if (empty($remote_properties)) {
			jomresRedirect( jomresURL( JOMRES_SITEPAGE_URL . "&task=channelmanagement_framework" ) , " No properties to import ");
		}
		
		$output = array();
		$pageoutput = array();
		
		$output['PAGETITLE'] = jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORT','CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORT',false);
		
		$output['PROPERTY_ID_STRING'] = "";

		$property_names = array();
		foreach ($remote_properties as $remote_property) {
			if ( $remote_property[ "remote_property_id"] > 0 && !in_array ( $remote_property[ "remote_property_id"] , $local_property_remote_uids ) ) {
				$r=array();
				$output['PROPERTY_ID_STRING'] .= $remote_property[ "remote_property_id"].",";
				$r["REMOTE_PROPERTY_ID"] = $remote_property[ "remote_property_id"];
				$r["REMOTE_PROPERTY_NAME"] = $remote_property[ "remote_property_name"];
				$property_names[] = $r;
			}
		}
		
		$output['PROPERTY_ID_STRING'] = substr( $output['PROPERTY_ID_STRING'], 0, strlen( $output['PROPERTY_ID_STRING'] ) - 1 );
		$output['CHANNEL_NAME'] = $channel_name;
		
		$pageoutput[] = $output;
		$tmpl = new patTemplate();
		$tmpl->addRows( 'pageoutput', $pageoutput );
		$tmpl->addRows( 'property_names', $property_names );
		$tmpl->setRoot( $ePointFilepath.'templates'.JRDS.find_plugin_template_directory() );
		$tmpl->readTemplatesFromInput( 'channelmanagement_framework_import_properties.html' );
		echo $tmpl->getParsedTemplate();
		
	}

	// This must be included in every Event/Mini-component
	function getRetVals()
		{
		return null;
		}
	}
