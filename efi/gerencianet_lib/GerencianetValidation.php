<?php
/**
 * Gerencianet Validation Class.
 */


class GerencianetValidation {

	/**
	 * Validates name field
	 * @param string $data
	 * @return boolean
	 */
	public function _name($data) {
		$validation = preg_match("/^[ ]*(?:[^\\s]+[ ]+)+[^\\s]+[ ]*$/",$data);
		if (!$validation || strlen($data) < 2) {
			return false;
		}
		return true;
	}
	
	/**
	 * Validates corporate field
	 * @param string $data
	 * @return boolean
	 */
	public function _corporate($data) {
		$validation = preg_match("/^[ ]*(?:[^\\s]+[ ]+)+[^\\s]+[ ]*$/",$data);
		if (!$validation || strlen($data) < 2) {
			return false;
		}
		return true;
	}
	

	/**
	 * Validates email field
	 * @param string $data
	 * @return boolean
	 */
	public function _email($data) {
		$validation = preg_match("/^[A-Za-z0-9_\\-]+(?:[.][A-Za-z0-9_\\-]+)*@[A-Za-z0-9_]+(?:[-.][A-Za-z0-9_]+)*\\.[A-Za-z0-9_]+$/",$data);
		if (!$validation) {
			return false;
		}
		return true;
	}
	
	/**
	 * Validates birthdate fields
	 * @param string $data
	 * @return boolean
	 */
	public function _birthdate($data) {
		$birth = explode("-",$data);
		$birth = $birth[0]."-".$birth[1]."-".$birth[2];
		$validation = preg_match("/^[12][0-9]{3}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12][0-9]|3[01])$/",$birth);
		if (!$validation) {
			return false;
		}
		return true;
	}
	
	/**

	 * Validates birthdate fields
	 * @param string $data
	 * @return boolean
	 */
	public function _phone_number($data) {
		$phone = preg_replace('/[^0-9]/', '',$data);
		$validation = preg_match("/^[1-9]{2}9?[0-9]{8}$/", $phone);
		if (!$validation) {
			return false;
		}
		return true;
	}
	
	/**
	 * Validates CPF data
	 * @param string $data
	 * @return boolean
	 */
	public function _cpf($data) {
		if(empty($data)) {
			return false;
		}

		$cpf = preg_replace('/[^0-9]/', '', $data);
		$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
		 
		if (strlen($cpf) != 11) {
			return false;
		} elseif ($cpf == '00000000000' ||
				$cpf == '11111111111' ||
				$cpf == '22222222222' ||
				$cpf == '33333333333' ||
				$cpf == '44444444444' ||
				$cpf == '55555555555' ||
				$cpf == '66666666666' ||
				$cpf == '77777777777' ||
				$cpf == '88888888888' ||
				$cpf == '99999999999') {
			return false;
		}else {
			for ($t = 9; $t < 11; $t++) {
				 
				for ($d = 0, $c = 0; $c < $t; $c++) {
					$d += $cpf[$c] * (($t + 1) - $c);
				}
				$d = ((10 * $d) % 11) % 10;
				if ($cpf[$c] != $d) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Validates CNPJ data
	 * @param string $data
	 * @return boolean
	 */
	public function _cnpj($cnpj) {
		if(empty($cnpj)) {
			return false;
		}
		
		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
		
		if (strlen($cnpj) != 14)
			return false;
		
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
		{
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
			return false;
		
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
		{
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}
		$resto = $soma % 11;
		return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
	}
	
	/**
	 * Validates zipcode fields
	 * @param string $data
	 * @return boolean
	 */
	public function _zipcode($data) {
	    $zipcode = preg_replace('/[^0-9]/', '',$data);
		if (strlen($zipcode) < 8) {
	        return false;
	    }
	    return true;
	}

	/**
	 * Validates street field
	 * @param string $data
	 * @return boolean
	 */
	public function _street($data) {
		if (strlen($data) < 2 || strlen($data) > 200) {
			return false;
		}
		return true;
	}

	/**
	 * Validates number field
	 * @param string $data
	 * @return boolean
	 */
	public function _number($data) {
		if (strlen($data) < 2 || strlen($data) > 55) {
			return false;
		}
		return true;
	}

	/**
	 * Validates neighborhood field
	 * @param string $data
	 * @return boolean
	 */
	public function _neighborhood($data) {
		if (strlen($data) < 1 || strlen($data) > 255) {
			return false;
		}
		return true;
	}

	/**
	 * Validates city field
	 * @param string $data
	 * @return boolean
	 */
	public function _city($data) {
		if (strlen($data) < 2 || strlen($data) > 255) {
			return false;
		}
		return true;
	}

	/**
	 * Validates state field
	 * @param string $data
	 * @return boolean
	 */
	public function _state($data) {
		$validation = preg_match("/^(?:A[CLPM]|BA|CE|DF|ES|GO|M[ATSG]|P[RBAEI]|R[JNSOR]|S[CEP]|TO)$/",$data);
		if (!$validation) {
			return false;
		}
		return true;
	}

	/**
	 * Validates card number field
	 * @param string $data
	 * @return boolean
	 */
	public function _cardNumber($data) {
		$validation = false;
		$brands = array(
			'mastercard' => '/^(5[1-5]\d{4}|677189)\d{10}$/',
			'elo'        => '/^4011(78|79)|^43(1274|8935)|^45(1416|7393|763(1|2))|^50(4175|6699|67[0-6][0-9]|677[0-8]|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])|^627780|^63(6297|6368|6369)|^65(0(0(3([1-3]|[5-9])|4([0-9])|5[0-1])|4(0[5-9]|[1-3][0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8]|4[1-9]|[5-8][0-9]|9[0-8])|7(0[0-9]|1[0-8]|2[0-7])|9(0[1-9]|[1-6][0-9]|7[0-8]))|16(5[2-9]|[6-7][0-9])|50(0[0-9]|1[0-9]|2[1-9]|[3-4][0-9]|5[0-8]))$/',
			'visa'       => '/^4\d{12}(\d{3})?$/',
			'diners'     => '/^3(0[0-5]|[68]\d)\d{11}$/',
			'amex'       => '/^3[47]\d{13}$/',
			'hipercard'  => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
		);
		if (preg_match($brands['mastercard'],$data)) {
			$validation = true;
		}elseif(preg_match($brands['elo'],$data)){
			$validation = true;
		}elseif(preg_match($brands['visa'],$data)){
			$validation = true;
		}elseif (preg_match($brands['diners'],$data)) {
			$validation = true;
		}elseif (preg_match($brands['amex'],$data)) {
			$validation = true;
		}elseif (preg_match($brands['hipercard'],$data)) {
			$validation = true;
		}
		return $validation;
	}
	
	
}