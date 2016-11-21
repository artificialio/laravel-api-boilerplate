<?php

use Tymon\JWTAuth\Facades\JWTAuth;

function user()
{
    return JWTAuth::parseToken()->toUser();
}