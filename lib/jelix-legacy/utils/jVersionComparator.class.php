<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Laurent Jouanneau
* @copyright   2008-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * class to compare version numbers. it supports the following keywords:
 * "pre", "-dev", "b", "beta", "a", "alpha".
 * It supports also the "*" wilcard. This wilcard must be the last part
 * of the version number
 * @since 1.2
 */
class jVersionComparator {

  /**
   * Compare two version numbers.
   *
   * @param string $version1
   * @param string $version2
   * @return int  0 if equal, -1 if $version1 < $version2, 1 if $version1 > $version2
   */
    static function compareVersion($version1, $version2) {

        if ($version1 == $version2)
            return 0;

        $v1 = explode('.', $version1);
        $v2 = explode('.', $version2);

        if (count($v1) > count($v2) ) {
            $v2 = array_pad($v2, count($v1), ($v2[count($v2)-1] == '*'?'*':'0'));
        }
        elseif (count($v1) < count($v2) ) {
            $v1 = array_pad($v1, count($v2), ($v1[count($v1)-1] == '*'?'*':'0'));
        }

        $r = '/^([0-9]+)([a-zA-Z]*|pre|-?dev)([0-9]*)(pre|-?dev)?$/';

        foreach ($v1 as $k=>$v) {

            if ($v == $v2[$k] || $v == '*' || $v2[$k] == '*')
                continue;

            $pm = preg_match($r, $v, $m1);
            $pm2 = preg_match($r, $v2[$k], $m2);

            if ($pm && $pm2) {
                if ($m1[1] != $m2[1]) {
                    return ($m1[1] < $m2[1] ? -1: 1);
                }

                self::normalizeVersionNumber($m1);
                self::normalizeVersionNumber($m2);

                if ($m1[2] != $m2[2]) {
                    return ($m1[2] < $m2[2] ? -1: 1);
                }
                if ($m1[3] != $m2[3]) {
                    return ($m1[3] < $m2[3] ? -1: 1);
                }

                $v1pre = ($m1[4] == 'dev');
                $v2pre = ($m2[4] == 'dev');

                if ($v1pre && !$v2pre) {
                    return -1;
                }
                elseif ($v2pre && !$v1pre) {
                    return 1;
                }
                else if (!isset($v1[$k+1]) && !isset($v2[$k+1])) {
                    return 0;
                }
            }
            elseif ($pm){
                throw new Exception ("bad version number :". $version2);
            }
            else
                throw new Exception ("bad version number :".$version1);
        }

        return 0;
    }

    static protected function normalizeVersionNumber(&$n) {
        $n[2] = strtolower($n[2]);
        if ($n[2] == 'pre' || $n[2] == 'dev' || $n[2] == '-dev') {
            $n[2] = '_';
            $n[3] = '';
            $n[4] = 'dev';
        }
        if (!isset($n[4]))
            $n[4] = '';
        else {
            $n[4] = strtolower($n[4]);
            if ($n[4] == 'pre' || $n[4] == '-dev' ) $n[4] = 'dev';
        }

        if ($n[2] == 'a') $n[2] = 'alpha';
        elseif($n[2] == 'b') $n[2] = 'beta';
        elseif($n[2] == '') $n[2] = 'zzz';
    }

    static public function getBranchVersion($version) {
      $v = explode('.', $version);
      $r = '/^([0-9]+)([a-zA-Z]*|pre|-?dev)([0-9]*)(pre|-?dev)?$/';
      if (count($v) < 2)
        $v[1] = '0';

      if (!preg_match($r, $v[0], $m)) {
        return $version;
      }

      $version = $m[1];

      if (!preg_match($r, $v[1], $m)) {
        return $version.'.0';
      }

      return $version.'.'.$m[1];
    }

    /**
     * create a string representing a version number in a manner that it could
     * be easily to be compared with an other serialized version. useful to
     * do comparison in a database for example.
     * @param int $starReplacement 1 if it should replace by max value, 0 for min value
     */
    static public function serializeVersion($version, $starReplacement = 0, $pad=4) {
        $vers = explode('.', $version);
        $r = '/^([0-9]+)([a-zA-Z]*|pre|-?dev)([0-9]*)(pre|-?dev)?$/';

        $sver = '';

        foreach ($vers as $k=>$v) {
            if ($v == '*') {
                $k--;
                break;
            }

            $pm = preg_match($r, $v, $m);
            if ($pm) {
                self::normalizeVersionNumber($m);

                $m[1] = str_pad($m[1], ($k > 1 ? 10:3), '0', STR_PAD_LEFT);
                $m[2] = substr($m[2],0,1); // alpha/beta
                $m[3] = ($m[3] == '' ? '99': str_pad($m[3], 2, '0', STR_PAD_LEFT)); // alpha/beta number
                $m[4] = ($m[4] == 'dev'? 'd':'z');
                if ($k)
                    $sver.='.';
                $sver.= $m[1].$m[2].$m[3].$m[4];
            }
            else
                throw new Exception ("bad version number");
        }
        for($i=$k+1; $i<$pad; $i++) {
            if ($i >0)
                $sver.='.';
            if ($starReplacement > 0)
                $sver.= ($i > 1 ? '9999999999':'999').'z99z';
            else
                $sver.= ($i > 1 ? '0000000000':'000').'a00a';
        }

        return $sver;
    }

    /**
     * @param string $version  a version number
     * @param string $range  a version expression respecting Composer range syntax
     * @return boolean  true if the given version match the given range
     */
    static public function compareVersionRange($version, $range) {

        if ($version == $range)
            return true;

        $expression = self::compileRange($range);
        return $expression->compare($version);
    }

    static protected function compileRange($range) {
        $or = preg_split('/\|/',$range, 2);
        if (count($or) > 1) {
            $left = self::compileRange($or[0]);
            $right = self::compileRange($or[1]);
            return new versionRangeBinaryOperator(versionRangeBinaryOperator::OP_OR, $left, $right);
        }
        $and = preg_split("/,/",$range, 2);
        if (count($and) > 1) {
            $left = self::compileRange($and[0]);
            $right = self::compileRange($and[1]);
            return new versionRangeBinaryOperator(versionRangeBinaryOperator::OP_AND, $left, $right);
        }
        $val = trim($range);
        if (preg_match("/^([\!>=<~]+)(.*)$/", $val, $m)) {
            switch($m[1]) {
                case '=':
                    $op = versionRangeUnaryOperator::OP_EQ;
                    break;
                case '<':
                    $op = versionRangeUnaryOperator::OP_LT;
                    break;
                case '>':
                    $op = versionRangeUnaryOperator::OP_GT;
                    break;
                case '<=':
                    $op = versionRangeUnaryOperator::OP_LTE;
                    break;
                case '>=':
                    $op = versionRangeUnaryOperator::OP_GTE;
                    break;
                case '!=':
                    $op = versionRangeUnaryOperator::OP_DIFF;
                    break;
                case '~':
                    $exp1 = new versionRangeUnaryOperator(versionRangeUnaryOperator::OP_GTE, $m[2]);
                    $v2 = explode('.', $m[2]);
                    $v2 = (intval($v2[0])+1).".0pre";
                    $exp2 = new versionRangeUnaryOperator(versionRangeUnaryOperator::OP_LT, $v2);
                    return new versionRangeBinaryOperator(versionRangeBinaryOperator::OP_AND, $exp1, $exp2);
                default:
                    throw new Exception("Version comparator: bad operator in the range ".$range);
            }
            return new versionRangeUnaryOperator($op, $m[2]);
        }
        return new versionRangeUnaryOperator(versionRangeUnaryOperator::OP_EQ, $range);
    }
}

/**
 * Represents a binary operator (AND or OR) in a version range expression
 */
class versionRangeBinaryOperator {
    const OP_OR = 0;
    const OP_AND = 1;

    protected $op = -1;

    protected $left = null;

    protected $right = null;

    function __construct($operator, $left, $right) {
        $this->op = $operator;
        $this->left = $left;
        $this->right = $right;
    }

    function compare($value) {
        if ($this->op == self::OP_OR) {
            if ($this->left->compare($value)) {
                return true;
            }
            if ($this->right->compare($value)) {
                return true;
            }
            return false;
        }
        if (!$this->left->compare($value)) {
            return false;
        }
        if (!$this->right->compare($value)) {
            return false;
        }
        return true;
    }
}

/**
 * Represents an unary operator (>,<,=,!=,<=,>=,~) in a version range expression
 */
class versionRangeUnaryOperator {
    const OP_EQ = 0;
    const OP_LT = 1;
    const OP_GT = 2;
    const OP_GTE = 3;
    const OP_LTE = 4;
    const OP_DIFF = 5;

    protected $op = -1;

    protected $operand = null;

    function __construct($operator, $operand) {
        $this->op = $operator;
        $this->operand = $operand;
    }

    function compare($value) {
        $result = jVersionComparator::compareVersion($value, $this->operand);
        switch ($this->op) {
            case self::OP_EQ:
                return ($result === 0);
            case self::OP_LT:
                return ($result === -1);
            case self::OP_GT:
                return ($result === 1);
            case self::OP_LTE:
                return ($result < 1);
                break;
            case self::OP_GTE:
                return ($result > -1);
                break;
            case self::OP_DIFF:
                return ($result != 0);
        }
        return false;
    }
}