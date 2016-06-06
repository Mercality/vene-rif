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
        	'headers' => ['Accept' => 'application/json']
    	]);

        $body = json_encode($res->getBody());

        dd($body);

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
}
