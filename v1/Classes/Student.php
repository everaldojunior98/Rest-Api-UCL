<?php
    class Student
    {
        public function Login()
        {
            if(!isset($_POST['user']) || !isset($_POST['password']))
                throw new Exception("2");

            $loginUrl = "https://eies.ucl.br/webaluno/login/?next=/webaluno/";

            $csrf_token_field_name = "csrfmiddlewaretoken";
            $params = array(
                "user" => $_POST['user'],
                "password" => $_POST['password']
            );
        
            $token_cookie= realpath("cookie.txt");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $loginUrl);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $token_cookie);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $token_cookie);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($ch);
        
            if (curl_errno($ch)) 
                die(curl_error($ch));
        
            libxml_use_internal_errors(true);
            $dom = new DomDocument();
            $dom->loadHTML($response);
            libxml_use_internal_errors(false);
            $tokens = $dom->getElementsByTagName("input");
            for ($i = 0; $i < $tokens->length; $i++) 
            {
                $meta = $tokens->item($i);
                if($meta->getAttribute('name') == 'csrfmiddlewaretoken')
                    $token = $meta->getAttribute('value');
            }
        
            if($token)
            {
                $postinfo = "";
                foreach($params as $param_key => $param_value) 
                    $postinfo .= $param_key ."=". $param_value . "&";	
                $postinfo .= $csrf_token_field_name ."=". $token;
        
                $headers = array();
                $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
                $header[] = "Cache-Control: max-age=0";
                $header[] = "Connection: keep-alive";
                $header[] = "Keep-Alive: 300";
                $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
                $header[] = "Accept-Language: en-us,en;q=0.5";
                $header[] = "Pragma: ";
                $headers[] = "X-CSRF-Token: $token";
                $headers[] = "Cookie: $token_cookie";

                curl_setopt($ch, CURLOPT_URL, $loginUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
                curl_setopt($ch, CURLOPT_COOKIEJAR, $token_cookie);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $token_cookie);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_REFERER, $loginUrl);
                curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 260);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
                ob_start();
                $loginHtml = curl_exec($ch);
                $result = curl_getinfo($ch);
                ob_get_clean();
        
                if(!(array_key_exists("redirect_count", $result) ? $result['redirect_count'] > 0 : false))
                    throw new Exception("3");
                    
                return $ch;
            }
        }

        public function Profile()
        {
            $curlSession = Student::Login();
            $profileFrameUrl = "https://eies.ucl.br/webaluno/";

            if($curlSession)
            {
                curl_setopt($curlSession, CURLOPT_URL, $profileFrameUrl);
                $profileHtml = curl_exec($curlSession);

                $profileDOM = new DOMDocument();
                $profileDOM->loadHTML($profileHtml);
    
                $userDOM = $profileDOM->getElementById('slide-out');
    
                $info = Student::GetElementsByClassName($profileDOM, 'center-align');
    
                $profile->Nome = preg_split('/$\R?^/m', trim($info[1]->textContent))[0];
                $profile->Imagem = $info[0]->getElementsByTagName('img')[0]->getAttribute('src');
    
                $info = $userDOM->getElementsByTagName('span');
    
                $profile->Email = trim($info[0]->textContent);
                $profile->Curso = trim($info[1]->textContent);
                $profile->Matricula = trim(explode(":", $info[2]->textContent)[1]);
                $profile->CR = trim(explode(":", $info[3]->textContent)[1]);

                return $profile;
            }
        }

        public function Grades()
        {
            $curlSession = Student::Login();
            $gradesFrameUrl = "https://eies.ucl.br/webaluno/quadrodenotas/";

            if($curlSession)
            {
                curl_setopt($curlSession, CURLOPT_URL, $gradesFrameUrl);
                $gradesFrameHtml = curl_exec($curlSession);
                
                $gradesFrameDOM = new DOMDocument();
                $gradesFrameDOM->loadHTML($gradesFrameHtml);
    
                $gradesDOM = $gradesFrameDOM->getElementById('aluno_notas');
                
                $gradesArray = array();
                $currentPeriodId = 0;

                foreach(preg_split('/$\R?^/m', trim(Student::GetElementsByClassName($gradesDOM, 'col s12')[0]->textContent)) as $period)
                {
                    $period = trim($period);
                    if(!empty($period))
                    {
                        $id = str_replace('/', '-', $period); 
                        $periodDOM = $gradesFrameDOM->getElementById($id);
                        $disciplineNames = array();
    
                        foreach(Student::GetElementsByClassName($periodDOM, 'collapsible-header') as $disciplineName)
                            $disciplineNames[] = trim(str_replace('keyboard_arrow_right', '', $disciplineName->textContent));
    
                        $disciplineInfo = array();
                        $count = 0;
                        foreach(Student::GetElementsByClassName($periodDOM, 'center-align') as $infos)
                        {
                            $disciplineInfo[$currentPeriodId][$count]["Professor"] = trim(explode("(", explode("\n", explode(":", Student::GetElementsByClassName($periodDOM, 'collection-item dismissable')[$count]->nodeValue)[1])[0])[0]);
                            
                            foreach(preg_split('/$\R?^/m', trim($infos->nodeValue)) as $info)
                                $disciplineInfo[$currentPeriodId][$count][explode(":", trim($info))[0]] = trim(explode(":", $info)[1]);
                            $count++;
                        }
                        
                        $disciplineId = 0;
                        foreach(Student::GetElementsByClassName($periodDOM, 'striped') as $discipline)
                        {
                            $gradesByGroupArray = array();
                            $headerArray = array();
    
                            $header = $discipline->getElementsByTagName('th');
                            $lines = $discipline->getElementsByTagName('td');
    
                            foreach($header as $nodeHeader) 
                                $headerArray[] = trim($nodeHeader->textContent);
    
                            $i = 0;
                            $j = 0;
                            foreach($lines as $line) 
                            {
                                $gradesByGroupArray[] = trim($line->textContent);
                                $i++;
                                $j = $i % count($headerArray) == 0 ? $j + 1 : $j;
                            }
    
                            $allGrades = array();
                            $i = 0;
    
                            foreach($gradesByGroupArray as $grade) 
                            {
                                if($i == 6)
                                    $i = 0;
                                else
                                {
                                    $allGrades[] = $grade;
                                    $i++;
                                }
                            }
    
                            $disciplineInfo[$currentPeriodId][$disciplineId]["Disciplina"] = $disciplineNames[$disciplineId];
                            $gradesArray[$period][$disciplineId] = $disciplineInfo[$currentPeriodId][$disciplineId];
                            
                            foreach(array_chunk($allGrades, 6) as $grades)
                            {
                                $newGrade = array();
                                $i = 0;
                                foreach($grades as $grade)
                                {
                                    $newGrade[$headerArray[$i]] = $grade;
                                    $i++;
                                }
                                
                                $gradesArray[$period][$disciplineId]['Notas'][] = $newGrade;
                            }
                            $disciplineId++;
                        }
                        $currentPeriodId++;
                    }
                }

                return $gradesArray;
            }
        }

        public function Financial()
        {
            $curlSession = Student::Login();
            $financialUrl = "https://eies.ucl.br/webaluno/financeiro/";

            if($curlSession)
            {
                curl_setopt($curlSession, CURLOPT_URL, $financialUrl);
                $financesHtml = curl_exec($curlSession);

                $financesDOM = new DOMDocument();
                $financesDOM->loadHTML($financesHtml);
                
                $openInvoicesDOM = $financesDOM->getElementById('fin1');

                $header = $openInvoicesDOM->getElementsByTagName('th');
                $lines = $openInvoicesDOM->getElementsByTagName('td');

                foreach($header as $nodeHeader)
                    $openInvoicesHeader[] = trim($nodeHeader->textContent);

                $i = 0;
                $j = 0;

                foreach($lines as $line) 
                {
                    $tempOpenInvoicesArray[$j][] = trim($line->textContent);
                    $i++;
                    $j = $i % count($openInvoicesHeader) == 0 ? $j + 1 : $j;
                }

                for($i = 0; $i < count($tempOpenInvoicesArray); $i++)            
                    for($j = 0; $j < count($openInvoicesHeader); $j++)
                        $openInvoicesArray[$i][$openInvoicesHeader[$j]] = $tempOpenInvoicesArray[$i][$j];

                $WebAluno->Financeiro->FaturasEmAberto = $openInvoicesArray;

                $allInvoicesDOM = $financesDOM->getElementById('fin2');

                $header = $allInvoicesDOM->getElementsByTagName('th');
                $lines = $allInvoicesDOM->getElementsByTagName('td');

                foreach($header as $nodeHeader) 
                    $allInvoicesHeader[] = trim($nodeHeader->textContent);
                
                $i = 0;
                $j = 0;
                
                foreach($lines as $line) 
                {
                    $tempAllInvoicesArray[$j][] = trim($line->textContent);
                    $i++;
                    $j = $i % count($allInvoicesHeader) == 0 ? $j + 1 : $j;
                }

                for($i = 0; $i < count($tempAllInvoicesArray); $i++)            
                    for($j = 0; $j < count($allInvoicesHeader); $j++)
                        $allInvoicesArray[$i][$allInvoicesHeader[$j]] = $tempAllInvoicesArray[$i][$j];
                
                return $allInvoicesArray;
            }
        }

        function GetElementsByClassName($dom, $ClassName, $tagName = null)
        {
            $Elements = $tagName ? $dom->getElementsByTagName($tagName) : $dom->getElementsByTagName("*");
    
            $Matched = array();
            for($i = 0; $i<$Elements->length; $i++)
                if($Elements->item($i)->attributes->getNamedItem('class'))
                    if(strpos($Elements->item($i)->attributes->getNamedItem('class')->nodeValue, $ClassName) !== false)
                        $Matched[]=$Elements->item($i);
    
            return $Matched;
        }
    }
?>