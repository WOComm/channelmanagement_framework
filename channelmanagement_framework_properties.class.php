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


class channelmanagement_framework_properties
{
	
	function __construct()
	{
		
	}

	public static function get_local_property_ids_for_channel( $channel = '' )
	{
		$channelmanagement_framework_singleton = jomres_singleton_abstract::getInstance('channelmanagement_framework_singleton'); 
		$response = $channelmanagement_framework_singleton->rest_api_communicate( $channel , 'GET' , 'cmf/properties/ids/' );
		if ( isset($response->data->response) ) {
			return $response->data->response;
		} else {
			return array();
		}
		
	}
	
	
	function check_remote_id_already_exists ($remote_id = 0 )
	{
		
	}

	
/* 
object(stdClass)#2361 (24) {
  ["remote_property_id"]=>
  string(7) "2174511"
  ["remote_ptype_id"]=>
  string(2) "20"
  ["local_ptype_id"]=>
  int(1)
  ["remote_currency"]=>
  string(3) "EUR"
  ["remote_name"]=>
  string(13) "Test property"
  ["remote_max_guests"]=>
  string(2) "30"
  ["remote_ptype"]=>
  string(2) "38"
  ["remote_street"]=>
  string(7) "test 20"
  ["remote_postcode"]=>
  string(5) "12345"
  ["remote_email"]=>
  string(16) "vince@jomres.net"
  ["remote_tel"]=>
  string(11) "01234567890"
  ["remote_licensenumber"]=>
  string(5) "asdf "
  ["remote_lat"]=>
  string(10) "38.7890109"
  ["remote_long"]=>
  string(17) "0.166081299999973"
  ["remote_property_description"]=>
  string(124) "Composition: 15x bedroom, 15x bathroom, 20x WC
Amenities: bed linen & towels, terrace, heating, swimming pool

TEST PROPERTY"
  ["remote_property_checkin_times"]=>
  string(38) "Check in 13:00 - 17:00 Check out 11:00"
  ["remote_deposit_type_id"]=>
  string(1) "3"
  ["remote_deposit_value"]=>
  string(7) "10.0000"
  ["remote_security_deposit_type_id"]=>
  string(1) "3"
  ["remote_security_deposit_value"]=>
  string(7) "10.0000"
  ["image_urls"]=>
  array(3) {
    [0]=>
    string(77) "https://dwe6atvmvow8k.cloudfront.net/ru/426764/2174511/636910942847881503.jpg"
    [1]=>
    string(77) "https://dwe6atvmvow8k.cloudfront.net/ru/426764/2174511/636910942913662393.jpg"
    [2]=>
    string(77) "https://dwe6atvmvow8k.cloudfront.net/ru/426764/2174511/636910942953506136.jpg"
  }
  ["remote_settings"]=>
  array(1) {
    ["mrp_or_srp"]=>
    int(0)
  }
  ["remote_room_features"]=>
  array(1) {
    [0]=>
    object(stdClass)#2074 (4) {
      ["item"]=>
      object(stdClass)#2075 (2) {
        ["xml_attributes"]=>
        object(stdClass)#2076 (1) {
          ["AmenityID"]=>
          string(2) "81"
        }
        ["value"]=>
        string(8) "Bathroom"
      }
      ["name"]=>
      string(8) "Bathroom"
      ["jomres_id"]=>
      int(6)
      ["remote_item_id"]=>
      string(2) "81"
    }
  }
  ["remote_property_features"]=>
  array(2) {
    [0]=>
    object(stdClass)#649 (4) {
      ["item"]=>
      object(stdClass)#650 (2) {
        ["xml_attributes"]=>
        object(stdClass)#651 (1) {
          ["AmenityID"]=>
          string(3) "100"
        }
        ["value"]=>
        string(7) "Terrace"
      }
      ["name"]=>
      string(7) "Terrace"
      ["jomres_id"]=>
      int(7)
      ["remote_item_id"]=>
      string(3) "100"
    }
    [1]=>
    object(stdClass)#775 (4) {
      ["item"]=>
      object(stdClass)#776 (2) {
        ["xml_attributes"]=>
        object(stdClass)#777 (1) {
          ["AmenityID"]=>
          string(3) "227"
        }
        ["value"]=>
        string(13) "swimming pool"
      }
      ["name"]=>
      string(13) "swimming pool"
      ["jomres_id"]=>
      int(58)
      ["remote_item_id"]=>
      string(3) "227"
    }
  }
}

*/

	private function validate_property_settings_array( $settings ) 
	{
		$channelmanagement_framework_singleton = jomres_singleton_abstract::getInstance('channelmanagement_framework_singleton'); 
		$response = $channelmanagement_framework_singleton->rest_api_communicate( $channel , 'GET' , 'cmf/property/validate/settings/' , $settings );
		var_dump($response);exit;
/* 		$mrConfig = getPropertySpecificSettings($property_id);
		foreach ($settings as $key=>$val) {
			if (!array_key_exists($key , $mrConfig )) {
				throw new Exception( jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_SETTINGS_FAILED_VALIDATION','CHANNELMANAGEMENT_FRAMEWORK_SETTINGS_FAILED_VALIDATION',false) ).$key;
			}
		} */
	}

	/*
	
	$property_id The id of the property
	
	
	*/
	private function set_property_settings( $property_id , $settings ) 
	{
		importSettings($property_id, 0);
		$this->validate_property_settings_array($settings);
		
		$mrConfig = getPropertySpecificSettings($property_id);  // Sets up the mrConfig array that holds the property settings
		
		foreach ($settings as $key=>$val) {
			if (array_key_exists($key , $mrConfig )) {
				$clean_val = trim(filter_var($val, FILTER_SANITIZE_SPECIAL_CHARS));
				insertSetting($property_id, $key, $clean_val);
			}
		}
	}
	

	public static function import_property( $channel_manager , $property  )
	{
		if (trim($channel_manager) == '' ){
			throw new Exception( jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORTING_CHANNEL_NAME_NOT_SUPPLIED','CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORTING_CHANNEL_NAME_NOT_SUPPLIED',false) );
		}
		
		if (!is_object($property)){
			throw new Exception( jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORTING_NEW_PROPERTY_OBJECT_NOT_SUPPLIED','CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORTING_NEW_PROPERTY_OBJECT_NOT_SUPPLIED',false) );
		}
		
		logging::log_message(jr_gettext('CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORTING','CHANNELMANAGEMENT_FRAMEWORK_PROPERTY_IMPORTING',FALSE), 'CHANNEL_MANAGEMENT_FRAMEWORK', 'DEBUG');

			/*
			<DepositType DepositTypeID="1">No deposit</DepositType>
			<DepositType DepositTypeID="2">Percentage of total price (without cleaning)</DepositType>
			<DepositType DepositTypeID="3">Percentage of total price</DepositType>
			<DepositType DepositTypeID="4">Fixed amount per day</DepositType>
			<DepositType DepositTypeID="5">Flat amount per stay</DepositType>
			 */

		try {
			$MiniComponents = jomres_singleton_abstract::getInstance('mcHandler');
			$MiniComponents->specificEvent('21400', 'channelmanagement_framework_property_details', array( "channel_manager" => $channel_manager , "data" => $property  )); 
			
			$MiniComponents->specificEvent('21500', 'channelmanagement_framework_'.$thingy, array( "channel_manager" => $channel_manager , "data" => $property  )); 
		} catch (Exception $e) {
			
		}
	}
	
	public static function import_tariff( $channel_manager , $property) 
	{
		
		// Tariff default values
		$rate_title       = "Tariff";
		$rate_description = "";
		$validfrom        = date( "Y/m/d" );
		$validto          = date( "Y/m/d", strtotime( "+10 years" ) );
		$validfrom_ts     = str_replace( "/", "-", $validfrom );
		$validto_ts       = str_replace( "/", "-", $validto );
		$mindays          = 1;
		$maxdays          = 1000;
		$minpeople        = 1;
		$ignore_pppn      = 0;
		$allow_ph         = 1;
		$allow_we         = 1;
		$weekendonly      = 0;
		
								// First we'll create the rooms for this property
								/* $room_number = 1;
								foreach ( $room_xref as $beds24_roomtype_id => $room )
									{
									for ( $i = 1; $i <= $room['qty'];$i++)
										{
										$query = "INSERT INTO #__jomres_rooms (
										`room_classes_uid`,`propertys_uid`,`room_number`,`max_people`,`singleperson_suppliment`)
										VALUES (
										'" . (int) $room['jomres_room_type_id'] . "'," . (int) $property_uid . ",'$room_number','2','0')";
										$room_id = doInsertSql( $query );
										$room_number++;
										}
									$query = "INSERT INTO #__jomres_beds24_room_type_xref ( `jomres_room_type` , `beds24_room_type` , `property_uid` ) VALUES ( ".$room['jomres_room_type_id'] ." , ".(int)$beds24_roomtype_id." ,  ".$property_uid." )";
									doInsertSql($query);
									
									// Now we can add a tariff for this room type
									$query = "INSERT INTO #__jomres_rates (
										`rate_title`,
										`rate_description`,
										`validfrom`,
										`validto`,
										`roomrateperday`,
										`mindays`,
										`maxdays`,
										`minpeople`,
										`maxpeople`,
										`roomclass_uid`,
										`ignore_pppn`,
										`allow_ph`,
										`allow_we`,
										`weekendonly`,
										`validfrom_ts`,
										`validto_ts`,
										`property_uid`
										)VALUES (
										'$rate_title',
										'$rate_description',
										'$validfrom',
										'$validto',
										'" . $room['minPrice'] . "',
										'" . (int) $mindays . "',
										'" . (int) $maxdays . "',
										'" . (int) $minpeople . "',
										'2',
										'" . (int) $room['jomres_room_type_id'] . "',
										'" . (int) $ignore_pppn . "',
										'" . (int) $allow_ph . "',
										'" . (int) $allow_we . "',
										'" . (int) $weekendonly . "',
										'$validfrom_ts',
										'$validto_ts',
										'" . (int) $property_uid . "'
										)";
									//echo $query."<br>";
									try 
										{
										doInsertSql($query);
										}
									catch (Exception $e) 
										{
										throw new Exception("Cannot insert tariffs during property import.");
										}
									} */
	}
}
