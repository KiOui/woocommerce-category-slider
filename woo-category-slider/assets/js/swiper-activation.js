for (let i = 0; i < swiper_configs.length; i++) {
	let swiper_config = swiper_configs[i];
	new Swiper(
		'#' + swiper_config.id,
		{
			spaceBetween: 20,
			centeredSlides: true,
			slidesPerView: 'auto',
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			freeMode: {
				enabled: true,
				sticky: true,
			},
			mousewheel: {
				enabled: true,
				forceToAxis: true,
			},
		}
	);
}