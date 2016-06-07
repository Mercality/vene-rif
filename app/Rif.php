<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Rif extends Model
{
    public function __construct($rif) {

        if ($this->_verify($rif) === false) throw new \Exception('Error en Validacion');
        $this->_getSeniatData($rif);
        $this->_rif = $rif;
    }

    private $_rif = '';

    public function getRifAttribute() {

        return $this->_rif;
    }

    private function _getSeniatData($rif) {

        $client = new \GuzzleHttp\Client();

        $res = $client->request('GET', 'http://contribuyente.seniat.gob.ve/getContribuyente/getrif?rif='.$rif, [
        	'headers' => ['Accept' => 'application/xml']
    	]);

        $body = $res->getBody()->getContents();

        $body = str_replace("encoding=\"ISO-8859-1\"", "encoding=\"UTF8\"", $body);

        $body = simplexml_load_string($body);

        $body = json_encode($body->children('rif'));
        dd(json_decode($body));

    }
    private function _validate($rif) {

        $validation = preg_match("/^([VEJCPG]{1})([0-9]{8,9}$)/", $rif);

        return $validation ? true : false;
    }

    private function _verify($rif) {

        if (!$this->_validate($rif)) return false;

        $digitos = str_split($rif);
        $digitos[8] *= 2;
        $digitos[7] *= 3;
        $digitos[6] *= 4;
        $digitos[5] *= 5;
        $digitos[4] *= 6;
        $digitos[3] *= 7;
        $digitos[2] *= 2;
        $digitos[1] *= 3;

        switch ($digitos[0]) {
           case 'V':
               $digitoSeniat = 1;
               break;
           case 'E':
               $digitoSeniat = 2;
               break;
           case 'J':
               $digitoSeniat = 3;
               break;
           case 'C':
               $digitoSeniat = 3;
               break;
           case 'P':
               $digitoSeniat = 4;
               break;
           case 'G':
               $digitoSeniat = 5;
               break;
        }
        $digitos[9] = $digitoSeniat * 4;
        $suma = array_sum($digitos);

        $verificacion = 11 - ($suma%11);

        return $verificacion;
    }

    private function convert_cp1252_to_utf8($input, $default = '', $replace = array()) {
        if ($input === null || $input == '') {
            return $default;
        }

        // https://en.wikipedia.org/wiki/UTF-8
        // https://en.wikipedia.org/wiki/ISO/IEC_8859-1
        // https://en.wikipedia.org/wiki/Windows-1252
        // http://www.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/WINDOWS/CP1252.TXT
        $encoding = mb_detect_encoding($input, array('Windows-1252', 'ISO-8859-1'), true);
        if ($encoding == 'ISO-8859-1' || $encoding == 'Windows-1252') {
            /*
             * Use the search/replace arrays if a character needs to be replaced with
             * something other than its Unicode equivalent.
             */

            /*$replace = array(
                128 => "&#x20AC;",      // http://www.fileformat.info/info/unicode/char/20AC/index.htm EURO SIGN
                129 => "",              // UNDEFINED
                130 => "&#x201A;",      // http://www.fileformat.info/info/unicode/char/201A/index.htm SINGLE LOW-9 QUOTATION MARK
                131 => "&#x0192;",      // http://www.fileformat.info/info/unicode/char/0192/index.htm LATIN SMALL LETTER F WITH HOOK
                132 => "&#x201E;",      // http://www.fileformat.info/info/unicode/char/201e/index.htm DOUBLE LOW-9 QUOTATION MARK
                133 => "&#x2026;",      // http://www.fileformat.info/info/unicode/char/2026/index.htm HORIZONTAL ELLIPSIS
                134 => "&#x2020;",      // http://www.fileformat.info/info/unicode/char/2020/index.htm DAGGER
                135 => "&#x2021;",      // http://www.fileformat.info/info/unicode/char/2021/index.htm DOUBLE DAGGER
                136 => "&#x02C6;",      // http://www.fileformat.info/info/unicode/char/02c6/index.htm MODIFIER LETTER CIRCUMFLEX ACCENT
                137 => "&#x2030;",      // http://www.fileformat.info/info/unicode/char/2030/index.htm PER MILLE SIGN
                138 => "&#x0160;",      // http://www.fileformat.info/info/unicode/char/0160/index.htm LATIN CAPITAL LETTER S WITH CARON
                139 => "&#x2039;",      // http://www.fileformat.info/info/unicode/char/2039/index.htm SINGLE LEFT-POINTING ANGLE QUOTATION MARK
                140 => "&#x0152;",      // http://www.fileformat.info/info/unicode/char/0152/index.htm LATIN CAPITAL LIGATURE OE
                141 => "",              // UNDEFINED
                142 => "&#x017D;",      // http://www.fileformat.info/info/unicode/char/017d/index.htm LATIN CAPITAL LETTER Z WITH CARON
                143 => "",              // UNDEFINED
                144 => "",              // UNDEFINED
                145 => "&#x2018;",      // http://www.fileformat.info/info/unicode/char/2018/index.htm LEFT SINGLE QUOTATION MARK
                146 => "&#x2019;",      // http://www.fileformat.info/info/unicode/char/2019/index.htm RIGHT SINGLE QUOTATION MARK
                147 => "&#x201C;",      // http://www.fileformat.info/info/unicode/char/201c/index.htm LEFT DOUBLE QUOTATION MARK
                148 => "&#x201D;",      // http://www.fileformat.info/info/unicode/char/201d/index.htm RIGHT DOUBLE QUOTATION MARK
                149 => "&#x2022;",      // http://www.fileformat.info/info/unicode/char/2022/index.htm BULLET
                150 => "&#x2013;",      // http://www.fileformat.info/info/unicode/char/2013/index.htm EN DASH
                151 => "&#x2014;",      // http://www.fileformat.info/info/unicode/char/2014/index.htm EM DASH
                152 => "&#x02DC;",      // http://www.fileformat.info/info/unicode/char/02DC/index.htm SMALL TILDE
                153 => "&#x2122;",      // http://www.fileformat.info/info/unicode/char/2122/index.htm TRADE MARK SIGN
                154 => "&#x0161;",      // http://www.fileformat.info/info/unicode/char/0161/index.htm LATIN SMALL LETTER S WITH CARON
                155 => "&#x203A;",      // http://www.fileformat.info/info/unicode/char/203A/index.htm SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
                156 => "&#x0153;",      // http://www.fileformat.info/info/unicode/char/0153/index.htm LATIN SMALL LIGATURE OE
                157 => "",              // UNDEFINED
                158 => "&#x017e;",      // http://www.fileformat.info/info/unicode/char/017E/index.htm LATIN SMALL LETTER Z WITH CARON
                159 => "&#x0178;",      // http://www.fileformat.info/info/unicode/char/0178/index.htm LATIN CAPITAL LETTER Y WITH DIAERESIS
            );*/

            if (count($replace) != 0) {
                $find = array();
                foreach (array_keys($replace) as $key) {
                    $find[] = chr($key);
                }
                $input = str_replace($find, array_values($replace), $input);
            }
            /*
             * Because ISO-8859-1 and CP1252 are identical except for 0x80 through 0x9F
             * and control characters, always convert from Windows-1252 to UTF-8.
             */
            $input = iconv('Windows-1252', 'UTF-8//IGNORE', $input);
            if (count($replace) != 0) {
                $input = html_entity_decode($input);
            }
        }
        return $input;
    }

}
