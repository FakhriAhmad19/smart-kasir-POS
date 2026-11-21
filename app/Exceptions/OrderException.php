<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A business-rule violation while ordering or paying (out of stock, invalid
 * modifier, underpayment, bill already closed, ...). Its message is safe to
 * show to the cashier as-is.
 */
class OrderException extends RuntimeException
{
}
