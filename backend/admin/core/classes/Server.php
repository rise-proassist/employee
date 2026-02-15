<?php

/**
 * SERVER変数クラス
 *
 * @author kanemiya
 */

class Server extends RequestVariables {

    protected function setValues() {

        foreach ($_SERVER as $key => $value) {

            $this->_values[$key] = $value;
        
        }

    }

}