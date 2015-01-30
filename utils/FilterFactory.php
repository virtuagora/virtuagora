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

    public static function radioToBool() {
        return function($v) {
            return $v == 'on';
        };
    }

    public static function explode($a) {
        return function($v) use ($a) {
            return explode($a, $v);
        };
    }

}
