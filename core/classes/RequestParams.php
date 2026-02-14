<?php

/**
 * REQUEST変数クラス
 *
 * @author kanemiya
 */

class RequestParams extends RequestVariables {

    protected function setValues() {

        foreach ($_REQUEST as $key => $value) {
    
            $this->_values[$key] = $value;
    
        }

    }

}