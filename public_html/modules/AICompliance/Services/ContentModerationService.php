<?php

namespace Modules\AICompliance\Services;

class ContentModerationService
{
    public function moderate($content) { return ['passed' => true, 'flags' => []]; }
}