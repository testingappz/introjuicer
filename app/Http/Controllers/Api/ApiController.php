<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Form;
use Session;

class ApiController extends Controller
{
    public function response($type = 'Success', $message = null, $data = [], $httpStatusCode = null)
    {
        if (!is_null($message) && substr($message, 0, 9) == "messages.")
            $message = trans($message);

        $response = [
            'type'    => $type,
            'message' => $message,
            'data'    => $data,
        ];

        if (isset($data['url']) || isset($data['uri']) || (isset($data['sessionMessage']) && $data['sessionMessage']))
        {
            Session::flash('messages.'.strtolower($type), $message);
        }

        if (!is_null($httpStatusCode))
        {
            return response($response, $httpStatusCode)->header('Content-Type', 'text/json');
        }

        return $response;
    }

    public function success($message = null, $data = [], $httpStatusCode = null)
    {
        if (!is_null($message) && strpos($message, ' ') === false && substr($message, 0, 9) != "messages.")
            $message = "messages.success.".$message;

        return $this->response('Success', $message, $data, $httpStatusCode);
    }

    public function error($message = null, $data = [], $httpStatusCode = null)
    {
        if (is_null($message))
            $message = "messages.errors.general";

        if (strpos($message, ' ') === false && substr($message, 0, 9) != "messages.")
            $message = "messages.errors.".$message;

        if (!isset($data['errors']))
            $data['errors'] = Form::getErrors();

        return $this->response('Error', $message, $data, $httpStatusCode);
    }

    public function warning($message = null, $data = [], $httpStatusCode = null)
    {
        return $this->response('Warning', $message, $data, $httpStatusCode);
    }

    public function info($message = null, $data = [], $httpStatusCode = null)
    {
        return $this->response('Info', $message, $data, $httpStatusCode);
    }
}
