<?php

namespace App\Models;

/**
 * Backward-compatibility wrapper so any references to BookPurchaseLink keep working.
 * Uses the same table and behavior as BookLink.
 */
class BookPurchaseLink extends BookLink
{
    // No extra code needed; inherits everything from BookLink
}
