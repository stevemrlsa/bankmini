<?php

namespace App\Http\Controllers\Admin;

/**
 * Legacy UserController extending AdminEmployeeController for full backward compatibility.
 */
class UserController extends AdminEmployeeController
{
    // Inherits index(), store(), update(), destroy(), toggleStatus() from AdminEmployeeController
}
