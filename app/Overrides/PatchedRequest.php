<?php

namespace App\Overrides;

use Illuminate\Http\Request;

class PatchedRequest extends Request
{
    protected string $fixedBaseUrl = '';
    protected string $fixedPathInfo = '';

    public function setBaseUrl(string $baseUrl): void
    {
        $this->fixedBaseUrl = $baseUrl;
    }

    public function setPathInfo(string $pathInfo): void
    {
        $this->fixedPathInfo = $pathInfo;
    }

    public function getBaseUrl(): string
    {
        if ($this->fixedBaseUrl !== '') {
            return $this->fixedBaseUrl;
        }
        return parent::getBaseUrl();
    }

    public function getPathInfo(): string
    {
        if ($this->fixedPathInfo !== '') {
            return $this->fixedPathInfo;
        }
        return parent::getPathInfo();
    }
}
