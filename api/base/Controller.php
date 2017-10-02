<?php

namespace api\base;

use Yii;

class Controller extends \yii\rest\Controller {

    public $postParamErrors = [];
    public $rules;

    protected function validateRequiredPostParams($params = []) {
        $errors = [];
        foreach ($params as $param) :
            $check = Yii::$app->getRequest()->getBodyParam($param, null);
            if (is_null($check) || $check == '') {
                array_push($errors, $param);
            }
        endforeach;

        if (empty($errors)) {
            return true;
        } else {
            $this->postParamErrors = $errors;
            return false;
        }
    }

    public function generatePostErrorMessage() {
        return [
            'status' => 'ERROR',
            'message' => 'Invalid Requst',
        ];
    }

    protected function throwError($code, $key, $message) {
        $respose = Yii::$app->getResponse();
        $respose->setStatusCode($code);
        throw new RequestException(400, $key, $message);
    }

    public function actionError() {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }

    public function validateRequest() {
        if (Yii::$app->request->getIsGet()) {
            $req = Yii::$app->request->get();
        } else {
            Yii::$app->request->parsers = [
                'application/json' => 'yii\web\JsonParser',
            ];
            $req = Yii::$app->request->getBodyParams();
        }

        foreach ($this->rules as $param => $rule) {
            if (isset($rule['required']) && $rule['required']) {
                if (!isset($req[$param]) || empty($req[$param])) {
                    return $this->throwError(400, $param, $param . ' is Required');
                }
            }
            if (isset($rule['enum'])) {
                if (isset($req[$param]) && !in_array($req[$param], $rule['enum'])) {
                    $errorInfo['Value not allowed'] = $param;
                    return $this->throwError(400, $param, $param . ' Value not allowed');
                }
            }
            //set type for sanitizing the request parameters
            if (isset($rule['type'])) {
                if (isset($req[$param]) && !empty($req[$param])) {
                    
                    switch ($rule['type']) {
                        case 'string':
                            if (intval($req[$param]) < 0) {
                                    $this->throwError(400, $param, $param . ' value must be positive');
                                }
                            $req[$param] = trim($req[$param]);
                            $req[$param] = addslashes($req[$param]);
                            break;

                        case 'integer':
                            if (!(is_numeric($req[$param]) && (int) $req[$param] == $req[$param])) {
                                return $this->throwError(400, $param, $param . ' value must be integer');
                            }
                            if (isset($rule['allow_negative']) && $rule['allow_negative']) {
                                //TODO
                            } else {
                                if ((int) $req[$param] < 0) {
                                    $this->throwError(400, $param, $param . ' value must be positive');
                                }
                            }
                            break;

                        case 'float':
                            if (!is_numeric($req[$param])) {
                                return $this->throwError(400, $param, $param . ' value must be a number');
                            }
                            if (isset($rule['allow_negative']) && $rule['allow_negative']) {
                                //TODO
                            } else {
                                if ((int) $req[$param] < 0) {
                                    $this->throwError(400, $param, $param . ' value must be positive');
                                }
                            }
                            break;
                            
                        case 'array':
                            if (!is_array($req[$param])) {
                                return $this->throwError(400, $param, $param . ' value must be a array');
                            }
                            
                            if(isset($rule['subitemtype']) && $rule['subitemtype'] == 'integer') {
                                foreach ($req[$param] as $item) {
                                    if (!(is_numeric($item) && (int) $item == $item)) {
                                        return $this->throwError(400, $param, $param . ' items must be integer');
                                    }
                                    
                                    if ((int) $item < 0) {
                                        $this->throwError(400, $param, $param . ' items must be positive');
                                    }
                                }
                            }
                            break;

                        case 'json':
                            $input = trim($req[$param]);

                            if (substr($input,0,1)!='{' OR substr($input,-1,1)!='}')
                                   $this->throwError(400, $param, $param . ' value must be JSON encoded array or object');

                            if(!is_array(@json_decode($input, true)))
                            {
                                $this->throwError(400, $param, $param . ' value must be JSON encoded array or object');
                            }                                    
                            break;   
                    }
                }
            }
        }
    }

    public function formatter($object, $attributes) {
        $vars = get_object_vars($object);
        $AttributesToRemove = array_diff($vars, $attributes);
        foreach ($AttributesToRemove as $Attribute) {
            unset($object->$Attribute);
        }
        return $object;
    }

    public function makeResponse($data, $success = true, $errors = []) {
        $return = [
            'success' => $success,
            'errors' => $errors,
            'response' => $data,
        ];
        return $return;
    }

}
    