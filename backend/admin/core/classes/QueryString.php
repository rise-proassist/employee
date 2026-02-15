<?php

/**
 * GET変数クラス
 *
 * @author kanemiya
 */

class QueryString extends RequestVariables {

    protected function setValues() {

        foreach ($_GET as $key => $value) {
    
            $this->_values[$key] = $value;
    
        }

    }

}