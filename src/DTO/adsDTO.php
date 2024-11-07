<?php

namespace App\DTO;

class adsDTO
{

    public $id;
    public $title;
    public $userName;

    public function __construct(int $id, string $title, string $userName)
    {
        $this->id = $id;
        $this->title = $title;
        $this->userName = $userName;
    }
    public function getId(){ return $this->id;}
    public function getTitle(){ return $this->title;}
    public function getUserName(){ return $this->userName;}
}?>
    