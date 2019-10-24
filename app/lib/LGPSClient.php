<?php
namespace App\lib;

use App\PaymentLogger;

use Exception;
class LGPSException extends Exception
{
// Custom Exception to handles exceptions occured while using this client
}

class LGPSClient
{
    /* Payment request variables */

    private $serviceCode; //Service Code of the Service
    private $transactionRefNo; //Transaction Reference No for this Transaction
    private $transactionAmount; //Transaction Amount for this Transaction
    private $returnURL; //Return URL which the response should be received

    /* Key variables */
    private $clientPublicKey; //Absolute path to Client Public Key
    private $clientPrivateKey; //Absolute path to Client Private Key Path
    private $clientPrivateKeyPasswd; //Passphase to access to Client Private Key
    private $lgpsPublicKey; //Absolute path to LGPS Public Key

    /* Payment Response variables */
    private $receivedTransactionRefNo; //Received transaction reference no
    private $receivedTransactionStatus; //Received transaction status
    private $receivedPaymentGatewayName; //Received payment gateway name
    private $convenienceFee;  //Received payment gateway convenienceFee

    //Set Service Code

    public function setServiceCode($value)
    {
        $this->serviceCode = $value;
    }

    //Set TransactionRef No
    public function setTransactionRefNo($value)
    {
        $this->transactionRefNo = $value;
    }

    //Set Transaction amount
    public function setTransactionAmount($value)
    {
        $this->transactionAmount = $value;
    }

    //Set return URL
    public function setReturnURL($value)
    {
        $this->returnURL = $value;
    }

    //Return Client Pubic Key
    private function getClientPublicKey()
    {
        $fp = @fopen($this->clientPublicKey, "r");
        if ($fp) {
            $pub_key = fread($fp, 8192);
            fclose($fp);
            $key_resource = openssl_get_publickey($pub_key);
            Logger::getInstance()->info("Getting Client Public Key ...");
            return $key_resource;
        } else {
            throw new LGPSException("Error in Accessing Client Public Key :Invalid Key");
        }
    }

    //Return LGPS Pubic Key
    private function getLGPSPublicKey()
    {
        $fp = @fopen($this->lgpsPublicKey, "r");
        if ($fp) {
            $pub_key = fread($fp, 8192);
            fclose($fp);
            $key_resource = openssl_get_publickey($pub_key);
            Logger::getInstance()->info("Getting LGPS Public Key ...");
            return $key_resource;
        } else {
            throw new LGPSException("Error in Accessing LGPS Public Key:Invalid Key");
        }
    }

    //Return Client Private Key
    private function getClientPrivateKey()
    {

        $fp = @fopen($this->clientPrivateKey, "r");
        if (!$fp) {
            throw new LGPSException("Error in Accessing Client Private Key:Invalid Key");
        } else {

            $priv_key = fread($fp, 8192);
            fclose($fp);
            // $passphrase is required if your key is encoded (suggested)
            $key_resource = openssl_get_privatekey($priv_key, $this->clientPrivateKeyPasswd);
            Logger::getInstance()->info("Getting Client Private Key...");
            return $key_resource;
        }
    }

    //Set Client Public Key
    public function setClientPublicKey($value)
    {
        Logger::getInstance()->info("Setting Client Public Key Path:" . $value);
        if ($value == "" || $value == null) {
            throw new LGPSException("Error in Setting Client Public Key Path :Key is empty or null");
        } else {
            $this->clientPublicKey = $value;
        }
    }

    //Set Client Private Key
    public function setClientPrivateKey($value, $passwd)
    {

        Logger::getInstance()->info("Setting Client Private Key Path:" . $value);

        if ($value == "" || $value == null) {
            throw new LGPSException("Error in Setting Client Private Key Path :Key is empty or null");
        } else {
            $this->clientPrivateKey = $value;
        }

        if ($passwd == "" || $passwd == null) {
            throw new LGPSException("Error in Accessing Client Private Key :Password is empty or null");
        } else {
            $this->clientPrivateKeyPasswd = $passwd;
        }
    }

    //Set LGPS Public Key
    public function setLgpsPublicKey($value)
    {

        Logger::getInstance()->info("Setting LGPS Public Key Path:" . $value);

        if ($value == "" || $value == null) {
            throw new LGPSException("Error in Setting LGPS Public Key Path :Key is empty or null");
        } else {
            $this->lgpsPublicKey = $value;
        }
    }

    //Return received transaction reference no in Payment response
    public function getReceivedTransactionRefNo()
    {
        return $this->receivedTransactionRefNo;
    }

    //Return received transaction Status in Payment response
    public function getReceivedTransactionStatus()
    {
        return $this->receivedTransactionStatus;
    }

    //Return received Payment Gateway name in Payment response
    public function getReceivedPaymentGateway()
    {
        return $this->receivedPaymentGatewayName;
    }

    //Return received Payment Gateway name in Payment response
    public function getConvenienceFee()
    {
        return $this->convenienceFee;
    }

    //====================================Get Encrypted Payment Request ===============================================//
    public function getPaymentRequest()
    {

        $payRequestXml = $this->getXmlRequest();
        $enPaymentRequest = $this->encrypt($payRequestXml);
        Logger::getInstance()->info("Encrypted Payment Request: " . $enPaymentRequest);
        return $enPaymentRequest;
    }

    //=========================================Generate XML Fomatted string with payment request parameters===============//
    private function getXmlRequest()
    {
        Logger::getInstance()->info("Generating Encrypted Payment Request");
        $pay_request = "";

        Logger::getInstance()->info("------------------------Payment Request Details--------------------------");
        Logger::getInstance()->info("Service Code:" . $this->serviceCode);
        Logger::getInstance()->info("Transaction Ref No:" . $this->transactionRefNo);
        Logger::getInstance()->info("Transaction Amount:" . $this->transactionAmount);
        Logger::getInstance()->info("Return URL:" . $this->returnURL);
        Logger::getInstance()->info("-------------------------------------------------------------------------");

        if ($this->serviceCode != ""
            && $this->transactionAmount != ""
            && $this->transactionRefNo != ""
            && $this->returnURL != "") {
            $middle = "<S_CODE>" . $this->serviceCode . "</S_CODE>" .
            "<TX_AMOUNT>" . $this->transactionAmount . "</TX_AMOUNT>" .
            "<TX_REF_NO>" . $this->transactionRefNo . "</TX_REF_NO>" .
            "<RTN_URL>" . $this->returnURL . "</RTN_URL>";
            $pay_request = "<PAY_REQUEST>" . $middle . "</PAY_REQUEST>";
        } else {
            throw new LGPSException("Cannot Generate Encrypted receipt : Invalid values in the Payment Request!!!");
        }

        return $pay_request;
    }

    //===========================================Encrypt the Payment request========================================//
    private function encrypt($payRequestXml = null)
    {
        //get signature
        Logger::getInstance()->info("Start - Encrypting Payment Request ...");
        $signature = $this->getSignature($payRequestXml);
        Logger::getInstance()->info("Signature created...");
        //Generate random 3DES Key
        $triple_des_key = $this->getTripleDESSecretKey();
        Logger::getInstance()->info("3DES Key generated...");
        //Encrypt PlainText with signature using 3DES Key
        $base_cipher_text = $this->getTripleDESEncrptedMessage($triple_des_key, $payRequestXml, $signature);
        Logger::getInstance()->info("3DES encryption completed...");
        //Encrypt 3DES Key
        $base_cipher_key = $this->getEnvelop($triple_des_key);
        Logger::getInstance()->info("Key encryption completed...");
        //Concat encrypted plaintext with signature and encypted 3DES Key
        $cipher_msg = $this->getEncryptedMessage($base_cipher_text, $base_cipher_key);
        //return final message
        Logger::getInstance()->info("End - Encryption completed successfully!!!");

        return $cipher_msg;
    }

    //===========================================Get Client Private Key ====================================//
    private function getSignature($plain_text)
    {
        // compute signature
        $signature = "";
        $client_private_key = $this->getClientPrivateKey();

        if (!$client_private_key) {

            throw new LGPSException("Error in getting Client Private Key:Invalid Key path or Invalid Password!!!");
        } else {
            //sign the plain text
            openssl_sign($plain_text, $signature, $client_private_key, OPENSSL_ALGO_SHA1);
            // free the client private key from memory
            openssl_free_key($client_private_key);
            $public_key = $this->getClientPublicKey();
            //state wheather signatue is ok
            $ok = openssl_verify($plain_text, $signature, $public_key, OPENSSL_ALGO_SHA1);
            // free the client public key from memory
            openssl_free_key($public_key);
            return $signature;
        }
    }

    //=====================================================Generate Random string===========================================//
    private function str_rand($length = 8, $seeds = 'alphanum')
    {
        // Possible seeds
        $seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
        $seedings['numeric'] = '0123456789';
        $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
        $seedings['hexidec'] = '0123456789abcdef';

        // Choose seed
        if (isset($seedings[$seeds])) {
            $seeds = $seedings[$seeds];
        }

        // Seed generator
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float) $sec + ((float) $usec * 100000);
        mt_srand($seed);

        // Generate
        $str = '';
        $seeds_count = strlen($seeds);

        for ($i = 0; $length > $i; $i++) {
            $str .= $seeds{mt_rand(0, $seeds_count - 1)};
        }
        return $str;
    }

    //===========================================Get Triple DES Key ====================================//
    private function getTripleDESSecretKey()
    {
        //Generate random number with 24 byte length
        $random_val = $this->str_rand(24, 'This@@is^ewrewrwerwerwerwerwe^the!!ICTA-LGPS##randaom$$seed.%%8^^used&&to**generate--3DES++secret==key)');
        return $random_val;
    }

    //===========================================Get Triple DES Ecrypted Message ====================================//
    private function getTripleDESEncrptedMessage($triple_des_key, $plain_text, $signature)
    {

        //Use 3DES Cipher
        $td = MCRYPT_3DES;
        //Get base 64 encoded signature
        $base_plain_text = base64_encode($plain_text);
        //Concat base 64 plaintext and base 64 plain text
        $base_signature = base64_encode($signature);
        //Get base 64 encoded plaintext
        $msg = $base_plain_text . $base_signature;
        //Generate Cipher Text
        $cipher_text = @mcrypt_encrypt($td, $triple_des_key, $msg, MCRYPT_MODE_ECB);

        //Encode cipher Text
        $base_cipher_text = base64_encode($cipher_text);
        //Return encoded cipher text
        return $base_cipher_text;

    }

    //==========================================Get Encrypted 3DES Key as a Envelop ======================================//
    private function getEnvelop($triple_des_key)
    {

        $lgps_pub_key = $this->getLGPSPublicKey();
        $cipher_key = null;
        openssl_public_encrypt($triple_des_key, $cipher_key, $lgps_pub_key);
        openssl_free_key($lgps_pub_key);
        return base64_encode($cipher_key);
    }

    //==========================================Get Complete Encrypted Message ======================================//
    private function getEncryptedMessage($base_cipher_text, $base_cipher_key)
    {
        //create encrypted message by concatenating cipher text and cipher 3DES Key
        $request_flag = "1";
        $encryptedMessage = $request_flag . $base_cipher_text . $base_cipher_key;
        //Return encrypted message
        return $encryptedMessage;
    }

    //==========================================Set Payment Response ================================================//
    public function setPaymentResponse($paymentResponse)
    {

        Logger::getInstance()->info("Setting Payment Response...");
        //Get Plain Text
        $plainText = $this->decrypt($paymentResponse);
        //Set Plain Text to extract values from plain text
        $this->setXMLResponse($plainText);
    }

    //=========================================== Decrypt Message Received =========================================//
    private function decrypt($cipherMessage)
    {

        if ($cipherMessage == "" ||
            $cipherMessage == null) {

            throw new LGPSException("Invalid Payment Response : Empty or NULL Response Message");
        } else {

            Logger::getInstance()->info("Received Payment Response : " . $cipherMessage);

            Logger::getInstance()->info("Start - Decrypting payment request...");
            //decoded the received payment response message
            $decoded_message = base64_decode($cipherMessage);

            if ($decoded_message) {
                //Extract the encrypted 3DES Key part
                $cipher_triple_des_key = $this->getExtractedEnvelop($decoded_message);
                Logger::getInstance()->info("Extracting Key for decryption...");
                //Extract the encrypted Plain text with signature
                $extractedPlainTextWithSignaure = $this->getExtractedCipherPlainTextWithSignature($decoded_message);
                Logger::getInstance()->info("Extracting message for decryption...");
                //Decrypt the 3DES Key
                $triple_des_key = $this->getDecryptedEnvelop($cipher_triple_des_key);
                Logger::getInstance()->info("Decrypting Key...");
                //Decrypt the Plain text with Signature
                $plainTextWithSignature = $this->getDecryptedPainTextWithSignature($triple_des_key, $extractedPlainTextWithSignaure);
                Logger::getInstance()->info("Decrypting Message...");
                //Extrcat signature part
                $signature = $this->getExtractedSignature($plainTextWithSignature);
                Logger::getInstance()->info("Extracting Signature to verify...");
                //Extract Plain Text part
                $plainText = $this->getExtractedPlianText($plainTextWithSignature);
                Logger::getInstance()->info("Extracting Plain text...");
                //Verify Signature
                $verifySignature = $this->verifySignature($plainText, $signature);
                if ($verifySignature == false) {
                    //throw exception of the the message is tampered
                    throw new LGPSException("Signature Verification Failed : Tampered Data");
                } else {
                    //Return Plain Text
                    Logger::getInstance()->info("End - Signature Verified Successfully!!!");
                    return $plainText;
                }
            } else {
                throw new LGPSException("Error in Decrypting Payment Response :Trying to decode Invalid message");
            }
        }
    }

    //===================================================Extract 3DES Key from Envelop==================================//
    private function getExtractedEnvelop($decoded_message)
    {
        //received response message length
        $decoded_message_len = strlen($decoded_message);
        //extract encrypted 3DES key from received payment response
        $encoded_3des_key = substr($decoded_message, $decoded_message_len - 256, 256);

        return $encoded_3des_key;
    }

    //==================================================Extract PlainTextWithSignature=================================//
    private function getExtractedCipherPlainTextWithSignature($decoded_message)
    {
        //received response message length
        $decoded_message_len = strlen($decoded_message);
        //extract encrypted Plaintext with signature from received payment response
        $encrypted_plaintext_with_signature = substr($decoded_message, 0, $decoded_message_len - 256);
        return $encrypted_plaintext_with_signature;
    }

    //====================================================Encrypt 3DES Key==============================================//
    private function getDecryptedEnvelop($cipher_triple_des_key)
    {
        //Load Client private key
        $client_private_key = $this->getClientPrivateKey();
        $triple_des_key = null;
        //Decrypt 3DES key using client private key
        $isDecrypted = openssl_private_decrypt($cipher_triple_des_key, $triple_des_key, $client_private_key);
        //free the client private key from memory
        openssl_free_key($client_private_key);
        if ($isDecrypted) {
            //return the decrypted 3DES key
            return $triple_des_key;
        } else {
            throw new LGPSException("Error in Decrypting Payment Response :Cannot Decrypt Symmetric Key");
        }
    }

    //====================================================Decrypt payment response received from LGPS===============//
    private function getDecryptedPainTextWithSignature($triple_des_key, $extractedPlainTextWithSignaure)
    {
        //Load 3DES cipher
        $td = MCRYPT_3DES;
        //decrypt data
        $data = @mcrypt_decrypt($td, $triple_des_key, $extractedPlainTextWithSignaure, MCRYPT_MODE_ECB);
        //get padding clock size
        $block = openssl_cipher_iv_length('des-ofb'); // mcrypt_get_block_size('des', 'ecb');
        //extract padding part
        $pad = ord($data[($len = strlen($data)) - 1]);
        //extract plain data part + signature
        $data = substr($data, 0, strlen($data) - $pad);
        //return data part + signature
        return $data;
    }

    //====================================================Extract Signature from PlaintextWithSignature Message=========//
    private function getExtractedSignature($plainTextWithSignature)
    {
        //Length of the Plain text with signature
        $plainTextWithSignature_len = strlen($plainTextWithSignature);
        //Extract the signature out of plaintex twith signature message
        $signature = substr($plainTextWithSignature, $plainTextWithSignature_len - 256, 256);
        return $signature;
    }

    //===================================================Extract Plain Text from laintextWithSignature Message=========//
    private function getExtractedPlianText($plainTextWithSignature)
    {
        //Length of the Plain text with signature
        $plainTextWithSignature_len = strlen($plainTextWithSignature);
        //extract plain text
        $plainText = substr($plainTextWithSignature, 0, $plainTextWithSignature_len - 256);
        return $plainText;
    }

    //===================================================Verify signature of the received data packet===================//
    private function verifySignature($data, $signature)
    {
        //Load lgps public key
        $lgps_pub_key = $this->getLGPSPublicKey();

        $ok = openssl_verify($data, $signature, $lgps_pub_key);
        //free the key from memory
        openssl_free_key($lgps_pub_key);
        // state whether signature is okay or not
        if ($ok == 1) {
            Logger::getInstance()->info("Sigature Verification Success...");
            return true;
        } elseif ($ok != 1) {
            Logger::getInstance()->info("Sigature Verification Failed...");
            return false;
        }
    }

    //==================================================Extract Response Values from Plain Text========================//
    private function setXMLResponse($sXml)
    {

        Logger::getInstance()->info("Received Plain Text Response Message: " . $sXml);
        //Extract received transaction Ref No
        $this->receivedTransactionRefNo = $this->getTagValue($sXml, "RECEIVED_TX_ID");
        //Extract received transactionstatus
        $this->receivedTransactionStatus = $this->getTagValue($sXml, "RECEIVED_TX_STATUS");
        //Extract received payment gateway name
        $this->receivedPaymentGatewayName = $this->getTagValue($sXml, "RECEIVED_PG_NAME");
        //Extract received payment gateway convenienceFee
        $this->convenienceFee = $this->getTagValue($sXml, "PG_C_FEE");

        Logger::getInstance()->info("------------------------Payment Response Details-------------------");
        Logger::getInstance()->info("Received Transaction Ref No:" . $this->receivedTransactionRefNo);
        Logger::getInstance()->info("Received Transaction Status:" . $this->receivedTransactionStatus);
        Logger::getInstance()->info("Received Payment Gateway Name:" . $this->receivedPaymentGatewayName);
        Logger::getInstance()->info("Received Payment Convenience Fee:" . $this->convenienceFee);
        Logger::getInstance()->info("-------------------------------------------------------------------");
    }

    //==================================================Extract Response Values from Plain Text========================//
    private function getTagValue($xml, $tag)
    {
        $iFirst = strpos($xml, $tag);
        $tagValue = "";
        $iLast = strpos($xml, $tag, $iFirst + 1);
        if ($iFirst && $iLast) {
            $tagValue = substr($xml, ($iFirst + strlen($tag) + 1), $iLast - ($iFirst + strlen($tag) + 3));
        }
        return $tagValue;
    }

    public function setLogs($value, $enabledLogs)
    {
        Logger::getInstance()->setLogs($value, $enabledLogs);
    }

    public function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

}

ini_set("memory_limit", "128M"); // Logger class is taking up memory

class Logger
{

    private $log_file_directory;
    private $first_run; // Flag to add line break at the beginning of script execution
    private $calling_script; // Base name of the calling script
    private $log_file; // log file path and name
    private $log_entry; // information to be logged
    private $log_level; // Log severity levels: error, warning, notice, debug, info
    private $fh; // File handle
    private $file_name; // File path and name
    private $file_parts; // Array of $file_name
    private $script_name; // Script Name
    private $script_parts; // Array of $script_name
    private $line_number_arr; // Line number of where the logging event occurred
    private $debug_flag; // Set to true if you want to log your debug logger
    private $log_enable_flag;
    private static $instance = null;

    private function __construct()
    {
        $this->first_run = true;
        $this->debug_flag = false;
        $this->calling_script = '';
        $this->log_file = '';
        $this->log_entry = '';
        $this->log_level = '';
        $this->fh = '';
        $this->file_name = '';
        $this->file_parts = '';
        $this->script_name = '';
        $this->script_parts = '';
        $this->line_number_arr = '';
        $this->log_enable_flag = true;
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    /**
     * @enableDebug
     */
    public function enableDebug()
    {
        $this->debug_flag = true;
    }

    /**
     * @disbaleDebug
     */
    public function disableDebug()
    {
        $this->debug_flag = false;
    }

    /**
     * @info
     */
    public function info($message)
    {
        if ($this->log_enable_flag) {
            if (($this->log_file_directory != "")
                || ($this->log_file_directory != null)) {
                $this->log_level = 'info';
                $this->line_number_arr = debug_backtrace();
                $this->addEntry($message);
            }
        } else {
            $this->log_enable_flag = false;
        }
    }

    /**
     * @error
     */
    public function error($message)
    {
        if ($this->log_enable_flag) {
            if (($this->log_file_directory != "")
                || ($this->log_file_directory != null)) {
                $this->log_level = 'error';
                $this->line_number_arr = debug_backtrace();
                $this->addEntry($message);
            }
        } else {
            $this->log_enable_flag = false;
        }
    }

    /**
     * @warning
     */
    public function warning($message)
    {
        $this->log_level = 'warning';
        $this->line_number_arr = debug_backtrace();
        $this->addEntry($message);
    }

    /**
     * @notice
     */
    public function notice($message)
    {
        $this->log_level = 'notice';
        $this->line_number_arr = debug_backtrace();
        $this->addEntry($message);
    }

    /**
     * @debug
     * must add the below to the script you wish to debug
     * define("DEBUG", true); // true enables, false disables
     */
    public function debug($message)
    {
        if ($this->debug_flag) {
            $this->log_level = 'debug';
            $this->line_number_arr = debug_backtrace();
            $this->addEntry($message);
        }
    }

    private function addEntry($message)
    {

        $this->calling_script = $this->getScriptBaseName();
        $this->log_file = $this->log_file_directory . "/" . $this->calling_script . ".log";

        $this->fh = @fopen($this->log_file, 'a') or die("Can't open log file: " . $this->log_file);

        if ($this->first_run) {
            $this->log_entry = "\n[" . date("Y-m-d H:i:s", time()) . "][line:" . $this->line_number_arr[0]['line'] . "|" . $this->log_level . "]:\t" . $message . "\n";
        } else {
            $this->log_entry = "[" . date("Y-m-d H:i:s", time()) . "][line:" . $this->line_number_arr[0]['line'] . "|" . $this->log_level . "]:\t" . $message . "\n";
        }

      //  $paymentLogger =  new PaymentLogger();
     //   $paymentLogger->log_type = $this->log_level;
      //  $paymentLogger->values = $this->log_entry;
      //  $paymentLogger->save(); 
        
        fwrite($this->fh, $this->log_entry);
        fclose($this->fh);

        $this->first_run = false;
    }

    /**
     * return the base name of the calling script
     */
    private function getScriptBaseName()
    {
        $this->file_name = $_SERVER["SCRIPT_NAME"];
        $this->file_parts = explode('/', $this->file_name);
        $this->script_name = $this->file_parts[count($this->file_parts) - 1];
        $this->script_parts = explode('.', $this->script_name);

        // If file doesn't exists don't add line break
        if (!file_exists($this->script_parts[0] . ".log")) {
            $this->first_run = false;
        }
        return $this->script_parts[0];
    }

    public function setLogs($value, $enable)
    {
        $this->log_file_directory = $value;
        $this->log_enable_flag = $enable;
    }

}