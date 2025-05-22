<?php

namespace SemesterApparatus\View\Helper\SemesterApparatus;

class SemesterApparatus extends \Laminas\View\Helper\AbstractHelper
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getUserType() {

    }
}
