<?php
/**
 * User: Anand Deshpande
 * Date: 02/10/17
 * Time: 17:24
 */
namespace api\controllers;

use api\base\Controller;

class TestController extends Controller
{
    public function actionNew() {
        return [
            'message' => 'Hello World'
        ];
    }
}
