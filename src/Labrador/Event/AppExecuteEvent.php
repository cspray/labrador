<?php

/**
 * An event triggered once when your application should execute its
 * processing logic and deliver a result to the user.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;

class AppExecuteEvent extends Event {

    public function __construct() {
        parent::__construct(Engine::APP_EXECUTE_EVENT);
    }

}
