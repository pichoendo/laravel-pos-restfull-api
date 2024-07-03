<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('salary:generate')->everyTenSeconds();
