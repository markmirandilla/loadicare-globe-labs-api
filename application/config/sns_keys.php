<?php

/*Common SNS configs*/
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL;
//Facebook
$facebook['dialog_url'] = 'https://www.facebook.com/dialog/oauth';
$facebook['token_url'] = 'https://graph.facebook.com/oauth/access_token';
$facebook['graph_url'] = 'https://graph.facebook.com';
$facebook['snsCallBackUrl'] = "https://".$host."/sns/oauthcallback?snstype=facebook";
$facebook['scope'] = 'email,user_location,publish_actions,publish_stream,user_birthday';

/*End Common configs*/

/*SNS API KEYS*/
//localhost config

//Production Config
$production = array('facebook' => array('appID'=>'1409137845989910',
                                        'apiSecretKey'=>'96cc2fadf5057501e484a5540f32efd5',
                                        'dialog_url'=> $facebook['dialog_url'],
                                        'token_url'=> $facebook['token_url'],
                                        'graph_url'=> $facebook['graph_url'],
                                        'snsCallBackUrl' => $facebook['snsCallBackUrl'],
                                        'scope' => $facebook['scope'])
                   );
$config['snsKeys'] = $production;
$config['fbPerms'] = array('friends_location','friends_hometown','friends_location','friends_hometown','read_stream','publish_stream','publish_actions');
