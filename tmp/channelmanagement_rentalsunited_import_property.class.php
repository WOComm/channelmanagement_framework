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


class channelmanagement_rentalsunited_import_property
{
	
	function __construct()
	{
		
	}

	public static function import_property( $remote_property_id = 0 , $mapped_dictionary_items = array()  )
	{
		if ( (int)$remote_property_id == 0 ) {
			throw new Exception( jr_gettext('CHANNELMANAGEMENT_RENTALSUNITED_IMPORT_PROPERTYID_NOTSET','CHANNELMANAGEMENT_RENTALSUNITED_IMPORT_PROPERTYID_NOTSET',false) );
		}
		
		if ( empty($mapped_dictionary_items) ) {
			throw new Exception( jr_gettext('CHANNELMANAGEMENT_RENTALSUNITED_IMPORT_MAPPEDDICTIONARYITEMS_NOTSET','CHANNELMANAGEMENT_RENTALSUNITED_IMPORT_MAPPEDDICTIONARYITEMS_NOTSET',false) );
		}
		
		$individual_property = $this->channelmanagement_rentalsunited_communication->communicate( array( "PropertyID" => $remote_property_id , 'Pull_ListSpecProp_RQ' ) );
					
		// IsArchived
		if ($individual_property['Property']['IsArchived'] != "true" ) {
			// We need to collate information about the property, room features, property features etc. Duplicates can appear so we need to array unique the property later
			
			// room features
			$property_room_features = array();
 			if (!empty($individual_property['Property']['CompositionRoomsAmenities']['CompositionRoomAmenities'])){
				foreach ($individual_property['Property']['CompositionRoomsAmenities']['CompositionRoomAmenities'] as $amenity) {
					$amenity_id = $amenity[$atts]['CompositionRoomID'];
					if ( array_key_exists ( $amenity_id , $mapped_dictionary_items['Pull_ListAmenitiesAvailableForRooms_RQ'] ) )
						$property_room_features[] = $mapped_dictionary_items['Pull_ListAmenitiesAvailableForRooms_RQ'][$amenity_id];
				}
				$property_room_features = array_unique($property_room_features, SORT_REGULAR);
			}
						
			/* 		
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
			} */

			// Property features
			$property_features = array();
			
 			if (!empty($individual_property['Property']['Amenities']['Amenity'])){
				foreach ($individual_property['Property']['Amenities']['Amenity'] as $amenity) {
					$amenity_id = $amenity['value'];
					if ( array_key_exists ( $amenity_id , $mapped_dictionary_items['Pull_ListAmenities_RQ'] ) )
						$property_features[] = $mapped_dictionary_items['Pull_ListAmenities_RQ'][$amenity_id];
				}
				$property_features = array_unique($property_features, SORT_REGULAR);
			}
			
			/* 
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
			*/
						
			$image_urls = array();
			if (!empty($individual_property['Property']['Images']['Image'])) {
				foreach ($individual_property['Property']['Images']['Image'] as $image ) {
					$image_urls[] = $image['value'];
				}
			}
		
			
			$new_property = new stdclass();
						
			$new_property->remote_property_uid				= $individual_property['Property']['ID']['value'];
			$new_property->remote_currency					= $individual_property['Property'][$atts]['Currency'];
			$new_property->remote_name						= $individual_property['Property']['Name'];
			$new_property->remote_max_guests				= $individual_property['Property']['CanSleepMax'];
			$new_property->remote_ptype						= $individual_property['Property']['PropertyTypeID'];
			$new_property->remote_street					= $individual_property['Property']['Street'];
			$new_property->remote_postcode					= $individual_property['Property']['ZipCode'];
			$new_property->remote_email						= $individual_property['Property']['ArrivalInstructions']['Email'];
			$new_property->remote_tel						= $individual_property['Property']['ArrivalInstructions']['Phone'];
			$new_property->remote_postcode					= $individual_property['Property']['ZipCode'];
			
			$new_property->remote_licensenumber				= $individual_property['Property']['LicenseNumber'];
			
			$new_property->remote_lat						= $individual_property['Property']['Coordinates']['Latitude'];
			$new_property->remote_long						= $individual_property['Property']['Coordinates']['Longitude'];
			
			$new_property->remote_property_description		= $individual_property['Property']['Descriptions']['Description']['Text'];
			$new_property->remote_property_checkin_times	= 
						
			jr_gettext('_JOMRES_ACTION_CHECKIN','_JOMRES_ACTION_CHECKIN',false,false)." ".
			$individual_property['Property']['CheckInOut']['CheckInFrom']." - ". $individual_property['Property']['CheckInOut']['CheckInTo']." ".
			jr_gettext('_JOMRES_ACTION_CHECKOUT','_JOMRES_ACTION_CHECKOUT',false,false)." ".
			$individual_property['Property']['CheckInOut']['CheckOutUntil'];
			
			/*
			<DepositType DepositTypeID="1">No deposit</DepositType>
			<DepositType DepositTypeID="2">Percentage of total price (without cleaning)</DepositType>
			<DepositType DepositTypeID="3">Percentage of total price</DepositType>
			<DepositType DepositTypeID="4">Fixed amount per day</DepositType>
			<DepositType DepositTypeID="5">Flat amount per stay</DepositType>
			 */
						
			$new_property->remote_deposit_type				= $individual_property['Property']['Deposit'][$atts]['DepositTypeID'];
			$new_property->remote_deposit_value				= $individual_property['Property']['Deposit']['value'];
						
			$new_property->remote_security_deposit_type		= $individual_property['Property']['Deposit'][$atts]['DepositTypeID'];
			$new_property->remote_security_deposit_value	= $individual_property['Property']['Deposit']['value'];
						
			$new_property->image_urls						= $image_urls;
			
			
			var_dump($new_property);exit;
			
			channelmanagement_framework_properties::import_property( $current_channel , $new_property );
		}
	}
}
