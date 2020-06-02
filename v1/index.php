<?php
    header('Content-Type: application/json; charset=utf-8');

    require_once 'Classes/Student.php';

    class Rest
    {
        public static function Open($request)
        {
            $url = explode('/', $request['url']);

            $class = ucfirst($url[0]);
            array_shift($url);

            $method = $url[0];
            array_shift($url);

            $parameters = array();
            $parameters = $url;

            try
            {
                if(class_exists($class) && method_exists($class, $method))
                    return Rest::GenerateJson(true, call_user_func_array(array(new $class, $method), $parameters));
                else
                    return Rest::GenerateJson(false, "1");
            }
            catch(Exception $e)
            {
                return Rest::GenerateJson(false, $e->getMessage());
            }
        }

        public static function GenerateJson($success, $data)
        {
            if(!$success)
                header('HTTP/1.1 500 Internal Server Error');

            return json_encode(array("success" => $success, "data" => $data));
        }
    }

    //Handle the request
    if(isset($_REQUEST))
        echo Rest::Open($_REQUEST);

    /*
    ERROR TABLE
        Em caso de erro servidor retornar http 500 error
        1 = Parametros incorretos ao efetuar o request
        2 = Parametros incorretos ao efetuar o login
        3 = Usuário e/ou senha inválidos
     */
?>