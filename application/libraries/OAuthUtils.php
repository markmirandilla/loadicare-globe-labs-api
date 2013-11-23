<?php
class OAuthUtils {
    
    public function getUserFromSession($userName=NULL){
        if (!isset($userName)) $userName = $_REQUEST['userName'];

	// This relies on ../callback.php, c. line 73: $_SESSION['userName'] = $userName;
        if (!isset($userName)) $userName = $_SESSION['userName'];

        //$userName = ($userName) ? $userName : $_REQUEST['username'];
        if (!isset($userName)) {
            $data = json_decode(stripslashes($_COOKIE['s']));
            $userName = $data->un;
        }
        return $userName;
    }
    
    /*
    public function linkSnsPost($id, $data, $sns) {
        $url = self::getRecmnUrl($data['url']);
        if (($id) && $url) {
            try {
                $rpc = new RpcClient(RPC_SERVICE_URL."/".REQ_SERVICE_NAME);
                $credentials = Credentials::fromSession($debug);
                return $rpc->linkSnsPost($url,$sns,$id,null, $credentials->serialize());
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }*/
    
    public function sanitize_post($txt){
        return str_replace('"','\"', trim(stripslashes($txt)));
    }
    
    # http://rec.mn/altair.1 returns altair.1
    public function getRecmnUrl($url){
        if (preg_match('/\/?([a-zA-Z][a-zA-Z0-9]+)\.([0-9]+)\??/', $url, $matches)) {
            return $matches[1].".".$matches[2];
        }
        return null;
    }
    
    public function getSnsPostId($url, $sns = 'facebook'){
        $url = self::getRecmnUrl($url);
        if ($url) {
            $rpc = new RpcClient(RPC_SERVICE_URL."/".REQ_SERVICE_NAME);
            $credentials = Credentials::fromSession($debug);
            $res = $rpc->getSnsPost($url,null, $credentials->serialize());
            return $res->result->$sns;
        }
        return null;
        
    }
    public function parseOauthSession($oauth_data=null, $sns='facebook'){
        $oauth_data = ($oauth_data) ? $oauth_data : $_REQUEST['oauth_data'];
        $oauth_data = json_decode(stripslashes($oauth_data));
        $res = ($oauth_data->main) ? $oauth_data->main: $oauth_data;
        $access = ($res->access) ? $res->access : $res;
        if (!$oauth_data && !$access) return null;
        $r['oauth_token'] = $access->token;
        $r['oauth_token_secret'] = $access->secret;
        return $r;
    } 
    
}
