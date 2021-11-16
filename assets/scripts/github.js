jQuery(document).ready(() => {
	let type = null;
	jQuery("#github-search").keyup((el) => {
		clearTimeout(type);
		type = setTimeout(function () {
			let search = el.target.value;
			jQuery.post(
				github_analyzer_ajax.ajax_url,
				{
					_ajax_nonce: github_analyzer_ajax.nonce,
					action: "github_analyzer",
					search,
				},
				(data) => {
					console.log(data);
				}
			);
		}, 600);
	});
});
