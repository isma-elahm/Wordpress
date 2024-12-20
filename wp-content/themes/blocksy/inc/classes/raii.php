<?php

namespace Blocksy;

class RaiiPattern {
	private $callback = null;

	public function __construct($callback) {
		$this->callback = $callback;
	}

	public function __destruct() {
		if ($this->callback) {
			call_user_func($this->callback);
		}
	}
}

