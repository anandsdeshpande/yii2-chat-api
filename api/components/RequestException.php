<?php

namespace api\components ;

class RequestException extends \yii\web\HttpException {
    
    public $param ;

    public function __construct($status, $param, $message = null, \Exception $previous = null) {
        $this->param = $param ;
        parent::__construct($status, $message, $code = 0, $previous = null) ;
    }
    
    public function toArray() {
        return [
            'status' => false ,
            'authenticated' => false ,
            'code' => $this->statusCode ,
            'errors' => [
                $this->param => $this->getMessage() ,
            ],
        ];
    }
}
