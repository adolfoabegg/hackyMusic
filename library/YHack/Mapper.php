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
		switch ($code) {
            case self::MOSTLY_CLOUDY_NIGHT:
            case self::CLEAR_NIGHT:
            case self::FAIR_NIGHT:
            case self::PARTLY_CLOUDY_NIGHT:
                return false;
                break;
            default:
                return true;
                break;
        }
	}
    
    /**
     * Maps a weather condition code to a mood
     * 
     * @param string $code 
     * @return string
     */
    public function mapToMood($code)
    {
        switch ($code) {
            case self::TORNADO:
            case self::TROPICAL_STORM:
            case self::HURRICANE:
            case self::SEVERE_THUNDERSTORMS:
            case self::THUNDERSHOWERS:
                return 'tense';
            case self::MIXED_RAIN_AND_HAIL:
            case self::MIXED_RAIN_AND_SLEET:
            case self::DRIZZLE:
            case self::FREEZING_RAIN:
            case self::SHOWERS_1:
            case self::SHOWERS_2:
            case self::BLUSTERY:
            case self::MIXED_RAIN_AND_SNOW:
            case self::COLD:
            case self::SNOW_SHOWERS:
                return 'sad';
            case self::SNOW:
            case self::SNOW_FLURRIES:
            case self::SNOW_SHOWERS:
            case self::MIXED_SNOW_AND_SLEET:
            case self::SNOW_FLURRIES:
                return 'christmas';
            case self::FOGGY:
            case self::DUST:
            case self::SMOKY:
            case self::HAZE:
                return 'paranoic';
            case self::CLOUDY:
                return 'bored';
            case self::FAIR_DAY:
            case self::NOT_AVAILABLE:
            case self::PARTLY_CLOUDY:
                return 'happy';
            case self::FAIR_NIGHT:
            case self::CLEAR_NIGHT:
                return 'romantic';
            case self::SUNNY:
                return 'energetic';
            case self::HOT:
                return 'lounge';
            case self::SCATTERED_THUNDERSTORMS_1:
            case self::SCATTERED_THUNDERSTORMS_2:
            case self::SEVERE_THUNDERSTORMS:
            case self::ISOLATED_THUNDERSTORMS:
                return 'angry';
            default:
                return 'happy';
        }
    }
    
    const TORNADO                 = '0';	
    const TROPICAL_STORM          = '1';	
    const HURRICANE               = '2';	
    const SEVERE_THUNDERSTORMS    = '3';	
    const THUNDERSTORMS           = '4';	
    const MIXED_RAIN_AND_SNOW     = '5';	
    const MIXED_RAIN_AND_SLEET    = '6';	
    const MIXED_SNOW_AND_SLEET    = '7';	
    const FREEZING_DRIZZLE        = '8';	
    const DRIZZLE                 = '9';	
    const FREEZING_RAIN           = '10';	
    const SHOWERS_1               = '11';	
    const SHOWERS_2               = '12';	
    const SNOW_FLURRIES           = '13';	
    const LIGHT_SNOW_SHOWERS      = '14';	
    const BLOWING_SNOW            = '15';	
    const SNOW                    = '16';	
    const HAIL                    = '17';	
    const SLEET                   = '18';	
    const DUST                    = '19';	
    const FOGGY                   = '20';	
    const HAZE                    = '21';	
    const SMOKY                   = '22';	
    const BLUSTERY                = '23';	
    const WINDY                   = '24';	
    const COLD                    = '25';	
    const CLOUDY                  = '26';	
    const MOSTLY_CLOUDY_NIGHT   = '27';	
    const MOSTLY_CLOUDY_DAY     = '28';	
    const PARTLY_CLOUDY_NIGHT   = '29';	
    const PARTLY_CLOUDY_DAY     = '30';	
    const CLEAR_NIGHT           = '31';	
    const SUNNY                   = '32';	
    const FAIR_NIGHT              = '33';	
    const FAIR_DAY                = '34';	
    const MIXED_RAIN_AND_HAIL     = '35';	
    const HOT                     = '36';	
    const ISOLATED_THUNDERSTORMS  = '37';	
    const SCATTERED_THUNDERSTORMS_1 = '38';	
    const SCATTERED_THUNDERSTORMS_2 = '39';	
    const SCATTERED_SHOWERS       = '40';	
    const HEAVY_SNOW_1            = '41';	
    const SCATTERED_SNOW_SHOWERS  = '42';	
    const HEAVY_SNOW_2            = '43';	
    const PARTLY_CLOUDY           = '44';	
    const THUNDERSHOWERS          = '45';	
    const SNOW_SHOWERS            = '46';	
    const ISOLATED_THUNDERSHOWERS = '47';	
    const NOT_AVAILABLE           = '3200';
}

