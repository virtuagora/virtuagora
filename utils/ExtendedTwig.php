<?php

class ExtendedTwig extends Twig_Extension {

    private $search  = array('[i]', '[/i]', '[s]', '[/s]', '[hr]',
                             '[/size]', '[/color]', '[/font]');
    private $replace = array('<em>', '</em>', '<del>', '</del>', '<hr>',
                             '</font>', '</span>', '</span>');

    private $searchRx  = array('~\[(/?)(b|u|sup|sub)\]~s',
                               '~\[(left|right|center|justify)\]~s',
                               '~\[/(?:left|right|center|justify)\](?:\R?)~s',
                               '~\[(/?)(ul|ol|li|table|tr|td)\](?:\R?)~s',
                               '~\[size=(.*?)\]~s', '~\[color=(.*?)\]~s', '~\[font=(.*?)\]~s',
                               '~\[url=(.*?)\](.*?)\[/url\]~s', '~\[url\](.*?)\[/url\]~s',
                               '~\[img=(.*?)\](.*?)\[/img\]~s', '~\[img\](.*?)\[/img\]~s',
                               '~\[youtube\](.*?)\[/youtube\]~s',
                               '~\R~s');
    private $replaceRx = array('<$1$2>',
                               '<p style="text-align:$1">',
                               '</p>',
                               '<$1$2>',
                               '<font size="$1">', '<span style="color:$1;">', '<span style="font-family:$1;">',
                               '<a href="$1">$2</a>', '<a href="$1">$1</a>',
                               '<img src="$2" alt="$1">', '<img src="$1" alt="">',
                               '<iframe width="560" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
                               '<br>');

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
                return 'https://www.gravatar.com/avatar/'.$hash.'?d=identicon&s='.$size;
            case 2:
                return 'http://graph.facebook.com/'.$hash.'/picture?width='.$size;
            default:
                return Slim\Slim::getInstance()->request()->getRootUri().'/img/usuario/'.$hash.'/'.$size.'.png';
        }
    }

    public function getName() {
        return 'extended_twig';
    }
}
