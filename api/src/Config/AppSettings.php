<?php
namespace App\Config;

class AppSettings implements IAppSettings
{
    private const API_KEY = ""; //Insert here your API key. If you want an authenticated API, leave blank

    public function GetApiKey(): string
    {
        return self::API_KEY;
    }

    private function __construct(Type $var = null)
    {
        $this->var = $var;
    }

    public static function getInstance(): IAppSettings
    {
        return new AppSettings();
    }
}
