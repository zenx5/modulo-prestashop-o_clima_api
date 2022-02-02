<div class="card-clima" style="display:flex;">
	<div id="clima-temp">{$result->main->temp} {$units['temp']}</div>
	<div class="clima-col-1">
		<div>Humedad <span id="clima-humi">{$result->main->humidity} %</span></div>
		<div>Viento <span id="clima-spee">{$result->wind->speed} {$units['speed']}</span></div>
	</div>
	<div class="clima-col-2">
		<div id="city">{$result->name}, {$result->sys->country}</div>
	</div>
</div>
