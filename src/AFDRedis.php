<?php

namespace AFDRedis;

use AFD\AFD;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class AFDRedis extends AFD
{
    protected $expiryTime = 2592000;
    protected $cache;
    
    /**
     * Set the expiry time of the Redis Cache
     * @param int $time The number of seconds until expiry
     * @return $this
     */
    public function setExpiryTime($time)
    {
        if (is_int($time)) {
            $this->expiryTime = $time;
        }
        return $this;
    }
    
    /**
     * Get the number of seconds for the expiry
     * @return type
     */
    public function getExpiryTime()
    {
        return $this->expiryTime;
    }
    
    /**
     * Add a server to connection pool
     * @param string $host This should be the host name or IP address you want to add to the Redis pool
     * @param int $port The port number where Redis can be accessed
     * @param boolean $persistent If you want this connection to be persistent set to true else set to false
     * @return $this
     */
    public function addServer($host, $port = 6379, $persistent = false)
    {
        if ($persistent === false) {
            $this->cache->connect($host, intval($port));
        } else {
            $this->cache->pconnect($host, intval($port));
        }
        return $this;
    }
    
    /**
     * Adds a value to be stored on the server
     * @param string $key This should be the key for the value you wish to add
     * @param mixed $value The value you wish to be stored with the given key
     * @param int $time How long should the value be stored for in seconds (0 = never expire) (max set value = 2592000 (30 Days))
     * @return boolean Returns true if successfully added or false on failure
     */
    public function save($key, $value)
    {
        if (!is_object($value) && !empty($value)) {
            return $this->cache->set($key, json_encode($value), ($this->getExpiryTime() > 0 ? $this->getExpiryTime() : null));
        }
        return false;
    }
    
    
    /**
     * Replaces a stored value for a given key
     * @param string $key This should be the key for the value you wish to replace
     * @param mixed $value The new value that you wish to give to that key
     * @param int $time How long should the value be stored for in seconds (0 = never expire) (max set value = 2592000 (30 Days))
     * @return boolean Returns true if successfully replaced or false on failure
     */
    public function replace($key, $value)
    {
        return $this->save($key, json_encode($value), $this->getExpiryTime());
    }
    
    /**
     * Returns the values store for the given key
     * @param string $key This should be the unique query key to get the value
     * @return mixed The store value will be returned
     */
    public function fetch($key)
    {
        $data = $this->cache->get($key);
        if ($data) {
            return json_decode($data, true);
        }
        return false;
    }
    
    /**
     * Deletes a single value from the server based on the given key
     * @param string $key This should be the key that you wish to delete the value for
     * @return boolean Returns true on success or false on failure
     */
    public function delete($key)
    {
        return (bool)$this->cache->delete($key);
    }
    
    /**
     * Deletes all values from the server
     * @return boolean Returns true on success or false on failure
     */
    public function deleteAll()
    {
        return (bool)$this->cache->flushAll();
    }
    
    /**
     * Gets the information from the URL given in XML format and turns it to an array
     * @param string $url This should be the URL with the given information
     * @return array Returns the results from the URL given in an array format
     */
    protected function getData($url)
    {
        $key = md5($url);
        $cached = $this->fetch($key);
        if ($cached) {
            return simplexml_load_string($cached);
        }
        $client = new Client(['timeout'  => 2.0]);
        try {
            $response = $client->get($url);
            if ($response->getStatusCode() === 200) {
                $body = $response->getBody();
                $this->save($key, $body);
                return simplexml_load_string($body);
            }
        } catch (ConnectException $e) {
            new \Exception($e->getMessage());
        }
        return false;
    }
}
