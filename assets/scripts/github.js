/**
 * Yes, yes. That's really could be better with React
 * But I'm lazy
 */
jQuery(document).ready(() => {
	let timeout = null;

	/**
	 * Keeps a single timeout instance
	 * @param {callback} callback
	 * @param {integer} ms
	 */
	const singleSetTimeout = (callback, ms = 500) => {
		clearTimeout(timeout);
		timeout = setTimeout(callback, ms);
	};

	/**
	 * Send an Ajax Request
	 *
	 * @param {callback} callback
	 * @param {object} settings
	 */
	const sendRequestSearch = (callback, settings) => {
		settings._ajax_nonce = github_analyzer_ajax.nonce;
		jQuery.post(github_analyzer_ajax.ajax_url, settings, callback);
	};

	/**
	 * GitHub Search
	 */
	jQuery("#github-search").keyup((el) => {
		singleSetTimeout(function () {
			sendRequestSearch(
				(data) => {
					if (data.code !== 200 || data.body.total_count < 1) {
						jQuery(".github-analizer-block .results").hide();
						return;
					}
					jQuery(".github-analizer-block .results").show();
					jQuery(".github-analizer-block .results ul li").remove();
					jQuery(
						".github-analizer-block .results div.header span.total span.value"
					).text(0);
					jQuery(
						".github-analizer-block .results div.header span.total span.value"
					).text(data.body.total_count);
					data.body.items.forEach((item) => {
						jQuery(".github-analizer-block .results ul").append(
							`<li>
								<img src="${item.avatar_url}" />
								${item.login}
							</li>`
						);
					});
				},
				{
					type: "users",
					search: el.target.value,
					action: "github_analyzer_search",
				}
			);
		});
	});
});
