<?php

class Welcome extends \Slim\Middleware
{
    private function startsWith($haystack, $needle, $case = true)
    {
        if ($case) {
            return (strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
        }
        return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
    }

    private function isLogin()
    {
        return isset($_SESSION[Config::$SESSION_ACCOUNT]);
    }

    private function sessionManager()
    {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            // last request was more than 30 minutes ago
            session_unset();     // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
    }

    private function checkToken($request)
    {
        $token = $request->headers->get('Authorization');
//        $token = "eyJtYWMiOiJ0ZXN0MiIsInRvIjoxNDc4ODc0NTc2LCJzaWduIjoiZDIxODIxYzM3Y2Q0N2FiZGQ1YjJlOTEzNmRkNmRjNjIifQ==";
        if ($token == null || strlen($token) <= 0) return 101;

        $token = substr($token, strlen("Bearer "));
        $data = json_decode(base64_decode($token));

        // check timeout
        if ($data->to < time()) return 103;

        // verify sign
        if (strcmp(md5($data->mac . $data->to . Config::$API_KEY), $data->sign) != 0) return 102;

        $_SESSION[Config::$SESSION_ACCOUNT] = $data->mac;
        return 0;
    }

    public function call()
    {
        $app = $this->app;
        $path = $app->request()->getPathInfo();

        if ($this->startsWith($path, '/api', false) || $this->startsWith($path, '/suggest', false)) {  //  api
            require_once __DIR__ . "/../ApiRespond.php";

            if (!$this->startsWith($path, '/api/v1/device/init', false)
                && !$this->startsWith($path, '/api/v1/device/activate', false)) {

                // check header
                /*
                $token_parse = $this->checkToken($app->request);
                if ($token_parse != 0) {
                    $messages = array(
                        101 => "No token!",
                        102 => "Unverified token!",
                        103 => "Timeout token!",
                    );

                    $app->response->body(json_encode((new ApiRespond(false, null, $token_parse, $messages[$token_parse]))->getDisplay()));
                    return;
                }
                */
            }

        } else {        // CMS
            $this->sessionManager();

            if (!$this->isLogin() && !$this->startsWith($path, '/auth', false)) {
                return $app->response()->redirect(Config::$MAIN_URL . '/auth/login');
            } else {
                $data = array(
                    "main_url" => Config::$MAIN_URL,
                    "root" => Config::$ROOT,
                );
                if ($this->isLogin()) {
                    $data = array_merge($data, $_SESSION);
                }
                $app->view()->appendData($data);
            }
        }

        $this->next->call();
    }
}
