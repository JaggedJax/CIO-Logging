<?php
   /*****************************************************************
   *                                                                *
   *                                                                *
   * System:  ANY                                                   *
   * Object:  CIOCrypt.php                                          *
   * Author:  William Wynn / CIO Technologies        Date: 03/15/12 *
   *                                                                *
   * Object contains functions for hashes and random generation     *
   *                                                                * 
   * Uses: N/A                                                      *            
   *                                                                *
   * Sign Date     Change                                           *
   * XXXX XX/XX/XX XXXXXXXXXXXXXXXXXXXXXXXXXX                       *
   *                                                                *
   *****************************************************************/

class CIOCrypt
{
	
	/**
	 * Perform and return a Blowfish hash on the given string. Strength value must be in the range 04-31
	 * Use this for password hashes.
	 */
	public static function hash_blowfish($toHash, $strength='07'){
		return crypt($toHash, '$2a$'.str_pad($strength, 2, '0', STR_PAD_LEFT).'$'.CIOCrypt::rand_alphanumeric(22).'$');
	}
	
	/**
	 * Verify whether or not an input matches the stored hash. Works with any hash created by php's crypt (Like blowfish).
	 */
	public static function verify($userInput, $storedHash){
		return (crypt($userInput, $storedHash) == $storedHash);
	}
	
	/**
	 * Generate random alpha-numeric string of specified length
	 */
	public static function rand_alphanumeric($length){
		return CIOCrypt::rand_string('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length);
	}
	
	/**
	 * Generate random string of specified length based off the given string of characters
	 */
	public static function rand_string($characters, $length){
		$string = '';
		mt_srand(CIOCrypt::make_seed());
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $string;
	}
	
	/**
	 * Base64 encode a string and make it URL safe (Must decode with base64url_decode from this object)
	 */
	public static function base64url_encode($data) { 
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	}
	
	/**
	 * Base64 decode a string from a URL that was encoded using base64url_encode from this object
	 */
	public static function base64url_decode($data) { 
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	} 
	
	/**
	 * Generate a seed based off the time
	 */
	private static function make_seed(){
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
}

?>