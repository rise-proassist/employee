<?php

/**
 * POST変数クラス
 *
 * @author kanemiya
 */

class Post extends RequestVariables {

    protected function setValues() {

        foreach ($_POST as $key => $value) {

            $this->_values[$key] = $value;
        
        }

    }

}