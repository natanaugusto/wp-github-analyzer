/**
 * Yes, yes. That's really could be better with React
 * But I'm lazy
 */
jQuery(document).ready(() => {
	let results;
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
	const sendRequest = (callback, settings) => {
		settings._ajax_nonce = github_analyzer_ajax.nonce;
		jQuery.post(github_analyzer_ajax.ajax_url, settings, callback);
	};

	const addCallback = (data) => {
		console.log(data);
	};

	/**
	 * The callback for the search from GitHub API return
	 * @param {object} data
	 */
	const searchCallback = (data) => {
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
		results = data.body.items;
		results.forEach((item, index) => {
			jQuery(".github-analizer-block .results ul").append(
				`<li key="${index}">
					<img src="${item.avatar_url}" />
					${item.login}
				</li>`
			);
		});
		jQuery(".github-analizer-block .results ul li").click((event) => {
			let key = jQuery(event.target).attr("key");
			sendRequest(addCallback, {
				type: "user",
				data: results[key],
				action: "github_analyzer_add",
			});
		});
	};

	/**
	 * GitHub Search
	 */
	jQuery("#github-search").keyup((event) => {
		singleSetTimeout(function () {
			sendRequest(searchCallback, {
				type: "users",
				search: event.target.value,
				action: "github_analyzer_search",
			});
		});
	});
});
