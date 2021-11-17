/**
 * Yes, yes. That's really could be better with React
 * But I'm lazy
 */
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
					for: "users",
					search,
				},
				(data) => {
					jQuery(".github-analizer-block .results").show();
					jQuery(
						".github-analizer-block .results ul div.header span.total span.value"
					).val(data.body.total_count);
					jQuery(".github-analizer-block .results ul li").remove();
					data.body.items.forEach((item) => {
						jQuery(".github-analizer-block .results ul").append(
							`<li>${item.login}</li>`
						);
					});
				}
			);
		}, 600);
	});
});
