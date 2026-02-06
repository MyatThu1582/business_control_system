<?php
function hasPermission($permission)
{
    if (!isset($_SESSION['permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['permissions']);
}
