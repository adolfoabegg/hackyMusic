<?php
/**
 * Code to mood mapping
 *
 * @category YHack
 * @package YahooAPI
 * @copyright Codefathers team
 */

class YHack_Mapper
{
	/**
	 * Singleton object
	 *  
	 * @static
	 * @var YHack_Mapper
	 */
	protected static $_instance = null;
	
	/**
	 * Class constructor - must not be called directly
	 * 
	 * @access protected
	 */
	protected function __construct()
	{
	
	}
	
	/**
	 * __clone() implementation that prevents singleton objects cloning
	 * 
	 * @access public 
	 * @return void
	 */
	public function __clone()
	{
		throw new Exception('Please do not clone singleton objects');    
	}
	
	/**
	 * Returns a singleton instance of this class
	 * 
	 * @static
	 * @access public
	 * @return YHack_Mapper
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new YHack_Mapper();
		}
	
		return self::$_instance;
	}
	
	/**
	 * Check if it's sunny
	 * 
	 * @todo implement
	 * @param numeric $code 
	 * @access public
	 * @return bool
	 */
	public function isSunny($code)
	{
		return true;
	}
}

