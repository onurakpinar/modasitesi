<?php

namespace App\Support\Deployment;

enum ReadinessStatus: string
{
    case Pass = 'PASS';
    case Warning = 'WARNING';
    case Fail = 'FAIL';
}
