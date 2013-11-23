<?php

include('SNSOAuth.php');
include('OAuthUtils.php');
require('fb/facebook.php');

class FacebookOAuth extends Facebook {
    function __construct($config = NULL,$overrideConfig = NULL) {
        //$conf = new Config('facebook');
        $CI =& get_instance();
        $snskeys = $CI->config->item('snsKeys');
        $this->client_id = ($overrideConfig['appID'])?$overrideConfig['appID']:$snskeys['facebook']['appID'];
        //$api_key = ($overrideConfig['apiKey'])?$overrideConfig['apiKey']:$snskeys['facebook']['apiKey'];
        $this->secret = ($overrideConfig['apiSecretKey'])?$overrideConfig['apiSecretKey']:$snskeys['facebook']['apiSecretKey'];
        $this->redirect_uri = ($overrideConfig['snsCallBackUrl'])?$overrideConfig['snsCallBackUrl']:$snskeys['facebook']['snsCallBackUrl'];
        //$this->accessTokenURL  = $snskeys['facebook']['access_token_url'];
        //$this->authorizeURL    = $snskeys['facebook']['authorize_url'];
        //$this->authenticateURL = $snskeys['facebook']['authenticate_url'];
        //$this->requestTokenURL = $snskeys['facebook']['request_token_url'];
        //$this->host            = $snskeys['facebook']['api_root_url'];
        $this->scope            = ($overrideConfig['scope'])?$overrideConfig['scope']:$snskeys['facebook']['scope'];
        $this->provider        = 'facebook';
        $this->display         = ($overrideConfig['display'])?($overrideConfig['display']):'page';
        $config = ($config) ? $config : array( 'appId'  => $this->client_id, 'secret' => $this->secret, 'cookie' => true, 'domain' => 'perkmeapp.com');
        parent::__construct($config);
    }

    function appendtoCallback($parameters) {
        $parameters = ($parameters) ? "&" . http_build_query($parameters) : "";
        $this->redirect_uri = $this->redirect_uri . $parameters;
        return $this->redirect_uri;
    }

    function getSession($fromdb = false) {
        $session = parent::getSession();
        if (!$session || $fromdb) {
            $userName = OAuthUtils::getUserFromSession();
            try {
                //get from CI Session
                $session2 = $this->getSessionFromToken($token_credentials['oauth_token']);
                // if session is from db try to validate by making an api call
                if (!$session2) throw new Exception('No session from DB.');
                $param['fields'] = 'id';
                $profile_info = $this->api('/me',$param);
                return $session2;
            } catch(Exception $e) {
                return false;
            }
        }
        return $session;
    }

    function getAppAccessToken()
    {
        $CI =& get_instance();
        $snsKeys = $CI->config->item('snsKeys');
        $appId = $snsKeys['facebook']['appID'];
        $appSecret = $snsKeys['facebook']['apiSecretKey'];
        // Get an App Access Token
      $token_url = 'https://graph.facebook.com/oauth/access_token?'
        . 'client_id=' . $appId
        . '&client_secret=' . $appSecret
        . '&grant_type=client_credentials';

      $token_response = file_get_contents($token_url);
      $params = null;
      parse_str($token_response, $params);
      $app_access_token = $params['access_token'];
      return $app_access_token;
    }

    function getSystemSession($oauth_data=null) {
        $token_credentials = OAuthUtils::parseOauthSession($oauth_data);
        $session = $this->getSessionFromToken($token_credentials['oauth_token']);
        return $session;
    }

    function getSessionFromToken($token) {
        $session = null;
        if ($token) {
            $sess = base64_decode(urldecode($token));
            $session = json_decode(
                           get_magic_quotes_gpc()
                           ? stripslashes($sess)
                           : $sess,
                           true
                       );
            $session = $this->validateSessionObject($session);
            $write_cookie = true;
            if ($session) $this->setSession($session, $write_cookie);
        }
        return $session;
    }

    function getAuthenticateUrl() {
        //if ($this->getSession()) return $this->redirect_uri;
        $p['cancel_url'] = $this->redirect_uri;
        $p['next'] = $this->redirect_uri;
        $p['display'] = $this->display;
        //if ($this->scope) $p['req_perms'] = $this->scope;
        if ($this->scope) $p['scope'] = $this->scope;
        return $this->getLoginUrl($p);
    }

    function checkPermission($permission) {
        $askForPermission = FALSE;
        $permissions = $this->api("/me/permissions");

        if(empty($permission)) return TRUE;
        foreach($permission as $permissionVal) {
            if(!array_key_exists($permissionVal, $permissions['data'][0]) ) {
                $askForPermission = TRUE;
                break;
            }
        }
        return $askForPermission;
    }
    function getEmail() {
        try {
            $uid = $this->getUser();
            $cacheGroup = __Class__ .' '. __Method__;
            $cacheKey = $uid;
            $authInfo =  NULL;
            //$authInfo = get_from_cache($cacheGroup,$cacheKey);
        } catch(Exception $e) {
            log_message('ERROR','Cannot get User info in '. __METHOD__);
        }
        if($uid) {
            $param['fields'] = 'id,email';
            $profile_info = $this->api('/me',$param);
            $profile['email'] = $profile_info['email'];
            $profile['verifiedEmail'] = $profile_info['email'];
            $profile['userId'] = $profile_info['id'];

            $authInfo['profile'] = $profile;
            //if(!empty($authInfo)) put_in_cache($cacheGroup,$cacheKey,$authInfo,360);
        }
        return $authInfo;
    }

    function getAuthInfo() {
        benchmark_start(__METHOD__);
        $param['fields'] = 'id,email,last_name,first_name,name,gender,link, birthday';
        $profile_info = $this->api('/me',$param);

        $profile_id = isset($profile_info['id']) ? $profile_info['id'] : NULL;
        $profile['providerName']    = 'facebook';
        $profile['authProvider']    = 'facebook';
        $profile['userId']          = $profile_id;
        $profile['email']           = isset($profile_info['email']) ? $profile_info['email'] : NULL;
        $profile['url']             = isset($profile_info['link']) ? $profile_info['link'] : NULL;
        $profile['identifier']      = "http://www.facebook.com/profile.php?id=" . $profile_id;
        $profile['photo']           = "https://graph.facebook.com/".$profile_id."/picture?type=large";
        $profile['gender']          = isset($profile_info['gender']) ? $profile_info['gender'] : NULL;
        $profile['birthday']        = isset($profile_info['birthday']) ? $profile_info['birthday'] : NULL;
        $name['familyName']         = isset($profile_info['last_name']) ? $profile_info['last_name'] : NULL;
        $name['givenName']          = isset($profile_info['first_name']) ? $profile_info['first_name'] : NULL;;
        $name['formatted']          = isset($profile_info['name']) ? $profile_info['name'] : NULL;
        $profile['name']            = $name;
        //$profile['location']        = assess_variable_value($profile_info['location']['name']);

        if (strpos($profile_info['link'],'profile.php') === false) {
            $uname = $this->getNameFromUrl($profile['url']);
        } else if ($profile_info['name']) {
            $uname = preg_replace('/[^a-zA-Z0-9-]/', '', $profile['name']);
        } else {
            $uname = preg_replace('/[^a-zA-Z0-9-]/', '', $name['givenName']);
        }

        if(is_array($uname)) $uname = $uname['formatted'];
        $uname = strtolower($uname);

        $profile['userName'] = $uname;
        $profile['preferredUsername'] = $uname;
        $authInfo['profile'] = $profile;

        benchmark_end(__METHOD__);
        return $authInfo;
    }

    // It's either http://www.facebook.com/<user name> or http://www.facebook.com/profile.php?id=xxxx
    function getNameFromUrl($link) {
        if (strpos($link,'profile.php') === false) {
            $uname = explode('/',$link);
            return end($uname);
        } else {
            $uname = explode('=',$link);
            return end($uname);
        }
        return false;
    }

    function publish($data = NULL) {
        $data = ($data) ? $data : $_GET;
        //try to post a comment on facebook post
        if ($data['urlReq']) {
            $res = $this->notifyAsker($data);
            return $res;
        }
        //Use Old REST API Service since the new Graph API doesn't support lot of stuff like (action_links and multiple images) plus its buggy grrrr...
        return $this->rest_publish($data);
    }

    function graph_publish($data=NULL) {
        $link        = isset($data['url']) ? $data['url'] : NULL;
        $name        = isset($data['name']) ? $data['name'] : NULL;
        $caption     = isset($data['title']) ? $data['title'] : NULL;
        $description = isset($data['description']) ? $data['description'] : NULL;
        $message     = isset($data['message']) ? $data['message'] : NULL;
        $picture     = isset($data['image']) ? $data['image'] : NULL;
        $place       = isset($data['place']) ? $data['place'] : NULL;
        $attribution = isset($data['attribution']) ? $data['attribution'] : NULL;

        if($message) $p["message"]          = $message;
        if ($picture) $p["picture"]         = urldecode($picture);
        if ($link) $p["link"]               = $link;
        if ($name) $p["name"]               = ($name) ? $name : $link;
        if ($caption) $p["caption"]         = $caption;
        if ($description) $p["description"] = $description;
        if ($place) $p['place']             = $place;
        $p["attribution"]                   = ($attribution) ? $attribution : "perkmeapp.com";
        $res = $this->api('me/feed', 'POST', $p);
        return (isset($res['id'])) ? $res['id'] : json_encode($res);
    }

    function publish_activity($data = array()) {
        $namespace = $data['namespace'];
        $link = $data['url'];
        $action = $data['action'];
        $object = $data['object'];
        $message = isset($data['message']) ? $data['message'] : NULL;
        $access_token = isset($data['access_token']) ? $data['access_token'] : NULL;
        $explicitly_shared = isset($data['explicitly_shared']) ? $data['explicitly_shared'] : FALSE;

        //$accessToken = $data['accessToken'];
        $p = array();
        if($message) $p['message'] = $message;
        if($access_token) $p['access_token'] = $access_token;
        if($explicitly_shared === TRUE) $p['fb:explicitly_shared'] = 'true';
        $p[$object] = $link;
        $res = $this->api("me/{$namespace}:{$action}", 'POST', $p);
        return $res;
    }

    function getAppRequests() {
        $res = $this->api('/me/apprequests/');
        return $res;
    }

    /*
     * array ( urlReq comment )
     * */
    function notifyAsker($data = null) {
        $data = ($data) ? $data : $_GET;
        if ($data['urlReq']) {
            //$rpc = new RpcClient(REQ_SERVICE_URL);
            //$rpc = new RpcClient(RPC_SERVICE_URL."/".REQ_SERVICE_NAME);
            //$credentials = Credentials::fromSession($debug);
            //$res= $rpc->getSnsPost($data['urlReq'],null, $credentials->serialize());
            //$post_id = $res->result->facebook;
            //if ($post_id) {
            //$comment = str_replace("{asker}", "", $data['comment']);
            //$p['message'] = $comment . " " . $data['url'];

            //if it fail to reply a comment because  their not friends publish to personal wall
            try {
                $res = $this->api("{$post_id}/comments", 'POST', $p);
                return(isset($res['id'])) ? $res['id'] : $res;
            } catch(Exception $e) {
                //return $this->rest_publish();
            }
        }
    }
#$res = $this->api('1356107248_1478416084480/comments', 'POST', $p);

    function get_user_current_location($uid)
    {
        $fql = "SELECT  current_location from user where uid={$uid}";
        $p['method'] = "fql.query";
        $p['format'] = "json";
        $p['query'] = $fql;
        $res = $this->api($p);
        return $res;
    }

    function getFriends($uid=null,$limit=null) {
        //$p['method'] = "friends.get";
        $uid = ($uid) ? $uid : $this->getUser();
        $limit = ($limit !== null && is_numeric($limit))?" LIMIT {$limit}":null;

        //$cacheGroup = __Class__ .' '. __Method__;
        //$cacheKey = $uid;

        //$res = get_from_cache($cacheGroup,$uid);
        //if(empty($res)) {
        //$fql = "SELECT user_id FROM like WHERE object_id IN (SELECT link_id FROM link WHERE owner=$uid) LIMIT 100";
        $fql = "SELECT uid, first_name,middle_name,last_name,pic_small,hometown_location,current_location
               FROM user
               WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=$uid)";
        $p['method'] = "fql.query";
        $p['format'] = "json";
        $p['query'] = $fql;
        $res = $this->api($p);
        //if(!empty($res)) put_in_cache($cacheGroup,$cacheKey,$res,60); //1 hour
        //}
        return $res;
    }

    function getTopFriends($uid=null) {
        $uid = ($uid) ? $uid : $this->getUser();
        $fql = "SELECT user_id FROM like WHERE object_id IN (SELECT link_id FROM link WHERE owner=$uid) LIMIT 100";
        //$fql = "SELECT link_id FROM link WHERE owner=$uid LIMIT 10";
        $p['method'] = "fql.query";
        $p['format'] = "json";
        $p['query'] = $fql;
        $n = $this->api($p);
        if (is_array($n) && count($n) < 1) $n = $this->getFriends();
        return $n;
    }
    function getFeed($uid = "me()") {
        $fql ="SELECT post_id,app_data,action_links,attachment,message,description
              FROM stream
              WHERE filter_key in (SELECT filter_key FROM stream_filter WHERE uid=$uid AND type='newsfeed') AND is_hidden = 0";
        $p['method'] = "fql.query";
        $p['format'] = "json";
        $p['query'] = $fql;
        $n = $this->api($p);
        return $n;
    }
    function getBatchFeeds($uids) {
        $request = array();
        $request['method'] = 'GET';
        $queries = array();

        foreach($uids as $id) {
            $request['relative_url'] = "/$id/feed";
            $queries[] = $request;
        }

        $batchResponse = $this->api('?batch='.json_encode($queries), 'POST');

        return $batchResponse;
    }
    function getFreighborFeeds($uids) {
        $request = array();
        $request['method'] = 'GET';
        $queries = array();

        foreach($uids as $id) {
            $request['relative_url'] = "/$id/photos";
            $queries[] = $request;
            $request['relative_url'] = "/$id/checkins";
            $queries[] = $request;
        }

        $batchResponse = $this->api('?batch='.json_encode($queries), 'POST');
        return $batchResponse;
    }

    function getStream() {
        $p['method'] = "stream.get";
        $p['format'] = "json";
        $p['limit'] = 50;
        $p['viewer_id'] = 0;
        $n = $this->api($p);
        return $n;
    }

    function sendEmail($ids, $data) {
        $p['method'] = "notifications.sendEmail";
        $p['format'] = "json";
        $p['subject'] = $data['subject'];
        $p['text'] = $data['text'];
        $p['recipients'] = implode(",",$ids);
        $n = $this->api($p);
        return $n;
    }

    function publishtoFriendsWall($ids, $data = null) {
        $data = ($data) ? $data : $_GET;
        $link        = $data['url'];
        $name        = $data['name'];
        $caption     = $data['title'];
        $description = $data['description'];
        $comment     = $data['comment'];
        $picture     = $data['image'];

        $p['method'] = "stream.publish";
        $p['format'] = "json";
        $ids = (is_array($ids)) ? implode(",",$ids) : $ids;
        $p['target_id'] = $ids;

        $p["message"] = stripslashes(($comment) ? $comment : $link);

        $attachment['name'] = ($name) ? $name : $link;

        $param['fields'] = 'first_name';
        $attachment['href'] = $link;
        $attachment['caption'] = OAuthUtils::sanitize_post($caption);
        $attachment['description'] = OAuthUtils::sanitize_post($description);
        if ($picture) {
            $image['type'] = "image";
            $image['src'] = urldecode($picture);
            $image['href'] = $link;
            $attachment['media'] = array($image);
        }

        $p['attachment'] = stripslashes(json_encode($attachment));
        $action = ($data['publish_action']) ? $data['publish_action'] : $data['action'];
        $action_url = ($data['action_url']) ? $data['action_url'] : $data['actionUrl'];
        if (strtolower($action) == 'reply') $action_url = "http://apps.facebook.com/" . $this->conf->canvas . "/q/" . end(explode('/q/',$link));
        if ($action && $action_url) {
            $action_links['text'] = $action;
            $action_links['href'] = $action_url;
            $p['action_links'] =  stripslashes(json_encode(Array($action_links)));
        }
        $n = $this->api($p);
        return $n;
    }

    /*
    function createPublishData($data = null, $profile = null) {
        $data = ($data) ? $data : $_GET;
        $link        = $data['url'];
        $name        = $data['name'];
        $caption     = $data['title'];
        $description = $data['description'];
        $comment     = $data['comment'];
        $picture     = $data['image'];
        $target_id   = $data['target_id'];

        $p['method'] = "stream.publish";
        $p['format'] = "json";

        $p["message"] = stripslashes($comment);
        if ($target_id) $p["target_id"] = $target_id;

        $attachment['name'] = ($name) ? $name : $link;

        if (strpos($attachment['name'], "{*actor*}") !== false) {
            $param['fields'] = 'first_name';
            $profile_info = ($profile) ? $profile : $this->api('/me',$param);
            $profile_info = $profile_info['first_name'];
            $attachment['name'] = str_replace("{*actor*}", $profile_info, $attachment['name']);
        }

        $attachment['href'] = $link;
        $attachment['caption'] = OAuthUtils::sanitize_post($caption);
        $attachment['description'] = OAuthUtils::sanitize_post($description);
        if ($picture) {
            $image['type'] = "image";
            $image['src'] = urldecode($picture);
            $image['href'] = $link;
            $attachment['media'] = array($image);
        }

        $p['attachment'] = stripslashes(json_encode($attachment));
        //'{"name":"Google","href":"http://www.google.com/","description":"Google Home Page"}';
        $action = ($data['publish_action']) ? $data['publish_action'] : $data['action'];
        $action_url = ($data['action_url']) ? $data['action_url'] : $data['actionUrl'];
        if ($action && $action_url) {
            $action_links['text'] = $action;
            $action_links['href'] = $action_url;
            $p['action_links'] =  stripslashes(json_encode(Array($action_links)));
            //'[{ "text": "Reply Via Recmn", "href": "http://www.myvideosite/videopage.html"}]';
        }
        return $p;
    }*/

    function rest_publish($data = null) {
        $data = ($data) ? $data : $_GET;
        $p = $this->createPublishData($data);
        $n = $this->api($p);
        //OAuthUtils::linkSnsPost($n, $data, $this->provider);
        return $n;
    }

    function publish2($data = NULL) {
        $data = ($data) ? $data : $_GET;
        $p = $this->createPublishData($data);
        $n = $this->api($p);
        return $n;
    }

    function getInfo($fields) {
        $p['method'] = "users.getInfo";
        $p['format'] = "json";
        $p['fields'] = $fields;
        $n = $this->api($p);
        return $n;
    }

    function comment($data = NULL) {
        $data = ($data) ? $data : $_GET;
        $p['method'] = "stream.addComment";
        $p['format'] = "json";
        $p['post_id'] = $data['post_id'];//OAuthUtils::getSnsPostId($data['in_reply_to'], $this->provider);
        $p['comment'] = stripslashes($data['comment']);
        if (!$p['post_id']) return "no post id";
        $n = $this->api($p);
        return $n;
    }

    function like($data = NULL) {
        $data = ($data) ? $data : $_GET;
        $p['method'] = "stream.addLike";
        $p['format'] = "json";
        $p['post_id'] = $data['post_id'];//OAuthUtils::getSnsPostId($data['url'], $this->provider);
        if (!$p['post_id']) return "no post id";
        $n = $this->api($p);
        return $n;
    }

    function getUsername() {
        $param['fields']      = "id,link";
        $profile_info         = $this->api('/me',$param);
        $user['uid']         = $profile_info['id'];
        $user['username']    = $this->getNameFromUrl($profile_info['link']);
        return $user;
    }

    /**/
    function curlToFB($params) {
        $url = $params['url'];
        unset($params['url']);
        $this->makeRequest($url,$params);
    }

    function clearAllData() {
        $this->clearAllPersistentData();
    }

    function refreshPersistentData($facebookId,$accessToken) {
        $this->setPersistentData('user_id',$facebookId);
        $this->setPersistentData('access_token',$accessToken);
    }
}
