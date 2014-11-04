<?php

class ExtendedTwig extends Twig_Extension {

    private $search  = array('[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[s]', '[/s]',
                             '[sup]', '[/sup]', '[sub]', '[/sub]', '[left]', '[/left]',
                             '[center]', '[/center]', '[right]', '[/right]',
                             '[/size]', '[/color]', '[/font]', "\r\n");
    private $replace = array('<b>', '</b>', '<em>', '</em>', '<u>', '</u>', '<del>', '</del>',
                             '<sup>', '</sup>', '<sub>', '</sub>', '<p style="text-align:left">', '</p>',
                             '<p style="text-align:center">', '</p>', '<p style="text-align:right">', '</p>',
                             '</font>', '</span>', '</span>', '<br>');

    private $searchRx  = array('~\[size=(.*?)\]~s', '~\[color=(.*?)\]~s', '~\[font=(.*?)\]~s',
                               '~\[url=(.*?)\](.*?)\[/url\]~s', '~\[url\](.*?)\[/url\]~s',
                               '~\[img=(.*?)\](.*?)\[/img\]~s', '~\[img\](.*?)\[/img\]~s');
    private $replaceRx = array('<font size="$1">', '<span style="color:$1;">', '<span style="font-family:$1;">',
                               '<a href="$1">$2</a>', '<a href="$1">$1</a>',
                               '<img src="$1" alt="$2">', '<img src="$1" alt="">');

    public function getFilters() {
        return array(
            new Twig_SimpleFilter('bbCode', array($this, 'bbCodeFilter'), array('is_safe' => array('html')))
        );
    }

    public function getFunctions() {
        return array(
            new Twig_SimpleFunction('avatarUrl', array($this, 'avatarUrlFunction'))
        );
    }

    public function bbCodeFilter($str) {
        return preg_replace($this->searchRx, $this->replaceRx, str_replace($this->search, $this->replace, $str));
    }

    public function avatarUrlFunction($type, $hash, $size) {
        switch ($type) {
            case 1:
                return 'http://www.gravatar.com/avatar/'.$hash.'?d=identicon&s='.$size;
            case 2:
                return 'http://graph.facebook.com/'.$hash.'/picture?width='.$size;
            default:
                return Slim\Slim::getInstance()->request()->getRootUri().'/img/usuarios/'.$hash.'/'.$size.'.jpg';
        }
    }

    public function getName() {
        return 'extended_twig';
    }
}
