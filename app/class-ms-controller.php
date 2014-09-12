<?php
/**
 * This file defines the MS_Controller object.
 *
 * @copyright Incsub (http://incsub.com/)
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 * 
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License, version 2, as  
 * published by the Free Software Foundation.                           
 *
 * This program is distributed in the hope that it will be useful,      
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        
 * GNU General Public License for more details.                         
 *
 * You should have received a copy of the GNU General Public License    
 * along with this program; if not, write to the Free Software          
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               
 * MA 02110-1301 USA                                                    
 *
*/

/**
 * Abstract class for all Controllers.
 *
 * All controllers will extend or inherit from the MS_Controller class.
 * Methods of this class will control the flow and behaviour of the plugin
 * by using MS_Model and MS_View objects.
 *
 * @since 4.0.0
 *
 * @uses MS_Model
 * @uses MS_View
 *
 * @package Membership
 */
class MS_Controller extends MS_Hooker {
	
	/**
	 * Capability required to use access metabox.
	 *
	 * @since 4.0.0
	 * @access private
	 * @var $capability
	 */
	protected $capability = 'manage_options';
	
	/**
	 * Parent constuctor of all controllers.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		
		/**
		 * Actions to execute when constructing the parent controller.
		 *
		 * @since 4.0.0
		 * @param object $this The MS_Controller object.
		 */
		do_action( 'membership_parent_controller_construct', $this );
	}
	
	/**
	 * Get action from request.
	 *
	 * @since 4.0.0
	 * 
	 * @return string
	 */
	public function get_action() {
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
		return apply_filters( 'ms_controller_get_action', $action );
	}
	
	/**
	 * Verify nonce.
	 *
	 * @since 4.0.0
	 * 
	 * @param string $action The action name to verify nonce.
	 * @param string $request_method POST or GET
	 * @param string $nonce_field The nonce field name
	 * @return bool True if verified, false otherwise.
	 */
	public function verify_nonce( $action = null, $request_method = 'POST', $nonce_field = '_wpnonce' ) {
		
		$verified = false;
		$request_fields = ( 'POST' == $request_method ) ? $_POST : $_GET;
		
		if( empty( $action ) ) {
			$action = ! empty( $request_fields['action'] ) ? $request_fields['action'] : '';
		}
		if( ! empty( $request_fields[ $nonce_field ] ) && wp_verify_nonce( $request_fields[ $nonce_field ], $action ) ) {
			$verified = true;
		}
		return $verified;
	}
	
	/**
	 * Verify if current user can perform management actions.
	 *
	 * @since 4.0.0
	 * @return bool True if can, false otherwise.
	 */
	public function is_admin_user() {
		$is_admin_user = false;
		$is_admin_user = MS_Model_Member::is_admin_user( null, $this->capability );
		return apply_filters( 'ms_controller_current_user_can', $is_admin_user, $this->capability );
	}
	
	/**
	 * Verify required fields aren't empty.
	 * 
	 * @since 4.0.0
	 * 
	 * @param string[] $fields The array of fields to validate.
	 * @param string $request_method POST or GET
	 * @param bool $not_empty if true use empty method, else use isset method.
	 * @return bool True all fields are validated
	 */
	public function validate_required( $fields, $request_method = 'POST', $not_empty = true ) {
		$validated = true;
		$request_fields = null;
		switch( $request_method ) {
			case 'GET':
				$request_fields = $_GET;
				break;
			case 'REQUEST':
				$request_fields = $_REQUEST;
				break;
			default:
			case 'POST':
				$request_fields = $_POST;
				break;
					
		}
		
		foreach( $fields as $field ) {
			if( $not_empty ) {
				if( empty( $request_fields[ $field ] ) ) {
					$validated = false;
				}				
			}
			else {
				if( ! isset( $request_fields[ $field ] ) ) {
					$validated = false;
				}				
			}
		}
		
		return apply_filters( 'ms_controller_validate_required', $validated, $fields );
	}
	
	/**
	 * Get field from request parameters.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id The field ID  
	 * @param mixed $default The default value of the field.
	 * @param string $request_method POST or GET
	 * @return mixed The value of the request field.
	 */
	public function get_request_field( $id, $default = '', $request_method = 'POST' ) {
		$value = $default;
		$request_fields = null;
		switch( $request_method ) {
			case 'GET':
				$request_fields = $_GET;
				break;
			case 'REQUEST':
				$request_fields = $_REQUEST;
				break;
			default:
			case 'POST':
				$request_fields = $_POST;
				break;
			
		}

		if( isset( $request_fields[ $id ] ) ) {
			$value = $request_fields[ $id ];
		}
		
		return apply_filters( 'ms_controller_get_request_field', $value, $id, $default );
	}
}