addEventListener( 'load',  _ => {
		if(navigator.geolocation){
			navigator.geolocation.getCurrentPosition( posicion => {
				let lon = posicion.coords.longitude, lat = posicion.coords.latitude;
				let xhr = new XMLHttpRequest()

				xhr.open("get",prestashop.urls.base_url+'index.php?fc=module&module=o_clima_api&controller=ajax&lon='+lon+'&lat='+lat);

				xhr.addEventListener( "load", ev => {
					let response = JSON.parse(ev.target.response);
					let city = response.name,
					country = response.sys.country,
					temp = response.main.temp,
					humidity = response.main.humidity,
					speed = response.wind.speed;

					if(humidity<0){
						humidity = humidity*100;
					}
					document.getElementsByClassName('card-clima')[0].style.display = "flex";
					document.getElementById('clima-temp').innerText = temp + ' ºC';
					document.getElementById('clima-humi').innerText = humidity + ' %';
					document.getElementById('clima-spee').innerText = speed + ' meter/sec';
					document.getElementById('city').innerText = city + ', '+country;

				})

				xhr.send();
				

			});

		}

	});