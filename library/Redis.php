<?


class Redis {
	const SLOW_QUERY_TIME = 0.1;
	
	// Static list of all our redis servers
	private static $hosts = array(
		'prod' => array(
			'master-01' => array('hostname' => '127.0.0.1', 'port' => 6401),
		)
	);
	
	// Holds an entry for each available redis instance once it has been connected to
	private static $instances = array();
	
	// Holds data we use to generate statistics around redis use and performance
	private static $statistics = array();
	
	// Commands that we can currently distribute amongst multiple servers with our sharding scheme
	private static $commands = array("APPEND", "DEBUG OBJECT", "DECR", "DECRBY", "DEL", "EXISTS", "EXPIRE", "EXPIREAT", "GET", "GETBIT", "GETRANGE", "GETSET", "HDEL", "HEXISTS", "HGET", "HGETALL", "HINCRBY", "HKEYS", "HLEN", "HMGET", "HMSET", "HSET", "HSETNX", "HVALS", "INCR", "INCRBY", "LINDEX", "LINSERT", "LLEN", "LPOP", "LPUSH", "LPUSHX", "LRANGE", "LREM", "LSET", "LTRIM", "MOVE", "PERSIST", "RPOP", "RPUSH", "RPUSHX", "SADD", "SCARD", "SET", "SETBIT", "SETEX", "SETNX", "SETRANGE", "SISMEMBER", "SMEMBERS", "SORT", "SPOP", "SRANDMEMBER", "SREM", "STRLEN", "TTL", "TYPE", "ZADD", "ZCARD", "ZCOUNT", "ZINCRBY", "ZRANGE", "ZRANGEBYSCORE", "ZRANK", "ZREM", "ZREMRANGEBYRANK", "ZREMRANGEBYSCORE", "ZREVRANGE", "ZREVRANGEBYSCORE", "ZREVRANK", "ZSCORE");
	
	public static function __callStatic($name, $args){
		if(!in_array(strtoupper($name), self::$commands)) throw new Exception("Command not currently supported");
		
		$key = $args[0];
		$instance = self::getInstance($key);
		$server_name = self::server_name($key);
		
		$start = microtime(true);
		
		$response = call_user_func_array(array($instance, $name), $args);
				
		// Log query time
		self::log_query_time($server_name, microtime(true) - $start);
		
		return $response;
	}
	
	public static function getInstance($key, $master = true){
		require_once 'vendors/redis/iRedis.php';
		
		$serverName = self::server_name($key, $master);
		
		if(!self::$instances[$serverName]){
			$start = microtime(true);
			
			try {
				self::$instances[$serverName] = new iRedis(self::$hosts['prod'][$serverName]);
			} catch (Exception $e){
				die($e->getMessage());
			}
			
			// Log connection time
			self::$statistics[$name][] = microtime(true) - $start;
		}
		
		return self::$instances[self::server_name($key, $master)];
	}
	
	/**********************************************************************************************/
	// - 
	// !Helpers
	// - 
	/**********************************************************************************************/
	
	public static function all_connections(){
		self::connect_all();
		return self::$instances;
	}
	
	private static function connect_all(){
		foreach(self::$hosts['prod'] as $name => $hostname){
			self::$instances[$name] = new iRedis($hostname);
		}
	}
	
	public static function server_name($key, $master = true){
		return ( $master ? 'master' : 'slave' ) . "-" . sprintf("%02d", (substr(base_convert(sha1($key), 16, 10), 0, 3) % count(self::$hosts['prod']) + 1));
	}
	
	/**********************************************************************************************/
	// - 
	// !Statistics
	// - 
	/**********************************************************************************************/
	
	private static function log_query_time($servername, $runtime) {
		// Add this query to our overall stats array
		self::$statistics[$servername][] = $runtime;
	}
	
	public static function num_queries(){
		$total = 0;
		foreach(self::$statistics as $server => $queries){
			$total += count($queries);
		}
		
		return $total;
	}
	
	public static function total_time(){
		$total = 0;
		foreach(self::$statistics as $server => $queries){
			foreach($queries as $time){
				$total += $time;
			}
		}
		
		return round($total, 4);
	}	
}

?>