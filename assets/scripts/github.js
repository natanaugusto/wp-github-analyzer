jQuery(document).ready(() => {
	jQuery("#github-search").keyup((el) => {
		let search = el.target.value;
		jQuery.get(`https://api.github.com/search/users?q=${search}`, (data) => {
			console.log(data);
		});
	});
});
