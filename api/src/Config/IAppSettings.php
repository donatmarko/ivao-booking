<?php
namespace App\Config;

interface IAppSettings {
    public function GetApiKey() : string;
}