<?php
    header('Content-Type: application/json; charset=utf-8');
	setlocale(LC_ALL,'pt_BR.UTF8');
	mb_internal_encoding('UTF8');
	mb_regex_encoding('UTF8');

    require_once 'Classes/WebAluno.php';
	require_once 'Classes/Utils.php';

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
                {
                    $data = call_user_func_array(array(new $class, $method), $parameters);

                    if($data == null)
                        return Rest::GenerateJson(null, Utils::InvalidResponse);
                    else
                        return Rest::GenerateJson($data, null);
                }
                else
                    return Rest::GenerateJson(null, Utils::IncorrectParameters);
            }
            catch(Exception $e)
            {
                return Rest::GenerateJson(null, $e->getMessage());
            }
        }

        public static function GenerateJson($data, $error)
        {
            if($error === null)
			    return json_encode(array("Message" => utf8_decode(Utils::Success), "Data" => $data));

            header("X-Error-Message: ".utf8_decode($error), true, 500);
			return json_encode(array("Message" => $error, "Data" => $data));
        }
    }

    //Handle the request
    if(isset($_REQUEST))
        echo Rest::Open($_REQUEST);
?>