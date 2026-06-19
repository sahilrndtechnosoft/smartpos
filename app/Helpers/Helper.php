<?php

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

function hcCurrentTime()
{
    $now = Carbon::now('Asia/Kolkata');
    return $now;
}
function hcAuthUser()
{
    return Auth::user();
}
function hcGetSlug($string)
{
    $slug = Str::slug($string);
    return $slug;
}
function hcNewUuid()
{
    return Str::uuid();
}
function hcGlobalDateFormate($formate = "d F Y h:i:a")
{
    return $formate;
}
function hcNumberToWord($number = 0)
{
    return Number::spell($number);
}

function hcDateFormate($date = '', $formate = "Y-m-d")
{
    return $date != '' ? date($formate, strtotime($date)) : '';
}
