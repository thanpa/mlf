<?php
class Controller_Default extends Controller_Abstract
{
    public function index()
    {
        return $this->getView()->render('index');
    }
}