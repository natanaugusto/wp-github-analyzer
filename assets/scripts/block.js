/* block.js */
var el = wp.element.createElement;

wp.blocks.registerBlockType("github-analyzer/github-analizer-block", {
	title: "GitHub",
	icon: "editor-code",
	category: "embed",

	attributes: {
		type: { type: "string", default: "default" },
		title: { type: "string" },
		content: { type: "array", source: "children", selector: "p" },
	},

	edit: (props) => {
		function updatePlaceholder(newdata) {
			props.setAttributes({ input_search_placeholder: newdata.target.value });
		}
		return el(
			"div",
			{
				class: "github-analizer-block",
			},
			el("label", null, "GitHub Search:"),
			el("input", {
				id: "github-search",
				name: "github-search",
				type: "text",
				placeholder:
					props.attributes.hasOwnProperty("input_search_placeholder") &&
					props.attributes.input_search_placeholder !== null
						? props.attributes.input_search_placeholder
						: "GitHub Search...",
				onChange: updatePlaceholder,
			})
		);
	},
	save: (props) => {
		return el(
			"div",
			{
				class: "github-analizer-block",
			},
			el("label", null, "GitHub Search:"),
			el("input", {
				id: "github-search",
				name: "github-search",
				type: "text",
				placeholder:
					props.attributes.hasOwnProperty("input_search_placeholder") &&
					props.attributes.input_search_placeholder !== null
						? props.attributes.input_search_placeholder
						: "GitHub Search...",
			})
		);
	},
});
