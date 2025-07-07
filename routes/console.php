<?php


use Illuminate\Support\Facades\Schedule;

Schedule::command('citas:enviar-recordatorios')->dailyAt('08:00');