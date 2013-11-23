<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * We provide simplified access to Memcached, particularly in key management.
 * If the client-provided key is not 32-char long, we automatically compute a key
 * based on the MD5 signature of the given key's string representation, as follows:
 *
 *	$computedKey = md5(print_r($suppliedGroup, $suppliedKey, true));
 *
 * $suppliedGroup is designed to help uniquely distinguish between different cache uses,
 * even when the $suppliedKey may be, e.g., the same URL, latlong, etc.
 */
class simple_cache_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    private $memcached = null;
    private $tempCache = array(); // this cache lasts for just one request, and is designed to minimize hits to memcached itself

    public function get($group, $key) {
        try {
            $theKey = $this->computeKey($group, $key);

            $value = isset($this->tempCache[$theKey]) ? $this->tempCache[$theKey] : NULL;
            if ($value) return $value;

            $value = $this->getMemcached()->get($theKey);
            $this->tempCache[$theKey] = $value;
            return $value;
        } catch (Exception $e) {
            log_message('error', $e);
            return null;
        }
    }

    public function put($group, $key, $value, $expireMinutes = 0) {
        try {
            if (is_object($value) && get_class($value) == "SimpleXMLElement") return; //Serialization SimpleXMLElement not allowed
            $theKey = $this->computeKey($group, $key);
            $expire = ($expireMinutes > 0 ? time() + $expireMinutes*60 : 0);
            $this->getMemcached()->set($theKey, $value, $expire);
            $this->tempCache[$theKey] = $value;
        } catch (Exception $e) {
            log_message('error', $e);
        }
    }

    public function delete($group, $key) {
        try {
            $theKey = $this->computeKey($group, $key);
            $this->getMemcached()->delete($theKey);
            $this->tempCache[$theKey] = null;
        } catch (Exception $e) {
            log_message('error', $e);
        }
    }

    private function getMemcached() {
        if ($this->memcached == null) {
            if (!class_exists('Memcached')) throw new Exception('Memcached not installed. Still works, but gonna be slower. Do aptitude install memcached php5-memcached.');
            $this->memcached = new Memcached();
            foreach ($this->config->item('memcachedServers') as $server) {
                $this->memcached->addServer($server, 11211);
            }
        }

        return $this->memcached;
    }

    private function computeKey($group, $key) {
        return md5($this->toString($group) . $this->toString($key));
    }

    private function toString($key) {
        if ($key) return is_string($key) ? $key : print_r($key, true);
        else return 'null';
    }
}
