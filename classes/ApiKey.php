<?php

namespace classes;

include_once($_SERVER['DOCUMENT_ROOT'] . '/API/inc/class_loader.php');
use classes\Utility;

class ApiKey
{

    // Insatnce Vars
    const NUM_DIGITS = 18;

    // Constructors

    /**
     * Default constructor
     */
    public function __construct()
    {
    }

    // Methods

    /**
     * Method to generate an API access key
     */
    public static function generateKey(): string
    {
        // Generate random int of X length
        $min = (int) (10 ** (ApiKey::NUM_DIGITS - 1));
        $max = (int) ((10 ** ApiKey::NUM_DIGITS) - 1);

        $key = random_int($min, $max);

        // Base 62 encode (0-9, a-z, A-Z)
        $key = Utility::toBase($key, 62);

        // Shouldnt need this, as base 62 encoded, but just to be safe - ensure URL friendly!
        $key = urlencode($key);

        return $key;
    }

    /**
     * Method to generate a signature from a given input using an access key
     */
    public static function generateSignature($key, array $inputs): string
    {
        // Sort a-z by keys
        ksort($inputs);

        $keys = array_keys($inputs);

        $message = "";
        // Concatonate string
        for ($loop = 0; $loop < count($inputs); $loop++) {
            $message .= $keys[$loop];
            $message .= $inputs[$keys[$loop]];
        }

        // Hash / encode string
        $sig = hash_hmac("md5", $message, $key, true);
        $sig = base64_encode($sig);

        return $sig;
    }
}
