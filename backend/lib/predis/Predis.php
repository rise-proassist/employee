<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/src/PredisAutoloader.php';

class Predis {
	
	static function get_connection() {

		Predis\PredisAutoloader::register();

		$host = RedisConst::$redis_setting['redis_host'];
		$port = RedisConst::$redis_setting['redis_port'];

		return new Predis\Client("tcp://{$host}:{$port}");

	}


}