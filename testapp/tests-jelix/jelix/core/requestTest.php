<?php
/**
 * @package     testapp
 * @subpackage  testsjelix
 * @author      Laurent Jouanneau
 * @copyright   2017 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class requestTest extends \Jelix\UnitTests\UnitTestCase
{


    function testMultipart() {
        $contentType = "multipart/form-data; boundary=---------------------------13779940781645714101731175461";
        $body = "-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"hiddenvalue\"

my hidden value
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"__JFORMS_TOKEN__\"

7b30c282fdfedf79e8d86fd23a756770
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"nom\"

aaaa
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"prenom\"

robert
";
        $body = str_replace("\n", "\r\n", $body);
        $values = jClassicRequest::parseMultipartBody($contentType, $body);
        $this->assertEquals(array(
            "hiddenvalue" => "my hidden value",
            "__JFORMS_TOKEN__" => "7b30c282fdfedf79e8d86fd23a756770",
            "nom" => "aaaa",
            "prenom" => "robert"
        ), $values);
    }

    function testMultipartArrays() {
        $contentType = "multipart/form-data; boundary=---------------------------13779940781645714101731175461";
        $body = "-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"nom\"

aaaa
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"description\"


-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"objets[]\"

voiture
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"datenaissance[month]\"

12
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"datenaissance[day]\"

31
-----------------------------13779940781645714101731175461
Content-Disposition: form-data; name=\"datenaissance[year]\"

2016
";
        $body = str_replace("\n", "\r\n", $body);
        $values = jClassicRequest::parseMultipartBody($contentType, $body);
        $this->assertEquals(array(
            "nom" => "aaaa",
            "description" => "",
            "objets" => array("voiture"),
            "datenaissance" => array("month"=>"12", "day"=>"31", "year"=>"2016")
        ), $values);
    }
}
