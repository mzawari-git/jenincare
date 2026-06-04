<?php

namespace Modules\Commerce\Services;

class MetaCapiService
{
    public function sendEvent($eventName, $data) { return true; }
    public function sendPurchaseEvent($order) { return true; }
}