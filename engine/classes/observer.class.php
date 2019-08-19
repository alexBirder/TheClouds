<?php

class Event {
	private $type, $dispatcher;
	private $stopped = false;

	public function setType($type){ $this->type = $type; }
	public function getType(){ return $this->type; }

	public function setDispatcher(TObserver $dispatcher){ $this->dispatcher = $dispatcher; }
	public function getDispatcher(){ return $this->dispatcher; }

	public function stop(){ $this->stopped = true; }
	public function stopped(){ return $this->stopped; }
}

abstract class TObserver {
	private $listeners = array();
	private $sorted = array();

    public function observe($type, $callback, $priority = 0){
        $this->listeners[$type][$priority][] = $callback;
        unset($this->sorted[$type]);
    }

	public function stopObserving($type, $callback){
		if(!array_key_exists($type, $this->listeners)) return;

		foreach($this->listeners[$type] as $priority => $listeners){
			if(($key = array_search($callback, $listeners)) !== false){
				unset($this->listeners[$type][$priority][$key], $this->sorted[$type]);
			}
		}
	}

	public function fire($type, Event $event = null){
		if($event === null) $event = new Event();

		$event->setDispatcher($this); $event->setType($type);

		if(!array_key_exists($type, $this->listeners)) return $event;
		$this->fireEvent($this->getListeners($type), $type, $event);

		return $event;
	}

	public function getListeners($type = null){
		if($type !== null){
			if(!array_key_exists($type, $this->sorted)) $this->sortListeners($type);
			return $this->sorted[$type];
		}

		foreach(array_keys($this->listeners) as $_type){
			if (!array_key_exists($_type, $this->sorted)) $this->sortListeners($_type);
		}

		return $this->sorted;
    }

	public function hasListeners($type = null){
        return sizeof($this->getListeners($type)) > 0;
	}

    protected function fireEvent($listeners, $type, Event $event){
		foreach($listeners as $callback){
			call_user_func($callback, $event);
			if($event->stopped()) break;
		}
	}

	private function sortListeners($type){
		$this->sorted[$type] = array();
		if(array_key_exists($type, $this->listeners)){
			krsort($this->listeners[$type]);
			$this->sorted[$type] = call_user_func_array('array_merge', $this->listeners[$type]);
		}
	}
}

?>