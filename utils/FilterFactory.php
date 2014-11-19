<?php

class FilterFactory {

    public static function emptyToNull() {
        return function($v) {
            return ($v==='') ? null : $v;
        };
    }

    public static function escapeHTML() {
        return function($v) {
            return htmlspecialchars($v, ENT_QUOTES);
        };
    }

}
