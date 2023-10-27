<?php
/**
 * @package    jelix
 * @subpackage auth
 *
 * @author     Laurent Jouanneau
 *
 * @copyright  2011-2023 Laurent Jouanneau
 *
 */

/**
 * @since 1.8.3
 */
class jAuthPassword
{

    /**
     * generate a password with random letters, numbers and special characters.
     *
     * @param int  $length              the length of the generated password
     * @param bool $withoutSpecialChars (optional, default false) the generated password may be use this characters : !@#$%^&*?_,~
     *
     * @return string the generated password
     */
    public static function getRandomPassword($length = 12, $withoutSpecialChars = false)
    {
        if ($length < 12) {
            $length = 12;
        }
        $nbNumber = floor($length / 4);
        if ($nbNumber < 2) {
            $nbNumber = 2;
        }
        if ($withoutSpecialChars) {
            $nbSpec = 0;
        } else {
            $nbSpec = floor($length / 5);
            if ($nbSpec < 1) {
                $nbSpec = 1;
            }
        }

        $nbLower = floor(($length - $nbNumber - $nbSpec) / 2);
        $nbUpper = $length - $nbNumber - $nbLower - $nbSpec;

        $pass = '';

        $letter = '1234567890';
        for ($i = 0; $i < $nbNumber; ++$i) {
            $pass .= $letter[rand(0, 9)];
        }

        $letter = '!@#$%^&*?_,~';
        for ($i = 0; $i < $nbSpec; ++$i) {
            $pass .= $letter[rand(0, 11)];
        }

        $letter = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < $nbLower; ++$i) {
            $pass .= $letter[rand(0, 25)];
        }

        $letter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $nbUpper; ++$i) {
            $pass .= $letter[rand(0, 25)];
        }

        return str_shuffle($pass);
    }


    const STRENGTH_NONE = 0;
    const STRENGTH_POOR = 1;
    const STRENGTH_BAD_PASS = -1;
    const STRENGTH_WEAK = 2;
    const STRENGTH_GOOD = 3;
    const STRENGTH_STRONG = 4;

    public static function checkPasswordStrength($password, $minLength=12)
    {
        $score = 0;

        if ($password == '') {
            return self::STRENGTH_NONE;
        }

        $len = mb_strlen($password);
        if ($minLength > 0 && $len < $minLength) {
            return self::STRENGTH_POOR;
        }

        $poolSize = 0;
        $poolSize += preg_match("/[A-Z]/", $password) ? 26 : 0;
        $poolSize += preg_match("/[a-z]/", $password) ? 26 : 0;
        $poolSize += preg_match("/[0-9]/", $password) ? 10 : 0;
        $poolSize += preg_match("/_/", $password) ? 1 : 0;
        $poolSize += preg_match("/ /", $password) ? 1 : 0;
        $poolSize += preg_match("/@/", $password) ? 1 : 0;
        $poolSize += preg_match("/[éèêÈÉÊçÇàÀßùÙ]/", $password) ? 13 : 0;
        $poolSize += preg_match("/[îûôâëäöïüÿðÂÛÎÔÖÏÜËÄŸ]/", $password) ? 21 : 0;
        $poolSize += preg_match("/[æœÆŒ]/", $password) ? 4 : 0;
        $poolSize += preg_match("/[\-−‑–—]/", $password) ? 5 : 0;
        $poolSize += preg_match("/[\"'()!:;,?«»¿¡‚„“”…]/", $password) ? 18 : 0;
        $poolSize += preg_match("/[+*\/×÷≠]/", $password) ? 6 : 0;
        $poolSize += preg_match("/[&\$£%µ€#¢]/", $password) ? 7 : 0;
        $poolSize += preg_match("/[²Ø~©®™]/", $password) ? 6 : 0;
        $poolSize += preg_match("/[¬ ÞĿÐ¥þ↓←↑→⋅∕]/", $password) ? 13 : 0;
        $poolSize += preg_match("/[\[\]{}|]/", $password) ? 5 : 0;

        $entropy =  $len * log($poolSize, 2);

        if ($entropy < 25) {
            return self::STRENGTH_POOR;
        }

        if ($entropy < 50) {
            return self::STRENGTH_WEAK;
        }
        $jAuthMostUsedPasswords = include(__DIR__.'/jAuthMostUsedPasswords.php');

        foreach($jAuthMostUsedPasswords as $badpassword) {
            //echo $badpassword."\n";
            if (preg_match("/(^|\\s)".$badpassword."($|\\s)/", $password)) {
                return self::STRENGTH_BAD_PASS;
            }
        }

        if ($entropy < 100) {
            return self::STRENGTH_GOOD;
        }
        return self::STRENGTH_STRONG;
    }
}