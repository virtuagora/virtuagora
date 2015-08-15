<?php

class FilterFactory {

    public static function calcHuella($exec = false) {
        $f = function($v) {
            return preg_replace('/[^[:alnum:]]/ui', '', $v);
        };
        return ($exec === false) ? $f : call_user_func($f, $exec);
    }

    public static function normalizeWhitespace($exec = false) {
        $f = function($v) {
            return trim(preg_replace('/[[:blank:]]+/', ' ', $v));
        };
        return ($exec === false) ? $f : call_user_func($f, $exec);
    }

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

    public static function booleanFilter() {
        return function($v) {
            return filter_var($v, FILTER_VALIDATE_BOOLEAN);
        };
    }

    public static function explode($a) {
        return function($v) use ($a) {
            return ($v === '')? array(): explode($a, $v);
        };
    }

    public static function json_decode() {
        return function($v) {
            return json_decode($v, true);
        };
    }

}
