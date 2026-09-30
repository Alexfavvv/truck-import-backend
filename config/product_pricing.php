<?php

return [
    // The existing --cbr help explicitly assumes EUR -> RUB. No live FX lookup.
    'ready_currency' => env('PRODUCT_READY_CURRENCY', 'EUR'),
    // The website takes orders without promising stock. Restore old API checks if required.
    'enforce_legacy_stock' => env('PRODUCT_ENFORCE_LEGACY_STOCK', false),
];
