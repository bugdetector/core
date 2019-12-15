<?php
/**
 * Implements JWT
 *
 * @author Murat Baki Yücel
 */
class JWT {
    private $alg;
    private $typ;
    private $payload;

    const ENCRYPTION_KEY = "ukkdyDrzCCx2wP9c";
    const IV = ";WAm{]y.chB^VB%~";

    public function __construct(string $alg = "AES128", string $typ = "JWT") {
        $this->alg = $alg;
        $this->typ = $typ;
    }
    
    public static function createFromString(string $string) : self{
        $parts = explode(".", $string);
        if(count($parts) != 3){
            throw new Exception("Invalid JWT.");
        }
        $instance = new self();
        $instance->setJoseHeader($parts[0]);
        $instance->setPayload($parts[1]);
        if($instance->validateSignature($parts[2])){
            throw new Exception("Signatures doesn't match.");
        }
        return $instance;
    }


    /**
     * Returns alg
     * @return string
     */
    public function getAlg() {
        return $this->alg;
    }

    /**
     * Returns typ
     * @return string
     */
    public function getTyp() {
        return $this->typ;
    }

    /**
     * Returns payload
     * @return string
     */
    public function getPayload() {
        return $this->payload;
    }

    /**
     * Sets alg
     * @param string $alg
     */
    public function setAlg(string $alg) {
        $this->alg = $alg;
    }

    /**
     * Sets typ
     * @param string $typ
     */
    public function setTyp(string $typ) {
        $this->typ = $typ;
    }

    /**
     * Sets $payload
     * @param $payload
     */
    public function setPayload($payload) {
    	if(is_array($payload) || is_object($payload)){
    		$this->payload = $payload;
    	}elseif(is_string($payload)){
	        $this->payload = json_decode(base64_decode($payload));
	    }
    }
    
    /**
     * Returns token
     * @return string
     */
    public function createToken(): string {
        $part1 = $this->getEncodedJoseHeader();
        $part2 = $this->getEncodedPayload();
        $part3 = $this->generateSignature($part1, $part2);
        return "$part1.$part2.$part3";
    }


    /**
     * returns generated signature
     */
    private function generateSignature() : string {
        return $this->encrypt($this->getEncodedJoseHeader().".".$this->getEncodedPayload());
    }
    
    public function validateSignature(string $signature):bool {
        return $this->decrypt($signature) == $this->generateSignature();
    }
    
    /**
     * Returns encoded JOSE Header
     * @return string
     */
    private function getEncodedJoseHeader() : string{
        return base64_encode(json_encode(["alg" => $this->alg, "typ" => $this->typ]));
    }
    
    /**
     * Sets decoded JOSE Header
     * @return string
     */
    private function setJoseHeader(string $string){
        $decoded_header = json_decode(base64_decode($string));
        if(!$decoded_header){
        	throw new Exception("Invalid decrypted JOSE Header.");
        }
        $this->setAlg($decoded_header->alg);
        $this->setTyp($decoded_header->typ);
    }
    
    /**
     * Returns encoded Payload
     * @return string
     */
    private function getEncodedPayload() : string{
        return base64_encode(json_encode($this->payload));
    }
    
    /**
     * 
     * @return type
     */
    private function encrypt($pure_string) {
        $encrypted_string = openssl_encrypt($pure_string, $this->alg, self::ENCRYPTION_KEY, OPENSSL_RAW_DATA, self::IV);
        return base64_encode($encrypted_string);
    }
    
    /**
     * 
     * @return type
     */
    private function decrypt($encrypted_string) {
        $encrypted_string = base64_decode($encrypted_string);
        $decrypted_string = openssl_decrypt($encrypted_string, $this->alg, self::ENCRYPTION_KEY, OPENSSL_RAW_DATA, self::IV);
        return $decrypted_string;
    }


}
 
